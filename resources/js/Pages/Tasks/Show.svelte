<script lang="ts">
  import Layout from '../Layout.svelte';
  import { Link, useForm, router, usePage } from '@inertiajs/svelte';
  import route from 'ziggy-js';

  let { project, task, members = [], logged_hours = 0, attachments = [], labels = [] }: {
    project: any; task: any; members?: any[]; logged_hours?: number; attachments?: any[]; labels?: { id: number; name: string; color: string }[];
  } = $props();

  const page = usePage();
  let auth = $derived($page.props.auth as any);

  const today = new Date().toISOString().split('T')[0];

  const statusConfig: Record<string, { label: string; color: string; dot: string }> = {
    todo:        { label: 'Por hacer',   color: 'bg-slate-100 text-slate-600 ring-1 ring-slate-200',      dot: 'bg-slate-400' },
    in_progress: { label: 'En progreso', color: 'bg-blue-50 text-blue-700 ring-1 ring-blue-200',          dot: 'bg-blue-500' },
    in_review:   { label: 'En revisión', color: 'bg-violet-50 text-violet-700 ring-1 ring-violet-200',    dot: 'bg-violet-500' },
    done:        { label: 'Hecho',       color: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200', dot: 'bg-emerald-500' },
    cancelled:   { label: 'Cancelado',   color: 'bg-rose-50 text-rose-600 ring-1 ring-rose-200',          dot: 'bg-rose-400' },
  };

  const priorityConfig: Record<number, { label: string; color: string; icon: string }> = {
    1: { label: 'Baja',    color: 'text-slate-500 bg-slate-100',  icon: '↓' },
    2: { label: 'Media',   color: 'text-blue-600 bg-blue-50',    icon: '→' },
    3: { label: 'Alta',    color: 'text-amber-600 bg-amber-50',  icon: '↑' },
    4: { label: 'Urgente', color: 'text-rose-600 bg-rose-50',    icon: '⚑' },
  };

  let status   = $derived(statusConfig[task.status]   ?? { label: task.status,   color: 'bg-slate-100 text-slate-600', dot: 'bg-slate-400' });
  let priority = $derived(priorityConfig[task.priority] ?? { label: `P${task.priority}`, color: 'text-slate-600 bg-slate-100', icon: '-' });
  let overdue  = $derived(!!task.due_date && task.due_date < today && task.status !== 'done' && task.status !== 'cancelled');

  // Status change
  const statusOptions = [
    { value: 'todo',        label: 'Por hacer' },
    { value: 'in_progress', label: 'En progreso' },
    { value: 'in_review',   label: 'En revisión' },
    { value: 'done',        label: 'Hecho' },
    { value: 'cancelled',   label: 'Cancelado' },
  ];

  let updatingStatus = $state(false);
  function changeStatus(newStatus: string) {
    if (newStatus === task.status || updatingStatus) return;
    updatingStatus = true;
    router.patch(
      route('projects.tasks.updateStatus', [project.uuid, task.uuid]),
      { status: newStatus },
      { preserveScroll: true, onFinish: () => { updatingStatus = false; } }
    );
  }

  // Time log form
  const timeForm = useForm({
    hours:       '',
    description: '',
    logged_date: today,
  });
  function submitTimeLog() {
    $timeForm.post(route('projects.tasks.time-logs.store', [project.uuid, task.uuid]), {
      preserveScroll: true,
      onSuccess: () => $timeForm.reset('hours', 'description'),
    });
  }
  function deleteTimeLog(id: number) {
    router.delete(route('projects.tasks.time-logs.destroy', [project.uuid, task.uuid, id]), {
      preserveScroll: true,
    });
  }

  // Attachment upload
  const attachForm = useForm({ file: null as File | null });
  function uploadAttachment(e: Event) {
    const input = e.target as HTMLInputElement;
    if (!input.files?.length) return;
    $attachForm.file = input.files[0];
    $attachForm.post(route('projects.tasks.attachments.store', [project.uuid, task.uuid]), {
      preserveScroll: true,
      onSuccess: () => { $attachForm.reset(); input.value = ''; },
      forceFormData: true,
    });
  }
  function deleteAttachment(id: number) {
    router.delete(route('projects.tasks.attachments.destroy', [project.uuid, task.uuid, id]), { preserveScroll: true });
  }

  function fileIcon(mime: string | null): string {
    if (!mime) return '📎';
    if (mime.startsWith('image/')) return '🖼';
    if (mime === 'application/pdf') return '📄';
    if (mime.includes('word') || mime.includes('document')) return '📝';
    if (mime.includes('sheet') || mime.includes('excel')) return '📊';
    if (mime.includes('zip') || mime.includes('rar')) return '📦';
    return '📎';
  }

  // @mention highlight
  function highlightMentions(text: string): string {
    return text.replace(/@([\w.\-]+)/g, '<span class="font-semibold text-indigo-600">@$1</span>');
  }

  // Comment form
  const commentForm = useForm({ body: '' });
  function submitComment() {
    if (!$commentForm.body.trim()) return;
    mentionActive = false;
    $commentForm.post(route('projects.tasks.comments.store', [project.uuid, task.uuid]), {
      preserveScroll: true,
      onSuccess: () => $commentForm.reset(),
    });
  }

  function deleteComment(commentId: number) {
    router.delete(route('projects.tasks.comments.destroy', [project.uuid, task.uuid, commentId]), {
      preserveScroll: true,
    });
  }

  // @mention autocomplete
  let mentionActive  = $state(false);
  let mentionQuery   = $state('');
  let mentionStart   = $state(0);
  let mentionIndex   = $state(0);
  let commentTextarea: HTMLTextAreaElement;
  let mentionSuggestions = $derived(mentionQuery === ''
    ? members.slice(0, 6)
    : members.filter(m => m.name.toLowerCase().includes(mentionQuery.toLowerCase())).slice(0, 6));

  function onCommentInput(e: Event) {
    const ta    = e.target as HTMLTextAreaElement;
    const pos   = ta.selectionStart ?? 0;
    const before = ta.value.slice(0, pos);
    const match  = before.match(/@(\w*)$/);
    if (match) {
      mentionQuery  = match[1];
      mentionStart  = pos - match[0].length;
      mentionActive = true;
      mentionIndex  = 0;
    } else {
      mentionActive = false;
    }
  }

  function onCommentKeydown(e: KeyboardEvent) {
    if (!mentionActive) return;
    if (e.key === 'ArrowDown') { e.preventDefault(); mentionIndex = Math.min(mentionIndex + 1, mentionSuggestions.length - 1); }
    if (e.key === 'ArrowUp')   { e.preventDefault(); mentionIndex = Math.max(mentionIndex - 1, 0); }
    if (e.key === 'Enter' || e.key === 'Tab') {
      if (mentionSuggestions[mentionIndex]) { e.preventDefault(); insertMention(mentionSuggestions[mentionIndex]); }
    }
    if (e.key === 'Escape') { mentionActive = false; }
  }

  function insertMention(member: any) {
    const ta     = commentTextarea;
    const pos    = ta.selectionStart ?? 0;
    const value  = $commentForm.body;
    const slug   = member.name.replace(/\s+/g, '');
    const insert = '@' + slug + ' ';
    $commentForm.body = value.slice(0, mentionStart) + insert + value.slice(pos);
    mentionActive = false;
    setTimeout(() => {
      ta.focus();
      const cur = mentionStart + insert.length;
      ta.setSelectionRange(cur, cur);
    }, 0);
  }

  // Avatar helpers
  const avatarColors = [
    'bg-indigo-500', 'bg-violet-500', 'bg-emerald-500', 'bg-amber-500',
    'bg-rose-500',   'bg-cyan-500',   'bg-pink-500',    'bg-teal-500',
  ];
  function avatarColor(id: number): string { return avatarColors[id % avatarColors.length]; }
  function getInitials(name: string): string { return name.split(' ').map((n: string) => n[0]).join('').toUpperCase().slice(0, 2); }

  function formatDate(dateStr: string): string {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' });
  }
</script>

<Layout title={task.title}>

  <!-- Breadcrumb -->
  <nav class="mb-6 flex items-center gap-2 text-sm text-slate-500 flex-wrap">
    <Link href={route('projects.index')} class="hover:text-slate-700">Proyectos</Link>
    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
    </svg>
    <Link href={route('projects.show', project.uuid)} class="hover:text-slate-700">{project.name}</Link>
    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
    </svg>
    <Link href={route('projects.tasks.index', project.uuid)} class="hover:text-slate-700">Tareas</Link>
    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
    </svg>
    <span class="font-medium text-slate-900 truncate max-w-[200px]">{task.title}</span>
  </nav>

  <div class="flex flex-col gap-6 lg:flex-row lg:items-start">

    <!-- Main content -->
    <div class="flex-1 min-w-0 space-y-6">

      <!-- Task header card -->
      <div class="rounded-2xl border bg-white p-6 shadow-sm {overdue ? 'border-rose-200' : 'border-slate-200'}">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-3">
              <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium {status.color}">
                <span class="h-1.5 w-1.5 rounded-full {status.dot}"></span>
                {status.label}
              </span>
              <span class="inline-flex items-center gap-1 rounded-lg px-2 py-0.5 text-xs font-bold {priority.color}">
                {priority.icon} {priority.label}
              </span>
              {#if overdue}
                <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-700 ring-1 ring-rose-200">
                  <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>
                  </svg>
                  Vencida
                </span>
              {/if}
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 {task.status === 'done' ? 'line-through text-slate-500' : ''}">{task.title}</h1>
            {#if task.labels?.length}
              <div class="mt-2 flex flex-wrap gap-1.5">
                {#each task.labels as label}
                  <span
                    class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-semibold text-white"
                    style="background-color: {label.color};"
                  >
                    {label.name}
                  </span>
                {/each}
              </div>
            {/if}
            {#if task.description}
              <p class="mt-3 whitespace-pre-wrap text-sm leading-relaxed text-slate-600">{task.description}</p>
            {/if}
          </div>
        </div>

        <!-- Meta row -->
        <div class="mt-5 flex flex-wrap gap-4 border-t border-slate-100 pt-4 text-sm text-slate-500">
          {#if task.due_date}
            <div class="flex items-center gap-1.5">
              <svg class="h-4 w-4 {overdue ? 'text-rose-500' : 'text-slate-400'}" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
              </svg>
              <span class="{overdue ? 'font-semibold text-rose-600' : ''}">
                Vence: {formatDate(task.due_date)}
              </span>
            </div>
          {/if}
          {#if task.estimated_hours}
            <div class="flex items-center gap-1.5">
              <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
              </svg>
              {task.estimated_hours}h estimadas
            </div>
          {/if}
          {#if task.creator}
            <div class="flex items-center gap-1.5">
              <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
              </svg>
              Creada por {task.creator.name}
            </div>
          {/if}
        </div>

        <!-- Change status -->
        <div class="mt-4 border-t border-slate-100 pt-4">
          <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">Cambiar estado</p>
          <div class="flex flex-wrap gap-2">
            {#each statusOptions as opt}
              {@const cfg = statusConfig[opt.value]}
              <button
                type="button"
                disabled={updatingStatus}
                onclick={() => changeStatus(opt.value)}
                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium transition
                  {task.status === opt.value
                    ? cfg.color + ' ring-2 ring-offset-1 ring-indigo-400'
                    : 'bg-slate-100 text-slate-500 hover:bg-slate-200 disabled:opacity-50'}"
              >
                <span class="h-1.5 w-1.5 rounded-full {cfg.dot}"></span>
                {opt.label}
              </button>
            {/each}
          </div>
        </div>
      </div>

      <!-- Comments -->
      <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-5 flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-slate-500">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
          </svg>
          Comentarios ({task.comments?.length ?? 0})
        </h2>

        <!-- Comment list -->
        {#if task.comments?.length}
          <div class="space-y-4 mb-6">
            {#each task.comments as comment}
              <div class="flex gap-3 group">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold text-white {avatarColor(comment.user.id)}">
                  {getInitials(comment.user.name)}
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-baseline justify-between gap-2">
                    <span class="text-sm font-semibold text-slate-800">{comment.user.name}</span>
                    <span class="text-xs text-slate-400 whitespace-nowrap">
                      {new Date(comment.created_at).toLocaleDateString('es-ES', { day: 'numeric', month: 'short' })}
                    </span>
                  </div>
                  <p class="mt-1 text-sm text-slate-600 whitespace-pre-wrap">{@html highlightMentions(comment.body)}</p>
                </div>
                {#if auth?.user?.id === comment.user_id || auth?.isAdmin}
                  <button
                    type="button"
                    onclick={() => deleteComment(comment.id)}
                    title="Eliminar comentario"
                    class="shrink-0 opacity-0 group-hover:opacity-100 transition rounded-lg p-1 text-slate-400 hover:text-rose-500 hover:bg-rose-50"
                  >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                  </button>
                {/if}
              </div>
            {/each}
          </div>
        {:else}
          <p class="mb-5 text-sm text-slate-400 italic">Aún no hay comentarios en esta tarea.</p>
        {/if}

        <!-- New comment form -->
        <form onsubmit={(e) => { e.preventDefault(); submitComment(); }} class="flex gap-3">
          {#if auth?.user}
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold text-white {avatarColor(auth.user.id)}">
              {getInitials(auth.user.name)}
            </div>
          {/if}
          <div class="relative flex-1">
            <!-- @mention dropdown -->
            {#if mentionActive && mentionSuggestions.length > 0}
              <div class="absolute bottom-full left-0 z-40 mb-1 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg">
                <p class="border-b border-slate-100 px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Mencionar</p>
                {#each mentionSuggestions as member, i}
                  <button
                    type="button"
                    onclick={() => insertMention(member)}
                    class="flex w-full items-center gap-2.5 px-3 py-2 text-left text-sm transition {mentionIndex === i ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50'}"
                  >
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-[10px] font-bold text-indigo-700">
                      {member.name.charAt(0).toUpperCase()}
                    </div>
                    <span class="truncate font-medium">{member.name}</span>
                    <span class="ml-auto text-[10px] text-slate-400">@{member.name.replace(/\s+/g, '')}</span>
                  </button>
                {/each}
              </div>
            {/if}

            <textarea
              bind:this={commentTextarea}
              bind:value={$commentForm.body}
              oninput={onCommentInput}
              onkeydown={onCommentKeydown}
              placeholder="Escribe un comentario... usa @ para mencionar"
              rows="2"
              class="block w-full resize-none rounded-xl border border-slate-300 bg-white py-2.5 px-3.5 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0
                {$commentForm.errors?.body ? 'border-rose-300 bg-rose-50' : ''}"
            ></textarea>
            {#if $commentForm.errors?.body}
              <p class="mt-1 text-xs text-rose-600">{$commentForm.errors.body}</p>
            {/if}
            <div class="mt-2 flex justify-end">
              <button
                type="submit"
                disabled={$commentForm.processing || !$commentForm.body.trim()}
                class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-50"
              >
                {#if $commentForm.processing}
                  <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                {:else}
                  <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>
                {/if}
                Comentar
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="w-full lg:w-72 space-y-4 shrink-0">

      <!-- Labels sidebar card -->
      {#if labels.length}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <div class="mb-3 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-700">Etiquetas</h3>
            <Link
              href={route('projects.tasks.edit', [project.uuid, task.uuid])}
              class="text-xs font-medium text-indigo-600 hover:text-indigo-700"
            >Editar</Link>
          </div>
          <div class="flex flex-wrap gap-1.5">
            {#each labels as label}
              {@const active = task.labels?.some((l: any) => l.id === label.id)}
              <span
                class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium transition
                  {active ? 'text-white' : 'bg-slate-100 text-slate-400'}"
                style={active ? `background-color: ${label.color};` : ''}
              >
                <span class="h-1.5 w-1.5 rounded-full" style="background-color: {active ? 'rgba(255,255,255,0.5)' : label.color};"></span>
                {label.name}
              </span>
            {/each}
          </div>
        </div>
      {/if}

      <!-- Time tracking card -->
      <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="mb-1 text-sm font-semibold text-slate-700">Registro de tiempo</h3>

        <!-- Estimated vs logged -->
        <div class="mb-4 mt-2 grid grid-cols-2 gap-2 text-center">
          <div class="rounded-xl bg-slate-50 border border-slate-100 py-2">
            <p class="text-lg font-bold text-slate-800">{logged_hours}h</p>
            <p class="text-[10px] text-slate-500 uppercase tracking-wide">registradas</p>
          </div>
          <div class="rounded-xl bg-slate-50 border border-slate-100 py-2">
            <p class="text-lg font-bold {task.estimated_hours && logged_hours > task.estimated_hours ? 'text-rose-600' : 'text-slate-800'}">
              {task.estimated_hours ?? '—'}h
            </p>
            <p class="text-[10px] text-slate-500 uppercase tracking-wide">estimadas</p>
          </div>
        </div>

        {#if task.estimated_hours && task.estimated_hours > 0}
          {@const pct = Math.min(Math.round((logged_hours / task.estimated_hours) * 100), 100)}
          <div class="mb-4">
            <div class="mb-1 flex justify-between text-xs text-slate-500">
              <span>Progreso de tiempo</span>
              <span class="font-semibold {logged_hours > task.estimated_hours ? 'text-rose-600' : 'text-slate-700'}">{pct}%</span>
            </div>
            <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
              <div
                class="h-full rounded-full transition-all {logged_hours > task.estimated_hours ? 'bg-rose-500' : 'bg-emerald-500'}"
                style="width: {pct}%;"
              ></div>
            </div>
            {#if logged_hours > task.estimated_hours}
              <p class="mt-1 text-xs text-rose-600 font-medium">+{(logged_hours - task.estimated_hours).toFixed(1)}h sobre estimado</p>
            {/if}
          </div>
        {/if}

        <!-- Log entries -->
        {#if task.timeLogs?.length}
          <div class="mb-3 space-y-2 max-h-40 overflow-y-auto">
            {#each task.timeLogs as log}
              <div class="group flex items-start gap-2 rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-[9px] font-bold text-white {avatarColor(log.user.id)}">
                  {getInitials(log.user.name)}
                </div>
                <div class="min-w-0 flex-1">
                  <div class="flex items-baseline gap-1">
                    <span class="text-xs font-bold text-indigo-600">{log.hours}h</span>
                    <span class="truncate text-[10px] text-slate-500">{log.logged_date}</span>
                  </div>
                  {#if log.description}
                    <p class="text-[10px] text-slate-500 truncate">{log.description}</p>
                  {/if}
                </div>
                {#if auth?.user?.id === log.user_id || auth?.isAdmin}
                  <button
                    type="button"
                    onclick={() => deleteTimeLog(log.id)}
                    class="shrink-0 opacity-0 group-hover:opacity-100 transition rounded p-0.5 text-slate-400 hover:text-rose-500"
                  >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                  </button>
                {/if}
              </div>
            {/each}
          </div>
        {/if}

        <!-- Add time form -->
        <form onsubmit={(e) => { e.preventDefault(); submitTimeLog(); }} class="space-y-2">
          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="mb-1 block text-[10px] font-medium uppercase tracking-wide text-slate-500">Horas</label>
              <input
                type="number" min="0.25" max="24" step="0.25"
                bind:value={$timeForm.hours}
                placeholder="0.0"
                class="block w-full rounded-lg border border-slate-300 bg-white py-1.5 px-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500
                  {$timeForm.errors?.hours ? 'border-rose-300' : ''}"
                required
              />
            </div>
            <div>
              <label class="mb-1 block text-[10px] font-medium uppercase tracking-wide text-slate-500">Fecha</label>
              <input
                type="date"
                bind:value={$timeForm.logged_date}
                class="block w-full rounded-lg border border-slate-300 bg-white py-1.5 px-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                required
              />
            </div>
          </div>
          <input
            type="text"
            bind:value={$timeForm.description}
            placeholder="Descripción opcional…"
            class="block w-full rounded-lg border border-slate-300 bg-white py-1.5 px-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
          />
          <button
            type="submit"
            disabled={$timeForm.processing}
            class="w-full rounded-lg bg-indigo-600 py-1.5 text-xs font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-50"
          >
            {$timeForm.processing ? 'Guardando…' : '+ Registrar tiempo'}
          </button>
        </form>
      </div>

      <!-- Attachments card -->
      <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="mb-3 text-sm font-semibold text-slate-700">
          Adjuntos
          {#if attachments.length > 0}
            <span class="ml-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-slate-100 text-[10px] font-bold text-slate-600">{attachments.length}</span>
          {/if}
        </h3>

        {#if attachments.length > 0}
          <div class="mb-3 space-y-1.5">
            {#each attachments as att}
              <div class="group flex items-center gap-2 rounded-lg border border-slate-100 bg-slate-50 px-3 py-2">
                <span class="text-base">{fileIcon(att.mime_type)}</span>
                <div class="min-w-0 flex-1">
                  <a href={att.url} target="_blank" rel="noopener" class="block truncate text-xs font-medium text-indigo-700 hover:underline">{att.original_name}</a>
                  <p class="text-[10px] text-slate-400">{att.formatted_size} · {att.user?.name ?? '—'}</p>
                </div>
                <button
                  onclick={() => deleteAttachment(att.id)}
                  class="hidden shrink-0 rounded-md p-1 text-slate-400 hover:bg-rose-50 hover:text-rose-600 group-hover:block"
                  title="Eliminar"
                >
                  <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            {/each}
          </div>
        {/if}

        <!-- Upload -->
        <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-dashed border-slate-200 px-4 py-3 text-sm text-slate-500 transition hover:border-indigo-300 hover:text-indigo-600">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
          </svg>
          {$attachForm.processing ? 'Subiendo...' : 'Adjuntar archivo'}
          <input type="file" class="sr-only" onchange={uploadAttachment} disabled={$attachForm.processing} />
        </label>
        {#if $attachForm.errors.file}
          <p class="mt-1 text-xs text-rose-600">{$attachForm.errors.file}</p>
        {/if}
      </div>

      <!-- Assignees card -->
      <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="mb-3 text-sm font-semibold text-slate-700">Asignados</h3>
        {#if task.assignees?.length}
          <div class="space-y-2.5">
            {#each task.assignees as assignee}
              <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold text-white {avatarColor(assignee.id)}">
                  {getInitials(assignee.name)}
                </div>
                <span class="text-sm text-slate-700 truncate">{assignee.name}</span>
              </div>
            {/each}
          </div>
        {:else}
          <p class="text-sm text-slate-400 italic">Sin asignados</p>
        {/if}
      </div>

      <!-- Project info card -->
      <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="mb-3 text-sm font-semibold text-slate-700">Proyecto</h3>
        <Link
          href={route('projects.show', project.uuid)}
          class="flex items-center gap-3 rounded-xl p-2 -mx-2 transition hover:bg-slate-50"
        >
          <div
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-white text-xs font-bold"
            style="background-color: {project.color ?? '#6366f1'};"
          >
            {project.name.charAt(0).toUpperCase()}
          </div>
          <span class="text-sm font-medium text-slate-700 truncate">{project.name}</span>
        </Link>
      </div>

      <!-- Meeting link -->
      {#if task.meeting_url}
        <div class="rounded-2xl border border-indigo-100 bg-indigo-50 p-4 shadow-sm">
          <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-indigo-500">Reunión vinculada</p>
          <a
            href={task.meeting_url}
            target="_blank"
            rel="noopener"
            class="flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700"
          >
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
            Unirse a la reunión
          </a>
        </div>
      {/if}

      <!-- Quick links -->
      <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="mb-3 text-sm font-semibold text-slate-700">Acciones</h3>
        <div class="space-y-1.5">
          <Link
            href={route('projects.tasks.edit', [project.uuid, task.uuid])}
            class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm text-slate-600 transition hover:bg-indigo-50 hover:text-indigo-700"
          >
            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
            </svg>
            Editar tarea
          </Link>
          <Link
            href={route('projects.tasks.index', project.uuid)}
            class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm text-slate-600 transition hover:bg-slate-50 hover:text-slate-900"
          >
            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
            </svg>
            Lista de tareas
          </Link>
          <Link
            href={route('projects.tasks.kanban', project.uuid)}
            class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm text-slate-600 transition hover:bg-slate-50 hover:text-slate-900"
          >
            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
            </svg>
            Tablero Kanban
          </Link>
        </div>
      </div>
    </div>

  </div>
</Layout>
