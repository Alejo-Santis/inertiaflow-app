<script lang="ts">
  import Layout from '../Layout.svelte';
  import { Link } from '@inertiajs/svelte';
  import route from 'ziggy-js';

  let { stats, by_status, by_priority, projects, activity }: {
    stats: any;
    by_status: Record<string, number>;
    by_priority: Record<string, number>;
    projects: any[];
    activity: Record<string, number>;
  } = $props();

  const statusConfig: Record<string, { label: string; color: string; bar: string }> = {
    todo:        { label: 'Por hacer',   color: 'text-slate-600',   bar: 'bg-slate-400' },
    in_progress: { label: 'En progreso', color: 'text-blue-600',    bar: 'bg-blue-500' },
    in_review:   { label: 'En revisión', color: 'text-violet-600',  bar: 'bg-violet-500' },
    done:        { label: 'Hecho',       color: 'text-emerald-600', bar: 'bg-emerald-500' },
    cancelled:   { label: 'Cancelado',   color: 'text-rose-500',    bar: 'bg-rose-400' },
  };

  const priorityConfig: Record<string, { label: string; bar: string }> = {
    '1': { label: 'Baja',    bar: 'bg-slate-400' },
    '2': { label: 'Media',   bar: 'bg-blue-500' },
    '3': { label: 'Alta',    bar: 'bg-amber-500' },
    '4': { label: 'Urgente', bar: 'bg-rose-500' },
  };

  // Activity chart — last 30 days
  const today = new Date();
  const days: { date: string; label: string; count: number }[] = Array.from({ length: 30 }, (_, i) => {
    const d = new Date(today);
    d.setDate(d.getDate() - (29 - i));
    const iso = d.toISOString().split('T')[0];
    return {
      date: iso,
      label: d.toLocaleDateString('es-ES', { day: 'numeric', month: 'short' }),
      count: activity[iso] ?? 0,
    };
  });

  const maxActivity = Math.max(...days.map(d => d.count), 1);

  // Status totals for bar chart
  const statusTotal = Object.values(by_status).reduce((a, b) => a + b, 0) || 1;

  // Priority totals
  const priorityTotal = Object.values(by_priority).reduce((a, b) => a + b, 0) || 1;

  function pct(n: number, total: number) {
    return total > 0 ? Math.round((n / total) * 100) : 0;
  }
</script>

<Layout title="Analíticas">

  <!-- Header -->
  <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-slate-900">Analíticas</h1>
      <p class="mt-1 text-sm text-slate-500">Resumen del rendimiento de tus proyectos y tareas</p>
    </div>
    <Link
      href={route('dashboard')}
      class="inline-flex items-center gap-2 self-start rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 sm:self-auto"
    >
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
      </svg>
      Dashboard
    </Link>
  </div>

  <!-- KPI cards -->
  <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
      <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total tareas</p>
      <p class="mt-2 text-3xl font-bold text-slate-900">{stats.total_tasks}</p>
      <div class="absolute bottom-0 left-0 h-0.5 w-full bg-gradient-to-r from-indigo-500 to-violet-500"></div>
    </div>
    <div class="group relative overflow-hidden rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
      <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Completadas</p>
      <p class="mt-2 text-3xl font-bold text-emerald-700">{stats.done_tasks}</p>
      <div class="absolute bottom-0 left-0 h-0.5 w-full bg-gradient-to-r from-emerald-400 to-teal-500"></div>
    </div>
    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
      <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Tasa de éxito</p>
      <p class="mt-2 text-3xl font-bold text-slate-900">{stats.completion_rate}%</p>
      <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
        <div class="h-full rounded-full bg-indigo-500 transition-all" style="width: {stats.completion_rate}%;"></div>
      </div>
    </div>
    <div class="group relative overflow-hidden rounded-2xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
      <p class="text-xs font-semibold uppercase tracking-wider text-rose-600">Vencidas</p>
      <p class="mt-2 text-3xl font-bold text-rose-700">{stats.overdue_tasks}</p>
      <div class="absolute bottom-0 left-0 h-0.5 w-full bg-gradient-to-r from-rose-400 to-pink-500"></div>
    </div>
    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
      <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Canceladas</p>
      <p class="mt-2 text-3xl font-bold text-slate-900">{stats.cancelled_tasks}</p>
      <div class="absolute bottom-0 left-0 h-0.5 w-full bg-slate-200"></div>
    </div>
  </div>

  <div class="grid gap-6 lg:grid-cols-3">

    <!-- Activity chart (2 cols) -->
    <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
      <h2 class="mb-5 text-sm font-semibold text-slate-700">Tareas creadas — últimos 30 días</h2>
      <div class="flex h-32 items-end gap-1">
        {#each days as day}
          <div class="group relative flex flex-1 flex-col items-center justify-end" title="{day.label}: {day.count}">
            <div
              class="w-full rounded-t-sm bg-indigo-400 transition-all duration-300 group-hover:bg-indigo-600"
              style="height: {day.count === 0 ? '2px' : `${Math.round((day.count / maxActivity) * 100)}%`}; min-height: 2px; opacity: {day.count === 0 ? 0.2 : 1};"
            ></div>
            <!-- Tooltip on hover -->
            {#if day.count > 0}
              <div class="pointer-events-none absolute bottom-full mb-1 hidden rounded-lg bg-slate-800 px-2 py-1 text-[10px] text-white group-hover:block whitespace-nowrap z-10">
                {day.label}: {day.count}
              </div>
            {/if}
          </div>
        {/each}
      </div>
      <div class="mt-2 flex justify-between text-[10px] text-slate-400">
        <span>{days[0].label}</span>
        <span>{days[14].label}</span>
        <span>{days[29].label}</span>
      </div>
    </div>

    <!-- By status -->
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
      <h2 class="mb-5 text-sm font-semibold text-slate-700">Por estado</h2>
      <div class="space-y-3">
        {#each Object.entries(statusConfig) as [key, cfg]}
          {@const count = by_status[key] ?? 0}
          {@const p = pct(count, statusTotal)}
          <div>
            <div class="mb-1 flex items-center justify-between text-xs">
              <span class="font-medium {cfg.color}">{cfg.label}</span>
              <span class="font-semibold text-slate-700">{count} <span class="font-normal text-slate-400">({p}%)</span></span>
            </div>
            <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
              <div class="h-full rounded-full transition-all duration-500 {cfg.bar}" style="width: {p}%;"></div>
            </div>
          </div>
        {/each}
      </div>
    </div>

    <!-- Projects table -->
    <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white shadow-sm">
      <div class="border-b border-slate-100 px-6 py-4">
        <h2 class="text-sm font-semibold text-slate-700">Rendimiento por proyecto</h2>
      </div>
      {#if projects.length}
        <div class="divide-y divide-slate-100">
          {#each projects as p}
            {@const done = p.done_tasks_count ?? 0}
            {@const total = p.tasks_count ?? 0}
            {@const progress = total > 0 ? Math.round((done / total) * 100) : 0}
            <div class="flex items-center gap-4 px-6 py-4">
              <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-xs font-bold text-white"
                style="background-color: {p.color ?? '#6366f1'};">
                {p.name.charAt(0).toUpperCase()}
              </div>
              <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between gap-2">
                  <Link
                    href={route('projects.show', p.uuid)}
                    class="truncate text-sm font-medium text-slate-800 hover:text-indigo-600"
                  >{p.name}</Link>
                  <span class="shrink-0 text-xs font-semibold text-slate-500">{done}/{total}</span>
                </div>
                <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                  <div class="h-full rounded-full transition-all {progress === 100 ? 'bg-emerald-500' : 'bg-indigo-500'}"
                    style="width: {progress}%;"></div>
                </div>
              </div>
              <div class="shrink-0 text-right text-xs">
                <span class="font-bold text-slate-700">{progress}%</span>
                {#if (p.overdue_tasks_count ?? 0) > 0}
                  <div class="mt-0.5 font-medium text-rose-500">{p.overdue_tasks_count} vencida{p.overdue_tasks_count !== 1 ? 's' : ''}</div>
                {/if}
              </div>
            </div>
          {/each}
        </div>
      {:else}
        <p class="px-6 py-8 text-sm text-slate-400 text-center">Sin datos de proyectos aún.</p>
      {/if}
    </div>

    <!-- By priority -->
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
      <h2 class="mb-5 text-sm font-semibold text-slate-700">Por prioridad</h2>
      <div class="space-y-3">
        {#each Object.entries(priorityConfig) as [key, cfg]}
          {@const count = by_priority[key] ?? 0}
          {@const p = pct(count, priorityTotal)}
          <div>
            <div class="mb-1 flex items-center justify-between text-xs">
              <span class="font-medium text-slate-600">{cfg.label}</span>
              <span class="font-semibold text-slate-700">{count} <span class="font-normal text-slate-400">({p}%)</span></span>
            </div>
            <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
              <div class="h-full rounded-full transition-all duration-500 {cfg.bar}" style="width: {p}%;"></div>
            </div>
          </div>
        {/each}
      </div>

      <!-- Summary -->
      <div class="mt-6 rounded-xl border border-slate-100 bg-slate-50 p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-3">Resumen global</p>
        <div class="grid grid-cols-2 gap-3 text-center">
          <div class="rounded-lg bg-white border border-slate-200 p-2">
            <p class="text-lg font-bold text-emerald-600">{stats.completion_rate}%</p>
            <p class="text-[10px] text-slate-500">completado</p>
          </div>
          <div class="rounded-lg bg-white border border-slate-200 p-2">
            <p class="text-lg font-bold text-rose-600">{stats.overdue_tasks}</p>
            <p class="text-[10px] text-slate-500">vencidas</p>
          </div>
          <div class="rounded-lg bg-white border border-slate-200 p-2">
            <p class="text-lg font-bold text-indigo-600">{stats.total_tasks - stats.done_tasks - stats.cancelled_tasks}</p>
            <p class="text-[10px] text-slate-500">en curso</p>
          </div>
          <div class="rounded-lg bg-white border border-slate-200 p-2">
            <p class="text-lg font-bold text-slate-500">{projects.length}</p>
            <p class="text-[10px] text-slate-500">proyectos</p>
          </div>
        </div>
      </div>
    </div>

  </div>

</Layout>
