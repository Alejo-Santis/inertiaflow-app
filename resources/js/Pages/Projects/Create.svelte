<script lang="ts">
  import Layout from '../Layout.svelte';
  import { Link, useForm } from '@inertiajs/svelte';
  import route from 'ziggy-js';

  const form = useForm({
    name: '',
    description: '',
    start_date: '',
    end_date: '',
    status: 'active',
    color: '#6366f1',
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

  const submit = async (event: Event) => {
    event.preventDefault();
    await $form.post(route('projects.store'));
  };
</script>

<Layout title="Nuevo proyecto">

  <!-- Breadcrumb -->
  <nav class="mb-6 flex items-center gap-2 text-sm text-slate-500">
    <Link href={route('projects.index')} class="hover:text-slate-700">Proyectos</Link>
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
    </svg>
    <span class="font-medium text-slate-900">Nuevo proyecto</span>
  </nav>

  <div class="mx-auto max-w-2xl">
    <div class="mb-6">
      <h1 class="text-2xl font-bold tracking-tight text-slate-900">Crear proyecto</h1>
      <p class="mt-1 text-sm text-slate-500">Completa la información para crear tu nuevo proyecto</p>
    </div>

    <form onsubmit={(e) => { e.preventDefault(); submit(); }} class="space-y-6">

      <!-- Basic info card -->
      <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-5 flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-slate-500">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
          </svg>
          Información básica
        </h2>

        <div class="space-y-4">
          <!-- Name -->
          <div>
            <label for="name" class="block text-sm font-medium text-slate-700">
              Nombre del proyecto <span class="text-rose-500">*</span>
            </label>
            <input
              id="name"
              type="text"
              placeholder="ej. Rediseño de app móvil"
              bind:value={$form.name}
              class="mt-1.5 block w-full rounded-xl border py-2.5 px-3.5 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition
                {$form.errors.name ? 'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-500' : 'border-slate-300 bg-white focus:border-indigo-500 focus:ring-indigo-500'}
                focus:outline-none focus:ring-2 focus:ring-offset-0"
              required
            />
            {#if $form.errors.name}
              <p class="mt-1.5 flex items-center gap-1 text-xs text-rose-600">
                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                {$form.errors.name}
              </p>
            {/if}
          </div>

          <!-- Description -->
          <div>
            <label for="description" class="block text-sm font-medium text-slate-700">Descripción</label>
            <textarea
              id="description"
              rows="3"
              placeholder="Describe brevemente el objetivo del proyecto..."
              bind:value={$form.description}
              class="mt-1.5 block w-full resize-none rounded-xl border border-slate-300 bg-white py-2.5 px-3.5 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
            ></textarea>
          </div>
        </div>
      </div>

      <!-- Dates + Status card -->
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
            <input
              id="start_date"
              type="date"
              bind:value={$form.start_date}
              class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white py-2.5 px-3.5 text-sm text-slate-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
            />
          </div>
          <div>
            <label for="end_date" class="block text-sm font-medium text-slate-700">Fecha de fin</label>
            <input
              id="end_date"
              type="date"
              bind:value={$form.end_date}
              class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white py-2.5 px-3.5 text-sm text-slate-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
            />
            {#if $form.errors.end_date}
              <p class="mt-1.5 text-xs text-rose-600">{$form.errors.end_date}</p>
            {/if}
          </div>
          <div class="sm:col-span-2">
            <label for="status" class="block text-sm font-medium text-slate-700">Estado inicial</label>
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
      </div>

      <!-- Color card -->
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
            <input
              type="color"
              bind:value={$form.color}
              class="h-8 w-8 cursor-pointer rounded-full border-2 border-slate-300 p-0 shadow-sm"
              title="Color personalizado"
            />
          </div>

          <!-- Preview -->
          <div class="ml-auto flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-1.5">
            <div class="h-4 w-4 rounded-full shadow-sm" style="background-color: {$form.color};"></div>
            <span class="text-xs font-mono text-slate-600">{$form.color}</span>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-end gap-3">
        <Link
          href={route('projects.index')}
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
            Crear proyecto
          {/if}
        </button>
      </div>
    </form>
  </div>

</Layout>
