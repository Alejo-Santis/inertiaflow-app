<script lang="ts">
  import Layout from '../Layout.svelte';
  import { Link, router } from '@inertiajs/svelte';
  import route from 'ziggy-js';

  export let project: any;
  export let tasks: any[];

  const today = new Date().toISOString().split('T')[0];

  const columns: { id: string; label: string; color: string; headerBg: string; dot: string }[] = [
    { id: 'todo',        label: 'Por hacer',   color: 'border-slate-300',  headerBg: 'bg-slate-100 text-slate-600',    dot: 'bg-slate-400' },
    { id: 'in_progress', label: 'En progreso', color: 'border-blue-300',   headerBg: 'bg-blue-50 text-blue-700',      dot: 'bg-blue-500' },
    { id: 'in_review',   label: 'En revisión', color: 'border-violet-300', headerBg: 'bg-violet-50 text-violet-700',  dot: 'bg-violet-500' },
    { id: 'done',        label: 'Hecho',       color: 'border-emerald-300',headerBg: 'bg-emerald-50 text-emerald-700',dot: 'bg-emerald-500' },
    { id: 'cancelled',   label: 'Cancelado',   color: 'border-rose-300',   headerBg: 'bg-rose-50 text-rose-600',      dot: 'bg-rose-400' },
  ];

  const priorityConfig: Record<number, { icon: string; color: string }> = {
    1: { icon: '↓', color: 'text-slate-400' },
    2: { icon: '→', color: 'text-blue-500' },
    3: { icon: '↑', color: 'text-amber-500' },
    4: { icon: '⚑', color: 'text-rose-500' },
  };

  // Group tasks by status — reactive so drag-drop updates reflect immediately
  $: grouped = Object.fromEntries(
    columns.map(col => [col.id, tasks.filter(t => t.status === col.id)])
  );

  // Drag state
  let draggingTaskId: number | null = null;
  let draggingOverCol: string | null = null;

  function onDragStart(event: DragEvent, task: any) {
    draggingTaskId = task.id;
    event.dataTransfer!.effectAllowed = 'move';
    event.dataTransfer!.setData('text/plain', String(task.id));
  }

  function onDragOver(event: DragEvent, colId: string) {
    event.preventDefault();
    event.dataTransfer!.dropEffect = 'move';
    draggingOverCol = colId;
  }

  function onDragLeave() {
    draggingOverCol = null;
  }

  function onDrop(event: DragEvent, newStatus: string) {
    event.preventDefault();
    draggingOverCol = null;
    const id = draggingTaskId;
    draggingTaskId = null;
    if (!id) return;

    const task = tasks.find(t => t.id === id);
    if (!task || task.status === newStatus) return;

    // Optimistic update
    tasks = tasks.map(t => t.id === id ? { ...t, status: newStatus } : t);

    router.patch(
      route('projects.tasks.updateStatus', [project.uuid, task.uuid]),
      { status: newStatus },
      { preserveScroll: true, onError: () => {
        // Revert on error
        tasks = tasks.map(t => t.id === id ? { ...t, status: task.status } : t);
      }}
    );
  }

  function onDragEnd() {
    draggingTaskId  = null;
    draggingOverCol = null;
  }

  function isOverdue(task: any): boolean {
    return !!task.due_date
      && task.due_date < today
      && task.status !== 'done'
      && task.status !== 'cancelled';
  }

  const avatarColors = [
    'bg-indigo-500', 'bg-violet-500', 'bg-emerald-500', 'bg-amber-500',
    'bg-rose-500',   'bg-cyan-500',   'bg-pink-500',    'bg-teal-500',
  ];
  function avatarColor(id: number): string { return avatarColors[id % avatarColors.length]; }
  function getInitials(name: string): string { return name.split(' ').map((n: string) => n[0]).join('').toUpperCase().slice(0, 2); }
</script>

<Layout title="Kanban — {project.name}">

  <!-- Breadcrumb -->
  <nav class="mb-6 flex items-center gap-2 text-sm text-slate-500">
    <Link href={route('projects.index')} class="hover:text-slate-700">Proyectos</Link>
    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
    </svg>
    <Link href={route('projects.show', project.uuid)} class="hover:text-slate-700">{project.name}</Link>
    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
    </svg>
    <span class="font-medium text-slate-900">Kanban</span>
  </nav>

  <!-- Header -->
  <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div class="flex items-center gap-3">
      <div
        class="flex h-10 w-10 items-center justify-center rounded-xl text-white font-bold text-sm shadow-sm"
        style="background-color: {project.color ?? '#6366f1'};"
      >
        {project.name.charAt(0).toUpperCase()}
      </div>
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">{project.name}</h1>
        <p class="text-sm text-slate-500">Tablero Kanban · {tasks.length} tareas</p>
      </div>
    </div>
    <div class="flex items-center gap-2">
      <Link
        href={route('projects.tasks.index', project.uuid)}
        class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
        </svg>
        Lista
      </Link>
      <Link
        href={route('projects.tasks.create', project.uuid)}
        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:from-indigo-700 hover:to-violet-700 hover:shadow-md"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Nueva tarea
      </Link>
    </div>
  </div>

  <!-- Kanban board -->
  <div class="flex gap-4 overflow-x-auto pb-4" style="min-height: 600px;">
    {#each columns as col}
      {@const colTasks = grouped[col.id] ?? []}
      <div
        class="flex flex-col rounded-2xl border-2 transition-colors duration-150
          {draggingOverCol === col.id
            ? 'border-indigo-400 bg-indigo-50/50'
            : col.color + ' bg-slate-50/50'}"
        style="min-width: 260px; width: 260px;"
        ondragover={(e) => onDragOver(e, col.id)}
        ondragleave={onDragLeave}
        ondrop={(e) => onDrop(e, col.id)}
        role="region"
        aria-label={col.label}
      >
        <!-- Column header -->
        <div class="flex items-center gap-2 rounded-t-xl px-4 py-3 {col.headerBg}">
          <span class="h-2 w-2 rounded-full {col.dot}"></span>
          <span class="flex-1 text-sm font-semibold">{col.label}</span>
          <span class="rounded-full bg-white/70 px-2 py-0.5 text-xs font-bold text-slate-600">{colTasks.length}</span>
        </div>

        <!-- Tasks -->
        <div class="flex-1 space-y-2.5 p-3">
          {#each colTasks as task (task.id)}
            {@const prio = priorityConfig[task.priority] ?? { icon: '-', color: 'text-slate-400' }}
            {@const overdue = isOverdue(task)}
            <div
              draggable="true"
              ondragstart={(e) => onDragStart(e, task)}
              ondragend={onDragEnd}
              class="group cursor-grab rounded-xl border bg-white p-3.5 shadow-sm transition
                active:cursor-grabbing active:shadow-lg active:scale-[1.02]
                {draggingTaskId === task.id ? 'opacity-40' : ''}
                {overdue ? 'border-rose-200' : 'border-slate-200 hover:border-slate-300 hover:shadow-md'}"
            >
              <!-- Priority + overdue -->
              <div class="mb-2 flex items-start justify-between gap-2">
                <span class="text-base font-bold leading-none {prio.color}">{prio.icon}</span>
                {#if overdue}
                  <span class="inline-flex items-center gap-0.5 rounded-full bg-rose-100 px-1.5 py-0.5 text-[10px] font-semibold text-rose-700">
                    <svg class="h-2.5 w-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                    Vencida
                  </span>
                {/if}
              </div>

              <!-- Title -->
              <Link
                href={route('projects.tasks.show', [project.uuid, task.uuid])}
                class="block text-sm font-semibold leading-snug text-slate-800 hover:text-indigo-600 transition
                  {task.status === 'done' ? 'line-through text-slate-400' : ''}"
              >
                {task.title}
              </Link>

              <!-- Due date -->
              {#if task.due_date}
                <p class="mt-1.5 text-xs {overdue ? 'text-rose-600 font-semibold' : 'text-slate-400'}">
                  {task.due_date}
                </p>
              {/if}

              <!-- Assignees -->
              {#if task.assignees?.length}
                <div class="mt-3 flex -space-x-1.5">
                  {#each task.assignees.slice(0, 4) as assignee}
                    <div
                      title={assignee.name}
                      class="flex h-6 w-6 items-center justify-center rounded-full text-[9px] font-bold text-white ring-2 ring-white {avatarColor(assignee.id)}"
                    >
                      {getInitials(assignee.name)}
                    </div>
                  {/each}
                  {#if task.assignees.length > 4}
                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-200 text-[9px] font-bold text-slate-600 ring-2 ring-white">
                      +{task.assignees.length - 4}
                    </div>
                  {/if}
                </div>
              {/if}
            </div>
          {/each}

          <!-- Drop hint -->
          {#if draggingTaskId !== null && draggingOverCol === col.id && colTasks.every(t => t.id !== draggingTaskId)}
            <div class="rounded-xl border-2 border-dashed border-indigo-300 bg-indigo-50/50 py-4 text-center text-xs text-indigo-400 font-medium">
              Soltar aquí
            </div>
          {/if}

          <!-- Empty state -->
          {#if colTasks.length === 0 && draggingOverCol !== col.id}
            <div class="rounded-xl border-2 border-dashed border-slate-200 py-6 text-center text-xs text-slate-400">
              Sin tareas
            </div>
          {/if}
        </div>
      </div>
    {/each}
  </div>

</Layout>
