<script lang="ts">
  import Layout from '../Layout.svelte';
  import { Link, router } from '@inertiajs/svelte';
  import route from 'ziggy-js';

  export let project: any;
  export let tasks: any;
  export let filters: any = {};

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

  const statusOptions = [
    { value: '',           label: 'Todos los estados' },
    { value: 'todo',        label: 'Por hacer' },
    { value: 'in_progress', label: 'En progreso' },
    { value: 'in_review',   label: 'En revisión' },
    { value: 'done',        label: 'Hecho' },
    { value: 'cancelled',   label: 'Cancelado' },
  ];

  const priorityOptions = [
    { value: '',  label: 'Todas las prioridades' },
    { value: '1', label: '↓ Baja' },
    { value: '2', label: '→ Media' },
    { value: '3', label: '↑ Alta' },
    { value: '4', label: '⚑ Urgente' },
  ];

  let selectedStatus   = filters?.status   ?? '';
  let selectedPriority = filters?.priority ?? '';

  function applyFilters() {
    const params: Record<string, string> = {};
    if (selectedStatus)   params.status   = selectedStatus;
    if (selectedPriority) params.priority = selectedPriority;
    router.get(route('projects.tasks.index', project.uuid), params, { preserveState: true });
  }

  function clearFilters() {
    selectedStatus   = '';
    selectedPriority = '';
    router.get(route('projects.tasks.index', project.uuid), {}, { preserveState: false });
  }

  function getStatus(status: string) {
    return statusConfig[status] ?? { label: status, color: 'bg-slate-100 text-slate-600', dot: 'bg-slate-400' };
  }

  function getPriority(priority: number) {
    return priorityConfig[priority] ?? { label: `P${priority}`, color: 'text-slate-600 bg-slate-100', icon: '-' };
  }

  function isOverdue(task: any): boolean {
    return !!task.due_date
      && task.due_date < today
      && task.status !== 'done'
      && task.status !== 'cancelled';
  }

  function getInitials(name: string): string {
    return name.split(' ').map((n: string) => n[0]).join('').toUpperCase().slice(0, 2);
  }

  const avatarColors = [
    'bg-indigo-500', 'bg-violet-500', 'bg-emerald-500', 'bg-amber-500',
    'bg-rose-500',   'bg-cyan-500',   'bg-pink-500',    'bg-teal-500',
  ];

  function avatarColor(id: number): string {
    return avatarColors[id % avatarColors.length];
  }

  $: hasFilters = !!filters?.status || !!filters?.priority;
</script>

<Layout title="Tareas">

  <!-- Breadcrumb -->
  <nav class="mb-6 flex items-center gap-2 text-sm text-slate-500">
    <Link href={route('projects.index')} class="hover:text-slate-700">Proyectos</Link>
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
    </svg>
    <Link href={route('projects.show', project.uuid)} class="hover:text-slate-700">{project.name}</Link>
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
    </svg>
    <span class="font-medium text-slate-900">Tareas</span>
  </nav>

  <!-- Page header -->
  <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex items-center gap-3">
      <div
        class="flex h-10 w-10 items-center justify-center rounded-xl text-white font-bold text-sm shadow-sm"
        style="background-color: {project.color ?? '#6366f1'};"
      >
        {project.name.charAt(0).toUpperCase()}
      </div>
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">{project.name}</h1>
        <p class="text-sm text-slate-500">
          {tasks?.total ?? 0} tarea{(tasks?.total ?? 0) !== 1 ? 's' : ''}
          {#if hasFilters}<span class="text-indigo-600 font-medium">(filtrado)</span>{/if}
        </p>
      </div>
    </div>

    <div class="flex items-center gap-2 self-start sm:self-auto">
      <Link
        href={route('projects.tasks.kanban', project.uuid)}
        class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
        </svg>
        Kanban
      </Link>
      <a
        href={route('projects.tasks.export', project.uuid)}
        class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50"
        title="Exportar a CSV"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
        </svg>
        CSV
      </a>
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

  <!-- Filters bar -->
  <div class="mb-6 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="flex-1 min-w-[160px]">
      <label class="mb-1.5 block text-xs font-medium text-slate-500 uppercase tracking-wide">Estado</label>
      <select
        bind:value={selectedStatus}
        onchange={applyFilters}
        class="block w-full rounded-xl border border-slate-300 bg-white py-2 px-3 text-sm text-slate-700 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
      >
        {#each statusOptions as opt}
          <option value={opt.value}>{opt.label}</option>
        {/each}
      </select>
    </div>

    <div class="flex-1 min-w-[160px]">
      <label class="mb-1.5 block text-xs font-medium text-slate-500 uppercase tracking-wide">Prioridad</label>
      <select
        bind:value={selectedPriority}
        onchange={applyFilters}
        class="block w-full rounded-xl border border-slate-300 bg-white py-2 px-3 text-sm text-slate-700 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
      >
        {#each priorityOptions as opt}
          <option value={opt.value}>{opt.label}</option>
        {/each}
      </select>
    </div>

    {#if hasFilters}
      <button
        type="button"
        onclick={clearFilters}
        class="flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-medium text-rose-600 transition hover:bg-rose-100"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
        </svg>
        Limpiar filtros
      </button>
    {/if}
  </div>

  {#if tasks?.data?.length}
    <div class="space-y-2">
      {#each tasks.data as task}
        {@const status = getStatus(task.status)}
        {@const priority = getPriority(task.priority)}
        {@const overdue = isOverdue(task)}
        <article class="group flex items-start gap-4 rounded-2xl border bg-white px-5 py-4 shadow-sm transition hover:shadow-md
          {overdue ? 'border-rose-300 bg-rose-50/30 hover:border-rose-400' : 'border-slate-200 hover:border-slate-300'}">

          <!-- Priority indicator -->
          <div class="mt-0.5 shrink-0">
            <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg text-xs font-bold {priority.color}">
              {priority.icon}
            </span>
          </div>

          <!-- Content -->
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
              <Link
                href={route('projects.tasks.show', [project.uuid, task.uuid])}
                class="font-semibold transition hover:text-indigo-600 {task.status === 'done' ? 'line-through text-slate-400 hover:text-slate-500' : 'text-slate-900'}"
              >
                {task.title}
              </Link>
              <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium {status.color}">
                <span class="h-1.5 w-1.5 rounded-full {status.dot}"></span>
                {status.label}
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

            {#if task.description}
              <p class="mt-1 line-clamp-2 text-sm text-slate-500">{task.description}</p>
            {/if}

            <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-slate-400">
              <span class="inline-flex items-center gap-1 rounded-md {priority.color} px-2 py-0.5 font-medium">
                {priority.label}
              </span>
              {#if task.due_date}
                <span class="inline-flex items-center gap-1 {overdue ? 'text-rose-600 font-semibold' : ''}">
                  <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                  </svg>
                  {task.due_date}
                </span>
              {/if}
              {#if task.estimated_hours}
                <span class="inline-flex items-center gap-1">
                  <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                  </svg>
                  {task.estimated_hours}h
                </span>
              {/if}
              {#if task.creator}
                <span class="inline-flex items-center gap-1">
                  <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                  </svg>
                  {task.creator.name}
                </span>
              {/if}
            </div>
          </div>

          <!-- Assignee avatars + arrow -->
          <div class="flex shrink-0 items-center gap-3">
            {#if task.assignees?.length}
              <div class="flex -space-x-2">
                {#each task.assignees.slice(0, 3) as assignee}
                  <div
                    title={assignee.name}
                    class="flex h-7 w-7 items-center justify-center rounded-full text-[10px] font-bold text-white ring-2 ring-white {avatarColor(assignee.id)}"
                  >
                    {getInitials(assignee.name)}
                  </div>
                {/each}
                {#if task.assignees.length > 3}
                  <div class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-200 text-[10px] font-bold text-slate-600 ring-2 ring-white">
                    +{task.assignees.length - 3}
                  </div>
                {/if}
              </div>
            {/if}
            <svg class="h-4 w-4 text-slate-300 opacity-0 transition group-hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
          </div>
        </article>
      {/each}
    </div>

    <!-- Pagination -->
    {#if tasks.last_page > 1}
      <div class="mt-6 flex items-center justify-between text-sm text-slate-500">
        <span>Página {tasks.current_page} de {tasks.last_page}</span>
        <div class="flex gap-2">
          {#if tasks.prev_page_url}
            <Link
              href={tasks.prev_page_url}
              class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 font-medium text-slate-700 transition hover:bg-slate-50"
            >← Anterior</Link>
          {/if}
          {#if tasks.next_page_url}
            <Link
              href={tasks.next_page_url}
              class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 font-medium text-slate-700 transition hover:bg-slate-50"
            >Siguiente →</Link>
          {/if}
        </div>
      </div>
    {/if}

  {:else}
    <!-- Empty state -->
    <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-white py-16 text-center">
      <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50">
        <svg class="h-8 w-8 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
        </svg>
      </div>
      {#if hasFilters}
        <h3 class="mt-4 text-lg font-semibold text-slate-800">Sin resultados</h3>
        <p class="mt-2 max-w-xs text-sm text-slate-500">No hay tareas con los filtros aplicados.</p>
        <button
          type="button"
          onclick={clearFilters}
          class="mt-6 inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
        >
          Quitar filtros
        </button>
      {:else}
        <h3 class="mt-4 text-lg font-semibold text-slate-800">Sin tareas aún</h3>
        <p class="mt-2 max-w-xs text-sm text-slate-500">Agrega la primera tarea a este proyecto para comenzar a trabajar.</p>
        <Link
          href={route('projects.tasks.create', project.uuid)}
          class="mt-6 inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:from-indigo-700 hover:to-violet-700"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Crear primera tarea
        </Link>
      {/if}
    </div>
  {/if}

</Layout>
