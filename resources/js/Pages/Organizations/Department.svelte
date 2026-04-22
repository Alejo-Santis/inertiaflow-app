<script lang="ts">
  import Layout from '../Layout.svelte';
  import { Link, useForm, router } from '@inertiajs/svelte';
  import route from 'ziggy-js';
  import { useOrgAbilities, OrgActions } from '../../lib/orgCan';
  import Swal from 'sweetalert2';

  let { organization, department, available, deptRoles }: { organization: any; department: any; available: any[]; deptRoles: string[] } = $props();

  // Permisos del usuario en esta organización
  const can = useOrgAbilities(organization.uuid);

  const roleLabels: Record<string, string> = {
    team_lead: 'Team Lead',
    tech_lead: 'Tech Lead',
    senior:    'Senior',
    member:    'Member',
  };

  const roleBadge: Record<string, string> = {
    team_lead: 'bg-violet-100 text-violet-700 ring-1 ring-violet-200',
    tech_lead: 'bg-indigo-100 text-indigo-700 ring-1 ring-indigo-200',
    senior:    'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
    member:    'bg-slate-100 text-slate-600 ring-1 ring-slate-200',
  };

  const addForm = useForm({ user_id: '', role: 'member' });

  const addMember = () => {
    if (!$addForm.user_id) return;
    $addForm.post(route('organizations.departments.members.store', [organization.uuid, department.uuid]), {
      onSuccess: () => { $addForm.user_id = ''; $addForm.role = 'member'; },
    });
  };

  const changeRole = (user: any, newRole: string) =>
    router.patch(
      route('organizations.departments.members.update', [organization.uuid, department.uuid, user.uuid]),
      { role: newRole },
      { preserveScroll: true }
    );

  const removeMember = async (user: any) => {
    const result = await Swal.fire({
      title: '¿Remover del departamento?',
      text: `${user.name} será removido del equipo.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc2626',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Sí, remover',
      cancelButtonText: 'Cancelar',
    });
    if (result.isConfirmed) {
      router.delete(
        route('organizations.departments.members.destroy', [organization.uuid, department.uuid, user.uuid]),
        { preserveScroll: true }
      );
    }
  };

  const getInitials = (name: string): string =>
    name.split(' ').map((n: string) => n[0]).slice(0, 2).join('').toUpperCase();

  // Ordenar miembros por jerarquía
  const roleOrder: Record<string, number> = { team_lead: 0, tech_lead: 1, senior: 2, member: 3 };
  let sortedMembers = $derived([...(department.members ?? [])].sort(
    (a, b) => (roleOrder[a.role] ?? 99) - (roleOrder[b.role] ?? 99)
  ));
</script>

<Layout title={department.name}>

  <!-- Breadcrumb -->
  <nav class="mb-6 flex items-center gap-2 text-sm text-slate-500">
    <Link href={route('organizations.index')} class="hover:text-slate-700">Organizaciones</Link>
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
    </svg>
    <Link href={route('organizations.show', organization.uuid)} class="hover:text-slate-700">{organization.name}</Link>
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
    </svg>
    <span class="font-medium text-slate-900">{department.name}</span>
  </nav>

  <div class="grid gap-6 lg:grid-cols-3">

    <!-- Main: Jerarquía del equipo -->
    <div class="space-y-6 lg:col-span-2">

      <!-- Dept header -->
      <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="h-1.5 w-full" style="background-color: {department.color ?? '#6366f1'};"></div>
        <div class="p-6">
          <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-lg font-bold text-white shadow-sm"
                 style="background-color: {department.color ?? '#6366f1'};">
              {department.name.charAt(0).toUpperCase()}
            </div>
            <div>
              <h1 class="text-xl font-bold text-slate-900">{department.name}</h1>
              {#if department.lead}
                <p class="mt-0.5 text-sm text-slate-500">
                  Team Lead: <span class="font-medium text-slate-700">{department.lead.name}</span>
                </p>
              {/if}
            </div>
          </div>
          {#if department.description}
            <p class="mt-4 text-sm text-slate-600">{department.description}</p>
          {/if}
        </div>
      </div>

      <!-- Jerarquía del equipo -->
      <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-6 py-4">
          <h2 class="font-semibold text-slate-900">Equipo</h2>
          <p class="mt-0.5 text-xs text-slate-500">{sortedMembers.length} miembro{sortedMembers.length !== 1 ? 's' : ''} · ordenados por jerarquía</p>
        </div>

        {#if sortedMembers.length > 0}
          <div class="divide-y divide-slate-100">
            {#each sortedMembers as member}
              <div class="flex items-center justify-between px-6 py-4">
                <div class="flex items-center gap-4">
                  <!-- Jerarquía visual: borde lateral según rol -->
                  <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-sm font-bold text-white"
                       style="background-color: {department.color ?? '#6366f1'};">
                    {getInitials(member.user?.name ?? '?')}
                  </div>
                  <div>
                    <p class="font-medium text-slate-900">{member.user?.name}</p>
                    <p class="text-xs text-slate-500">{member.user?.email}</p>
                  </div>
                </div>

                <div class="flex items-center gap-3">
                  <!-- Badge de rol -->
                  <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {roleBadge[member.role] ?? roleBadge.member}">
                    {roleLabels[member.role] ?? member.role}
                  </span>

                  {#if can(OrgActions.MANAGE_DEPT_MEMBERS)}
                    <!-- Cambiar rol -->
                    <select
                      value={member.role}
                      onchange={(e) => changeRole(member.user, (e.target as HTMLSelectElement).value)}
                      class="rounded-lg border border-slate-200 bg-white py-1 pl-2 pr-6 text-xs text-slate-700 focus:border-indigo-400 focus:outline-none"
                    >
                      {#each deptRoles as r}
                        <option value={r}>{roleLabels[r] ?? r}</option>
                      {/each}
                    </select>

                    <button
                      onclick={() => removeMember(member.user)}
                      class="rounded-lg p-1.5 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600"
                      title="Remover del departamento"
                    >
                      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                      </svg>
                    </button>
                  {/if}
                </div>
              </div>
            {/each}
          </div>
        {:else}
          <div class="flex flex-col items-center py-12 text-center">
            <p class="text-sm text-slate-500">Sin miembros aún.</p>
          </div>
        {/if}
      </div>

      <!-- Proyectos del departamento -->
      {#if department.projects?.length > 0}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
          <div class="border-b border-slate-100 px-6 py-4">
            <h2 class="font-semibold text-slate-900">Proyectos asignados</h2>
          </div>
          <div class="divide-y divide-slate-100">
            {#each department.projects as project}
              <div class="flex items-center justify-between px-6 py-3">
                <div class="flex items-center gap-3">
                  <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-xs font-bold text-white"
                       style="background-color: {project.color ?? '#6366f1'};">
                    {project.name.charAt(0).toUpperCase()}
                  </div>
                  <div>
                    <p class="text-sm font-medium text-slate-800">{project.name}</p>
                    <p class="text-xs text-slate-500">{project.tasks_count ?? 0} tareas</p>
                  </div>
                </div>
                <Link
                  href={route('projects.show', project.uuid)}
                  class="text-xs font-medium text-indigo-600 hover:text-indigo-800"
                >Ver →</Link>
              </div>
            {/each}
          </div>
        </div>
      {/if}

    </div>

    <!-- Sidebar: Agregar miembro -->
    {#if can(OrgActions.MANAGE_DEPT_MEMBERS)}
    <div>
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
          <h2 class="font-semibold text-slate-900">Agregar al equipo</h2>
          <p class="mt-0.5 text-xs text-slate-500">Solo miembros de la organización</p>
        </div>

        {#if available.length > 0}
          <div class="space-y-3 p-4">
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600" for="user-select">Usuario</label>
              <select
                id="user-select"
                bind:value={$addForm.user_id}
                class="w-full rounded-xl border border-slate-300 bg-white py-2 px-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
              >
                <option value="">Seleccionar…</option>
                {#each available as u}
                  <option value={u.id}>{u.name}</option>
                {/each}
              </select>
            </div>

            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600" for="role-select">Rol en el equipo</label>
              <select
                id="role-select"
                bind:value={$addForm.role}
                class="w-full rounded-xl border border-slate-300 bg-white py-2 px-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
              >
                {#each deptRoles as r}
                  <option value={r}>{roleLabels[r] ?? r}</option>
                {/each}
              </select>
            </div>

            <!-- Leyenda de roles -->
            <div class="rounded-xl bg-slate-50 p-3 space-y-1.5">
              <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Roles disponibles</p>
              {#each deptRoles as r}
                <div class="flex items-center justify-between">
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium {roleBadge[r] ?? ''}">
                    {roleLabels[r]}
                  </span>
                  <span class="text-[10px] text-slate-400">
                    {r === 'team_lead' ? 'Gestión del equipo' :
                     r === 'tech_lead' ? 'Decisiones técnicas' :
                     r === 'senior'    ? 'Desarrollador senior' :
                                         'Miembro del equipo'}
                  </span>
                </div>
              {/each}
            </div>

            <button
              onclick={addMember}
              disabled={!$addForm.user_id || $addForm.processing}
              class="w-full rounded-xl bg-indigo-600 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-50"
            >
              {$addForm.processing ? 'Agregando…' : 'Agregar al equipo'}
            </button>
          </div>
        {:else}
          <p class="px-5 py-6 text-center text-sm text-slate-400">
            Todos los miembros de la org ya están en este departamento.
          </p>
        {/if}
      </div>
    </div>
    {/if}

  </div>

</Layout>
