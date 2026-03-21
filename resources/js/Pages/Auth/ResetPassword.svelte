<script lang="ts">
  import { Link, useForm } from '@inertiajs/svelte';
  import route from 'ziggy-js';

  export let token: string;
  export let email: string = '';

  const form = useForm({
    token,
    email,
    password: '',
    password_confirmation: '',
  });

  const submit = (e: Event) => {
    e.preventDefault();
    $form.post(route('password.update'), {
      onSuccess: () => $form.reset('password', 'password_confirmation'),
    });
  };
</script>

<svelte:head>
  <title>Nueva contraseña — InertiaFlow</title>
</svelte:head>

<div class="flex min-h-screen bg-slate-50 font-sans">

  <!-- Left panel -->
  <div class="relative hidden w-1/2 overflow-hidden bg-gradient-to-br from-indigo-600 via-indigo-700 to-violet-800 lg:flex lg:flex-col lg:justify-between p-12">
    <div class="absolute inset-0 opacity-10"
      style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 32px 32px;">
    </div>
    <div class="relative">
      <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
          <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
          </svg>
        </div>
        <span class="text-xl font-bold text-white">InertiaFlow</span>
      </div>
    </div>
    <div class="relative">
      <h2 class="text-4xl font-bold leading-tight text-white">
        Elige una nueva<br />contraseña segura.
      </h2>
      <p class="mt-4 text-lg text-indigo-200">
        Usa al menos 8 caracteres. Te recomendamos combinar letras, números y símbolos.
      </p>
    </div>
    <div class="relative text-sm text-indigo-300">
      © {new Date().getFullYear()} InertiaFlow. Todos los derechos reservados.
    </div>
  </div>

  <!-- Right panel -->
  <div class="flex flex-1 flex-col items-center justify-center px-6 py-12">
    <div class="w-full max-w-md">

      <!-- Mobile logo -->
      <div class="mb-8 flex items-center gap-2 lg:hidden">
        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600">
          <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
          </svg>
        </div>
        <span class="text-lg font-bold text-slate-900">InertiaFlow</span>
      </div>

      <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Restablecer contraseña</h1>
        <p class="mt-1.5 text-sm text-slate-500">Ingresa y confirma tu nueva contraseña.</p>
      </div>

      <form onsubmit={submit} class="mt-8 space-y-5">

        <!-- Email (read-only, pre-filled) -->
        <div>
          <label for="email" class="block text-sm font-medium text-slate-700">Correo electrónico</label>
          <input
            id="email"
            type="email"
            bind:value={$form.email}
            class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-600 shadow-sm focus:outline-none"
            readonly
          />
          {#if $form.errors.email}
            <p class="mt-1.5 text-xs text-rose-600">{$form.errors.email}</p>
          {/if}
        </div>

        <!-- New password -->
        <div>
          <label for="password" class="block text-sm font-medium text-slate-700">Nueva contraseña</label>
          <div class="relative mt-1.5">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
              <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
              </svg>
            </div>
            <input
              id="password"
              type="password"
              autocomplete="new-password"
              placeholder="Mínimo 8 caracteres"
              bind:value={$form.password}
              class="block w-full rounded-xl border py-2.5 pl-10 pr-4 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition
                {$form.errors.password
                  ? 'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-500'
                  : 'border-slate-300 bg-white focus:border-indigo-500 focus:ring-indigo-500'}
                focus:outline-none focus:ring-2 focus:ring-offset-0"
              required
            />
          </div>
          {#if $form.errors.password}
            <p class="mt-1.5 text-xs text-rose-600">{$form.errors.password}</p>
          {/if}
        </div>

        <!-- Confirm password -->
        <div>
          <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirmar contraseña</label>
          <div class="relative mt-1.5">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
              <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
              </svg>
            </div>
            <input
              id="password_confirmation"
              type="password"
              autocomplete="new-password"
              placeholder="Repite la contraseña"
              bind:value={$form.password_confirmation}
              class="block w-full rounded-xl border py-2.5 pl-10 pr-4 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition
                {$form.errors.password_confirmation
                  ? 'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-500'
                  : 'border-slate-300 bg-white focus:border-indigo-500 focus:ring-indigo-500'}
                focus:outline-none focus:ring-2 focus:ring-offset-0"
              required
            />
          </div>
          {#if $form.errors.password_confirmation}
            <p class="mt-1.5 text-xs text-rose-600">{$form.errors.password_confirmation}</p>
          {/if}
        </div>

        <button
          type="submit"
          disabled={$form.processing}
          class="relative flex w-full items-center justify-center gap-2 overflow-hidden rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:from-indigo-700 hover:to-violet-700 hover:shadow focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-60"
        >
          {#if $form.processing}
            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Guardando...
          {:else}
            Restablecer contraseña
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
            </svg>
          {/if}
        </button>
      </form>

      <p class="mt-6 text-center text-sm text-slate-500">
        <Link href={route('login')} class="font-medium text-indigo-600 hover:text-indigo-700">
          ← Volver al inicio de sesión
        </Link>
      </p>
    </div>
  </div>
</div>
