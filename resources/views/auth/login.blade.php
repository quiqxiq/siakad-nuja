@extends('layouts.app')

@section('title', 'Masuk')

@section('content')
<div class="flex min-h-screen items-center justify-center bg-slate-100 dark:bg-slate-950 px-4 py-12">
    <div class="w-full max-w-md">
        <div class="mb-8 flex flex-col items-center text-center">
            <a href="{{ route('landing') }}" class="group flex flex-col items-center transition focus:outline-none" title="Kembali ke Beranda / Landing Page">
                <div class="relative flex h-16 w-16 items-center justify-center transition-transform duration-300 group-hover:scale-105">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Yayasan Nurul Jadid Karduluk" class="h-full w-full object-contain filter drop-shadow-[0_2px_8px_rgba(0,140,227,0.45)]">
                </div>
                <h1 class="mt-3 text-2xl font-bold text-slate-900 dark:text-white transition-colors group-hover:text-brand-600 dark:group-hover:text-brand-400">SIAKAD NUJA</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Sistem Informasi Akademik Nurul Jadid</p>
            </a>
        </div>

        <div class="rounded-2xl bg-white dark:bg-slate-800 p-6 sm:p-8 shadow-sm ring-1 ring-slate-200/70 dark:ring-slate-700/70">
            @if ($errors->any())
                <div class="mb-5">
                    <x-alert type="error">{{ $errors->first() }}</x-alert>
                </div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}" class="space-y-5">
                @csrf
                <x-form.input label="Email / Username" name="email" type="text" :value="old('email')" placeholder="admin@siakadnuja.sch.id atau admin" required autofocus autocomplete="username" />
                <x-form.input label="Password" name="password" type="password" placeholder="••••••••" required autocomplete="current-password" />

                <div class="flex items-center justify-between">
                    <x-form.checkbox label="Ingat saya" name="remember" />
                </div>

                <x-button type="submit" variant="primary" class="w-full">
                    <x-icon name="logout" class="h-4 w-4" /> Masuk
                </x-button>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} SIAKAD NUJA — Yayasan Nurul Jadid Karduluk
        </p>
    </div>
</div>
@endsection
