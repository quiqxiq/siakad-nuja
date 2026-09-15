@extends('layouts.app')

@section('title', 'Buat Password Baru')

@section('content')
<div class="flex min-h-screen items-center justify-center bg-slate-100 dark:bg-slate-950 px-4 py-12">
    <div class="w-full max-w-md">
        <div class="mb-8 flex flex-col items-center text-center">
            <a href="{{ route('landing') }}" class="group flex flex-col items-center transition focus:outline-none" title="Kembali ke Beranda">
                <div class="relative flex h-16 w-16 items-center justify-center transition-transform duration-300 group-hover:scale-105">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Yayasan Nurul Jadid Karduluk" class="h-full w-full object-contain filter drop-shadow-[0_2px_8px_rgba(0,140,227,0.45)]">
                </div>
                <h1 class="mt-3 text-2xl font-bold text-slate-900 dark:text-white transition-colors group-hover:text-brand-600 dark:group-hover:text-brand-400">SIAKAD NUJA</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Atur Ulang Password</p>
            </a>
        </div>

        <div class="rounded-2xl bg-white dark:bg-slate-800 p-6 sm:p-8 shadow-sm ring-1 ring-slate-200/70 dark:ring-slate-700/70">
            <div class="mb-6 text-center">
                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 text-brand-600 dark:bg-brand-950/60 dark:text-brand-400">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Buat Password Baru</h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Kode OTP berhasil diverifikasi. Silakan masukkan password baru untuk akun <strong>{{ $user->nama }}</strong>.
                </p>
            </div>

            @if (session('success'))
                <div class="mb-5">
                    <x-alert type="success">{{ session('success') }}</x-alert>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-5">
                    <x-alert type="error">{{ session('error') }}</x-alert>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5">
                    <x-alert type="error">{{ $errors->first() }}</x-alert>
                </div>
            @endif

            <form method="POST" action="{{ route('password.reset_password') }}" class="space-y-5">
                @csrf

                <x-form.input
                    label="Password Baru"
                    name="password"
                    type="password"
                    placeholder="Minimal 8 karakter"
                    required
                    autofocus
                    autocomplete="new-password"
                    hint="Gunakan minimal 8 karakter yang aman."
                />

                <x-form.input
                    label="Konfirmasi Password Baru"
                    name="password_confirmation"
                    type="password"
                    placeholder="Ketik ulang password baru"
                    required
                    autocomplete="new-password"
                />

                <x-button type="submit" variant="primary" class="w-full justify-center">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan Password Baru
                </x-button>
            </form>

            <div class="mt-6 border-t border-slate-200/80 dark:border-slate-700/80 pt-4 text-center">
                <a href="{{ route('login') }}" class="inline-flex items-center text-xs font-medium text-brand-600 hover:text-brand-500 dark:text-brand-400 dark:hover:text-brand-300 transition">
                    <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Batalkan dan Kembali ke Halaman Masuk
                </a>
            </div>
        </div>

        <p class="mt-6 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} SIAKAD NUJA — Yayasan Nurul Jadid Karduluk
        </p>
    </div>
</div>
@endsection
