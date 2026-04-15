<script lang="ts">
  import { usePage, router, Link } from '@inertiajs/svelte';
  import route from 'ziggy-js';

  export let status: 'pending' | 'expired' | 'already_accepted';
  export let invitation: {
    email: string;
    role: string;
    role_label: string;
    expires_at: string;
    accepted_at?: string;
    organization: { name: string; uuid: string; description?: string };
    inviter: { name: string };
  };
  export let token: string | null = null;

  const page = usePage();
  $: user = ($page.props.auth as any)?.user;

  const roleColors: Record<string, string> = {
    owner:   'bg-violet-100 text-violet-700',
    admin:   'bg-rose-50 text-rose-700',
    manager: 'bg-amber-50 text-amber-700',
    member:  'bg-indigo-50 text-indigo-700',
  };

  let accepting = false;

  function accept() {
    if (!token) return;
    accepting = true;
    router.post(route('organizations.invitations.accept', [invitation.organization.uuid, token]), {}, {
      onFinish: () => (accepting = false),
    });
  }
</script>

<svelte:head>
  <title>Invitación — {invitation.organization.name}</title>
</svelte:head>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-indigo-50/30 to-violet-50/20 flex items-center justify-center p-4">
  <div class="w-full max-w-md">

    <!-- Logo -->
    <div class="mb-8 flex justify-center">
      <div class="flex items-center gap-2.5">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 shadow-md">
          <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
          </svg>
        </div>
        <span class="bg-gradient-to-r from-indigo-600 to-violet-600 bg-clip-text text-xl font-bold text-transparent">InertiaFlow</span>
      </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">

      <!-- ── ESTADO: PENDIENTE (válida) ───────────────────────────────── -->
      {#if status === 'pending'}
        <div class="h-1.5 w-full bg-gradient-to-r from-indigo-500 to-violet-600"></div>
        <div class="p-8">
          <div class="text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-2xl font-bold text-white shadow-md">
              {invitation.organization.name.charAt(0).toUpperCase()}
            </div>
            <h1 class="text-xl font-bold text-slate-900">Te han invitado a unirte</h1>
            <p class="mt-1 text-sm text-slate-500">
              <strong class="text-slate-700">{invitation.inviter.name}</strong> te invita a:
            </p>
          </div>

          <!-- Org card -->
          <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-5">
            <p class="font-semibold text-slate-900 text-lg">{invitation.organization.name}</p>
            {#if invitation.organization.description}
              <p class="mt-1 text-sm text-slate-500">{invitation.organization.description}</p>
            {/if}
            <div class="mt-3 flex items-center gap-2">
              <span class="text-xs text-slate-500">Tu rol:</span>
              <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {roleColors[invitation.role] ?? 'bg-slate-100 text-slate-700'}">
                {invitation.role_label}
              </span>
            </div>
            <p class="mt-2 text-xs text-slate-400">Invitado al correo: {invitation.email}</p>
            <p class="text-xs text-slate-400">Válido hasta: {invitation.expires_at}</p>
          </div>

          <!-- Acciones según estado de autenticación -->
          <div class="mt-6">
            {#if user}
              {#if user.email.toLowerCase() === invitation.email.toLowerCase()}
                <!-- Usuario logueado con el email correcto: puede aceptar -->
                <button
                  onclick={accept}
                  disabled={accepting}
                  class="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 py-3 text-sm font-semibold text-white shadow-sm transition hover:from-indigo-700 hover:to-violet-700 disabled:opacity-50"
                >
                  {accepting ? 'Aceptando…' : 'Aceptar invitación'}
                </button>
                <p class="mt-2 text-center text-xs text-slate-400">
                  Ingresarás como <strong>{user.name}</strong> ({user.email})
                </p>
              {:else}
                <!-- Usuario logueado pero con otro email -->
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-center">
                  <p class="text-sm font-medium text-amber-800">Email incorrecto</p>
                  <p class="mt-1 text-xs text-amber-700">
                    Esta invitación es para <strong>{invitation.email}</strong> pero estás logueado como <strong>{user.email}</strong>.
                    Cierra sesión y vuelve a intentarlo.
                  </p>
                </div>
                <Link
                  href={route('dashboard')}
                  class="mt-3 block w-full rounded-xl border border-slate-200 py-2.5 text-center text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                >
                  Ir al dashboard
                </Link>
              {/if}
            {:else}
              <!-- No está logueado: pedir que inicie sesión -->
              <div class="space-y-3">
                <p class="text-center text-sm text-slate-600">
                  Inicia sesión con <strong>{invitation.email}</strong> para aceptar.
                </p>
                <Link
                  href="{route('login')}?redirect=/invitations/{token}"
                  class="block w-full rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 py-3 text-center text-sm font-semibold text-white shadow-sm transition hover:from-indigo-700 hover:to-violet-700"
                >
                  Iniciar sesión para aceptar
                </Link>
                <p class="text-center text-xs text-slate-400">
                  ¿No tienes cuenta? Pide al administrador que te cree un usuario.
                </p>
              </div>
            {/if}
          </div>
        </div>

      <!-- ── ESTADO: EXPIRADA ─────────────────────────────────────────── -->
      {:else if status === 'expired'}
        <div class="h-1.5 w-full bg-gradient-to-r from-amber-400 to-orange-500"></div>
        <div class="p-8 text-center">
          <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-100">
            <svg class="h-8 w-8 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
          </div>
          <h1 class="text-xl font-bold text-slate-900">Invitación expirada</h1>
          <p class="mt-2 text-sm text-slate-500">
            Esta invitación a <strong class="text-slate-700">{invitation.organization.name}</strong> expiró el {invitation.expires_at}.
          </p>
          <p class="mt-2 text-sm text-slate-500">
            Contacta a un administrador de la organización para que te envíe una nueva invitación.
          </p>
          {#if user}
            <Link href={route('dashboard')} class="mt-6 block text-sm font-medium text-indigo-600 hover:text-indigo-800">
              Ir al dashboard →
            </Link>
          {:else}
            <Link href={route('login')} class="mt-6 block text-sm font-medium text-indigo-600 hover:text-indigo-800">
              Iniciar sesión →
            </Link>
          {/if}
        </div>

      <!-- ── ESTADO: YA ACEPTADA ──────────────────────────────────────── -->
      {:else if status === 'already_accepted'}
        <div class="h-1.5 w-full bg-gradient-to-r from-emerald-400 to-teal-500"></div>
        <div class="p-8 text-center">
          <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-100">
            <svg class="h-8 w-8 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
            </svg>
          </div>
          <h1 class="text-xl font-bold text-slate-900">Invitación ya aceptada</h1>
          <p class="mt-2 text-sm text-slate-500">
            Esta invitación a <strong class="text-slate-700">{invitation.organization.name}</strong>
            ya fue aceptada el {invitation.accepted_at}.
          </p>
          {#if user}
            <Link
              href={route('organizations.show', invitation.organization.uuid)}
              class="mt-6 inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:from-indigo-700 hover:to-violet-700"
            >
              Ir a {invitation.organization.name}
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
              </svg>
            </Link>
          {/if}
        </div>
      {/if}

    </div>
  </div>
</div>
