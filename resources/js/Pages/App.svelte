<script>
    let { user } = $props();
</script>
<div class="min-h-screen bg-gray-100">
    <div class="flex items-center justify-center h-screen">
        <div class="bg-white p-8 rounded shadow-md">
            <h1 class="text-2xl font-bold mb-4">Welcome to {user.page}, {user.name}!</h1>
            <p class="text-gray-600">Your email is: {user.email}</p>
        </div>
    </div>
</div>
