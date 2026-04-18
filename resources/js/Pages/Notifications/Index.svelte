<script lang="ts">
  import Layout from '../Layout.svelte';
  import { Link, router } from '@inertiajs/svelte';
  import route from 'ziggy-js';

  let { notifications }: { notifications: any } = $props();

  const typeConfig: Record<string, { label: string; icon: string; color: string }> = {
    task_assigned:  { label: 'Tarea asignada',   icon: '✓',  color: 'bg-indigo-100 text-indigo-700' },
    comment_added:  { label: 'Comentario',        icon: '💬', color: 'bg-sky-100 text-sky-700' },
    mentioned:      { label: 'Mención',           icon: '@',  color: 'bg-violet-100 text-violet-700' },
    member_added:   { label: 'Miembro agregado',  icon: '👤', color: 'bg-emerald-100 text-emerald-700' },
    task_due:       { label: 'Vencimiento',       icon: '⏰', color: 'bg-rose-100 text-rose-700' },
    meeting_invite: { label: 'Reunión',           icon: '📅', color: 'bg-amber-100 text-amber-700' },
  };

  function getType(type: string) {
    return typeConfig[type] ?? { label: type, icon: '●', color: 'bg-slate-100 text-slate-600' };
  }

  function markRead(id: number) {
    router.patch(route('notifications.markRead', id), {}, { preserveScroll: true });
  }

  function markAllRead() {
    router.post(route('notifications.markAllRead'), {}, { preserveScroll: true });
  }

  function remove(id: number) {
    router.delete(route('notifications.destroy', id), { preserveScroll: true });
  }
</script>

<Layout title="Notificaciones">

  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-slate-900">Notificaciones</h1>
      <p class="mt-1 text-sm text-slate-500">Historial de actividad relacionada contigo</p>
    </div>
    {#if notifications.data.some((n: any) => !n.read_at)}
      <button
        onclick={markAllRead}
        class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        Marcar todas como leídas
      </button>
    {/if}
  </div>

  {#if notifications.data.length === 0}
    <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white py-20 text-center">
      <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100">
        <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
        </svg>
      </div>
      <p class="mt-4 font-medium text-slate-700">Sin notificaciones</p>
      <p class="mt-1 text-sm text-slate-500">Cuando haya actividad relacionada contigo aparecerá aquí</p>
    </div>
  {:else}
    <div class="divide-y divide-slate-100 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
      {#each notifications.data as notif}
        {@const cfg = getType(notif.type)}
        <div class="flex items-start gap-4 p-4 transition {notif.read_at ? 'bg-white' : 'bg-indigo-50/40'}">

          <!-- Icon -->
          <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-sm font-bold {cfg.color}">
            {cfg.icon}
          </div>

          <!-- Content -->
          <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-2">
              <div>
                <p class="text-sm font-medium text-slate-900 {!notif.read_at ? 'font-semibold' : ''}">{notif.title}</p>
                {#if notif.message}
                  <p class="mt-0.5 text-xs text-slate-500">{notif.message}</p>
                {/if}
              </div>
              <span class="shrink-0 text-xs text-slate-400">{new Date(notif.created_at).toLocaleDateString('es', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' })}</span>
            </div>

            <div class="mt-2 flex items-center gap-3">
              {#if notif.url}
                <Link href={notif.url} class="text-xs font-medium text-indigo-600 hover:text-indigo-700">Ver →</Link>
              {/if}
              {#if !notif.read_at}
                <button onclick={() => markRead(notif.id)} class="text-xs text-slate-500 hover:text-slate-700">Marcar como leída</button>
              {:else}
                <span class="text-xs text-slate-400">Leída</span>
              {/if}
              <button onclick={() => remove(notif.id)} class="ml-auto text-xs text-slate-400 hover:text-rose-600">Eliminar</button>
            </div>
          </div>
        </div>
      {/each}
    </div>

    <!-- Pagination -->
    {#if notifications.last_page > 1}
      <div class="mt-6 flex items-center justify-center gap-2">
        {#each notifications.links as link}
          {#if link.url}
            <Link
              href={link.url}
              class="rounded-lg px-3 py-1.5 text-sm {link.active ? 'bg-indigo-600 text-white font-semibold' : 'bg-white border border-slate-300 text-slate-700 hover:bg-slate-50'}"
            >
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
