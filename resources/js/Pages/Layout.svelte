<script lang="ts">
  import { Link, usePage, router } from '@inertiajs/svelte';
  import Avatar from '../lib/Avatar.svelte';
  import Swal from 'sweetalert2';
  import route from 'ziggy-js';

  export let title = 'InertiaFlow';

  const page = usePage();

  let menuOpen      = false;
  let notifOpen     = false;
  let mobileNavOpen = false;

  const navItems = [
    {
      label: 'Dashboard', routeName: 'dashboard', adminOnly: false,
      icon: 'm2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
    },
    {
      label: 'Proyectos', routeName: 'projects.index', adminOnly: false,
      icon: 'M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z',
    },
    {
      label: 'Mis tareas', routeName: 'my-tasks', adminOnly: false,
      icon: 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
    },
    {
      label: 'Reuniones', routeName: 'meetings.index', adminOnly: false,
      icon: 'M15.75 10.5l4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z',
    },
    {
      label: 'Organizaciones', routeName: 'organizations.index', adminOnly: false,
      icon: 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21',
    },
    {
      label: 'Analíticas', routeName: 'analytics', adminOnly: false,
      icon: 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z',
    },
    {
      label: 'Carga de trabajo', routeName: 'workload', adminOnly: false,
      icon: 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z',
    },
    {
      label: 'Usuarios', routeName: 'admin.users.index', adminOnly: true,
      icon: 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z',
    },
  ] as const;

  $: notifications     = ($page.props.notifications as any[]) ?? [];
  $: unreadCount       = ($page.props.unread_notif_count as number) ?? 0;
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

  // ── Global search (Ctrl+K / Cmd+K) ────────────────────────────────────────
  let searchOpen    = false;
  let searchQuery   = '';
  let searchResults = {
    projects: [] as any[], tasks: [] as any[],
    organizations: [] as any[], departments: [] as any[], members: [] as any[],
  };
  let searchLoading = false;
  let searchIndex   = 0;   // keyboard navigation
  let searchDebounce: ReturnType<typeof setTimeout>;
  let searchInput: HTMLInputElement;

  // Acciones rápidas que se muestran cuando el buscador está vacío
  const quickActions = [
    {
      label: 'Nuevo proyecto',
      hint: 'Crear',
      routeName: 'projects.create',
      icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />`,
      color: '#6366f1',
    },
    {
      label: 'Nueva organización',
      hint: 'Crear',
      routeName: 'organizations.create',
      icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />`,
      color: '#8b5cf6',
    },
    {
      label: 'Mis tareas',
      hint: 'Ir a',
      routeName: 'my-tasks',
      icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />`,
      color: '#14b8a6',
    },
    {
      label: 'Proyectos',
      hint: 'Ir a',
      routeName: 'projects.index',
      icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />`,
      color: '#f59e0b',
    },
    {
      label: 'Organizaciones',
      hint: 'Ir a',
      routeName: 'organizations.index',
      icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />`,
      color: '#ec4899',
    },
    {
      label: 'Reuniones',
      hint: 'Ir a',
      routeName: 'meetings.index',
      icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />`,
      color: '#10b981',
    },
  ] as const;

  function openSearch() {
    searchOpen  = true;
    searchQuery = '';
    searchResults = { projects: [], tasks: [], organizations: [], departments: [], members: [] };
    searchIndex = 0;
    setTimeout(() => searchInput?.focus(), 50);
  }

  function closeSearch() {
    searchOpen = false;
    searchQuery = '';
  }

  async function doSearch(q: string) {
    if (q.length < 2) {
      searchResults = { projects: [], tasks: [], organizations: [], departments: [], members: [] };
      return;
    }
    searchLoading = true;
    try {
      const res  = await fetch(route('search') + '?q=' + encodeURIComponent(q));
      searchResults = await res.json();
      searchIndex   = 0;
    } finally {
      searchLoading = false;
    }
  }

  function onSearchInput() {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(() => doSearch(searchQuery), 220);
  }

  $: allResults = [
    ...searchResults.projects.map((p: any)      => ({ type: 'project',      ...p })),
    ...searchResults.tasks.map((t: any)          => ({ type: 'task',         ...t })),
    ...searchResults.organizations.map((o: any)  => ({ type: 'organization', ...o })),
    ...searchResults.departments.map((d: any)    => ({ type: 'department',   ...d })),
    ...searchResults.members.map((m: any)        => ({ type: 'member',       ...m })),
  ];

  // Lista unificada de items navegables: acciones rápidas cuando vacío, resultados cuando hay query
  $: navigableItems = searchQuery.length === 0
    ? quickActions.map(a => ({ type: 'quick' as const, ...a }))
    : allResults;

  // Resetear índice cuando cambia el modo (vacío ↔ con resultados) o llegan nuevos resultados
  let _prevMode = '';
  $: {
    const mode = searchQuery.length === 0 ? 'quick' : 'results:' + allResults.length;
    if (mode !== _prevMode) { _prevMode = mode; searchIndex = 0; }
  }

  function navigateResult(item: any) {
    closeSearch();
    if (item.type === 'quick') {
      router.visit(route(item.routeName));
    } else if (item.type === 'project') {
      router.visit(route('projects.show', item.uuid));
    } else if (item.type === 'task') {
      router.visit(route('projects.tasks.show', [item.project_uuid, item.uuid]));
    } else if (item.type === 'organization') {
      router.visit(route('organizations.show', item.uuid));
    } else if (item.type === 'department') {
      router.visit(route('organizations.departments.show', [item.org_uuid, item.uuid]));
    } else if (item.type === 'member') {
      router.visit(route('organizations.show', item.org_uuid));
    }
  }

  function onSearchKeydown(e: KeyboardEvent) {
    const last = navigableItems.length - 1;
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      searchIndex = last < 0 ? 0 : Math.min(searchIndex + 1, last);
      scrollActiveIntoView();
    }
    if (e.key === 'ArrowUp') {
      e.preventDefault();
      searchIndex = Math.max(searchIndex - 1, 0);
      scrollActiveIntoView();
    }
    if (e.key === 'Enter' && navigableItems[searchIndex]) navigateResult(navigableItems[searchIndex]);
    if (e.key === 'Escape') closeSearch();
  }

  function scrollActiveIntoView() {
    // Esperar un tick para que Svelte actualice el DOM antes de hacer scroll
    setTimeout(() => {
      const el = document.querySelector('[data-search-active="true"]');
      el?.scrollIntoView({ block: 'nearest' });
    }, 0);
  }

  import { onMount } from 'svelte';
  onMount(() => {
    function handleGlobalKey(e: KeyboardEvent) {
      if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        searchOpen ? closeSearch() : openSearch();
      }
    }
    window.addEventListener('keydown', handleGlobalKey);
    return () => window.removeEventListener('keydown', handleGlobalKey);
  });
</script>

<svelte:head>
  <title>{title} — InertiaFlow</title>
</svelte:head>

<!-- svelte-ignore a11y-click-events-have-key-events a11y-no-static-element-interactions -->
{#if menuOpen}
  <div class="fixed inset-0 z-30" onclick={() => (menuOpen = false)}></div>
{/if}

<!-- ── Global search modal ───────────────────────────────────────────────── -->
{#if searchOpen}
  <!-- Backdrop -->
  <div class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm" onclick={closeSearch}></div>

  <!-- Panel -->
  <div class="fixed left-1/2 top-[12%] z-50 w-full max-w-2xl -translate-x-1/2 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">

    <!-- Input -->
    <div class="flex items-center gap-3 border-b border-slate-100 px-4 py-3">
      {#if searchLoading}
        <svg class="h-5 w-5 animate-spin shrink-0 text-indigo-500" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
      {:else}
        <svg class="h-5 w-5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
      {/if}
      <input
        bind:this={searchInput}
        bind:value={searchQuery}
        oninput={onSearchInput}
        onkeydown={onSearchKeydown}
        type="text"
        placeholder="Buscar proyectos, tareas, organizaciones, departamentos..."
        class="flex-1 bg-transparent text-sm text-slate-900 placeholder-slate-400 focus:outline-none"
      />
      <kbd class="hidden rounded-md border border-slate-200 bg-slate-50 px-1.5 py-0.5 text-[10px] font-medium text-slate-500 sm:block">ESC</kbd>
    </div>

    <!-- Results / Quick actions -->
    {#if searchQuery.length === 0}
      <!-- Quick actions panel -->
      <div class="py-2">
        <p class="px-4 pb-1.5 pt-2 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Acciones rápidas</p>
        <div class="grid grid-cols-2 gap-1.5 px-3 pb-3">
          {#each quickActions as action, i}
            <button
              onclick={() => navigateResult({ type: 'quick', ...action })}
              data-search-active={searchIndex === i ? 'true' : 'false'}
              class="flex items-center gap-2.5 rounded-xl border px-3 py-2.5 text-left transition {searchIndex === i ? 'border-indigo-200 bg-indigo-50 shadow-sm' : 'border-slate-100 bg-slate-50 hover:border-slate-200 hover:bg-white hover:shadow-sm'}"
            >
              <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-white"
                   style="background-color: {action.color};">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  {@html action.icon}
                </svg>
              </div>
              <div class="min-w-0">
                <p class="truncate text-xs font-semibold text-slate-700">{action.label}</p>
                <p class="text-[10px] text-slate-400">{action.hint}</p>
              </div>
            </button>
          {/each}
        </div>
      </div>
    {:else if allResults.length > 0}
      <div class="max-h-[26rem] overflow-y-auto py-2">

        {#if searchResults.projects.length > 0}
          {@const offset = 0}
          <p class="px-4 pb-1 pt-2 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Proyectos</p>
          {#each searchResults.projects as item, i}
            {@const idx = offset + i}
            <button
              onclick={() => navigateResult({ type: 'project', ...item })}
              data-search-active={searchIndex === idx ? 'true' : 'false'}
              class="flex w-full items-center gap-3 px-4 py-2.5 text-left transition {searchIndex === idx ? 'bg-indigo-50' : 'hover:bg-slate-50'}"
            >
              <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-xs font-bold text-white"
                   style="background-color: {item.color ?? '#6366f1'};">
                {item.name.charAt(0).toUpperCase()}
              </div>
              <span class="flex-1 truncate text-sm font-medium text-slate-800">{item.name}</span>
              <span class="shrink-0 rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-medium text-indigo-600">Proyecto</span>
            </button>
          {/each}
        {/if}

        {#if searchResults.tasks.length > 0}
          {@const offset = searchResults.projects.length}
          <p class="px-4 pb-1 pt-3 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Tareas</p>
          {#each searchResults.tasks as item, i}
            {@const idx = offset + i}
            <button
              onclick={() => navigateResult({ type: 'task', ...item })}
              data-search-active={searchIndex === idx ? 'true' : 'false'}
              class="flex w-full items-center gap-3 px-4 py-2.5 text-left transition {searchIndex === idx ? 'bg-indigo-50' : 'hover:bg-slate-50'}"
            >
              <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-[10px] font-bold text-white"
                   style="background-color: {item.project_color ?? '#6366f1'};">
                {item.project_name?.charAt(0).toUpperCase()}
              </div>
              <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium text-slate-800">{item.title}</p>
                <p class="text-[10px] text-slate-400">{item.project_name}</p>
              </div>
              <span class="shrink-0 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-medium text-amber-600">Tarea</span>
            </button>
          {/each}
        {/if}

        {#if searchResults.organizations.length > 0}
          {@const offset = searchResults.projects.length + searchResults.tasks.length}
          <p class="px-4 pb-1 pt-3 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Organizaciones</p>
          {#each searchResults.organizations as item, i}
            {@const idx = offset + i}
            <button
              onclick={() => navigateResult({ type: 'organization', ...item })}
              data-search-active={searchIndex === idx ? 'true' : 'false'}
              class="flex w-full items-center gap-3 px-4 py-2.5 text-left transition {searchIndex === idx ? 'bg-indigo-50' : 'hover:bg-slate-50'}"
            >
              <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 text-xs font-bold text-white">
                {item.name.charAt(0).toUpperCase()}
              </div>
              <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium text-slate-800">{item.name}</p>
                {#if item.description}
                  <p class="truncate text-[10px] text-slate-400">{item.description}</p>
                {/if}
              </div>
              <span class="shrink-0 rounded-full bg-violet-50 px-2 py-0.5 text-[10px] font-medium text-violet-600">Org</span>
            </button>
          {/each}
        {/if}

        {#if searchResults.departments.length > 0}
          {@const offset = searchResults.projects.length + searchResults.tasks.length + searchResults.organizations.length}
          <p class="px-4 pb-1 pt-3 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Departamentos</p>
          {#each searchResults.departments as item, i}
            {@const idx = offset + i}
            <button
              onclick={() => navigateResult({ type: 'department', ...item })}
              data-search-active={searchIndex === idx ? 'true' : 'false'}
              class="flex w-full items-center gap-3 px-4 py-2.5 text-left transition {searchIndex === idx ? 'bg-indigo-50' : 'hover:bg-slate-50'}"
            >
              <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-xs font-bold text-white"
                   style="background-color: {item.color ?? '#8b5cf6'};">
                {item.name.charAt(0).toUpperCase()}
              </div>
              <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium text-slate-800">{item.name}</p>
                <p class="truncate text-[10px] text-slate-400">{item.org_name}</p>
              </div>
              <span class="shrink-0 rounded-full bg-pink-50 px-2 py-0.5 text-[10px] font-medium text-pink-600">Depto</span>
            </button>
          {/each}
        {/if}

        {#if searchResults.members.length > 0}
          {@const offset = searchResults.projects.length + searchResults.tasks.length + searchResults.organizations.length + searchResults.departments.length}
          <p class="px-4 pb-1 pt-3 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Miembros en organizaciones</p>
          {#each searchResults.members as item, i}
            {@const idx = offset + i}
            <button
              onclick={() => navigateResult({ type: 'member', ...item })}
              data-search-active={searchIndex === idx ? 'true' : 'false'}
              class="flex w-full items-center gap-3 px-4 py-2.5 text-left transition {searchIndex === idx ? 'bg-indigo-50' : 'hover:bg-slate-50'}"
            >
              <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-teal-400 to-emerald-600 text-[10px] font-bold text-white">
                {item.user_name?.charAt(0).toUpperCase()}
              </div>
              <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium text-slate-800">{item.user_name}</p>
                <p class="truncate text-[10px] text-slate-400">{item.role} · {item.org_name}</p>
              </div>
              <span class="shrink-0 rounded-full bg-teal-50 px-2 py-0.5 text-[10px] font-medium text-teal-600">Miembro</span>
            </button>
          {/each}
        {/if}

      </div>
    {:else if searchQuery.length >= 2 && !searchLoading}
      <div class="px-4 py-10 text-center text-sm text-slate-500">
        Sin resultados para <strong>"{searchQuery}"</strong>
      </div>
    {/if}

    <!-- Footer hint -->
    <div class="flex items-center gap-4 border-t border-slate-100 px-4 py-2">
      <span class="text-[10px] text-slate-400"><kbd class="rounded border border-slate-200 bg-slate-50 px-1 py-px">↑↓</kbd> navegar</span>
      <span class="text-[10px] text-slate-400"><kbd class="rounded border border-slate-200 bg-slate-50 px-1 py-px">↵</kbd> abrir</span>
      <span class="text-[10px] text-slate-400"><kbd class="rounded border border-slate-200 bg-slate-50 px-1 py-px">ESC</kbd> cerrar</span>
    </div>
  </div>
{/if}
{#if notifOpen}
  <div class="fixed inset-0 z-30" onclick={() => (notifOpen = false)}></div>
{/if}

<!-- Mobile nav drawer -->
{#if mobileNavOpen}
  <div class="fixed inset-0 z-50 md:hidden">
    <!-- Backdrop -->
    <button
      type="button"
      class="absolute inset-0 w-full bg-slate-900/50 backdrop-blur-sm"
      onclick={() => (mobileNavOpen = false)}
      aria-label="Cerrar menú"
    ></button>

    <!-- Drawer -->
    <div class="absolute left-0 top-0 h-full w-72 overflow-y-auto bg-white shadow-2xl">
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
        <Link href={route('dashboard')} onclick={() => (mobileNavOpen = false)} class="flex items-center gap-2.5 font-bold">
          <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600">
            <svg class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
            </svg>
          </div>
          <span class="bg-gradient-to-r from-indigo-600 to-violet-600 bg-clip-text text-transparent">InertiaFlow</span>
        </Link>
        <button
          onclick={() => (mobileNavOpen = false)}
          class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600"
          aria-label="Cerrar menú"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Nav links -->
      <nav class="p-3 space-y-0.5">
        {#each navItems as item}
          {#if !item.adminOnly || isAdmin}
            {#if item.adminOnly}
              <div class="my-2 border-t border-slate-100"></div>
              <p class="px-3 pb-1 pt-0.5 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Administración</p>
            {/if}
            <Link
              href={route(item.routeName)}
              onclick={() => (mobileNavOpen = false)}
              class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors {isActive(item.routeName) ? (item.adminOnly ? 'bg-violet-50 text-violet-700' : 'bg-indigo-50 text-indigo-700') : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'}"
            >
              <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d={item.icon} />
              </svg>
              {item.label}
            </Link>
          {/if}
        {/each}
      </nav>

      <!-- User info at bottom -->
      {#if user}
        <div class="border-t border-slate-100 p-4">
          <div class="flex items-center gap-3">
            <Avatar user={user} size="md" />
            <div class="min-w-0">
              <p class="truncate text-sm font-semibold text-slate-800">{user.name}</p>
              <p class="truncate text-xs text-slate-400">{user.email}</p>
            </div>
          </div>
          <div class="mt-3 space-y-0.5">
            <Link
              href={route('profile.show')}
              onclick={() => (mobileNavOpen = false)}
              class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100"
            >
              <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
              </svg>
              Mi perfil
            </Link>
            <button
              onclick={logout}
              class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-medium text-rose-600 hover:bg-rose-50"
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
  </div>
{/if}

<div class="min-h-screen bg-slate-50 font-sans">

  <!-- Navbar -->
  <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur-md">
    <div class="mx-auto flex h-16 max-w-screen-2xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">

      <!-- Logo + nav -->
      <div class="flex min-w-0 items-center gap-3">

        <!-- Hamburger — mobile only -->
        <button
          onclick={() => (mobileNavOpen = true)}
          class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50 md:hidden"
          aria-label="Abrir menú"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
          </svg>
        </button>

        <!-- Logo -->
        <Link href={route('dashboard')} class="flex shrink-0 items-center gap-2.5 font-bold">
          <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 shadow-sm">
            <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
            </svg>
          </div>
          <span class="bg-gradient-to-r from-indigo-600 to-violet-600 bg-clip-text text-transparent text-lg hidden sm:block">
            InertiaFlow
          </span>
        </Link>

        <!-- Nav desktop: icons + labels (xl+) -->
        <nav class="hidden items-center gap-0.5 xl:flex">
          {#each navItems as item}
            {#if !item.adminOnly || isAdmin}
              {#if item.adminOnly}
                <div class="mx-1 h-5 w-px bg-slate-200"></div>
              {/if}
              <Link
                href={route(item.routeName)}
                title={item.label}
                class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium transition-colors {isActive(item.routeName) ? (item.adminOnly ? 'bg-violet-50 text-violet-700' : 'bg-indigo-50 text-indigo-700') : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'}"
              >
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d={item.icon} />
                </svg>
                {item.label}
              </Link>
            {/if}
          {/each}
        </nav>

        <!-- Nav tablet: icons only (md → xl) -->
        <nav class="hidden items-center gap-0.5 md:flex xl:hidden">
          {#each navItems as item}
            {#if !item.adminOnly || isAdmin}
              {#if item.adminOnly}
                <div class="mx-1 h-5 w-px bg-slate-200"></div>
              {/if}
              <Link
                href={route(item.routeName)}
                title={item.label}
                class="flex h-9 w-9 items-center justify-center rounded-lg transition-colors {isActive(item.routeName) ? (item.adminOnly ? 'bg-violet-50 text-violet-700' : 'bg-indigo-50 text-indigo-700') : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900'}"
              >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d={item.icon} />
                </svg>
              </Link>
            {/if}
          {/each}
        </nav>

      </div>

      <!-- Notifications bell + User menu -->
      {#if user}
        <div class="flex items-center gap-2">

        <!-- Search button -->
        <button
          onclick={openSearch}
          class="hidden items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-500 shadow-sm transition hover:border-slate-300 hover:text-slate-700 sm:flex"
          title="Buscar (Ctrl+K)"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
          </svg>
          <span class="hidden lg:block">Buscar</span>
          <kbd class="hidden rounded border border-slate-200 bg-slate-50 px-1.5 py-px text-[10px] font-medium lg:block">Ctrl K</kbd>
        </button>

        <!-- Notifications bell -->
        <div class="relative">
          <button
            onclick={() => { notifOpen = !notifOpen; if (notifOpen) menuOpen = false; }}
            class="relative flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:border-slate-300 hover:text-slate-700"
            title="Notificaciones"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
            </svg>
            {#if unreadCount > 0}
              <span class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[9px] font-bold text-white">
                {unreadCount > 9 ? '9+' : unreadCount}
              </span>
            {/if}
          </button>

          {#if notifOpen}
            <div class="absolute right-0 top-full z-50 mt-2 w-80 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg shadow-slate-200/60">
              <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                <p class="text-sm font-semibold text-slate-800">Notificaciones</p>
                {#if unreadCount > 0}
                  <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-semibold text-rose-700">{unreadCount} sin leer</span>
                {/if}
              </div>
              <div class="max-h-72 overflow-y-auto divide-y divide-slate-100">
                {#if notifications.length === 0}
                  <p class="px-4 py-6 text-center text-xs text-slate-500">Sin notificaciones aún</p>
                {:else}
                  {#each notifications as notif}
                    {@const isUnread = !notif.read_at}
                    <a
                      href={notif.url ?? route('notifications.index')}
                      onclick={() => (notifOpen = false)}
                      class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 transition {isUnread ? 'bg-indigo-50/40' : ''}"
                    >
                      <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-indigo-100 text-[10px] font-bold text-indigo-700">
                        {notif.type === 'task_assigned' ? '✓' : notif.type === 'comment_added' ? '💬' : notif.type === 'mentioned' ? '@' : notif.type === 'meeting_invite' ? '📅' : notif.type === 'task_due' ? '⏰' : '●'}
                      </div>
                      <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-medium text-slate-800">{notif.title}</p>
                        <p class="text-[10px] text-slate-500">{notif.created_at}</p>
                      </div>
                      {#if isUnread}
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-indigo-500"></span>
                      {/if}
                    </a>
                  {/each}
                {/if}
              </div>
              <div class="border-t border-slate-100 px-4 py-2.5 text-center">
                <Link
                  href={route('notifications.index')}
                  onclick={() => (notifOpen = false)}
                  class="text-xs font-medium text-indigo-600 hover:text-indigo-700"
                >
                  Ver todas las notificaciones →
                </Link>
              </div>
            </div>
          {/if}
        </div>

        <div class="relative">
          <button
            onclick={() => (menuOpen = !menuOpen)}
            class="flex items-center gap-2.5 rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-sm font-medium text-slate-700 shadow-sm transition hover:border-slate-300 hover:shadow"
          >
            <div class="relative shrink-0">
              {#if user.avatar_url}
                <img src={user.avatar_url} alt={user.name} class="h-7 w-7 rounded-lg object-cover" />
              {:else}
                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 text-xs font-bold text-white">
                  {initials}
                </div>
              {/if}
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
                  <Link
                    href={route('admin.audit-log')}
                    onclick={() => (menuOpen = false)}
                    class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                  >
                    <svg class="h-4 w-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                    </svg>
                    Audit Log
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
  <main class="mx-auto w-full max-w-screen-2xl px-4 py-8 sm:px-6 lg:px-8">
    <slot />
  </main>
</div>
