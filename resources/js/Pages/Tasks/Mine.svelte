<script lang="ts">
  import Layout from '../Layout.svelte';
  import { Link, router } from '@inertiajs/svelte';
  import route from 'ziggy-js';

  export let tasks: any;
  export let filters: { status?: string; priority?: string };

  let status   = filters.status   ?? '';
  let priority = filters.priority ?? '';

  function applyFilters() {
    router.get(route('my-tasks'), { status, priority }, { preserveState: true, replace: true });
  }

  const statusConfig: Record<string, { label: string; color: string; dot: string }> = {
    todo:        { label: 'Por hacer',   color: 'bg-slate-100 text-slate-600 ring-1 ring-slate-200',     dot: 'bg-slate-400' },
    in_progress: { label: 'En progreso', color: 'bg-blue-50 text-blue-700 ring-1 ring-blue-200',         dot: 'bg-blue-500' },
    review:      { label: 'En revisión', color: 'bg-violet-50 text-violet-700 ring-1 ring-violet-200',   dot: 'bg-violet-500' },
    done:        { label: 'Completada',  color: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200', dot: 'bg-emerald-500' },
    cancelled:   { label: 'Cancelada',   color: 'bg-rose-50 text-rose-500 ring-1 ring-rose-200',         dot: 'bg-rose-400' },
  };

  const priorityConfig: Record<string, { label: string; color: string }> = {
    low:      { label: 'Baja',   color: 'text-slate-500' },
    medium:   { label: 'Media',  color: 'text-amber-600' },
    high:     { label: 'Alta',   color: 'text-rose-600' },
    critical: { label: 'Crítica', color: 'text-rose-700 font-bold' },
  };

  function isOverdue(due: string | null, status: string) {
    if (!due || status === 'done' || status === 'cancelled') return false;
    return new Date(due) < new Date(new Date().toDateString());
  }
</script>

<Layout title="Mis tareas">

  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-slate-900">Mis tareas</h1>
      <p class="mt-1 text-sm text-slate-500">Todas las tareas asignadas a ti</p>
    </div>
    <span class="rounded-full bg-indigo-50 px-3 py-1 text-sm font-semibold text-indigo-700 ring-1 ring-indigo-200">
      {tasks.total} tareas
    </span>
  </div>

  <!-- Filters -->
  <div class="mb-5 flex flex-wrap items-center gap-3">
    <select bind:value={status} onchange={applyFilters}
      class="rounded-xl border border-slate-300 bg-white py-2 px-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0">
      <option value="">Todos los estados</option>
      <option value="todo">Por hacer</option>
      <option value="in_progress">En progreso</option>
      <option value="review">En revisión</option>
      <option value="done">Completadas</option>
      <option value="cancelled">Canceladas</option>
    </select>
    <select bind:value={priority} onchange={applyFilters}
      class="rounded-xl border border-slate-300 bg-white py-2 px-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0">
      <option value="">Todas las prioridades</option>
      <option value="low">Baja</option>
      <option value="medium">Media</option>
      <option value="high">Alta</option>
      <option value="critical">Crítica</option>
    </select>
    {#if status || priority}
      <button onclick={() => { status = ''; priority = ''; applyFilters(); }}
        class="text-sm text-slate-500 hover:text-slate-700 underline underline-offset-2">
        Limpiar filtros
      </button>
    {/if}
  </div>

  {#if tasks.data.length === 0}
    <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white py-20 text-center">
      <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100">
        <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
      </div>
      <p class="mt-4 font-medium text-slate-700">Sin tareas asignadas</p>
      <p class="mt-1 text-sm text-slate-500">Cuando te asignen una tarea aparecerá aquí</p>
    </div>
  {:else}
    <div class="space-y-3">
      {#each tasks.data as task}
        {@const st  = statusConfig[task.status] ?? { label: task.status, color: 'bg-slate-100 text-slate-600 ring-1 ring-slate-200', dot: 'bg-slate-400' }}
        {@const pri = priorityConfig[task.priority] ?? { label: task.priority, color: 'text-slate-500' }}
        {@const overdue = isOverdue(task.due_date, task.status)}
        <Link
          href={route('projects.tasks.show', [task.project?.uuid, task.uuid])}
          class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md hover:-translate-y-0.5 group"
        >
          <!-- Project color dot -->
          <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-white text-sm font-bold shadow-sm"
               style="background-color: {task.project?.color ?? '#6366f1'};">
            {task.project?.name?.charAt(0).toUpperCase() ?? '?'}
          </div>

          <!-- Main info -->
          <div class="min-w-0 flex-1">
            <p class="truncate font-semibold text-slate-900 group-hover:text-indigo-700 transition-colors">{task.title}</p>
            <p class="mt-0.5 truncate text-xs text-slate-500">{task.project?.name ?? '—'}</p>
          </div>

          <!-- Meta -->
          <div class="flex shrink-0 items-center gap-3">
            <span class="hidden text-xs font-medium {pri.color} sm:block">{pri.label}</span>
            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium {st.color}">
              <span class="h-1.5 w-1.5 rounded-full {st.dot}"></span>
              {st.label}
            </span>
            {#if task.due_date}
              <span class="hidden whitespace-nowrap text-xs {overdue ? 'text-rose-600 font-semibold' : 'text-slate-500'} sm:block">
                {overdue ? '⚠ ' : ''}{task.due_date}
              </span>
            {/if}
          </div>
        </Link>
      {/each}
    </div>

    <!-- Pagination -->
    {#if tasks.last_page > 1}
      <div class="mt-6 flex items-center justify-center gap-2">
        {#each tasks.links as link}
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

</Layout>
