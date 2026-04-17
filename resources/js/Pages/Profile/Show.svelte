<script lang="ts">
  import Layout from '../Layout.svelte';
  import { useForm, router } from '@inertiajs/svelte';
  import route from 'ziggy-js';

  export let user: {
    id: number; name: string; email: string;
    avatar_url?: string | null;
    roles?: { name: string }[];
  };

  // Profile form
  const profileForm = useForm({
    name:  user.name,
    email: user.email,
  });

  // Password form
  const passwordForm = useForm({
    current_password:      '',
    password:              '',
    password_confirmation: '',
  });

  // Avatar upload
  let avatarInput: HTMLInputElement;
  let avatarPreview: string | null = user.avatar_url ?? null;
  let avatarFile: File | null = null;
  let avatarUploading = false;

  const submitProfile = (e: Event) => {
    e.preventDefault();
    $profileForm.put(route('profile.update'));
  };

  const submitPassword = (e: Event) => {
    e.preventDefault();
    $passwordForm.put(route('profile.password'), {
      onSuccess: () => $passwordForm.reset(),
    });
  };

  function onAvatarChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;
    avatarFile = file;
    avatarPreview = URL.createObjectURL(file);
  }

  function uploadAvatar() {
    if (!avatarFile) return;
    avatarUploading = true;
    router.post(route('profile.avatar'), { avatar: avatarFile }, {
      forceFormData: true,
      onFinish: () => {
        avatarUploading = false;
        avatarFile = null;
      },
    });
  }

  function deleteAvatar() {
    if (!confirm('¿Eliminar foto de perfil?')) return;
    router.delete(route('profile.avatar.delete'), {
      onSuccess: () => { avatarPreview = null; },
    });
  }

  $: roleLabel = user.roles?.map(r => r.name).join(', ') ?? '';
  $: initials = user.name.split(' ').map((n: string) => n[0]).slice(0, 2).join('').toUpperCase();
</script>

<Layout title="Mi perfil">
  <div class="mx-auto max-w-2xl space-y-8">

    <!-- Page header -->
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Mi perfil</h1>
      <p class="mt-1 text-sm text-slate-500">Administra tu información personal y seguridad de cuenta.</p>
    </div>

    <!-- Avatar + role card -->
    <div class="flex items-center gap-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
      <div class="relative shrink-0">
        {#if avatarPreview}
          <img src={avatarPreview} alt={user.name} class="h-16 w-16 rounded-2xl object-cover" />
        {:else}
          <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-2xl font-bold text-white">
            {initials}
          </div>
        {/if}
        <button
          type="button"
          onclick={() => avatarInput.click()}
          class="absolute -bottom-1.5 -right-1.5 flex h-6 w-6 items-center justify-center rounded-full border-2 border-white bg-slate-700 text-white shadow hover:bg-slate-800"
          title="Cambiar foto"
        >
          <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
          </svg>
        </button>
        <input bind:this={avatarInput} type="file" accept="image/*" class="hidden" onchange={onAvatarChange} />
      </div>

      <div class="min-w-0 flex-1">
        <p class="text-lg font-semibold text-slate-900">{user.name}</p>
        <p class="text-sm text-slate-500">{user.email}</p>
        {#if roleLabel}
          <span class="mt-1.5 inline-block rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-semibold capitalize text-indigo-700">
            {roleLabel}
          </span>
        {/if}
      </div>

      <div class="flex shrink-0 flex-col gap-2">
        {#if avatarFile}
          <button
            type="button"
            onclick={uploadAvatar}
            disabled={avatarUploading}
            class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700 disabled:opacity-50"
          >
            {avatarUploading ? 'Subiendo…' : 'Guardar foto'}
          </button>
          <button type="button" onclick={() => { avatarFile = null; avatarPreview = user.avatar_url ?? null; }} class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50">Cancelar</button>
        {:else if user.avatar_url}
          <button type="button" onclick={deleteAvatar} class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-medium text-rose-600 hover:bg-rose-50">Eliminar foto</button>
        {/if}
      </div>
    </div>

    <!-- Personal info card -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
      <div class="border-b border-slate-100 px-6 py-4">
        <h2 class="text-base font-semibold text-slate-800">Información personal</h2>
        <p class="mt-0.5 text-xs text-slate-500">Actualiza tu nombre y correo electrónico.</p>
      </div>
      <form onsubmit={submitProfile} class="space-y-5 p-6">

        <!-- Name -->
        <div>
          <label for="name" class="block text-sm font-medium text-slate-700">Nombre completo</label>
          <input
            id="name"
            type="text"
            bind:value={$profileForm.name}
            class="mt-1.5 block w-full rounded-xl border py-2.5 px-4 text-sm text-slate-900 shadow-sm transition
              {$profileForm.errors.name
                ? 'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-500'
                : 'border-slate-300 bg-white focus:border-indigo-500 focus:ring-indigo-500'}
              focus:outline-none focus:ring-2 focus:ring-offset-0"
            required
          />
          {#if $profileForm.errors.name}
            <p class="mt-1.5 text-xs text-rose-600">{$profileForm.errors.name}</p>
          {/if}
        </div>

        <!-- Email -->
        <div>
          <label for="email" class="block text-sm font-medium text-slate-700">Correo electrónico</label>
          <input
            id="email"
            type="email"
            bind:value={$profileForm.email}
            class="mt-1.5 block w-full rounded-xl border py-2.5 px-4 text-sm text-slate-900 shadow-sm transition
              {$profileForm.errors.email
                ? 'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-500'
                : 'border-slate-300 bg-white focus:border-indigo-500 focus:ring-indigo-500'}
              focus:outline-none focus:ring-2 focus:ring-offset-0"
            required
          />
          {#if $profileForm.errors.email}
            <p class="mt-1.5 text-xs text-rose-600">{$profileForm.errors.email}</p>
          {/if}
        </div>

        <div class="flex justify-end">
          <button
            type="submit"
            disabled={$profileForm.processing}
            class="flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-60"
          >
            {#if $profileForm.processing}
              <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
              </svg>
            {/if}
            Guardar cambios
          </button>
        </div>
      </form>
    </div>

    <!-- Change password card -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
      <div class="border-b border-slate-100 px-6 py-4">
        <h2 class="text-base font-semibold text-slate-800">Cambiar contraseña</h2>
        <p class="mt-0.5 text-xs text-slate-500">Usa al menos 8 caracteres para mayor seguridad.</p>
      </div>
      <form onsubmit={submitPassword} class="space-y-5 p-6">

        <!-- Current password -->
        <div>
          <label for="current_password" class="block text-sm font-medium text-slate-700">Contraseña actual</label>
          <input
            id="current_password"
            type="password"
            autocomplete="current-password"
            placeholder="••••••••"
            bind:value={$passwordForm.current_password}
            class="mt-1.5 block w-full rounded-xl border py-2.5 px-4 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition
              {$passwordForm.errors.current_password
                ? 'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-500'
                : 'border-slate-300 bg-white focus:border-indigo-500 focus:ring-indigo-500'}
              focus:outline-none focus:ring-2 focus:ring-offset-0"
            required
          />
          {#if $passwordForm.errors.current_password}
            <p class="mt-1.5 text-xs text-rose-600">{$passwordForm.errors.current_password}</p>
          {/if}
        </div>

        <!-- New password -->
        <div>
          <label for="new_password" class="block text-sm font-medium text-slate-700">Nueva contraseña</label>
          <input
            id="new_password"
            type="password"
            autocomplete="new-password"
            placeholder="Mínimo 8 caracteres"
            bind:value={$passwordForm.password}
            class="mt-1.5 block w-full rounded-xl border py-2.5 px-4 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition
              {$passwordForm.errors.password
                ? 'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-500'
                : 'border-slate-300 bg-white focus:border-indigo-500 focus:ring-indigo-500'}
              focus:outline-none focus:ring-2 focus:ring-offset-0"
            required
          />
          {#if $passwordForm.errors.password}
            <p class="mt-1.5 text-xs text-rose-600">{$passwordForm.errors.password}</p>
          {/if}
        </div>

        <!-- Confirm new password -->
        <div>
          <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirmar nueva contraseña</label>
          <input
            id="password_confirmation"
            type="password"
            autocomplete="new-password"
            placeholder="Repite la nueva contraseña"
            bind:value={$passwordForm.password_confirmation}
            class="mt-1.5 block w-full rounded-xl border py-2.5 px-4 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition
              {$passwordForm.errors.password_confirmation
                ? 'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-500'
                : 'border-slate-300 bg-white focus:border-indigo-500 focus:ring-indigo-500'}
              focus:outline-none focus:ring-2 focus:ring-offset-0"
            required
          />
          {#if $passwordForm.errors.password_confirmation}
            <p class="mt-1.5 text-xs text-rose-600">{$passwordForm.errors.password_confirmation}</p>
          {/if}
        </div>

        <div class="flex justify-end">
          <button
            type="submit"
            disabled={$passwordForm.processing}
            class="flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-60"
          >
            {#if $passwordForm.processing}
              <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
              </svg>
            {/if}
            Actualizar contraseña
          </button>
        </div>
      </form>
    </div>

  </div>
</Layout>
