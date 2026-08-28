<!DOCTYPE html>
<html lang="id" x-data="{ dark: $persist(false) }" :class="{ 'dark': dark }" x-cloak>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name', 'SIAKAD NUJA') }}</title>
    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Siakad Nuja" />
    <link rel="manifest" href="/site.webmanifest" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100 antialiased font-sans">

@auth
<div x-data="{ sidebarOpen: false }" class="min-h-screen">

    {{-- Sidebar desktop --}}
    <aside class="hidden lg:fixed lg:inset-y-0 lg:left-0 lg:z-40 lg:block lg:w-64">
        @include('layouts.partials.sidebar')
    </aside>

    {{-- Sidebar mobile (drawer) --}}
    <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-50 lg:hidden" style="display:none">
        <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" class="absolute inset-0 bg-slate-900/60"></div>
        <div x-show="sidebarOpen"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
             class="absolute inset-y-0 left-0 w-64">
            <button @click="sidebarOpen = false" class="absolute -right-10 top-3 text-white/80 hover:text-white">
                <x-icon name="close" class="h-6 w-6" />
            </button>
            @include('layouts.partials.sidebar')
        </div>
    </div>

    {{-- Konten utama --}}
    <div class="lg:pl-64">
        {{-- Topbar --}}
        <header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 px-4 backdrop-blur sm:px-6">
            <button @click="sidebarOpen = true" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 lg:hidden">
                <x-icon name="menu" class="h-6 w-6" />
            </button>

            <div class="flex-1 text-sm font-semibold text-slate-700 dark:text-slate-200">
                @yield('title', 'Dashboard')
            </div>

            {{-- Toggle dark mode --}}
            <button @click="dark = !dark" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800" title="Ganti tema">
                <x-icon name="sun" class="h-5 w-5 hidden dark:block" />
                <x-icon name="moon" class="h-5 w-5 block dark:hidden" />
            </button>

            {{-- Menu user --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center gap-2 rounded-lg p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-600 text-sm font-semibold text-white">
                        {{ strtoupper(substr(auth()->user()->nama ?? 'U', 0, 1)) }}
                    </div>
                    <x-icon name="chevron-down" class="hidden h-4 w-4 text-slate-400 sm:block" />
                </button>
                <div x-show="open" x-cloak @click.outside="open = false" x-transition
                     class="absolute right-0 mt-2 w-48 origin-top-right rounded-lg bg-white dark:bg-slate-800 py-1 shadow-lg ring-1 ring-slate-200 dark:ring-slate-700" style="display:none">
                    <div class="border-b border-slate-100 dark:border-slate-700 px-4 py-2">
                        <p class="truncate text-sm font-medium text-slate-900 dark:text-white">{{ auth()->user()->nama }}</p>
                        <p class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</p>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700">
                        <x-icon name="user" class="h-4 w-4" /> Profil Saya
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-slate-50 dark:hover:bg-slate-700">
                            <x-icon name="logout" class="h-4 w-4" /> Logout
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="p-4 sm:p-6 lg:p-8">
            @if (session('success'))
                <div class="mb-4"><x-alert type="success">{{ session('success') }}</x-alert></div>
            @endif
            @if (session('error'))
                <div class="mb-4"><x-alert type="error">{{ session('error') }}</x-alert></div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
@else
    @yield('content')
@endauth

</body>
</html>
