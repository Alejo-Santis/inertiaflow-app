<script lang="ts">
  import Layout from '../Layout.svelte';
  import { Link, useForm, router } from '@inertiajs/svelte';
  import route from 'ziggy-js';

  export let project: any;
  export let members: any[];
  export let available: any[];

  const statusConfig: Record<string, { label: string; color: string; dot: string }> = {
    active:    { label: 'Activo',     color: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200', dot: 'bg-emerald-500' },
    on_hold:   { label: 'En espera', color: 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',       dot: 'bg-amber-500' },
    completed: { label: 'Completado', color: 'bg-sky-50 text-sky-700 ring-1 ring-sky-200',            dot: 'bg-sky-500' },
    cancelled: { label: 'Cancelado',  color: 'bg-slate-100 text-slate-500 ring-1 ring-slate-200',     dot: 'bg-slate-400' },
  };

  $: status = statusConfig[project.status] ?? { label: project.status, color: 'bg-slate-100', dot: 'bg-slate-400' };

  const priorityConfig: Record<string, { label: string; color: string; icon: string }> = {
    low:    { label: 'Prioridad baja',  color: 'text-slate-500', icon: '▼' },
    medium: { label: 'Prioridad media', color: 'text-amber-600', icon: '●' },
    high:   { label: 'Prioridad alta',  color: 'text-rose-600',  icon: '▲' },
  };
  $: priority = priorityConfig[project.priority ?? 'medium'];
  $: deadlineOverdue = project.deadline && new Date(project.deadline) < new Date(new Date().toDateString());

  $: progress = project.tasks_count > 0
    ? Math.round((project.done_tasks_count / project.tasks_count) * 100)
    : 0;

  const addForm = useForm({ user_id: '' });

  function addMember() {
    if (!$addForm.user_id) return;
    $addForm.post(route('projects.members.store', project.uuid), {
      onSuccess: () => { $addForm.user_id = ''; },
    });
  }

  function removeMember(user: any) {
    router.delete(route('projects.members.destroy', [project.uuid, user.uuid]), {
      preserveScroll: true,
    });
  }

  function getInitials(name: string) {
    return name.split(' ').map((n: string) => n[0]).slice(0, 2).join('').toUpperCase();
  }
</script>

<Layout title={project.name}>

  <!-- Breadcrumb -->
  <nav class="mb-6 flex items-center gap-2 text-sm text-slate-500">
    <Link href={route('projects.index')} class="hover:text-slate-700">Proyectos</Link>
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
    </svg>
    <span class="font-medium text-slate-900">{project.name}</span>
  </nav>

  <div class="grid gap-6 lg:grid-cols-3">

    <!-- Main column -->
    <div class="space-y-6 lg:col-span-2">

      <!-- Project header card -->
      <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="h-2" style="background-color: {project.color ?? '#6366f1'};"></div>
        <div class="p-6">
          <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-4">
              <div class="flex h-12 w-12 items-center justify-center rounded-xl text-white text-lg font-bold shadow-sm"
                   style="background-color: {project.color ?? '#6366f1'};">
                {project.name.charAt(0).toUpperCase()}
              </div>
              <div>
                <h1 class="text-xl font-bold text-slate-900">{project.name}</h1>
                <p class="mt-0.5 text-sm text-slate-500">Por {project.owner?.name ?? 'Desconocido'}</p>
              </div>
            </div>
            <div class="flex shrink-0 items-center gap-2 flex-wrap">
              <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium {status.color}">
                <span class="h-1.5 w-1.5 rounded-full {status.dot}"></span>
                {status.label}
              </span>
              {#if priority}
                <span class="inline-flex items-center gap-1 text-xs font-medium {priority.color}">
                  {priority.icon} {priority.label}
                </span>
              {/if}
              {#if project.deadline}
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-medium {deadlineOverdue ? 'bg-rose-50 text-rose-700 ring-1 ring-rose-200' : 'bg-slate-50 text-slate-600 ring-1 ring-slate-200'}">
                  <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                  </svg>
                  {deadlineOverdue ? '¡Vencido!' : 'Límite:'} {project.deadline}
                </span>
              {/if}
            </div>
          </div>

          {#if project.description}
            <p class="mt-4 text-sm leading-relaxed text-slate-600">{project.description}</p>
          {/if}

          <!-- Dates -->
          {#if project.start_date || project.end_date}
            <div class="mt-4 flex flex-wrap gap-4 text-sm text-slate-500">
              {#if project.start_date}
                <span class="flex items-center gap-1.5">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25" />
                  </svg>
                  Inicio: {project.start_date}
                </span>
              {/if}
              {#if project.end_date}
                <span class="flex items-center gap-1.5">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5" />
                  </svg>
                  Fin: {project.end_date}
                </span>
              {/if}
            </div>
          {/if}

          <!-- Progress -->
          <div class="mt-5">
            <div class="mb-2 flex items-center justify-between text-sm">
              <span class="font-medium text-slate-700">Progreso general</span>
              <span class="font-bold text-slate-900">{progress}%</span>
            </div>
            <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
              <div
                class="h-full rounded-full transition-all duration-500"
                style="width: {progress}%; background-color: {project.color ?? '#6366f1'};"
              ></div>
            </div>
            <p class="mt-1.5 text-xs text-slate-500">
              {project.done_tasks_count ?? 0} de {project.tasks_count ?? 0} tareas completadas
            </p>
          </div>
        </div>
      </div>

      <!-- Quick actions -->
      <div class="grid gap-3 sm:grid-cols-2">
        <Link
          href={route('projects.edit', project.uuid)}
          class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-amber-200 hover:shadow-md"
        >
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
            </svg>
          </div>
          <div>
            <p class="font-semibold text-slate-800">Editar proyecto</p>
            <p class="text-xs text-slate-500">Nombre, fechas, color y estado</p>
          </div>
          <svg class="ml-auto h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
          </svg>
        </Link>
      </div>

      <div class="grid gap-3 sm:grid-cols-2">
        <Link
          href={route('projects.tasks.index', project.uuid)}
          class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-200 hover:shadow-md"
        >
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Z" />
            </svg>
          </div>
          <div>
            <p class="font-semibold text-slate-800">Lista de tareas</p>
            <p class="text-xs text-slate-500">{project.tasks_count ?? 0} tareas en total</p>
          </div>
          <svg class="ml-auto h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
          </svg>
        </Link>

        <Link
          href={route('projects.tasks.kanban', project.uuid)}
          class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-violet-200 hover:shadow-md"
        >
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
            </svg>
          </div>
          <div>
            <p class="font-semibold text-slate-800">Tablero Kanban</p>
            <p class="text-xs text-slate-500">Vista visual por columnas</p>
          </div>
          <svg class="ml-auto h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
          </svg>
        </Link>
      </div>

    </div>

    <!-- Sidebar: Members -->
    <div class="space-y-4">
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
          <h2 class="font-semibold text-slate-900">Miembros del proyecto</h2>
          <p class="mt-0.5 text-xs text-slate-500">{members.length} miembro{members.length !== 1 ? 's' : ''}</p>
        </div>

        <!-- Members list -->
        <div class="divide-y divide-slate-100">
          {#each members as member}
            <div class="flex items-center justify-between px-5 py-3">
              <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 text-xs font-bold text-white">
                  {getInitials(member.name)}
                </div>
                <div>
                  <p class="text-sm font-medium text-slate-800">{member.name}</p>
                  <p class="text-xs text-slate-400">{member.roles?.[0]?.name ?? 'member'}</p>
                </div>
              </div>
              <button
                onclick={() => removeMember(member)}
                class="rounded-lg p-1.5 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600"
                title="Remover miembro"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          {/each}

          {#if members.length === 0}
            <p class="px-5 py-4 text-sm text-slate-400">Sin miembros aún.</p>
          {/if}
        </div>

        <!-- Add member -->
        {#if available.length > 0}
          <div class="border-t border-slate-100 p-4">
            <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Agregar miembro</p>
            <div class="flex gap-2">
              <select
                bind:value={$addForm.user_id}
                class="flex-1 rounded-xl border border-slate-300 bg-white py-2 px-3 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
              >
                <option value="">Seleccionar usuario…</option>
                {#each available as u}
                  <option value={u.id}>{u.name}</option>
                {/each}
              </select>
              <button
                onclick={addMember}
                disabled={!$addForm.user_id || $addForm.processing}
                class="rounded-xl bg-indigo-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-50"
              >
                {#if $addForm.processing}
                  <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                  </svg>
                {:else}
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                  </svg>
                {/if}
              </button>
            </div>
          </div>
        {:else}
          <p class="border-t border-slate-100 px-5 py-3 text-xs text-slate-400">Todos los usuarios ya son miembros.</p>
        {/if}
      </div>

      <!-- Nueva tarea shortcut -->
      <Link
        href={route('projects.tasks.create', project.uuid)}
        class="flex w-full items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-slate-200 bg-white py-4 text-sm font-semibold text-slate-500 transition hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-600"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Crear nueva tarea
      </Link>
    </div>

  </div>
</Layout>
