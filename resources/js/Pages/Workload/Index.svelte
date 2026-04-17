<script lang="ts">
  import Layout from '../Layout.svelte';
  import Avatar from '../../lib/Avatar.svelte';
  import { router } from '@inertiajs/svelte';
  import route from 'ziggy-js';

  export let workload: {
    id: number; uuid: string; name: string; email: string;
    avatar_url: string | null;
    open_tasks: number; in_progress_tasks: number; in_review_tasks: number; todo_tasks: number;
  }[];

  export let organizations: { id: number; uuid: string; name: string; color: string }[];
  export let projects: { id: number; uuid: string; name: string }[];
  export let filters: { project_id?: string; org_id?: string };

  let selectedProject = filters.project_id ?? '';
  let selectedOrg     = filters.org_id ?? '';

  function applyFilters() {
    router.get(route('workload'), {
      project_id: selectedProject || undefined,
      org_id:     selectedOrg     || undefined,
    }, { preserveState: true, replace: true });
  }

  $: maxTasks = Math.max(...workload.map(u => u.open_tasks), 1);

  function barColor(count: number) {
    const ratio = count / maxTasks;
    if (ratio > 0.75) return 'bg-rose-500';
    if (ratio > 0.5)  return 'bg-amber-400';
    return 'bg-emerald-500';
  }
</script>

<Layout title="Carga de trabajo">

  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Carga de trabajo</h1>
      <p class="mt-1 text-sm text-slate-500">Tareas abiertas por persona del equipo.</p>
    </div>
  </div>

  <!-- Filters -->
  <div class="mb-6 flex flex-wrap gap-3">
    <select
      bind:value={selectedOrg}
      onchange={applyFilters}
      class="rounded-xl border border-slate-300 bg-white py-2 pl-3 pr-8 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
    >
      <option value="">Todas las organizaciones</option>
      {#each organizations as org}
        <option value={org.uuid}>{org.name}</option>
      {/each}
    </select>

    <select
      bind:value={selectedProject}
      onchange={applyFilters}
      class="rounded-xl border border-slate-300 bg-white py-2 pl-3 pr-8 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
    >
      <option value="">Todos los proyectos</option>
      {#each projects as project}
        <option value={project.uuid}>{project.name}</option>
      {/each}
    </select>
  </div>

  {#if workload.length === 0}
    <div class="rounded-2xl border border-slate-200 bg-white p-12 text-center shadow-sm">
      <svg class="mx-auto mb-3 h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
      </svg>
      <p class="text-slate-500">No hay tareas abiertas asignadas en este contexto.</p>
    </div>
  {:else}
    <div class="space-y-4">
      {#each workload as member}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
          <div class="flex items-center gap-4 p-5">
            <!-- Avatar -->
            <Avatar user={member} size="md" />

            <!-- Info -->
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-3">
                <p class="font-semibold text-slate-900">{member.name}</p>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700">
                  {member.open_tasks} tarea{member.open_tasks !== 1 ? 's' : ''}
                </span>
              </div>
              <p class="mt-0.5 truncate text-xs text-slate-400">{member.email}</p>

              <!-- Bar -->
              <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                <div
                  class="h-2 rounded-full transition-all {barColor(member.open_tasks)}"
                  style="width: {(member.open_tasks / maxTasks) * 100}%"
                ></div>
              </div>
            </div>

            <!-- Status breakdown -->
            <div class="hidden shrink-0 gap-4 sm:flex">
              <div class="text-center">
                <p class="text-lg font-bold text-blue-600">{member.in_progress_tasks}</p>
                <p class="text-xs text-slate-400">En progreso</p>
              </div>
              <div class="text-center">
                <p class="text-lg font-bold text-amber-500">{member.in_review_tasks}</p>
                <p class="text-xs text-slate-400">En revisión</p>
              </div>
              <div class="text-center">
                <p class="text-lg font-bold text-slate-500">{member.todo_tasks}</p>
                <p class="text-xs text-slate-400">Por hacer</p>
              </div>
            </div>
          </div>
        </div>
      {/each}
    </div>
  {/if}

</Layout>
