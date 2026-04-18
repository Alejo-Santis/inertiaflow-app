<script lang="ts">
  import Layout from '../Layout.svelte';
  import { Link } from '@inertiajs/svelte';
  import route from 'ziggy-js';

  export let organizations: any[];

  const getInitials = (name: string): string =>
    name.split(' ').map((n: string) => n[0]).slice(0, 2).join('').toUpperCase();
</script>

<Layout title="Organizaciones">

  <!-- Header -->
  <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-slate-900">Organizaciones</h1>
      <p class="mt-1 text-sm text-slate-500">
        {organizations.length} organización{organizations.length !== 1 ? 'es' : ''} en total
      </p>
    </div>
    <Link
      href={route('organizations.create')}
      class="inline-flex items-center gap-2 self-start rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:from-indigo-700 hover:to-violet-700 hover:shadow-md sm:self-auto"
    >
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
      </svg>
      Nueva organización
    </Link>
  </div>

  {#if organizations.length > 0}
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
      {#each organizations as org}
        <article class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md hover:-translate-y-0.5">

          <!-- Top accent -->
          <div class="h-1.5 w-full bg-gradient-to-r from-indigo-500 to-violet-600"></div>

          <div class="flex flex-1 flex-col p-5">
            <!-- Header -->
            <div class="flex items-start gap-3">
              <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-sm font-bold text-white shadow-sm">
                {getInitials(org.name)}
              </div>
              <div class="min-w-0 flex-1">
                <h2 class="truncate font-semibold text-slate-900">{org.name}</h2>
                <p class="text-xs text-slate-500">Por {org.owner?.name ?? '—'}</p>
              </div>
            </div>

            {#if org.description}
              <p class="mt-3 line-clamp-2 text-sm leading-relaxed text-slate-500">{org.description}</p>
            {:else}
              <p class="mt-3 text-sm italic text-slate-400">Sin descripción</p>
            {/if}

            <!-- Stats -->
            <div class="mt-4 flex items-center gap-4 text-xs text-slate-500">
              <span class="flex items-center gap-1.5">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
                <strong class="font-semibold text-slate-700">{org.members_count ?? 0}</strong> miembros
              </span>
              <span class="flex items-center gap-1.5">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                </svg>
                <strong class="font-semibold text-slate-700">{org.departments_count ?? 0}</strong> depts
              </span>
              <span class="flex items-center gap-1.5">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                </svg>
                <strong class="font-semibold text-slate-700">{org.projects_count ?? 0}</strong> proyectos
              </span>
            </div>

            <!-- Footer -->
            <div class="mt-auto flex items-center justify-end border-t border-slate-100 pt-4">
              <Link
                href={route('organizations.show', org.uuid)}
                class="inline-flex items-center gap-1.5 rounded-lg bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-indigo-50 hover:text-indigo-700"
              >
                Ver organización
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
              </Link>
            </div>
          </div>
        </article>
      {/each}
    </div>

  {:else}
    <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-white py-20 text-center">
      <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-indigo-50">
        <svg class="h-10 w-10 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
        </svg>
      </div>
      <h3 class="mt-4 text-lg font-semibold text-slate-800">Sin organizaciones aún</h3>
      <p class="mt-2 max-w-xs text-sm text-slate-500">Crea tu primera organización para gestionar equipos y proyectos de manera estructurada.</p>
      <Link
        href={route('organizations.create')}
        class="mt-6 inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:from-indigo-700 hover:to-violet-700"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Crear organización
      </Link>
    </div>
  {/if}

</Layout>
