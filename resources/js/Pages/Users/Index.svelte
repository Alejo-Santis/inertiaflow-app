<script lang="ts">
  import Layout from '../Layout.svelte';
  import { Link, router } from '@inertiajs/svelte';
  import route from 'ziggy-js';
  import Swal from 'sweetalert2';

  let { users }: { users: any } = $props();

  const roleConfig: Record<string, { label: string; color: string }> = {
    admin:   { label: 'Admin',    color: 'bg-violet-100 text-violet-700 ring-1 ring-violet-200' },
    manager: { label: 'Manager',  color: 'bg-blue-100 text-blue-700 ring-1 ring-blue-200' },
    member:  { label: 'Miembro',  color: 'bg-slate-100 text-slate-600 ring-1 ring-slate-200' },
  };

  function getRoleBadge(roleName: string) {
    return roleConfig[roleName] ?? { label: roleName, color: 'bg-slate-100 text-slate-600' };
  }

  function getInitials(name: string): string {
    return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
  }

  async function deleteUser(user: any) {
    const result = await Swal.fire({
      title: '¿Eliminar usuario?',
      text: `"${user.name}" será eliminado permanentemente.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc2626',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
    });
    if (result.isConfirmed) {
      router.delete(route('admin.users.destroy', user.uuid));
    }
  }
</script>

<Layout title="Gestión de usuarios">

  <!-- Header -->
  <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <div class="flex items-center gap-2 text-sm text-slate-500">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
        </svg>
        Panel de administración
      </div>
      <h1 class="mt-0.5 text-2xl font-bold tracking-tight text-slate-900">Gestión de usuarios</h1>
      <p class="mt-1 text-sm text-slate-500">{users?.total ?? 0} usuarios registrados</p>
    </div>
    <Link
      href={route('admin.users.create')}
      class="inline-flex items-center gap-2 self-start rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:from-indigo-700 hover:to-violet-700 hover:shadow-md sm:self-auto"
    >
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
      </svg>
      Nuevo usuario
    </Link>
  </div>

  <!-- Users table -->
  <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-100">
        <thead>
          <tr class="bg-slate-50">
            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Usuario</th>
            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Correo</th>
            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Rol</th>
            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Registrado</th>
            <th class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Acciones</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
          {#each users?.data ?? [] as user}
            {@const role = getRoleBadge(user.roles?.[0]?.name ?? '')}
            <tr class="group transition hover:bg-slate-50/60">
              <td class="whitespace-nowrap px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-xs font-bold text-white">
                    {getInitials(user.name)}
                  </div>
                  <div>
                    <p class="font-medium text-slate-900">{user.name}</p>
                    <p class="text-xs text-slate-400 font-mono">{user.uuid?.slice(0, 8)}…</p>
                  </div>
                </div>
              </td>
              <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">{user.email}</td>
              <td class="whitespace-nowrap px-6 py-4">
                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {role.color}">
                  {role.label}
                </span>
              </td>
              <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                {new Date(user.created_at).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' })}
              </td>
              <td class="whitespace-nowrap px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-2 opacity-0 transition group-hover:opacity-100">
                  <Link
                    href={route('admin.users.edit', user.uuid)}
                    class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:border-indigo-300 hover:text-indigo-700"
                  >
                    Editar
                  </Link>
                  <button
                    onclick={() => deleteUser(user)}
                    class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-rose-600 shadow-sm hover:border-rose-300 hover:bg-rose-50"
                  >
                    Eliminar
                  </button>
                </div>
              </td>
            </tr>
          {/each}

          {#if !users?.data?.length}
            <tr>
              <td colspan="5" class="py-16 text-center">
                <div class="flex flex-col items-center gap-2">
                  <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100">
                    <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                  </div>
                  <p class="font-medium text-slate-700">Sin usuarios</p>
                  <p class="text-sm text-slate-400">Crea el primer usuario del sistema</p>
                </div>
              </td>
            </tr>
          {/if}
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    {#if users?.last_page > 1}
      <div class="flex items-center justify-between border-t border-slate-100 px-6 py-3">
        <p class="text-sm text-slate-500">
          Mostrando {users.from}–{users.to} de {users.total} usuarios
        </p>
        <div class="flex gap-2">
          {#if users.prev_page_url}
            <Link href={users.prev_page_url} class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50">← Anterior</Link>
          {/if}
          {#if users.next_page_url}
            <Link href={users.next_page_url} class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50">Siguiente →</Link>
          {/if}
        </div>
      </div>
    {/if}
  </div>

</Layout>
