<script lang="ts">
  import Layout from '../Layout.svelte';
  import { Link, useForm, router } from '@inertiajs/svelte';
  import route from 'ziggy-js';

  export let project: any;

  const form = useForm({
    name:        project.name,
    description: project.description ?? '',
    start_date:  project.start_date  ?? '',
    end_date:    project.end_date    ?? '',
    status:      project.status,
    color:       project.color ?? '#6366f1',
  });

  const statusOptions = [
    { value: 'active',    label: 'Activo' },
    { value: 'on_hold',   label: 'En espera' },
    { value: 'completed', label: 'Completado' },
    { value: 'cancelled', label: 'Cancelado' },
  ];

  const colorPresets = [
    '#6366f1', '#8b5cf6', '#ec4899', '#ef4444',
    '#f97316', '#eab308', '#22c55e', '#06b6d4',
    '#3b82f6', '#14b8a6', '#64748b', '#1e293b',
  ];

  let confirmingDelete = false;

  function submit() {
    $form.put(route('projects.update', project.uuid));
  }

  function deleteProject() {
    router.delete(route('projects.destroy', project.uuid));
  }
</script>

<Layout title="Editar proyecto">

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
    <span class="font-medium text-slate-900">Editar</span>
  </nav>

  <div class="mx-auto max-w-2xl">
    <div class="mb-6 flex items-start justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Editar proyecto</h1>
        <p class="mt-1 text-sm text-slate-500">Actualiza la información del proyecto</p>
      </div>

      <!-- Delete -->
      {#if confirmingDelete}
        <div class="flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2">
          <span class="text-xs font-medium text-rose-700">¿Eliminar proyecto y todas sus tareas?</span>
          <button
            type="button"
            onclick={deleteProject}
            class="rounded-lg bg-rose-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-rose-700"
          >Sí, eliminar</button>
          <button
            type="button"
            onclick={() => confirmingDelete = false}
            class="rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50"
          >Cancelar</button>
        </div>
      {:else}
        <button
          type="button"
          onclick={() => confirmingDelete = true}
          class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-white px-3 py-2 text-sm font-medium text-rose-600 shadow-sm transition hover:bg-rose-50"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
          </svg>
          Eliminar
        </button>
      {/if}
    </div>

    <form onsubmit={(e) => { e.preventDefault(); submit(); }} class="space-y-6">

      <!-- Basic info -->
      <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-5 flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-slate-500">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
          </svg>
          Información básica
        </h2>
        <div class="space-y-4">
          <div>
            <label for="name" class="block text-sm font-medium text-slate-700">Nombre <span class="text-rose-500">*</span></label>
            <input
              id="name"
              type="text"
              bind:value={$form.name}
              class="mt-1.5 block w-full rounded-xl border py-2.5 px-3.5 text-sm text-slate-900 shadow-sm transition
                {$form.errors.name ? 'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-500' : 'border-slate-300 bg-white focus:border-indigo-500 focus:ring-indigo-500'}
                focus:outline-none focus:ring-2 focus:ring-offset-0"
              required
            />
            {#if $form.errors.name}
              <p class="mt-1.5 text-xs text-rose-600">{$form.errors.name}</p>
            {/if}
          </div>
          <div>
            <label for="description" class="block text-sm font-medium text-slate-700">Descripción</label>
            <textarea
              id="description"
              rows="3"
              bind:value={$form.description}
              class="mt-1.5 block w-full resize-none rounded-xl border border-slate-300 bg-white py-2.5 px-3.5 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
            ></textarea>
          </div>
        </div>
      </div>

      <!-- Dates + Status -->
      <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-5 flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-slate-500">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
          </svg>
          Fechas y estado
        </h2>
        <div class="grid gap-4 sm:grid-cols-2">
          <div>
            <label for="start_date" class="block text-sm font-medium text-slate-700">Fecha de inicio</label>
            <input id="start_date" type="date" bind:value={$form.start_date}
              class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white py-2.5 px-3.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0" />
          </div>
          <div>
            <label for="end_date" class="block text-sm font-medium text-slate-700">Fecha de fin</label>
            <input id="end_date" type="date" bind:value={$form.end_date}
              class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white py-2.5 px-3.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0" />
            {#if $form.errors.end_date}
              <p class="mt-1.5 text-xs text-rose-600">{$form.errors.end_date}</p>
            {/if}
          </div>
          <div class="sm:col-span-2">
            <label for="status" class="block text-sm font-medium text-slate-700">Estado</label>
            <select id="status" bind:value={$form.status}
              class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white py-2.5 px-3.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0">
              {#each statusOptions as opt}
                <option value={opt.value}>{opt.label}</option>
              {/each}
            </select>
          </div>
        </div>
      </div>

      <!-- Color -->
      <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-5 flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-slate-500">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.098 19.902a3.75 3.75 0 0 0 5.304 0l6.401-6.402M6.75 21A3.75 3.75 0 0 1 3 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 0 0 3.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072M10.5 8.197l2.88-2.88c.438-.439 1.15-.439 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008Z" />
          </svg>
          Color del proyecto
        </h2>
        <div class="flex flex-wrap items-center gap-3">
          {#each colorPresets as preset}
            <button
              type="button"
              onclick={() => ($form.color = preset)}
              class="h-8 w-8 rounded-full shadow-sm transition hover:scale-110 focus:outline-none focus:ring-2 focus:ring-offset-2
                {$form.color === preset ? 'ring-2 ring-offset-2 scale-110' : ''}"
              style="background-color: {preset}; --tw-ring-color: {preset};"
              title={preset}
            ></button>
          {/each}
          <div class="relative ml-1">
            <input type="color" bind:value={$form.color}
              class="h-8 w-8 cursor-pointer rounded-full border-2 border-slate-300 p-0 shadow-sm" />
          </div>
          <div class="ml-auto flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-1.5">
            <div class="h-4 w-4 rounded-full shadow-sm" style="background-color: {$form.color};"></div>
            <span class="font-mono text-xs text-slate-600">{$form.color}</span>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-end gap-3">
        <Link
          href={route('projects.show', project.uuid)}
          class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50"
        >
          Cancelar
        </Link>
        <button
          type="submit"
          disabled={$form.processing}
          class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:from-indigo-700 hover:to-violet-700 hover:shadow disabled:opacity-60"
        >
          {#if $form.processing}
            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Guardando...
          {:else}
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            Guardar cambios
          {/if}
        </button>
      </div>
    </form>
  </div>

</Layout>
