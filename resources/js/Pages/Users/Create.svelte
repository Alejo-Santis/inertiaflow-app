<script lang="ts">
  import Layout from '../Layout.svelte';
  import { Link, useForm } from '@inertiajs/svelte';
  import route from 'ziggy-js';

  export let roles: any[];

  const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: roles[0]?.name ?? 'member',
  });

  const roleInfo: Record<string, { desc: string; icon: string }> = {
    admin:   { desc: 'Acceso total: puede gestionar usuarios, proyectos y tareas de todo el sistema.', icon: '🛡️' },
    manager: { desc: 'Puede crear y gestionar proyectos y tareas. No gestiona usuarios.',              icon: '📋' },
    member:  { desc: 'Puede ver proyectos asignados y trabajar en sus tareas.',                       icon: '👤' },
  };

  $: selectedRoleInfo = roleInfo[$form.role] ?? { desc: '', icon: '👤' };

  const submit = async (event: Event) => {
    event.preventDefault();
    await $form.post(route('admin.users.store'), {
      onSuccess: () => $form.reset(),
    });
  };
</script>

<Layout title="Nuevo usuario">

  <!-- Breadcrumb -->
  <nav class="mb-6 flex items-center gap-2 text-sm text-slate-500">
    <Link href={route('admin.users.index')} class="hover:text-slate-700">Usuarios</Link>
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
    </svg>
    <span class="font-medium text-slate-900">Nuevo usuario</span>
  </nav>

  <div class="mx-auto max-w-2xl">
    <div class="mb-6">
      <h1 class="text-2xl font-bold tracking-tight text-slate-900">Crear usuario</h1>
      <p class="mt-1 text-sm text-slate-500">El usuario recibirá sus credenciales para iniciar sesión</p>
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
              autocomplete="off"
              placeholder="Juan García"
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
              autocomplete="off"
              placeholder="juan@empresa.com"
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

      <!-- Contraseña -->
      <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-5 flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-slate-500">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
          </svg>
          Contraseña inicial
        </h2>
        <div class="grid gap-4 sm:grid-cols-2">
          <div>
            <label for="password" class="block text-sm font-medium text-slate-700">Contraseña <span class="text-rose-500">*</span></label>
            <input
              id="password"
              type="password"
              autocomplete="new-password"
              placeholder="Mín. 8 caracteres"
              bind:value={$form.password}
              class="mt-1.5 block w-full rounded-xl border py-2.5 px-3.5 text-sm shadow-sm transition
                {$form.errors.password ? 'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-500' : 'border-slate-300 bg-white focus:border-indigo-500 focus:ring-indigo-500'}
                focus:outline-none focus:ring-2 focus:ring-offset-0"
              required
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
              required
            />
          </div>
        </div>
        <p class="mt-3 flex items-center gap-1.5 text-xs text-slate-500">
          <svg class="h-3.5 w-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
          Comparte esta contraseña con el usuario de forma segura. Podrá cambiarla después.
        </p>
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

        {#if $form.errors.role}
          <p class="mt-2 text-xs text-rose-600">{$form.errors.role}</p>
        {/if}
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
            Creando...
          {:else}
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Crear usuario
          {/if}
        </button>
      </div>
    </form>
  </div>

</Layout>
