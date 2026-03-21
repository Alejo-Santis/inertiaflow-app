<script lang="ts">
  import Layout from '../Layout.svelte';
  import { Link } from '@inertiajs/svelte';
  import route from 'ziggy-js';

  export let projects: any;
  export let user: any;

  const statusConfig: Record<string, { label: string; color: string; dot: string }> = {
    active:    { label: 'Activo',     color: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',  dot: 'bg-emerald-500' },
    on_hold:   { label: 'En espera', color: 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',        dot: 'bg-amber-500' },
    completed: { label: 'Completado', color: 'bg-sky-50 text-sky-700 ring-1 ring-sky-200',             dot: 'bg-sky-500' },
    cancelled: { label: 'Cancelado',  color: 'bg-slate-100 text-slate-500 ring-1 ring-slate-200',      dot: 'bg-slate-400' },
  };

  function getStatus(status: string) {
    return statusConfig[status] ?? { label: status, color: 'bg-slate-100 text-slate-600', dot: 'bg-slate-400' };
  }

  $: totalProjects = projects?.total ?? 0;
  $: activeProjects = projects?.data?.filter((p: any) => p.status === 'active').length ?? 0;
  $: completedProjects = projects?.data?.filter((p: any) => p.status === 'completed').length ?? 0;

  const greeting = (() => {
    const h = new Date().getHours();
    if (h < 12) return 'Buenos días';
    if (h < 18) return 'Buenas tardes';
    return 'Buenas noches';
  })();
</script>

<Layout title="Dashboard">

  <!-- Welcome header -->
  <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <p class="text-sm font-medium text-indigo-600">{greeting} 👋</p>
      <h1 class="mt-0.5 text-2xl font-bold tracking-tight text-slate-900">{user.name}</h1>
      <p class="mt-1 text-sm text-slate-500">Aquí tienes el resumen de tus proyectos</p>
    </div>
    <Link
      href={route('projects.create')}
      class="inline-flex items-center gap-2 self-start rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:from-indigo-700 hover:to-violet-700 hover:shadow-md sm:self-auto"
    >
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
      </svg>
      Nuevo proyecto
    </Link>
  </div>

  <!-- Stats cards -->
  <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
      <div class="flex items-start justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total proyectos</p>
          <p class="mt-2 text-3xl font-bold text-slate-900">{totalProjects}</p>
        </div>
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
          </svg>
        </div>
      </div>
      <div class="absolute bottom-0 left-0 h-0.5 w-full bg-gradient-to-r from-indigo-500 to-violet-500 opacity-0 transition-opacity group-hover:opacity-100"></div>
    </div>

    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
      <div class="flex items-start justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Activos</p>
          <p class="mt-2 text-3xl font-bold text-slate-900">{activeProjects}</p>
        </div>
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
          </svg>
        </div>
      </div>
      <div class="absolute bottom-0 left-0 h-0.5 w-full bg-gradient-to-r from-emerald-500 to-teal-500 opacity-0 transition-opacity group-hover:opacity-100"></div>
    </div>

    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
      <div class="flex items-start justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Completados</p>
          <p class="mt-2 text-3xl font-bold text-slate-900">{completedProjects}</p>
        </div>
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
        </div>
      </div>
      <div class="absolute bottom-0 left-0 h-0.5 w-full bg-gradient-to-r from-sky-500 to-cyan-500 opacity-0 transition-opacity group-hover:opacity-100"></div>
    </div>

    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
      <div class="flex items-start justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Cuenta</p>
          <p class="mt-2 text-sm font-semibold text-slate-700 truncate max-w-[120px]">{user.email}</p>
        </div>
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
          </svg>
        </div>
      </div>
      <div class="absolute bottom-0 left-0 h-0.5 w-full bg-gradient-to-r from-violet-500 to-purple-500 opacity-0 transition-opacity group-hover:opacity-100"></div>
    </div>
  </div>

  <!-- Bottom section: 2-column grid on large screens -->
  <div class="grid gap-6 lg:grid-cols-3">

    <!-- Recent projects (2/3 width) -->
    <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white shadow-sm">
      <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
        <div>
          <h2 class="font-semibold text-slate-900">Proyectos recientes</h2>
          <p class="mt-0.5 text-xs text-slate-500">Tus últimos {projects?.data?.length ?? 0} proyectos</p>
        </div>
        <Link
          href={route('projects.index')}
          class="flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-700"
        >
          Ver todos
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
          </svg>
        </Link>
      </div>

      {#if projects?.data?.length}
        <div class="divide-y divide-slate-100">
          {#each projects.data as project}
            {@const status = getStatus(project.status)}
            <div class="group flex items-center gap-4 px-6 py-4 transition hover:bg-slate-50/60">
              <div
                class="h-10 w-10 shrink-0 rounded-xl shadow-sm"
                style="background-color: {project.color ?? '#6366f1'};"
              ></div>

              <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                  <p class="truncate font-medium text-slate-900">{project.name}</p>
                  <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium {status.color}">
                    <span class="h-1.5 w-1.5 rounded-full {status.dot}"></span>
                    {status.label}
                  </span>
                </div>
                {#if (project.tasks_count ?? 0) > 0}
                  {@const pct = Math.round(((project.done_tasks_count ?? 0) / project.tasks_count) * 100)}
                  <div class="mt-1.5 flex items-center gap-2">
                    <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                      <div
                        class="h-full rounded-full {pct === 100 ? 'bg-emerald-500' : 'bg-indigo-500'}"
                        style="width: {pct}%;"
                      ></div>
                    </div>
                    <span class="shrink-0 text-xs text-slate-400">{pct}%</span>
                  </div>
                {:else}
                  <p class="mt-0.5 text-xs text-slate-400">Sin tareas</p>
                {/if}
              </div>

              <div class="shrink-0 text-right">
                <p class="text-sm font-semibold text-slate-700">{project.tasks_count ?? 0}</p>
                <p class="text-xs text-slate-400">tareas</p>
              </div>

              <Link
                href={route('projects.show', project.uuid)}
                class="shrink-0 rounded-lg p-2 text-slate-400 opacity-0 transition hover:bg-indigo-50 hover:text-indigo-600 group-hover:opacity-100"
                title="Abrir proyecto"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
              </Link>
            </div>
          {/each}
        </div>
      {:else}
        <div class="flex flex-col items-center justify-center gap-3 py-16 text-center">
          <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100">
            <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
            </svg>
          </div>
          <div>
            <p class="font-semibold text-slate-700">Sin proyectos aún</p>
            <p class="mt-1 text-sm text-slate-500">Crea tu primer proyecto para comenzar</p>
          </div>
          <Link
            href={route('projects.create')}
            class="mt-1 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Crear proyecto
          </Link>
        </div>
      {/if}
    </div>

    <!-- Sidebar (1/3 width) -->
    <div class="flex flex-col gap-6">

      <!-- Estado de proyectos -->
      <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="mb-4 font-semibold text-slate-900">Estado de proyectos</h3>
        <div class="space-y-3">
          {#each [
            { key: 'active',    label: 'Activos',     color: 'bg-emerald-500', bg: 'bg-emerald-50' },
            { key: 'on_hold',   label: 'En espera',   color: 'bg-amber-500',   bg: 'bg-amber-50'   },
            { key: 'completed', label: 'Completados', color: 'bg-sky-500',     bg: 'bg-sky-50'     },
            { key: 'cancelled', label: 'Cancelados',  color: 'bg-slate-400',   bg: 'bg-slate-100'  },
          ] as s}
            {@const count = projects?.data?.filter((p: any) => p.status === s.key).length ?? 0}
            {@const pct = totalProjects > 0 ? Math.round((count / totalProjects) * 100) : 0}
            <div>
              <div class="mb-1 flex items-center justify-between text-sm">
                <span class="text-slate-600">{s.label}</span>
                <span class="font-semibold text-slate-800">{count}</span>
              </div>
              <div class="h-2 overflow-hidden rounded-full {s.bg}">
                <div class="h-full rounded-full {s.color} transition-all duration-500" style="width: {pct}%;"></div>
              </div>
            </div>
          {/each}
        </div>
      </div>

      <!-- Accesos rápidos -->
      <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="mb-4 font-semibold text-slate-900">Accesos rápidos</h3>
        <div class="space-y-2">
          <Link
            href={route('projects.create')}
            class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-700 transition hover:bg-indigo-50 hover:text-indigo-700"
          >
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
              </svg>
            </span>
            Nuevo proyecto
          </Link>
          <Link
            href={route('my-tasks')}
            class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-700 transition hover:bg-violet-50 hover:text-violet-700"
          >
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-100 text-violet-600">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
              </svg>
            </span>
            Mis tareas
          </Link>
          <Link
            href={route('meetings.index')}
            class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-700 transition hover:bg-emerald-50 hover:text-emerald-700"
          >
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
              </svg>
            </span>
            Reuniones
          </Link>
          <Link
            href={route('analytics')}
            class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-700 transition hover:bg-sky-50 hover:text-sky-700"
          >
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-100 text-sky-600">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
              </svg>
            </span>
            Analíticas
          </Link>
        </div>
      </div>

    </div>
  </div>

</Layout>
