<script lang="ts">
  import Layout from '../Layout.svelte';
  import { Link } from '@inertiajs/svelte';
  import route from 'ziggy-js';

  let { projects, canCreate = false }: { projects: any; canCreate: boolean } = $props();

  const statusConfig: Record<string, { label: string; color: string; dot: string }> = {
    active:    { label: 'Activo',     color: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',  dot: 'bg-emerald-500' },
    on_hold:   { label: 'En espera', color: 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',        dot: 'bg-amber-500' },
    completed: { label: 'Completado', color: 'bg-sky-50 text-sky-700 ring-1 ring-sky-200',             dot: 'bg-sky-500' },
    cancelled: { label: 'Cancelado',  color: 'bg-slate-100 text-slate-500 ring-1 ring-slate-200',      dot: 'bg-slate-400' },
  };

  function getStatus(status: string) {
    return statusConfig[status] ?? { label: status, color: 'bg-slate-100 text-slate-600', dot: 'bg-slate-400' };
  }

  const priorityConfig: Record<string, { label: string; color: string; icon: string }> = {
    low:    { label: 'Baja',  color: 'text-slate-500', icon: '▼' },
    medium: { label: 'Media', color: 'text-amber-600', icon: '●' },
    high:   { label: 'Alta',  color: 'text-rose-600',  icon: '▲' },
  };

  function getPriority(p: string) {
    return priorityConfig[p] ?? { label: p, color: 'text-slate-500', icon: '●' };
  }

  function isOverdue(deadline: string | null) {
    if (!deadline) return false;
    return new Date(deadline) < new Date(new Date().toDateString());
  }
</script>

<Layout title="Proyectos">

  <!-- Page header -->
  <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-slate-900">Proyectos</h1>
      <p class="mt-1 text-sm text-slate-500">
        {projects?.total ?? 0} proyecto{(projects?.total ?? 0) !== 1 ? 's' : ''} en total
      </p>
    </div>
    {#if canCreate}
      <Link
        href={route('projects.create')}
        class="inline-flex items-center gap-2 self-start rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:from-indigo-700 hover:to-violet-700 hover:shadow-md sm:self-auto"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Nuevo proyecto
      </Link>
    {/if}
  </div>

  {#if projects?.data?.length}
    <!-- Projects grid -->
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
      {#each projects.data as project}
        {@const status = getStatus(project.status)}
        {@const pri = getPriority(project.priority ?? 'medium')}
        <article class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md hover:-translate-y-0.5">

          <!-- Color bar -->
          <div class="h-1.5 w-full" style="background-color: {project.color ?? '#6366f1'};"></div>

          <div class="flex flex-1 flex-col p-5">
            <!-- Header -->
            <div class="flex items-start justify-between gap-3">
              <div class="flex items-center gap-3 min-w-0">
                <div
                  class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-white text-sm font-bold shadow-sm"
                  style="background-color: {project.color ?? '#6366f1'};"
                >
                  {project.name.charAt(0).toUpperCase()}
                </div>
                <h2 class="truncate font-semibold text-slate-900">{project.name}</h2>
              </div>
              <span class="inline-flex shrink-0 items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium {status.color}">
                <span class="h-1.5 w-1.5 rounded-full {status.dot}"></span>
                {status.label}
              </span>
            </div>

            <!-- Org badge -->
            {#if project.organization}
              <div class="mt-2 flex items-center gap-1.5">
                <span class="inline-block h-2 w-2 rounded-full" style="background-color: {project.organization.color ?? '#6366f1'};"></span>
                <span class="text-xs text-slate-500">{project.organization.name}</span>
              </div>
            {/if}

            <!-- Description -->
            {#if project.description}
              <p class="mt-3 line-clamp-2 text-sm leading-relaxed text-slate-500">{project.description}</p>
            {:else}
              <p class="mt-3 text-sm italic text-slate-400">Sin descripción</p>
            {/if}

            <!-- Footer -->
            <div class="mt-auto pt-4">
              <!-- Progress bar -->
              {#if (project.tasks_count ?? 0) > 0}
                {@const pct = Math.round(((project.done_tasks_count ?? 0) / project.tasks_count) * 100)}
                <div class="mb-3">
                  <div class="mb-1 flex items-center justify-between text-xs text-slate-500">
                    <span>{project.done_tasks_count ?? 0} / {project.tasks_count} completadas</span>
                    <span class="font-semibold text-slate-700">{pct}%</span>
                  </div>
                  <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                    <div
                      class="h-full rounded-full transition-all duration-500 {pct === 100 ? 'bg-emerald-500' : 'bg-indigo-500'}"
                      style="width: {pct}%;"
                    ></div>
                  </div>
                </div>
              {/if}

              <!-- Priority + Deadline row -->
              <div class="mb-3 flex items-center gap-2 flex-wrap">
                <span class="inline-flex items-center gap-1 text-xs font-medium {pri.color}">
                  <span>{pri.icon}</span>{pri.label}
                </span>
                {#if project.deadline}
                  <span class="ml-auto inline-flex items-center gap-1 text-xs {isOverdue(project.deadline) ? 'text-rose-600 font-semibold' : 'text-slate-500'}">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    {isOverdue(project.deadline) ? '¡Vencido!' : 'Límite:'} {project.deadline}
                  </span>
                {/if}
              </div>

              {#if project.end_date}
                <div class="mb-2 flex items-center gap-1.5 text-xs text-slate-500">
                  <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                  </svg>
                  Ejecución: {project.start_date ?? '—'} → {project.end_date}
                </div>
              {/if}

              <div class="flex items-center justify-between border-t border-slate-100 pt-3">
                <div class="flex items-center gap-3 text-xs text-slate-500">
                  <span class="flex items-center gap-1">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                    </svg>
                    <strong class="font-semibold text-slate-700">{project.tasks_count ?? 0}</strong>
                  </span>
                  {#if (project.members_count ?? 0) > 0}
                    <span class="flex items-center gap-1">
                      <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                      </svg>
                      <strong class="font-semibold text-slate-700">{project.members_count}</strong>
                    </span>
                  {/if}
                </div>

                <Link
                  href={route('projects.show', project.uuid)}
                  class="inline-flex items-center gap-1.5 rounded-lg bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-indigo-50 hover:text-indigo-700"
                >
                  Abrir
                  <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                  </svg>
                </Link>
              </div>
            </div>
          </div>
        </article>
      {/each}
    </div>

    <!-- Pagination -->
    {#if projects.nextPageUrl || projects.prevPageUrl}
      <div class="mt-6 flex items-center justify-center gap-2">
        {#if projects.prevPageUrl}
          <Link href={projects.prevPageUrl} class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Anterior
          </Link>
        {/if}
        {#if projects.nextPageUrl}
          <Link href={projects.nextPageUrl} class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50">
            Siguiente
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
            </svg>
          </Link>
        {/if}
      </div>
    {/if}

  {:else}
    <!-- Empty state -->
    <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-white py-20 text-center">
      <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-indigo-50">
        <svg class="h-10 w-10 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
        </svg>
      </div>
      <h3 class="mt-4 text-lg font-semibold text-slate-800">Sin proyectos aún</h3>
      <p class="mt-2 max-w-xs text-sm text-slate-500">
        {#if canCreate}
          Crea tu primer proyecto y comienza a organizar tu trabajo en equipo.
        {:else}
          Aún no perteneces a ningún proyecto. Contacta a tu administrador para que te invite.
        {/if}
      </p>
      {#if canCreate}
        <Link
          href={route('projects.create')}
          class="mt-6 inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:from-indigo-700 hover:to-violet-700"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Crear primer proyecto
        </Link>
      {/if}
    </div>
  {/if}

</Layout>
