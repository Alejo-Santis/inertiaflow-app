<script lang="ts">
  import Layout from '../Layout.svelte';
  import { Link, useForm, router, usePage } from '@inertiajs/svelte';
  import route from 'ziggy-js';

  export let todayList: any[];
  export let upcoming:  any[];
  export let past:      any[];
  export let projects:  any[];
  export let users:     any[];

  const page = usePage();
  $: me = ($page.props.auth as any)?.user;

  // ── Create / Edit form ──────────────────────────────────────────────────
  let showForm    = false;
  let editTarget: any = null;

  const emptyForm = () => ({
    title: '', description: '', meeting_url: '', platform: 'meet',
    scheduled_at: '', duration_minutes: 60,
    project_id: null as number | null,
    participants: [] as number[],
  });

  const form = useForm(emptyForm());

  function openCreate() {
    editTarget = null;
    const e = emptyForm();
    $form.title            = e.title;
    $form.description      = e.description;
    $form.meeting_url      = e.meeting_url;
    $form.platform         = e.platform;
    $form.scheduled_at     = e.scheduled_at;
    $form.duration_minutes = e.duration_minutes;
    $form.project_id       = e.project_id;
    $form.participants     = e.participants;
    showForm = true;
  }

  function openEdit(m: any) {
    editTarget = m;
    $form.title            = m.title;
    $form.description      = m.description ?? '';
    $form.meeting_url      = m.meeting_url ?? '';
    $form.platform         = m.platform ?? 'meet';
    $form.scheduled_at     = m.scheduled_at;
    $form.duration_minutes = m.duration_minutes;
    $form.project_id       = null;
    $form.participants     = m.participants.map((p: any) => p.id);
    showForm = true;
  }

  function submitForm() {
    if (editTarget) {
      $form.put(route('meetings.update', editTarget.uuid), {
        onSuccess: () => { showForm = false; },
      });
    } else {
      $form.post(route('meetings.store'), {
        onSuccess: () => { showForm = false; },
      });
    }
  }

  function deleteMeeting(uuid: string) {
    if (confirm('¿Eliminar esta reunión?')) {
      router.delete(route('meetings.destroy', uuid));
    }
  }

  function toggleParticipant(id: number) {
    const arr: number[] = $form.participants ?? [];
    $form.participants = arr.includes(id) ? arr.filter(x => x !== id) : [...arr, id];
  }

  // ── Platform helpers ────────────────────────────────────────────────────
  const platformConfig: Record<string, { label: string; color: string; bg: string }> = {
    zoom:  { label: 'Zoom',         color: 'text-blue-700',   bg: 'bg-blue-50 ring-blue-200' },
    meet:  { label: 'Google Meet',  color: 'text-emerald-700', bg: 'bg-emerald-50 ring-emerald-200' },
    teams: { label: 'Teams',        color: 'text-violet-700', bg: 'bg-violet-50 ring-violet-200' },
    other: { label: 'Enlace',       color: 'text-slate-600',  bg: 'bg-slate-100 ring-slate-200' },
  };

  function getPlatform(p: string | null) {
    return platformConfig[p ?? 'other'] ?? platformConfig.other;
  }

  function formatDuration(min: number): string {
    if (min < 60) return `${min} min`;
    const h = Math.floor(min / 60);
    const m = min % 60;
    return m ? `${h}h ${m}min` : `${h}h`;
  }
</script>

<Layout title="Reuniones">

  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-slate-900">Mis reuniones</h1>
      <p class="mt-1 text-sm text-slate-500">Vista diaria y próximas reuniones</p>
    </div>
    <button
      onclick={openCreate}
      class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:from-indigo-700 hover:to-violet-700"
    >
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
      </svg>
      Nueva reunión
    </button>
  </div>

  <!-- ── TODAY ──────────────────────────────────────────────────────────── -->
  <section class="mb-8">
    <div class="mb-3 flex items-center gap-2">
      <span class="flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-[10px] font-bold text-white">{todayList.length}</span>
      <h2 class="text-base font-bold text-slate-900">Hoy</h2>
      <span class="text-sm text-slate-500">{new Date().toLocaleDateString('es', { weekday: 'long', day: 'numeric', month: 'long' })}</span>
    </div>

    {#if todayList.length === 0}
      <div class="flex items-center gap-3 rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-5 text-sm text-slate-400">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
        </svg>
        Sin reuniones para hoy — ¡disfruta el día!
      </div>
    {:else}
      <div class="space-y-3">
        {#each todayList as m}
          {@const plat = getPlatform(m.platform)}
          <div class="overflow-hidden rounded-2xl border border-indigo-100 bg-white shadow-sm ring-1 ring-indigo-100">
            <div class="flex items-stretch">
              <!-- Time column -->
              <div class="flex w-20 shrink-0 flex-col items-center justify-center bg-indigo-600 py-4 text-white">
                <span class="text-xl font-bold leading-none">{m.time}</span>
                <span class="mt-1 text-[10px] text-indigo-200">{formatDuration(m.duration_minutes)}</span>
              </div>
              <!-- Content -->
              <div class="flex flex-1 items-center gap-4 px-5 py-4">
                <div class="min-w-0 flex-1">
                  <div class="flex items-center gap-2">
                    <h3 class="truncate font-semibold text-slate-900">{m.title}</h3>
                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-medium ring-1 {plat.bg} {plat.color}">{plat.label}</span>
                  </div>
                  {#if m.description}
                    <p class="mt-0.5 truncate text-xs text-slate-500">{m.description}</p>
                  {/if}
                  <div class="mt-1.5 flex flex-wrap items-center gap-3 text-xs text-slate-500">
                    {#if m.project}
                      <span class="flex items-center gap-1">
                        <span class="h-2 w-2 rounded-full" style="background:{m.project.color}"></span>
                        {m.project.name}
                      </span>
                    {/if}
                    <span>{m.participants.length} participantes · {m.organizer.name}</span>
                  </div>
                </div>
                <!-- Actions -->
                <div class="flex shrink-0 items-center gap-2">
                  {#if m.meeting_url}
                    <a
                      href={m.meeting_url}
                      target="_blank"
                      rel="noopener"
                      class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700"
                    >
                      <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                      </svg>
                      Unirse
                    </a>
                  {/if}
                  {#if m.is_organizer}
                    <button onclick={() => openEdit(m)} class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" /></svg>
                    </button>
                    <button onclick={() => deleteMeeting(m.uuid)} class="rounded-lg p-1.5 text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition">
                      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                    </button>
                  {/if}
                </div>
              </div>
            </div>
          </div>
        {/each}
      </div>
    {/if}
  </section>

  <!-- ── UPCOMING ──────────────────────────────────────────────────────── -->
  {#if upcoming.length > 0}
    <section class="mb-8">
      <h2 class="mb-3 text-base font-bold text-slate-900">Próximas</h2>
      <div class="space-y-2">
        {#each upcoming as m}
          {@const plat = getPlatform(m.platform)}
          <div class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white px-5 py-3.5 shadow-sm transition hover:shadow-md">
            <div class="w-24 shrink-0 text-center">
              <p class="text-sm font-bold text-slate-800">{new Date(m.scheduled_at).toLocaleDateString('es', { day: 'numeric', month: 'short' })}</p>
              <p class="text-xs text-slate-500">{m.time} · {formatDuration(m.duration_minutes)}</p>
            </div>
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2">
                <span class="truncate font-medium text-slate-900">{m.title}</span>
                <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-medium ring-1 {plat.bg} {plat.color}">{plat.label}</span>
              </div>
              <p class="text-xs text-slate-500">{m.participants.length} participantes · {m.organizer.name}</p>
            </div>
            <div class="flex shrink-0 items-center gap-2">
              {#if m.meeting_url}
                <a href={m.meeting_url} target="_blank" rel="noopener"
                  class="rounded-xl border border-indigo-300 px-3 py-1.5 text-xs font-medium text-indigo-700 transition hover:bg-indigo-50">
                  Ver enlace
                </a>
              {/if}
              {#if m.is_organizer}
                <button onclick={() => openEdit(m)} class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 transition">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" /></svg>
                </button>
                <button onclick={() => deleteMeeting(m.uuid)} class="rounded-lg p-1.5 text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                </button>
              {/if}
            </div>
          </div>
        {/each}
      </div>
    </section>
  {/if}

  <!-- ── PAST (collapsible) ─────────────────────────────────────────────── -->
  {#if past.length > 0}
    <details class="group">
      <summary class="mb-3 cursor-pointer list-none">
        <span class="flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-slate-700">
          <svg class="h-4 w-4 transition-transform group-open:rotate-90" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
          </svg>
          Pasadas ({past.length})
        </span>
      </summary>
      <div class="space-y-2">
        {#each past.slice(0, 10) as m}
          <div class="flex items-center gap-4 rounded-2xl border border-slate-100 bg-slate-50 px-5 py-3 opacity-70">
            <div class="w-24 shrink-0 text-center">
              <p class="text-xs font-medium text-slate-600">{new Date(m.scheduled_at).toLocaleDateString('es', { day: 'numeric', month: 'short' })}</p>
              <p class="text-[10px] text-slate-400">{m.time}</p>
            </div>
            <p class="flex-1 truncate text-sm text-slate-600 line-through">{m.title}</p>
            {#if m.is_organizer}
              <button onclick={() => deleteMeeting(m.uuid)} class="rounded-lg p-1.5 text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
              </button>
            {/if}
          </div>
        {/each}
      </div>
    </details>
  {/if}

</Layout>

<!-- ── Create/Edit modal ─────────────────────────────────────────────────── -->
{#if showForm}
  <!-- Backdrop -->
  <!-- svelte-ignore a11y-click-events-have-key-events a11y-no-static-element-interactions -->
  <div class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm" onclick={() => (showForm = false)}></div>

  <div class="fixed left-1/2 top-6 z-50 max-h-[90vh] w-full max-w-lg -translate-x-1/2 overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-2xl">
    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
      <h2 class="font-bold text-slate-900">{editTarget ? 'Editar reunión' : 'Nueva reunión'}</h2>
      <button onclick={() => (showForm = false)} class="rounded-lg p-1 text-slate-400 hover:bg-slate-100">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
      </button>
    </div>

    <form onsubmit={(e) => { e.preventDefault(); submitForm(); }} class="space-y-4 p-6">

      <!-- Title -->
      <div>
        <label class="block text-sm font-medium text-slate-700">Título <span class="text-rose-500">*</span></label>
        <input type="text" bind:value={$form.title} required
          class="mt-1 block w-full rounded-xl border border-slate-300 bg-white py-2.5 px-3.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
          placeholder="Reunión de seguimiento semanal" />
        {#if $form.errors.title}<p class="mt-1 text-xs text-rose-600">{$form.errors.title}</p>{/if}
      </div>

      <!-- Date + Duration -->
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-sm font-medium text-slate-700">Fecha y hora <span class="text-rose-500">*</span></label>
          <input type="datetime-local" bind:value={$form.scheduled_at} required
            class="mt-1 block w-full rounded-xl border border-slate-300 bg-white py-2.5 px-3.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0" />
          {#if $form.errors.scheduled_at}<p class="mt-1 text-xs text-rose-600">{$form.errors.scheduled_at}</p>{/if}
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Duración (min)</label>
          <select bind:value={$form.duration_minutes}
            class="mt-1 block w-full rounded-xl border border-slate-300 bg-white py-2.5 px-3.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0">
            {#each [15, 30, 45, 60, 90, 120, 180] as d}
              <option value={d}>{formatDuration(d)}</option>
            {/each}
          </select>
        </div>
      </div>

      <!-- Platform + URL -->
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-sm font-medium text-slate-700">Plataforma</label>
          <select bind:value={$form.platform}
            class="mt-1 block w-full rounded-xl border border-slate-300 bg-white py-2.5 px-3.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0">
            <option value="meet">Google Meet</option>
            <option value="zoom">Zoom</option>
            <option value="teams">Teams</option>
            <option value="other">Otro</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Enlace</label>
          <input type="url" bind:value={$form.meeting_url}
            class="mt-1 block w-full rounded-xl border border-slate-300 bg-white py-2.5 px-3.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
            placeholder="https://..." />
          {#if $form.errors.meeting_url}<p class="mt-1 text-xs text-rose-600">{$form.errors.meeting_url}</p>{/if}
        </div>
      </div>

      <!-- Project -->
      <div>
        <label class="block text-sm font-medium text-slate-700">Proyecto (opcional)</label>
        <select bind:value={$form.project_id}
          class="mt-1 block w-full rounded-xl border border-slate-300 bg-white py-2.5 px-3.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0">
          <option value={null}>Sin proyecto</option>
          {#each projects as p}
            <option value={p.id}>{p.name}</option>
          {/each}
        </select>
      </div>

      <!-- Description -->
      <div>
        <label class="block text-sm font-medium text-slate-700">Descripción / agenda</label>
        <textarea bind:value={$form.description} rows="2"
          class="mt-1 block w-full resize-none rounded-xl border border-slate-300 bg-white py-2.5 px-3.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
          placeholder="Temas a tratar..."></textarea>
      </div>

      <!-- Participants -->
      <div>
        <label class="block text-sm font-medium text-slate-700">Participantes</label>
        <div class="mt-2 max-h-36 overflow-y-auto space-y-1 rounded-xl border border-slate-200 p-2">
          {#each users.filter(u => u.id !== me?.id) as u}
            <label class="flex cursor-pointer items-center gap-2.5 rounded-lg px-2 py-1.5 hover:bg-slate-50">
              <input type="checkbox"
                checked={($form.participants ?? []).includes(u.id)}
                onchange={() => toggleParticipant(u.id)}
                class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
              <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-[10px] font-bold text-indigo-700">
                {u.name.charAt(0)}
              </div>
              <span class="text-sm text-slate-700">{u.name}</span>
            </label>
          {/each}
        </div>
      </div>

      <!-- Actions -->
      <div class="flex justify-end gap-3 pt-2">
        <button type="button" onclick={() => (showForm = false)}
          class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
          Cancelar
        </button>
        <button type="submit" disabled={$form.processing}
          class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:from-indigo-700 hover:to-violet-700 disabled:opacity-60">
          {$form.processing ? 'Guardando...' : (editTarget ? 'Guardar cambios' : 'Crear reunión')}
        </button>
      </div>
    </form>
  </div>
{/if}
