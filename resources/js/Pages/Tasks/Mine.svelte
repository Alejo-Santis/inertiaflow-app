<script lang="ts">
  import { untrack } from 'svelte';
  import Layout from '../Layout.svelte';
  import { Link, router } from '@inertiajs/svelte';
  import route from 'ziggy-js';
  import Swal from 'sweetalert2';

  let {
    projectTasks,
    personalTasks,
    filters,
    isAdmin = false,
    canCreatePersonal = false,
  }: {
    projectTasks: any;
    personalTasks: any[];
    filters: { status?: string; priority?: string };
    isAdmin?: boolean;
    canCreatePersonal?: boolean;
  } = $props();

  // ─── Tab state (persisted in URL) ────────────────────────────────────────────
  const urlParams  = new URLSearchParams(window.location.search);
  let activeTab    = $state<'project' | 'personal'>(
    (urlParams.get('tab') as 'project' | 'personal') ?? 'project'
  );

  function switchTab(tab: 'project' | 'personal') {
    activeTab = tab;
    router.get(route('my-tasks'), { tab }, { preserveState: true, replace: true });
  }

  // ─── Project tasks filters ────────────────────────────────────────────────────
  let status   = $state(untrack(() => filters.status   ?? ''));
  let priority = $state(untrack(() => filters.priority ?? ''));

  function applyFilters() {
    router.get(route('my-tasks'), { tab: 'project', status, priority }, { preserveState: true, replace: true });
  }

  // ─── Status / Priority configs ────────────────────────────────────────────────
  const statusConfig: Record<string, { label: string; color: string; dot: string }> = {
    todo:        { label: 'Por hacer',   color: 'bg-slate-100 text-slate-600 ring-1 ring-slate-200',     dot: 'bg-slate-400' },
    in_progress: { label: 'En progreso', color: 'bg-blue-50 text-blue-700 ring-1 ring-blue-200',         dot: 'bg-blue-500' },
    in_review:   { label: 'En revisión', color: 'bg-violet-50 text-violet-700 ring-1 ring-violet-200',   dot: 'bg-violet-500' },
    done:        { label: 'Completada',  color: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200', dot: 'bg-emerald-500' },
    cancelled:   { label: 'Cancelada',   color: 'bg-rose-50 text-rose-500 ring-1 ring-rose-200',         dot: 'bg-rose-400' },
  };

  const priorityConfig: Record<number, { label: string; color: string }> = {
    1: { label: 'Baja',    color: 'text-slate-500' },
    2: { label: 'Media',   color: 'text-amber-600' },
    3: { label: 'Alta',    color: 'text-rose-600' },
    4: { label: 'Urgente', color: 'text-rose-700 font-bold' },
  };

  const statusOptions = [
    { value: 'todo',        label: 'Por hacer' },
    { value: 'in_progress', label: 'En progreso' },
    { value: 'in_review',   label: 'En revisión' },
    { value: 'done',        label: 'Completada' },
    { value: 'cancelled',   label: 'Cancelada' },
  ];

  function localDateStr(): string {
    const d = new Date();
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
  }

  function isOverdue(due: string | null, dueTime: string | null, taskStatus: string): boolean {
    if (!due || taskStatus === 'done' || taskStatus === 'cancelled') return false;
    const today = localDateStr();
    if (due < today) return true;                  // fecha pasada → siempre vencida
    if (due > today) return false;                 // fecha futura → nunca vencida
    // due === today: vencida solo si la hora ya pasó
    if (!dueTime) return false;                    // sin hora → no vencida hoy
    const now     = new Date();
    const nowTime = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
    return dueTime <= nowTime;
  }

  function formatDue(dateStr: string, timeStr: string | null): string {
    const today    = localDateStr();
    const d        = new Date();
    d.setDate(d.getDate() + 1);
    const tomorrow = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    let label = dateStr === today    ? 'Hoy'
              : dateStr === tomorrow ? 'Mañana'
              : new Date(dateStr + 'T00:00:00').toLocaleDateString('es', { day: 'numeric', month: 'short' });
    if (timeStr) label += ` ${timeStr.slice(0, 5)}`;
    return label;
  }

  // ─── Personal tasks — crear ───────────────────────────────────────────────────
  let newTitle    = $state('');
  let newPriority = $state(2);
  let newDueDate  = $state('');
  let newDueTime  = $state('');
  let submitting  = $state(false);

  // ─── Modal de edición ─────────────────────────────────────────────────────────
  let editTask     = $state<any>(null);   // tarea en edición, null = modal cerrado
  let editTitle    = $state('');
  let editPriority = $state(2);
  let editDueDate  = $state('');
  let editDueTime  = $state('');
  let editStatus   = $state('todo');
  let saving       = $state(false);

  function openEdit(task: any) {
    editTask     = task;
    editTitle    = task.title;
    editPriority = task.priority;
    editDueDate  = task.due_date  ?? '';
    editDueTime  = task.due_time  ? task.due_time.slice(0, 5) : '';
    editStatus   = task.status;
  }

  function closeEdit() {
    editTask = null;
  }

  function focusInput(node: HTMLElement) {
    node.focus();
  }

  function saveEdit(e: Event) {
    e.preventDefault();
    if (!editTitle.trim() || saving) return;
    saving = true;
    router.patch(
      route('personal-tasks.update', editTask.uuid),
      {
        title:    editTitle.trim(),
        priority: editPriority,
        due_date: editDueDate || null,
        due_time: editDueTime || null,
        status:   editStatus,
      },
      {
        preserveScroll: true,
        onSuccess: () => closeEdit(),
        onFinish:  () => { saving = false; },
      }
    );
  }

  function createPersonalTask(e: Event) {
    e.preventDefault();
    if (!newTitle.trim() || submitting) return;
    submitting = true;
    router.post(
      route('personal-tasks.store'),
      {
        title:    newTitle.trim(),
        priority: newPriority,
        due_date: newDueDate || null,
        due_time: newDueTime || null,
      },
      {
        preserveScroll: true,
        onFinish: () => {
          submitting  = false;
          newTitle    = '';
          newPriority = 2;
          newDueDate  = '';
          newDueTime  = '';
        },
      }
    );
  }

  function updateStatus(task: any, newStatus: string) {
    router.patch(
      route('personal-tasks.updateStatus', task.uuid),
      { status: newStatus },
      { preserveScroll: true }
    );
  }

  async function deletePersonalTask(task: any) {
    const result = await Swal.fire({
      title: '¿Eliminar tarea?',
      text: `"${task.title}" será eliminada permanentemente.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#64748b',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
    });
    if (!result.isConfirmed) return;
    router.delete(route('personal-tasks.destroy', task.uuid), { preserveScroll: true });
  }

  // Personal tasks split by done/active for better UX
  let activeTasks = $derived(personalTasks.filter((t: any) => t.status !== 'done' && t.status !== 'cancelled'));
  let doneTasks   = $derived(personalTasks.filter((t: any) => t.status === 'done' || t.status === 'cancelled'));
</script>

<Layout title={isAdmin ? 'Tareas' : 'Mis tareas'}>

  <!-- Header -->
  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-slate-900">{isAdmin ? 'Todas las tareas' : 'Mis tareas'}</h1>
      <p class="mt-1 text-sm text-slate-500">{isAdmin ? 'Tareas del sistema y personales' : 'Tareas asignadas a ti y personales'}</p>
    </div>
  </div>

  <!-- Tabs -->
  <div class="mb-6 flex border-b border-slate-200">
    <button
      onclick={() => switchTab('project')}
      class="relative px-5 py-3 text-sm font-medium transition-colors {activeTab === 'project'
        ? 'text-indigo-700 after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-indigo-600'
        : 'text-slate-500 hover:text-slate-700'}"
    >
      De proyectos
      <span class="ml-2 rounded-full px-2 py-0.5 text-xs font-semibold {activeTab === 'project' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600'}">
        {projectTasks.total}
      </span>
    </button>
    <button
      onclick={() => switchTab('personal')}
      class="relative px-5 py-3 text-sm font-medium transition-colors {activeTab === 'personal'
        ? 'text-indigo-700 after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-indigo-600'
        : 'text-slate-500 hover:text-slate-700'}"
    >
      Personales
      <span class="ml-2 rounded-full px-2 py-0.5 text-xs font-semibold {activeTab === 'personal' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600'}">
        {personalTasks.length}
      </span>
    </button>
  </div>

  <!-- ═══════════════════ TAB: DE PROYECTOS ═══════════════════ -->
  {#if activeTab === 'project'}

    <!-- Filters -->
    <div class="mb-5 flex flex-wrap items-center gap-3">
      <select bind:value={status} onchange={applyFilters}
        class="rounded-xl border border-slate-300 bg-white py-2 px-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0">
        <option value="">Todos los estados</option>
        {#each statusOptions as opt}
          <option value={opt.value}>{opt.label}</option>
        {/each}
      </select>
      <select bind:value={priority} onchange={applyFilters}
        class="rounded-xl border border-slate-300 bg-white py-2 px-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0">
        <option value="">Todas las prioridades</option>
        <option value="1">Baja</option>
        <option value="2">Media</option>
        <option value="3">Alta</option>
        <option value="4">Urgente</option>
      </select>
      {#if status || priority}
        <button onclick={() => { status = ''; priority = ''; applyFilters(); }}
          class="text-sm text-slate-500 hover:text-slate-700 underline underline-offset-2">
          Limpiar filtros
        </button>
      {/if}
    </div>

    {#if projectTasks.data.length === 0}
      <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white py-20 text-center">
        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100">
          <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
        </div>
        <p class="mt-4 font-medium text-slate-700">Sin tareas asignadas</p>
        <p class="mt-1 text-sm text-slate-500">Cuando te asignen una tarea en un proyecto aparecerá aquí</p>
      </div>
    {:else}
      <div class="space-y-3">
        {#each projectTasks.data as task}
          {@const st    = statusConfig[task.status] ?? { label: task.status, color: 'bg-slate-100 text-slate-600 ring-1 ring-slate-200', dot: 'bg-slate-400' }}
          {@const pri   = priorityConfig[task.priority] ?? { label: String(task.priority), color: 'text-slate-500' }}
          {@const overdue = isOverdue(task.due_date, null, task.status)}
          <Link
            href={route('projects.tasks.show', [task.project?.uuid, task.uuid])}
            class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md hover:-translate-y-0.5 group"
          >
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-white text-sm font-bold shadow-sm"
                 style="background-color: {task.project?.color ?? '#6366f1'};">
              {task.project?.name?.charAt(0).toUpperCase() ?? '?'}
            </div>
            <div class="min-w-0 flex-1">
              <p class="truncate font-semibold text-slate-900 group-hover:text-indigo-700 transition-colors">{task.title}</p>
              <p class="mt-0.5 truncate text-xs text-slate-500">{task.project?.name ?? '—'}</p>
            </div>
            <div class="flex shrink-0 items-center gap-3">
              {#if isAdmin && task.assignees?.length}
                <div class="hidden items-center gap-1 sm:flex">
                  {#each task.assignees.slice(0, 2) as assignee}
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{assignee.name.split(' ')[0]}</span>
                  {/each}
                  {#if task.assignees.length > 2}
                    <span class="text-xs text-slate-400">+{task.assignees.length - 2}</span>
                  {/if}
                </div>
              {/if}
              <span class="hidden text-xs font-medium {pri.color} sm:block">{pri.label}</span>
              <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium {st.color}">
                <span class="h-1.5 w-1.5 rounded-full {st.dot}"></span>
                {st.label}
              </span>
              {#if task.due_date}
                <span class="hidden whitespace-nowrap text-xs {overdue ? 'text-rose-600 font-semibold' : 'text-slate-500'} sm:block">
                  {overdue ? '⚠ ' : ''}{formatDue(task.due_date, null)}
                </span>
              {/if}
            </div>
          </Link>
        {/each}
      </div>

      {#if projectTasks.last_page > 1}
        <div class="mt-6 flex items-center justify-center gap-2">
          {#each projectTasks.links as link}
            {#if link.url}
              <Link href={link.url} class="rounded-lg px-3 py-1.5 text-sm {link.active ? 'bg-indigo-600 text-white font-semibold' : 'bg-white border border-slate-300 text-slate-700 hover:bg-slate-50'}">
                {@html link.label}
              </Link>
            {:else}
              <span class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm text-slate-400">{@html link.label}</span>
            {/if}
          {/each}
        </div>
      {/if}
    {/if}

  <!-- ═══════════════════ TAB: PERSONALES ═══════════════════ -->
  {:else}

    <!-- Quick-create form -->
    {#if canCreatePersonal}
      <form onsubmit={createPersonalTask} class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="mb-3 text-sm font-semibold text-slate-700">Nueva tarea personal</p>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
          <div class="flex-1">
            <input
              type="text"
              bind:value={newTitle}
              placeholder="¿Qué necesitas hacer?"
              class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
              required
            />
          </div>
          <div class="flex flex-wrap items-center gap-2">
            <select
              bind:value={newPriority}
              class="rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
            >
              <option value={1}>↓ Baja</option>
              <option value={2}>→ Media</option>
              <option value={3}>↑ Alta</option>
              <option value={4}>⚑ Urgente</option>
            </select>
            <input
              type="date"
              bind:value={newDueDate}
              class="rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
            />
            <input
              type="time"
              bind:value={newDueTime}
              disabled={!newDueDate}
              title={!newDueDate ? 'Selecciona primero una fecha' : 'Hora de vencimiento'}
              class="rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0 disabled:opacity-40 disabled:cursor-not-allowed"
            />
            <button
              type="submit"
              disabled={submitting || !newTitle.trim()}
              class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {#if submitting}
                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
              {:else}
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
              {/if}
              Agregar
            </button>
          </div>
        </div>
      </form>
    {/if}

    <!-- Active personal tasks -->
    {#if activeTasks.length === 0 && doneTasks.length === 0}
      <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white py-20 text-center">
        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50">
          <svg class="h-8 w-8 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75" />
          </svg>
        </div>
        <p class="mt-4 font-medium text-slate-700">Sin tareas personales</p>
        <p class="mt-1 text-sm text-slate-500">Usa el formulario de arriba para agregar tu primera tarea</p>
      </div>
    {:else}

      <!-- Active tasks -->
      {#if activeTasks.length > 0}
        <div class="space-y-2">
          {#each activeTasks as task}
            {@const st      = statusConfig[task.status] ?? { label: task.status, color: 'bg-slate-100 text-slate-600 ring-1 ring-slate-200', dot: 'bg-slate-400' }}
            {@const pri     = priorityConfig[task.priority] ?? { label: String(task.priority), color: 'text-slate-500' }}
            {@const overdue = isOverdue(task.due_date, task.due_time, task.status)}
            <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
              <!-- Status toggle -->
              <select
                value={task.status}
                onchange={(e) => updateStatus(task, (e.target as HTMLSelectElement).value)}
                class="shrink-0 rounded-lg border-0 bg-transparent py-0 text-xs font-medium focus:outline-none focus:ring-0 cursor-pointer {st.color.includes('bg-') ? '' : ''}"
              >
                {#each statusOptions as opt}
                  <option value={opt.value}>{opt.label}</option>
                {/each}
              </select>

              <!-- Title — click para editar -->
              <button
                onclick={() => openEdit(task)}
                class="min-w-0 flex-1 truncate text-left text-sm font-medium text-slate-800 hover:text-indigo-700 transition-colors"
              >
                {task.title}
              </button>

              <!-- Meta -->
              <div class="flex shrink-0 items-center gap-1">
                <span class="hidden text-xs {pri.color} sm:block">{pri.label}</span>
                {#if task.due_date}
                  <span class="hidden whitespace-nowrap text-xs {overdue ? 'text-rose-600 font-semibold' : 'text-slate-400'} sm:block">
                    {overdue ? '⚠ ' : ''}{formatDue(task.due_date, task.due_time)}
                  </span>
                {/if}
                <!-- Edit -->
                <button
                  onclick={() => openEdit(task)}
                  class="rounded-lg p-1.5 text-slate-400 transition hover:bg-indigo-50 hover:text-indigo-600"
                  title="Editar"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                  </svg>
                </button>
                <!-- Delete -->
                <button
                  onclick={() => deletePersonalTask(task)}
                  class="rounded-lg p-1.5 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600"
                  title="Eliminar"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                  </svg>
                </button>
              </div>
            </div>
          {/each}
        </div>
      {/if}

      <!-- Done / cancelled tasks (collapsed section) -->
      {#if doneTasks.length > 0}
        <details class="mt-4 group">
          <summary class="flex cursor-pointer items-center gap-2 rounded-xl px-2 py-2 text-sm font-medium text-slate-500 hover:text-slate-700 select-none list-none">
            <svg class="h-4 w-4 transition-transform group-open:rotate-90" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
            Completadas y canceladas ({doneTasks.length})
          </summary>
          <div class="mt-2 space-y-2">
            {#each doneTasks as task}
              {@const st  = statusConfig[task.status] ?? { label: task.status, color: 'bg-slate-100 text-slate-500 ring-1 ring-slate-200', dot: 'bg-slate-300' }}
              <div class="flex items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 opacity-70">
                <select
                  value={task.status}
                  onchange={(e) => updateStatus(task, (e.target as HTMLSelectElement).value)}
                  class="shrink-0 rounded-lg border-0 bg-transparent py-0 text-xs font-medium focus:outline-none focus:ring-0 cursor-pointer"
                >
                  {#each statusOptions as opt}
                    <option value={opt.value}>{opt.label}</option>
                  {/each}
                </select>
                <p class="min-w-0 flex-1 truncate text-sm text-slate-500 line-through">{task.title}</p>
                <div class="flex shrink-0 items-center gap-2">
                  <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium {st.color}">
                    <span class="h-1.5 w-1.5 rounded-full {st.dot}"></span>
                    {st.label}
                  </span>
                  <button
                    onclick={() => deletePersonalTask(task)}
                    class="ml-1 rounded-lg p-1.5 text-slate-300 transition hover:bg-rose-50 hover:text-rose-600"
                    title="Eliminar"
                  >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                  </button>
                </div>
              </div>
            {/each}
          </div>
        </details>
      {/if}

    {/if}
  {/if}

</Layout>

<!-- ═══════════════════ MODAL DE EDICIÓN ═══════════════════ -->
{#if editTask}
  <!-- Overlay -->
  <div
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 backdrop-blur-sm"
    onclick={(e) => { if (e.target === e.currentTarget) closeEdit(); }}
    onkeydown={(e) => { if (e.key === 'Escape') closeEdit(); }}
    role="dialog"
    aria-modal="true"
    aria-labelledby="edit-modal-title"
    tabindex="-1"
  >
    <div class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">

      <!-- Header -->
      <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
        <h2 id="edit-modal-title" class="text-sm font-semibold text-slate-900">Editar tarea personal</h2>
        <button
          onclick={closeEdit}
          aria-label="Cerrar modal"
          class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Form -->
      <form onsubmit={saveEdit} class="px-6 py-5 space-y-4">

        <!-- Título -->
        <div>
          <label class="mb-1.5 block text-xs font-medium text-slate-600" for="edit-title">Título</label>
          <input
            id="edit-title"
            type="text"
            bind:value={editTitle}
            required
            use:focusInput
            class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
          />
        </div>

        <!-- Estado + Prioridad -->
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="mb-1.5 block text-xs font-medium text-slate-600" for="edit-status">Estado</label>
            <select
              id="edit-status"
              bind:value={editStatus}
              class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
            >
              {#each statusOptions as opt}
                <option value={opt.value}>{opt.label}</option>
              {/each}
            </select>
          </div>
          <div>
            <label class="mb-1.5 block text-xs font-medium text-slate-600" for="edit-priority">Prioridad</label>
            <select
              id="edit-priority"
              bind:value={editPriority}
              class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
            >
              <option value={1}>↓ Baja</option>
              <option value={2}>→ Media</option>
              <option value={3}>↑ Alta</option>
              <option value={4}>⚑ Urgente</option>
            </select>
          </div>
        </div>

        <!-- Fecha + Hora -->
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="mb-1.5 block text-xs font-medium text-slate-600" for="edit-date">Fecha límite</label>
            <input
              id="edit-date"
              type="date"
              bind:value={editDueDate}
              class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
            />
          </div>
          <div>
            <label class="mb-1.5 block text-xs font-medium text-slate-600" for="edit-time">Hora</label>
            <input
              id="edit-time"
              type="time"
              bind:value={editDueTime}
              disabled={!editDueDate}
              class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0 disabled:opacity-40 disabled:cursor-not-allowed"
            />
          </div>
        </div>

        <!-- Botones -->
        <div class="flex justify-end gap-2 pt-1">
          <button
            type="button"
            onclick={closeEdit}
            class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
          >
            Cancelar
          </button>
          <button
            type="submit"
            disabled={saving || !editTitle.trim()}
            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {#if saving}
              <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
              </svg>
            {/if}
            Guardar cambios
          </button>
        </div>
      </form>

    </div>
  </div>
{/if}
