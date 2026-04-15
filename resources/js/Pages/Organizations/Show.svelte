<script lang="ts">
  import Layout from '../Layout.svelte';
  import { Link, useForm, router } from '@inertiajs/svelte';
  import route from 'ziggy-js';
  import { useOrgAbilities, OrgActions } from '../../lib/orgCan';

  export let organization: any;
  export let available: any[];
  export let orgRoles: string[];
  export let pendingInvitations: any[] = [];

  // Permisos del usuario actual en esta org
  const can = useOrgAbilities(organization.uuid);

  const roleLabels: Record<string, string> = {
    owner:   'Owner',
    admin:   'Admin',
    manager: 'Manager',
    member:  'Member',
  };

  const roleBadge: Record<string, string> = {
    owner:   'bg-violet-100 text-violet-700 ring-1 ring-violet-200',
    admin:   'bg-rose-50 text-rose-700 ring-1 ring-rose-200',
    manager: 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
    member:  'bg-slate-100 text-slate-600 ring-1 ring-slate-200',
  };

  // Invitaciones
  const inviteForm = useForm({ email: '', role: 'member' });

  function sendInvite() {
    if (!$inviteForm.email) return;
    $inviteForm.post(route('organizations.invitations.store', organization.uuid), {
      onSuccess: () => { $inviteForm.email = ''; $inviteForm.role = 'member'; },
    });
  }

  function cancelInvitation(invitationId: number) {
    if (confirm('¿Cancelar esta invitación?')) {
      router.delete(route('organizations.invitations.destroy', [organization.uuid, invitationId]), {
        preserveScroll: true,
      });
    }
  }

  // Modal de nuevo departamento
  let deptModal = false;
  const deptForm = useForm({ name: '', description: '', color: '#6366f1', lead_id: '' });

  function createDept() {
    $deptForm.post(route('organizations.departments.store', organization.uuid), {
      onSuccess: () => { deptModal = false; $deptForm.reset(); },
    });
  }

  // Agregar miembro a la org
  const memberForm = useForm({ user_id: '', role: 'member' });

  function addMember() {
    if (!$memberForm.user_id) return;
    $memberForm.post(route('organizations.members.store', organization.uuid), {
      onSuccess: () => { $memberForm.user_id = ''; $memberForm.role = 'member'; },
    });
  }

  function removeMember(user: any) {
    if (confirm(`¿Remover a ${user.name} de la organización?`)) {
      router.delete(route('organizations.members.destroy', [organization.uuid, user.uuid]), {
        preserveScroll: true,
      });
    }
  }

  function changeRole(user: any, newRole: string) {
    router.patch(route('organizations.members.update', [organization.uuid, user.uuid]), {
      role: newRole,
    }, { preserveScroll: true });
  }

  function deleteDept(dept: any) {
    if (confirm(`¿Eliminar el departamento "${dept.name}"? Esta acción no se puede deshacer.`)) {
      router.delete(route('organizations.departments.destroy', [organization.uuid, dept.uuid]), {
        preserveScroll: true,
      });
    }
  }

  function getInitials(name: string): string {
    return name.split(' ').map((n: string) => n[0]).slice(0, 2).join('').toUpperCase();
  }

  const deptColors = [
    '#6366f1', '#8b5cf6', '#ec4899', '#14b8a6',
    '#f59e0b', '#10b981', '#3b82f6', '#ef4444',
  ];
</script>

<Layout title={organization.name}>

  <!-- Breadcrumb -->
  <nav class="mb-6 flex items-center gap-2 text-sm text-slate-500">
    <Link href={route('organizations.index')} class="hover:text-slate-700">Organizaciones</Link>
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
    </svg>
    <span class="font-medium text-slate-900">{organization.name}</span>
  </nav>

  <div class="grid gap-6 lg:grid-cols-3">

    <!-- Main column: Departamentos -->
    <div class="space-y-6 lg:col-span-2">

      <!-- Org header -->
      <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="h-1.5 w-full bg-gradient-to-r from-indigo-500 to-violet-600"></div>
        <div class="flex items-start justify-between gap-4 p-6">
          <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-lg font-bold text-white shadow-sm">
              {organization.name.charAt(0).toUpperCase()}
            </div>
            <div>
              <h1 class="text-xl font-bold text-slate-900">{organization.name}</h1>
              <p class="mt-0.5 text-sm text-slate-500">Owner: {organization.owner?.name ?? '—'}</p>
            </div>
          </div>
          {#if can(OrgActions.EDIT_ORG)}
            <Link
              href={route('organizations.edit', organization.uuid)}
              class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50"
            >
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
              </svg>
              Editar
            </Link>
          {/if}
        </div>
        {#if organization.description}
          <p class="border-t border-slate-100 px-6 pb-5 pt-3 text-sm text-slate-600">{organization.description}</p>
        {/if}
      </div>

      <!-- Departamentos -->
      <div>
        <div class="mb-4 flex items-center justify-between">
          <div>
            <h2 class="text-lg font-semibold text-slate-900">Departamentos</h2>
            <p class="text-sm text-slate-500">{organization.departments?.length ?? 0} departamento{(organization.departments?.length ?? 0) !== 1 ? 's' : ''}</p>
          </div>
          {#if can(OrgActions.CREATE_DEPT)}
          <button
              onclick={() => (deptModal = true)}
              class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
              </svg>
              Nuevo departamento
            </button>
          {/if}
        </div>

        {#if organization.departments?.length > 0}
          <div class="grid gap-4 sm:grid-cols-2">
            {#each organization.departments as dept}
              <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
                <div class="h-1 w-full" style="background-color: {dept.color ?? '#6366f1'};"></div>
                <div class="p-5">
                  <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-3">
                      <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-white text-sm font-bold"
                           style="background-color: {dept.color ?? '#6366f1'};">
                        {dept.name.charAt(0).toUpperCase()}
                      </div>
                      <div>
                        <h3 class="font-semibold text-slate-900">{dept.name}</h3>
                        {#if dept.lead}
                          <p class="text-xs text-slate-500">Lead: {dept.lead.name}</p>
                        {/if}
                      </div>
                    </div>
                    <div class="flex items-center gap-1">
                      <Link
                        href={route('organizations.departments.show', [organization.uuid, dept.uuid])}
                        class="rounded-lg p-1.5 text-slate-400 transition hover:bg-indigo-50 hover:text-indigo-600"
                        title="Ver departamento"
                      >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                      </Link>
                      {#if can(OrgActions.DELETE_DEPT)}
                        <button
                          onclick={() => deleteDept(dept)}
                          class="rounded-lg p-1.5 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600"
                          title="Eliminar"
                        >
                          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                          </svg>
                        </button>
                      {/if}
                    </div>
                  </div>

                  {#if dept.description}
                    <p class="mt-2 line-clamp-2 text-xs text-slate-500">{dept.description}</p>
                  {/if}

                  <div class="mt-3 flex items-center gap-3 text-xs text-slate-500">
                    <span class="flex items-center gap-1">
                      <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                      </svg>
                      <strong class="text-slate-700">{dept.members_count ?? 0}</strong> miembros
                    </span>
                  </div>
                </div>
              </div>
            {/each}
          </div>
        {:else}
          <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-white py-12 text-center">
            <p class="text-sm text-slate-500">Sin departamentos aún.</p>
            {#if can(OrgActions.CREATE_DEPT)}
              <button
                onclick={() => (deptModal = true)}
                class="mt-3 text-sm font-semibold text-indigo-600 hover:text-indigo-800"
              >+ Crear departamento</button>
            {/if}
          </div>
        {/if}
      </div>

    </div>

    <!-- Sidebar: Miembros de la org -->
    <div>
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
          <h2 class="font-semibold text-slate-900">Miembros de la organización</h2>
          <p class="mt-0.5 text-xs text-slate-500">{organization.members?.length ?? 0} miembro{(organization.members?.length ?? 0) !== 1 ? 's' : ''}</p>
        </div>

        <div class="divide-y divide-slate-100">
          {#each (organization.members ?? []) as member}
            <div class="flex items-center justify-between px-5 py-3">
              <div class="flex items-center gap-3 min-w-0">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 text-xs font-bold text-white">
                  {getInitials(member.user?.name ?? '?')}
                </div>
                <div class="min-w-0">
                  <p class="truncate text-sm font-medium text-slate-800">{member.user?.name}</p>
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium {roleBadge[member.role] ?? roleBadge.member}">
                    {roleLabels[member.role] ?? member.role}
                  </span>
                </div>
              </div>
              {#if member.role !== 'owner' && can(OrgActions.MANAGE_ORG_MEMBERS)}
                <div class="flex shrink-0 items-center gap-1">
                  <select
                    value={member.role}
                    onchange={(e) => changeRole(member.user, (e.target as HTMLSelectElement).value)}
                    class="rounded-lg border border-slate-200 bg-white py-1 pl-2 pr-6 text-xs text-slate-700 focus:border-indigo-400 focus:outline-none"
                  >
                    {#each orgRoles.filter(r => r !== 'owner') as r}
                      <option value={r}>{roleLabels[r] ?? r}</option>
                    {/each}
                  </select>
                  <button
                    onclick={() => removeMember(member.user)}
                    class="rounded-lg p-1 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600"
                    title="Remover"
                  >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              {/if}
            </div>
          {/each}
        </div>

        <!-- Agregar miembro existente (solo owner/admin) -->
        {#if available.length > 0 && can(OrgActions.MANAGE_ORG_MEMBERS)}
          <div class="border-t border-slate-100 p-4 space-y-2">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Agregar miembro existente</p>
            <select
              bind:value={$memberForm.user_id}
              class="w-full rounded-xl border border-slate-300 bg-white py-2 px-3 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              <option value="">Seleccionar usuario…</option>
              {#each available as u}
                <option value={u.id}>{u.name}</option>
              {/each}
            </select>
            <select
              bind:value={$memberForm.role}
              class="w-full rounded-xl border border-slate-300 bg-white py-2 px-3 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              {#each orgRoles.filter(r => r !== 'owner') as r}
                <option value={r}>{roleLabels[r] ?? r}</option>
              {/each}
            </select>
            <button
              onclick={addMember}
              disabled={!$memberForm.user_id || $memberForm.processing}
              class="w-full rounded-xl bg-indigo-600 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-50"
            >
              {$memberForm.processing ? 'Agregando…' : 'Agregar'}
            </button>
          </div>
        {:else}
          <p class="border-t border-slate-100 px-5 py-3 text-xs text-slate-400">Todos los usuarios ya son miembros.</p>
        {/if}
      </div>

      <!-- Invitar por email (owner / admin / manager) -->
      {#if can(OrgActions.INVITE_MEMBERS)}
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
          <h2 class="font-semibold text-slate-900">Invitar por email</h2>
          <p class="mt-0.5 text-xs text-slate-500">El usuario recibirá un enlace de acceso</p>
        </div>
        <div class="space-y-2 p-4">
          <input
            type="email"
            bind:value={$inviteForm.email}
            placeholder="correo@empresa.com"
            class="w-full rounded-xl border border-slate-300 bg-white py-2 px-3 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />
          {#if $inviteForm.errors.email}
            <p class="text-xs text-rose-600">{$inviteForm.errors.email}</p>
          {/if}
          <select
            bind:value={$inviteForm.role}
            class="w-full rounded-xl border border-slate-300 bg-white py-2 px-3 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          >
            {#each orgRoles.filter(r => r !== 'owner') as r}
              <option value={r}>{roleLabels[r] ?? r}</option>
            {/each}
          </select>
          <button
            onclick={sendInvite}
            disabled={!$inviteForm.email || $inviteForm.processing}
            class="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 py-2 text-sm font-semibold text-white transition hover:from-indigo-700 hover:to-violet-700 disabled:opacity-50"
          >
            {$inviteForm.processing ? 'Enviando…' : 'Enviar invitación'}
          </button>
        </div>

        <!-- Invitaciones pendientes -->
        {#if pendingInvitations.length > 0}
          <div class="border-t border-slate-100">
            <p class="px-5 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
              Pendientes ({pendingInvitations.length})
            </p>
            <div class="divide-y divide-slate-100">
              {#each pendingInvitations as inv}
                <div class="flex items-center justify-between px-5 py-3">
                  <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-slate-800">{inv.email}</p>
                    <div class="flex items-center gap-2 mt-0.5">
                      <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium {roleBadge[inv.role] ?? roleBadge.member}">
                        {roleLabels[inv.role] ?? inv.role}
                      </span>
                      <span class="text-[10px] text-slate-400">vence {inv.expires_at}</span>
                    </div>
                  </div>
                  <button
                    onclick={() => cancelInvitation(inv.id)}
                    title="Cancelar invitación"
                    class="ml-2 shrink-0 rounded-lg p-1.5 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600"
                  >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              {/each}
            </div>
          </div>
        {/if}
      </div>
      {/if}
    </div>

  </div>
</Layout>

<!-- Modal: Nuevo departamento -->
{#if deptModal}
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
    <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">
      <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
        <h3 class="font-semibold text-slate-900">Nuevo departamento</h3>
        <button onclick={() => (deptModal = false)}
        aria-label="Cerrar"
        class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div class="space-y-4 p-6">
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700" for="dept-name">Nombre *</label>
          <input
            id="dept-name"
            bind:value={$deptForm.name}
            type="text"
            placeholder="Ej: Desarrollo, Diseño, QA…"
            class="w-full rounded-xl border border-slate-300 py-2.5 px-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />
          {#if $deptForm.errors.name}
            <p class="mt-1 text-xs text-rose-600">{$deptForm.errors.name}</p>
          {/if}
        </div>

        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700" for="dept-description">Descripción</label>
          <textarea
            id="dept-description"
            bind:value={$deptForm.description}
            rows="2"
            placeholder="Descripción opcional…"
            class="w-full resize-none rounded-xl border border-slate-300 py-2.5 px-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          ></textarea>
        </div>

        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700" for="dept-color">Color</label>
          <div class="flex flex-wrap gap-2">
            {#each deptColors as c}
              <button
                aria-label="Seleccionar color {c}"
                onclick={() => ($deptForm.color = c)}
                class="h-7 w-7 rounded-lg transition hover:scale-110 {$deptForm.color === c ? 'ring-2 ring-offset-2 ring-slate-400' : ''}"
                style="background-color: {c};"
              ></button>
            {/each}
          </div>
        </div>

        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700" for="dept-lead">Team Le    ad (opcional)</label>
          <select
            id="dept-lead"
            bind:value={$deptForm.lead_id}
            class="w-full rounded-xl border border-slate-300 bg-white py-2.5 px-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          >
            <option value="">Sin asignar</option>
            {#each (organization.members ?? []) as m}
              <option value={m.user?.id}>{m.user?.name}</option>
            {/each}
          </select>
        </div>
      </div>

      <div class="flex justify-end gap-3 border-t border-slate-100 px-6 py-4">
        <button
          onclick={() => (deptModal = false)}
          class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
        >Cancelar</button>
        <button
          onclick={createDept}
          disabled={!$deptForm.name || $deptForm.processing}
          class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-50"
        >
          {$deptForm.processing ? 'Creando…' : 'Crear departamento'}
        </button>
      </div>
    </div>
  </div>
{/if}
