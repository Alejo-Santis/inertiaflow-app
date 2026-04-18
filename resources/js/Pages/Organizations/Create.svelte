<script lang="ts">
  import Layout from '../Layout.svelte';
  import { Link, useForm } from '@inertiajs/svelte';
  import route from 'ziggy-js';
  import { calcDV } from '../../lib/nitDV';

  const colorPresets = [
    '#6366f1', '#8b5cf6', '#ec4899', '#ef4444',
    '#f97316', '#eab308', '#22c55e', '#06b6d4',
    '#3b82f6', '#14b8a6', '#64748b', '#1e293b',
  ];

  const form = useForm({ name: '', nit: '', dv: '', description: '', color: '#6366f1' });

  const onNitInput = () => {
    $form.nit = $form.nit.replace(/\D/g, '');
    $form.dv  = calcDV($form.nit);
  };

  const submit = () => $form.post(route('organizations.store'));
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

        <!-- Nombre -->
        <div>
          <label for="org-name" class="mb-1.5 block text-sm font-medium text-slate-700">
            Nombre de la organización *
          </label>
          <input
            id="org-name"
            bind:value={$form.name}
            type="text"
            placeholder="Ej: ACME Tech, Startup Labs…"
            class="w-full rounded-xl border border-slate-300 py-2.5 px-3.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />
          {#if $form.errors.name}
            <p class="mt-1 text-xs text-rose-600">{$form.errors.name}</p>
          {/if}
        </div>

        <!-- NIT + DV -->
        <div>
          <label for="org-nit" class="mb-1.5 block text-sm font-medium text-slate-700">
            NIT *
            <span class="ml-1 text-[10px] font-normal text-slate-400">(sin dígito de verificación)</span>
          </label>
          <div class="flex items-start gap-3">
            <div class="flex-1">
              <input
                id="org-nit"
                bind:value={$form.nit}
                oninput={onNitInput}
                type="text"
                inputmode="numeric"
                maxlength="15"
                placeholder="Ej: 901249232"
                class="w-full rounded-xl border border-slate-300 py-2.5 px-3.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
              />
              {#if $form.errors.nit}
                <p class="mt-1 text-xs text-rose-600">{$form.errors.nit}</p>
              {/if}
            </div>

            <div class="w-24 shrink-0">
              <label for="org-dv" class="mb-1.5 block text-[11px] font-medium text-slate-500">DV</label>
              <input
                id="org-dv"
                value={$form.dv}
                type="text"
                disabled
                placeholder="—"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 px-3.5 text-center text-sm font-bold text-slate-700 cursor-not-allowed"
              />
            </div>
          </div>
          <p class="mt-1.5 text-[11px] text-slate-400">
            El dígito de verificación se calcula automáticamente según el algoritmo DIAN.
          </p>
        </div>

        <!-- Descripción -->
        <div>
          <label for="org-description" class="mb-1.5 block text-sm font-medium text-slate-700">Descripción</label>
          <textarea
            id="org-description"
            bind:value={$form.description}
            rows="3"
            placeholder="¿Qué hace esta organización?…"
            class="w-full resize-none rounded-xl border border-slate-300 py-2.5 px-3.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          ></textarea>
        </div>

        <!-- Color -->
        <div>
          <label for="org-color-picker" class="mb-1.5 block text-sm font-medium text-slate-700">Color de la organización</label>
          <div class="flex flex-wrap items-center gap-2">
            {#each colorPresets as preset}
              <button
                type="button"
                onclick={() => ($form.color = preset)}
                class="h-7 w-7 rounded-lg shadow-sm transition hover:scale-110 focus:outline-none focus:ring-2 focus:ring-offset-2
                  {$form.color === preset ? 'ring-2 ring-offset-2 scale-110' : ''}"
                style="background-color: {preset}; --tw-ring-color: {preset};"
                title={preset}
              ></button>
            {/each}
            <input
              id="org-color-picker"
              type="color"
              bind:value={$form.color}
              class="h-7 w-7 cursor-pointer rounded-lg border-2 border-slate-300 p-0 shadow-sm"
              title="Color personalizado"
            />
            <span class="ml-1 flex items-center gap-1.5 rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-mono text-slate-600">
              <span class="inline-block h-3 w-3 rounded-full" style="background-color: {$form.color};"></span>
              {$form.color}
            </span>
          </div>
        </div>

      </div>

      <div class="flex justify-end gap-3 border-t border-slate-100 px-6 py-4">
        <Link
          href={route('organizations.index')}
          class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
        >Cancelar</Link>
        <button
          onclick={submit}
          disabled={!$form.name || !$form.nit || $form.processing}
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
