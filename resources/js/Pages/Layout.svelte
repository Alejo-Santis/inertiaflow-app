<script lang="ts">
  import { Link, usePage, router } from '@inertiajs/svelte';
  import Swal from 'sweetalert2';
  import route from 'ziggy-js';

  export let title = 'InertiaFlow';

  const page = usePage();

  let menuOpen  = false;
  let notifOpen = false;

  $: notifications = ($page.props.notifications as any[]) ?? [];
  $: user    = $page.props.auth?.user;
  $: isAdmin = $page.props.auth?.isAdmin ?? false;

  $: initials = user?.name
    ? user.name.split(' ').map((n: string) => n[0]).slice(0, 2).join('').toUpperCase()
    : '?';

  $: {
    const flash = $page?.props?.flash;
    if (flash?.success) {
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: flash.success,
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
      });
    }
    if (flash?.error) {
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'error',
        title: flash.error,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
      });
    }
  }

  function logout() {
    menuOpen = false;
    router.post(route('logout'));
  }

  function isActive(routeName: string): boolean {
    try {
      return window.location.pathname === new URL(route(routeName)).pathname;
    } catch {
      return false;
    }
  }
</script>

<svelte:head>
  <title>{title} — InertiaFlow</title>
</svelte:head>

<!-- svelte-ignore a11y-click-events-have-key-events a11y-no-static-element-interactions -->
{#if menuOpen}
  <div class="fixed inset-0 z-30" onclick={() => (menuOpen = false)}></div>
{/if}
{#if notifOpen}
  <div class="fixed inset-0 z-30" onclick={() => (notifOpen = false)}></div>
{/if}

<div class="min-h-screen bg-slate-50 font-sans">

  <!-- Navbar -->
  <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur-md">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">

      <!-- Logo + nav -->
      <div class="flex items-center gap-6">
        <!-- Logo -->
        <Link href={route('dashboard')} class="flex items-center gap-2.5 font-bold">
          <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 shadow-sm">
            <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
            </svg>
          </div>
          <span class="bg-gradient-to-r from-indigo-600 to-violet-600 bg-clip-text text-transparent text-lg">
            InertiaFlow
          </span>
        </Link>

        <!-- Nav links -->
        <nav class="hidden items-center gap-1 sm:flex">
          <Link
            href={route('dashboard')}
            class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium transition-colors {isActive('dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'}"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg>
            Dashboard
          </Link>
          <Link
            href={route('projects.index')}
            class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium transition-colors {isActive('projects.index') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'}"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
            </svg>
            Proyectos
          </Link>

          <Link
            href={route('analytics')}
            class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium transition-colors {isActive('analytics') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'}"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
            </svg>
            Analíticas
          </Link>

          {#if isAdmin}
            <!-- Divider -->
            <div class="mx-1 h-5 w-px bg-slate-200"></div>
            <Link
              href={route('admin.users.index')}
              class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium transition-colors {isActive('admin.users.index') ? 'bg-violet-50 text-violet-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'}"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
              </svg>
              Usuarios
            </Link>
          {/if}
        </nav>
      </div>

      <!-- Notifications bell + User menu -->
      {#if user}
        <div class="flex items-center gap-2">

        <!-- Notifications -->
        {#if notifications.length > 0}
          <div class="relative">
            <button
              onclick={() => { notifOpen = !notifOpen; if (notifOpen) menuOpen = false; }}
              class="relative flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:border-slate-300 hover:text-slate-700"
              title="Actividad reciente"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
              </svg>
              <span class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-indigo-600 text-[9px] font-bold text-white">
                {notifications.length}
              </span>
            </button>

            {#if notifOpen}
              <div class="absolute right-0 top-full z-50 mt-2 w-80 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg shadow-slate-200/60">
                <div class="border-b border-slate-100 px-4 py-3">
                  <p class="text-sm font-semibold text-slate-800">Actividad reciente</p>
                </div>
                <div class="max-h-72 overflow-y-auto divide-y divide-slate-100">
                  {#each notifications as notif}
                    <Link
                      href={route('projects.tasks.show', [notif.project_uuid, notif.uuid])}
                      onclick={() => notifOpen = false}
                      class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 transition"
                    >
                      <div
                        class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-md text-[10px] font-bold text-white"
                        style="background-color: {notif.project_color ?? '#6366f1'};"
                      >
                        {notif.project_name.charAt(0).toUpperCase()}
                      </div>
                      <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-medium text-slate-800">{notif.title}</p>
                        <p class="text-[10px] text-slate-500">{notif.project_name} · {notif.updated_at}</p>
                      </div>
                    </Link>
                  {/each}
                </div>
              </div>
            {/if}
          </div>
        {/if}

        <div class="relative">
          <button
            onclick={() => (menuOpen = !menuOpen)}
            class="flex items-center gap-2.5 rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-sm font-medium text-slate-700 shadow-sm transition hover:border-slate-300 hover:shadow"
          >
            <div class="relative flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 text-xs font-bold text-white">
              {initials}
              {#if isAdmin}
                <span class="absolute -right-1 -top-1 flex h-3 w-3 items-center justify-center rounded-full bg-violet-600 ring-2 ring-white text-[7px] text-white font-bold">A</span>
              {/if}
            </div>
            <span class="hidden max-w-[120px] truncate sm:block">{user.name}</span>
            <svg class="h-4 w-4 text-slate-400 transition-transform {menuOpen ? 'rotate-180' : ''}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
          </button>

          {#if menuOpen}
            <div class="absolute right-0 top-full z-50 mt-2 w-52 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg shadow-slate-200/60">
              <div class="border-b border-slate-100 px-4 py-3">
                <div class="flex items-center justify-between">
                  <p class="text-xs font-medium text-slate-500">Sesión iniciada como</p>
                  {#if isAdmin}
                    <span class="rounded-full bg-violet-100 px-2 py-0.5 text-[10px] font-semibold text-violet-700">Admin</span>
                  {/if}
                </div>
                <p class="mt-0.5 truncate text-sm font-semibold text-slate-800">{user.email}</p>
              </div>
              <div class="border-b border-slate-100 p-1">
                <Link
                  href={route('profile.show')}
                  onclick={() => (menuOpen = false)}
                  class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                >
                  <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                  </svg>
                  Mi perfil
                </Link>
              </div>
              {#if isAdmin}
                <div class="border-b border-slate-100 p-1">
                  <Link
                    href={route('admin.users.index')}
                    onclick={() => (menuOpen = false)}
                    class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                  >
                    <svg class="h-4 w-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                    Gestión de usuarios
                  </Link>
                </div>
              {/if}
              <div class="p-1">
                <button
                  onclick={logout}
                  class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-rose-600 transition hover:bg-rose-50"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
                  </svg>
                  Cerrar sesión
                </button>
              </div>
            </div>
          {/if}
        </div>

        </div> <!-- end flex wrapper notifications + user -->
      {/if}
    </div>
  </header>

  <!-- Page content -->
  <main class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <slot />
  </main>
</div>
