<script lang="ts">
  import Layout from '../Layout.svelte';
  import { Link, router } from '@inertiajs/svelte';
  import route from 'ziggy-js';

  export let logs: any;
  export let filters: { log_name?: string; event?: string; search?: string };

  let search   = filters.search   ?? '';
  let log_name = filters.log_name ?? '';
  let event    = filters.event    ?? '';

  function applyFilters() {
    router.get(route('admin.audit-log'), { search, log_name, event }, { preserveState: true, replace: true });
  }

  function clearFilters() {
    search = ''; log_name = ''; event = '';
    applyFilters();
  }

  const logNameLabels: Record<string, string> = {
    project: 'Proyecto',
    task:    'Tarea',
    default: 'Sistema',
  };

  const eventColors: Record<string, string> = {
    created: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    updated: 'bg-amber-50 text-amber-700 ring-amber-200',
    deleted: 'bg-rose-50 text-rose-700 ring-rose-200',
  };

  function formatChanges(properties: any): string[] {
    if (!properties?.attributes) return [];
    return Object.keys(properties.attributes).map(key => {
      const old = properties.old?.[key] ?? '—';
      const nv  = properties.attributes[key] ?? '—';
      return `${key}: "${old}" → "${nv}"`;
    });
  }
</script>

<Layout title="Audit Log">

  <div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Audit Log</h1>
    <p class="mt-1 text-sm text-slate-500">Trazabilidad completa de todas las operaciones del sistema</p>
  </div>

  <!-- Filters -->
  <div class="mb-6 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="flex-1 min-w-48">
      <label class="block text-xs font-medium text-slate-600 mb-1">Buscar descripción</label>
      <input
        type="text"
        bind:value={search}
        placeholder="Buscar..."
        onkeydown={(e) => e.key === 'Enter' && applyFilters()}
        class="block w-full rounded-xl border border-slate-300 bg-white py-2 px-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
      />
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">Tipo</label>
      <select bind:value={log_name} onchange={applyFilters}
        class="block rounded-xl border border-slate-300 bg-white py-2 px-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0">
        <option value="">Todos</option>
        <option value="project">Proyectos</option>
        <option value="task">Tareas</option>
      </select>
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">Evento</label>
      <select bind:value={event} onchange={applyFilters}
        class="block rounded-xl border border-slate-300 bg-white py-2 px-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0">
        <option value="">Todos</option>
        <option value="created">Creado</option>
        <option value="updated">Actualizado</option>
        <option value="deleted">Eliminado</option>
      </select>
    </div>
    <button onclick={applyFilters}
      class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition">
      Filtrar
    </button>
    {#if search || log_name || event}
      <button onclick={clearFilters} class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 transition">
        Limpiar
      </button>
    {/if}
    <span class="ml-auto text-xs text-slate-500">{logs.total} registros</span>
  </div>

  <!-- Table -->
  {#if logs.data.length === 0}
    <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white py-16 text-center">
      <p class="text-slate-500">No hay registros de auditoría</p>
    </div>
  {:else}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
      <table class="min-w-full divide-y divide-slate-100">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Fecha</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Usuario</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Tipo</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Evento</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Descripción</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Cambios</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          {#each logs.data as log}
            {@const changes = formatChanges(log.properties)}
            <tr class="hover:bg-slate-50 transition">
              <td class="whitespace-nowrap px-4 py-3">
                <p class="text-xs font-medium text-slate-800">{log.created_at}</p>
                <p class="text-[10px] text-slate-400">{log.created_human}</p>
              </td>
              <td class="px-4 py-3">
                <p class="text-sm font-medium text-slate-800">{log.causer_name}</p>
              </td>
              <td class="px-4 py-3">
                <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                  {logNameLabels[log.log_name] ?? log.log_name}
                </span>
              </td>
              <td class="px-4 py-3">
                {#if log.event}
                  <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium ring-1 {eventColors[log.event] ?? 'bg-slate-100 text-slate-600 ring-slate-200'}">
                    {log.event}
                  </span>
                {/if}
              </td>
              <td class="max-w-xs px-4 py-3">
                <p class="truncate text-sm text-slate-700">{log.description}</p>
                {#if log.subject_type}
                  <p class="text-[10px] text-slate-400">{log.subject_type} #{log.subject_id}</p>
                {/if}
              </td>
              <td class="px-4 py-3">
                {#if changes.length > 0}
                  <ul class="space-y-0.5">
                    {#each changes.slice(0, 3) as ch}
                      <li class="text-[10px] text-slate-500">{ch}</li>
                    {/each}
                    {#if changes.length > 3}
                      <li class="text-[10px] text-slate-400">+{changes.length - 3} más</li>
                    {/if}
                  </ul>
                {:else}
                  <span class="text-xs text-slate-400">—</span>
                {/if}
              </td>
            </tr>
          {/each}
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    {#if logs.last_page > 1}
      <div class="mt-6 flex items-center justify-center gap-2">
        {#each logs.links as link}
          {#if link.url}
            <Link href={link.url} class="rounded-lg px-3 py-1.5 text-sm {link.active ? 'bg-indigo-600 text-white font-semibold' : 'bg-white border border-slate-300 text-slate-700 hover:bg-slate-50'}">
              {@html link.label}
            </Link>
          {:else}
            <span class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm text-slate-400">{@html link.label}</span>
          {/if}
        {/each}
      </div>
    {/if}
  {/if}

</Layout>
