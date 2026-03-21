<script lang="ts">
  import Layout from '../Layout.svelte';
  import { Link, useForm } from '@inertiajs/svelte';
  import route from 'ziggy-js';

  export let user: any;
  export let roles: any[];
  export let userRole: string;

  const form = useForm({
    name:                  user.name,
    email:                 user.email,
    password:              '',
    password_confirmation: '',
    role:                  userRole ?? roles[0]?.name ?? 'member',
  });

  const roleInfo: Record<string, { desc: string; icon: string }> = {
    admin:   { desc: 'Acceso total: puede gestionar usuarios, proyectos y tareas de todo el sistema.', icon: '🛡️' },
    manager: { desc: 'Puede crear y gestionar proyectos y tareas. No gestiona usuarios.',              icon: '📋' },
    member:  { desc: 'Puede ver proyectos asignados y trabajar en sus tareas.',                       icon: '👤' },
  };

  let showPasswordSection = false;

  const submit = async (event: Event) => {
    event.preventDefault();
    await $form.put(route('admin.users.update', user.uuid));
  };
</script>

<Layout title="Editar usuario">

  <!-- Breadcrumb -->
  <nav class="mb-6 flex items-center gap-2 text-sm text-slate-500">
    <Link href={route('admin.users.index')} class="hover:text-slate-700">Usuarios</Link>
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
    </svg>
    <span class="font-medium text-slate-900">Editar {user.name}</span>
  </nav>

  <div class="mx-auto max-w-2xl">

    <!-- User summary card -->
    <div class="mb-6 flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
      <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-lg font-bold text-white">
        {user.name.split(' ').map((n: string) => n[0]).slice(0, 2).join('').toUpperCase()}
      </div>
      <div>
        <p class="font-semibold text-slate-900">{user.name}</p>
        <p class="text-sm text-slate-500">{user.email}</p>
        <p class="mt-1 text-xs text-slate-400">UUID: {user.uuid}</p>
      </div>
    </div>

    <form onsubmit={(e) => { e.preventDefault(); submit(); }} class="space-y-6">

      <!-- Datos personales -->
      <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-5 flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-slate-500">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
          </svg>
          Datos personales
        </h2>
        <div class="space-y-4">
          <div>
            <label for="name" class="block text-sm font-medium text-slate-700">Nombre completo <span class="text-rose-500">*</span></label>
            <input
              id="name"
              type="text"
              bind:value={$form.name}
              class="mt-1.5 block w-full rounded-xl border py-2.5 px-3.5 text-sm shadow-sm transition
                {$form.errors.name ? 'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-500' : 'border-slate-300 bg-white focus:border-indigo-500 focus:ring-indigo-500'}
                focus:outline-none focus:ring-2 focus:ring-offset-0"
              required
            />
            {#if $form.errors.name}
              <p class="mt-1.5 text-xs text-rose-600">{$form.errors.name}</p>
            {/if}
          </div>
          <div>
            <label for="email" class="block text-sm font-medium text-slate-700">Correo electrónico <span class="text-rose-500">*</span></label>
            <input
              id="email"
              type="email"
              bind:value={$form.email}
              class="mt-1.5 block w-full rounded-xl border py-2.5 px-3.5 text-sm shadow-sm transition
                {$form.errors.email ? 'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-500' : 'border-slate-300 bg-white focus:border-indigo-500 focus:ring-indigo-500'}
                focus:outline-none focus:ring-2 focus:ring-offset-0"
              required
            />
            {#if $form.errors.email}
              <p class="mt-1.5 text-xs text-rose-600">{$form.errors.email}</p>
            {/if}
          </div>
        </div>
      </div>

      <!-- Cambiar contraseña (opcional) -->
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <button
          type="button"
          onclick={() => { showPasswordSection = !showPasswordSection; if (!showPasswordSection) { $form.password = ''; $form.password_confirmation = ''; } }}
          class="flex w-full items-center justify-between px-6 py-4 text-left transition hover:bg-slate-50"
        >
          <span class="flex items-center gap-2 text-sm font-semibold text-slate-700">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
            Cambiar contraseña
            <span class="text-xs font-normal text-slate-400">(opcional)</span>
          </span>
          <svg class="h-4 w-4 text-slate-400 transition-transform {showPasswordSection ? 'rotate-180' : ''}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
          </svg>
        </button>

        {#if showPasswordSection}
          <div class="border-t border-slate-100 px-6 pb-6 pt-5">
            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label for="password" class="block text-sm font-medium text-slate-700">Nueva contraseña</label>
                <input
                  id="password"
                  type="password"
                  autocomplete="new-password"
                  placeholder="Mín. 8 caracteres"
                  bind:value={$form.password}
                  class="mt-1.5 block w-full rounded-xl border py-2.5 px-3.5 text-sm shadow-sm transition
                    {$form.errors.password ? 'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-500' : 'border-slate-300 bg-white focus:border-indigo-500 focus:ring-indigo-500'}
                    focus:outline-none focus:ring-2 focus:ring-offset-0"
                />
                {#if $form.errors.password}
                  <p class="mt-1.5 text-xs text-rose-600">{$form.errors.password}</p>
                {/if}
              </div>
              <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirmar</label>
                <input
                  id="password_confirmation"
                  type="password"
                  autocomplete="new-password"
                  placeholder="Repite la contraseña"
                  bind:value={$form.password_confirmation}
                  class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white py-2.5 px-3.5 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
                />
              </div>
            </div>
          </div>
        {/if}
      </div>

      <!-- Rol -->
      <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-5 flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-slate-500">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
          </svg>
          Rol y permisos
        </h2>

        <div class="grid gap-3 sm:grid-cols-3">
          {#each roles as roleOpt}
            {@const info = roleInfo[roleOpt.name] ?? { desc: '', icon: '👤' }}
            <label
              class="relative flex cursor-pointer flex-col gap-2 rounded-xl border-2 p-4 transition
                {$form.role === roleOpt.name ? 'border-indigo-500 bg-indigo-50' : 'border-slate-200 bg-white hover:border-slate-300'}"
            >
              <input type="radio" bind:group={$form.role} value={roleOpt.name} class="sr-only" />
              <span class="text-xl">{info.icon}</span>
              <span class="font-semibold text-sm capitalize {$form.role === roleOpt.name ? 'text-indigo-700' : 'text-slate-800'}">{roleOpt.name}</span>
              <span class="text-xs text-slate-500 leading-relaxed">{info.desc}</span>
              {#if $form.role === roleOpt.name}
                <div class="absolute right-3 top-3 flex h-4 w-4 items-center justify-center rounded-full bg-indigo-600">
                  <svg class="h-2.5 w-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                  </svg>
                </div>
              {/if}
            </label>
          {/each}
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-end gap-3">
        <Link href={route('admin.users.index')} class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
          Cancelar
        </Link>
        <button
          type="submit"
          disabled={$form.processing}
          class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:from-indigo-700 hover:to-violet-700 disabled:opacity-60"
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
