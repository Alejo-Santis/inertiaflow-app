<script lang="ts">
  import Layout from '../Layout.svelte';
  import { Link, useForm } from '@inertiajs/svelte';
  import route from 'ziggy-js';

  const form = useForm({ name: '', description: '' });

  function submit() {
    $form.post(route('organizations.store'));
  }
</script>

<Layout title="Nueva organización">

  <nav class="mb-6 flex items-center gap-2 text-sm text-slate-500">
    <Link href={route('organizations.index')} class="hover:text-slate-700">Organizaciones</Link>
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
    </svg>
    <span class="font-medium text-slate-900">Nueva organización</span>
  </nav>

  <div class="mx-auto max-w-xl">
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
      <div class="border-b border-slate-100 px-6 py-5">
        <h1 class="text-lg font-semibold text-slate-900">Crear organización</h1>
        <p class="mt-0.5 text-sm text-slate-500">Define el espacio de trabajo para tu empresa o equipo.</p>
      </div>

      <div class="space-y-5 p-6">
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700">
            Nombre de la organización *
          </label>
          <input
            bind:value={$form.name}
            type="text"
            placeholder="Ej: ACME Tech, Startup Labs…"
            class="w-full rounded-xl border border-slate-300 py-2.5 px-3.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />
          {#if $form.errors.name}
            <p class="mt-1 text-xs text-rose-600">{$form.errors.name}</p>
          {/if}
        </div>

        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700">Descripción</label>
          <textarea
            bind:value={$form.description}
            rows="3"
            placeholder="¿Qué hace esta organización?…"
            class="w-full resize-none rounded-xl border border-slate-300 py-2.5 px-3.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          ></textarea>
        </div>
      </div>

      <div class="flex justify-end gap-3 border-t border-slate-100 px-6 py-4">
        <Link
          href={route('organizations.index')}
          class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
        >Cancelar</Link>
        <button
          onclick={submit}
          disabled={!$form.name || $form.processing}
          class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:from-indigo-700 hover:to-violet-700 disabled:opacity-50"
        >
          {#if $form.processing}
            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
          {/if}
          Crear organización
        </button>
      </div>
    </div>
  </div>

</Layout>
