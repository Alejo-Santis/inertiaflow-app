<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — InertiaFlow</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-50 font-sans antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-16">

        <!-- Logo -->
        <div class="mb-8 flex items-center gap-2">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-600 to-violet-600 shadow-sm">
                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                </svg>
            </div>
            <span class="text-lg font-bold text-slate-900">InertiaFlow</span>
        </div>

        <!-- Card -->
        <div class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="h-1 w-full @yield('bar-color')"></div>
            <div class="px-8 py-10 text-center">

                <!-- Error code -->
                <p class="text-8xl font-black tracking-tighter @yield('code-color')">@yield('code')</p>

                <!-- Title -->
                <h1 class="mt-4 text-xl font-bold text-slate-900">@yield('heading')</h1>

                <!-- Message -->
                <p class="mt-2 text-sm leading-relaxed text-slate-500">@yield('message')</p>

                <!-- Actions -->
                <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
                    <a
                        href="{{ url('/dashboard') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                        </svg>
                        Ir al dashboard
                    </a>
                    <button
                        onclick="history.back()"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                        Volver atrás
                    </button>
                </div>
            </div>
        </div>

        <p class="mt-6 text-xs text-slate-400">
            ¿El problema persiste? Contacta a tu administrador.
        </p>
    </div>
</body>
</html>
