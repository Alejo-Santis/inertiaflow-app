<script lang="ts">
  export let user: { name: string; avatar_url?: string | null };
  export let size: 'xs' | 'sm' | 'md' | 'lg' = 'sm';

  const sizeClasses = {
    xs: 'h-5 w-5 text-xs',
    sm: 'h-7 w-7 text-xs',
    md: 'h-9 w-9 text-sm',
    lg: 'h-16 w-16 text-2xl',
  };

  $: initials = user.name.split(' ').map((n: string) => n[0]).slice(0, 2).join('').toUpperCase();
  $: cls = sizeClasses[size];
</script>

{#if user.avatar_url}
  <img src={user.avatar_url} alt={user.name} class="{cls} rounded-full object-cover shrink-0" />
{:else}
  <div class="{cls} rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center font-semibold text-white shrink-0">
    {initials}
  </div>
{/if}
