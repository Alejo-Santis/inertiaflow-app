<script lang="ts">
  import Layout from '../Layout.svelte';
  import { Link, useForm } from '@inertiajs/svelte';
  import route from 'ziggy-js';

  let { project, members = [], labels = [] }: { project: any; members?: any[]; labels?: { id: number; name: string; color: string }[] } = $props();

  const form = useForm({
    title: '',
    description: '',
    priority: 2,
    status: 'todo',
    due_date: '',
    estimated_hours: null,
    meeting_url: '',
    assignees: [] as number[],
    label_ids: [] as number[],
  });

  function toggleAssignee(id: number) {
    const current: number[] = $form.assignees ?? [];
    if (current.includes(id)) {
      $form.assignees = current.filter((x: number) => x !== id);
    } else {
      $form.assignees = [...current, id];
    }
  }

  function toggleLabel(id: number) {
    const current: number[] = $form.label_ids ?? [];
    if (current.includes(id)) {
      $form.label_ids = current.filter((x: number) => x !== id);
    } else {
      $form.label_ids = [...current, id];
    }
  }

  const avatarColors = [
    'bg-indigo-500', 'bg-violet-500', 'bg-emerald-500', 'bg-amber-500',
    'bg-rose-500',   'bg-cyan-500',   'bg-pink-500',    'bg-teal-500',
  ];
  function avatarColor(id: number): string { return avatarColors[id % avatarColors.length]; }
  function getInitials(name: string): string { return name.split(' ').map((n: string) => n[0]).join('').toUpperCase().slice(0, 2); }

  const statusOptions = [
    { value: 'todo',        label: 'Por hacer' },
    { value: 'in_progress', label: 'En progreso' },
    { value: 'in_review',   label: 'En revisión' },
    { value: 'done',        label: 'Hecho' },
    { value: 'cancelled',   label: 'Cancelado' },
  ];

  const priorityOptions = [
    { value: 1, label: 'Baja',    icon: '↓', color: 'text-slate-600' },
    { value: 2, label: 'Media',   icon: '→', color: 'text-blue-600' },
    { value: 3, label: 'Alta',    icon: '↑', color: 'text-amber-600' },
    { value: 4, label: 'Urgente', icon: '⚑', color: 'text-rose-600' },
  ];

  const submit = async (event: Event) => {
    event.preventDefault();
    await $form.post(route('projects.tasks.store', project.uuid));
  };
</script>

<Layout title="Nueva tarea">

  <!-- Breadcrumb -->
  <nav class="mb-6 flex items-center gap-2 text-sm text-slate-500">
    <Link href={route('projects.index')} class="hover:text-slate-700">Proyectos</Link>
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
    </svg>
    <Link href={route('projects.tasks.index', project.uuid)} class="hover:text-slate-700">{project.name}</Link>
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
    </svg>
    <span class="font-medium text-slate-900">Nueva tarea</span>
  </nav>

  <div class="mx-auto max-w-2xl">
    <div class="mb-6">
      <h1 class="text-2xl font-bold tracking-tight text-slate-900">Crear tarea</h1>
      <p class="mt-1 text-sm text-slate-500">
        Proyecto:
        <span class="font-medium text-slate-700">{project.name}</span>
      </p>
    </div>

    <form onsubmit={(e) => { e.preventDefault(); submit(e); }} class="space-y-6">

      <!-- Basic info -->
      <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-5 flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-slate-500">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
          </svg>
          Información de la tarea
        </h2>

        <div class="space-y-4">
          <!-- Title -->
          <div>
            <label for="title" class="block text-sm font-medium text-slate-700">
              Título <span class="text-rose-500">*</span>
            </label>
            <input
              id="title"
              type="text"
              placeholder="ej. Diseñar pantalla de inicio"
              bind:value={$form.title}
              class="mt-1.5 block w-full rounded-xl border py-2.5 px-3.5 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition
                {$form.errors.title ? 'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-500' : 'border-slate-300 bg-white focus:border-indigo-500 focus:ring-indigo-500'}
                focus:outline-none focus:ring-2 focus:ring-offset-0"
              required
            />
            {#if $form.errors.title}
              <p class="mt-1.5 flex items-center gap-1 text-xs text-rose-600">
                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                {$form.errors.title}
              </p>
            {/if}
          </div>

          <!-- Description -->
          <div>
            <label for="description" class="block text-sm font-medium text-slate-700">Descripción</label>
            <textarea
              id="description"
              rows="3"
              placeholder="Describe los detalles de esta tarea..."
              bind:value={$form.description}
              class="mt-1.5 block w-full resize-none rounded-xl border border-slate-300 bg-white py-2.5 px-3.5 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
            ></textarea>
          </div>
        </div>
      </div>

      <!-- Priority selector -->
      <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-5 flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-slate-500">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h14.25M3 9h9.75M3 13.5h9.75m4.5-4.5v12m0 0-3.75-3.75M17.25 21 21 17.25" />
          </svg>
          Prioridad y estado
        </h2>

        <!-- Priority buttons -->
        <div>
          <p class="mb-2 text-sm font-medium text-slate-700">Prioridad</p>
          <div class="grid grid-cols-4 gap-2">
            {#each priorityOptions as opt}
              <button
                type="button"
                onclick={() => ($form.priority = opt.value)}
                class="flex flex-col items-center gap-1 rounded-xl border-2 py-3 px-2 text-center transition
                  {$form.priority === opt.value
                    ? 'border-indigo-500 bg-indigo-50'
                    : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50'}"
              >
                <span class="text-lg {opt.color}">{opt.icon}</span>
                <span class="text-xs font-medium {$form.priority === opt.value ? 'text-indigo-700' : 'text-slate-600'}">{opt.label}</span>
              </button>
            {/each}
          </div>
        </div>

        <!-- Status -->
        <div class="mt-4">
          <label for="status" class="block text-sm font-medium text-slate-700">Estado</label>
          <select
            id="status"
            bind:value={$form.status}
            class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white py-2.5 px-3.5 text-sm text-slate-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
          >
            {#each statusOptions as opt}
              <option value={opt.value}>{opt.label}</option>
            {/each}
          </select>
        </div>
      </div>

      <!-- Dates card -->
      <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-5 flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-slate-500">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
          </svg>
          Planificación
        </h2>

        <div class="grid gap-4 sm:grid-cols-2">
          <div>
            <label for="due_date" class="block text-sm font-medium text-slate-700">Fecha límite</label>
            <input
              id="due_date"
              type="date"
              bind:value={$form.due_date}
              class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white py-2.5 px-3.5 text-sm text-slate-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
            />
          </div>
          <div>
            <label for="estimated_hours" class="block text-sm font-medium text-slate-700">Horas estimadas</label>
            <div class="relative mt-1.5">
              <input
                id="estimated_hours"
                type="number"
                min="0"
                step="0.5"
                placeholder="0"
                bind:value={$form.estimated_hours}
                class="block w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-3.5 pr-12 text-sm text-slate-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
              />
              <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5">
                <span class="text-xs text-slate-400">horas</span>
              </div>
            </div>
          </div>
          <div class="sm:col-span-2">
            <label for="meeting_url" class="block text-sm font-medium text-slate-700">Enlace de reunión</label>
            <div class="relative mt-1.5">
              <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                </svg>
              </div>
              <input
                id="meeting_url"
                type="url"
                placeholder="https://meet.google.com/... o https://zoom.us/..."
                bind:value={$form.meeting_url}
                class="block w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-10 pr-3.5 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0
                  {$form.errors.meeting_url ? 'border-rose-300 bg-rose-50' : ''}"
              />
            </div>
            {#if $form.errors.meeting_url}
              <p class="mt-1 text-xs text-rose-600">{$form.errors.meeting_url}</p>
            {/if}
          </div>
        </div>
      </div>

      <!-- Labels card -->
      {#if labels.length}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
          <h2 class="mb-4 flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-slate-500">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
            </svg>
            Etiquetas
          </h2>
          <div class="flex flex-wrap gap-2">
            {#each labels as label}
              {@const selected = ($form.label_ids ?? []).includes(label.id)}
              <button
                type="button"
                onclick={() => toggleLabel(label.id)}
                class="inline-flex items-center gap-1.5 rounded-full border-2 px-3 py-1 text-xs font-semibold transition
                  {selected ? 'border-transparent text-white shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300'}"
                style={selected ? `background-color: ${label.color}; border-color: ${label.color};` : ''}
              >
                <span class="h-2 w-2 rounded-full" style="background-color: {label.color}; {selected ? 'background-color: rgba(255,255,255,0.6)' : ''}"></span>
                {label.name}
              </button>
            {/each}
          </div>
        </div>
      {/if}

      <!-- Assignees card -->
      {#if members.length}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
          <h2 class="mb-5 flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-slate-500">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
            Asignar miembros
          </h2>
          <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
            {#each members as member}
              {@const selected = ($form.assignees ?? []).includes(member.id)}
              <button
                type="button"
                onclick={() => toggleAssignee(member.id)}
                class="flex items-center gap-3 rounded-xl border-2 px-3 py-2.5 text-left transition
                  {selected ? 'border-indigo-500 bg-indigo-50' : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50'}"
              >
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold text-white {avatarColor(member.id)}">
                  {getInitials(member.name)}
                </div>
                <span class="flex-1 truncate text-sm font-medium {selected ? 'text-indigo-700' : 'text-slate-700'}">{member.name}</span>
                {#if selected}
                  <svg class="h-4 w-4 text-indigo-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/>
                  </svg>
                {/if}
              </button>
            {/each}
          </div>
        </div>
      {/if}

      <!-- Actions -->
      <div class="flex items-center justify-end gap-3">
        <Link
          href={route('projects.tasks.index', project.uuid)}
          class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50"
        >
          Cancelar
        </Link>
        <button
          type="submit"
          disabled={$form.processing}
          class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:from-indigo-700 hover:to-violet-700 hover:shadow focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-60"
        >
          {#if $form.processing}
            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Creando...
          {:else}
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Crear tarea
          {/if}
        </button>
      </div>
    </form>
  </div>

</Layout>
