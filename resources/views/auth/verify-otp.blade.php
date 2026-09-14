@extends('layouts.app')

@section('title', 'Verifikasi Kode OTP WhatsApp')

@section('content')
<div class="flex min-h-screen items-center justify-center bg-slate-100 dark:bg-slate-950 px-4 py-12"
    x-data="{
        cooldown: {{ $cooldown ?? 0 }},
        init() {
            if (this.cooldown > 0) {
                const timer = setInterval(() => {
                    this.cooldown--;
                    if (this.cooldown <= 0) clearInterval(timer);
                }, 1000);
            }
        }
    }">
    <div class="w-full max-w-md">
        <div class="mb-8 flex flex-col items-center text-center">
            <a href="{{ route('landing') }}" class="group flex flex-col items-center transition focus:outline-none" title="Kembali ke Beranda">
                <div class="relative flex h-16 w-16 items-center justify-center transition-transform duration-300 group-hover:scale-105">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Yayasan Nurul Jadid Karduluk" class="h-full w-full object-contain filter drop-shadow-[0_2px_8px_rgba(0,140,227,0.45)]">
                </div>
                <h1 class="mt-3 text-2xl font-bold text-slate-900 dark:text-white transition-colors group-hover:text-brand-600 dark:group-hover:text-brand-400">SIAKAD NUJA</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Verifikasi Kode OTP</p>
            </a>
        </div>

        <div class="rounded-2xl bg-white dark:bg-slate-800 p-6 sm:p-8 shadow-sm ring-1 ring-slate-200/70 dark:ring-slate-700/70">
            <div class="mb-6 text-center">
                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400">
                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.297.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.203c.043.072.043.419-.101.824z"/>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Masukkan Kode OTP</h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Kode verifikasi 6 digit telah dikirimkan ke nomor WhatsApp Anda:
                </p>
                <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    <span>{{ $maskedPhone }}</span>
                </div>
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

            <form method="POST" action="{{ route('password.reset_attempt') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="otp" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5 text-center">
                        Kode OTP (6 Digit)
                    </label>
                    <input
                        id="otp"
                        name="otp"
                        type="text"
                        maxlength="6"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        value="{{ old('otp') }}"
                        required
                        autofocus
                        autocomplete="one-time-code"
                        placeholder="••••••"
                        class="block w-full text-center text-2xl font-mono tracking-[0.6em] rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition placeholder:text-slate-300 dark:placeholder:text-slate-600 {{ $errors->has('otp') ? 'border-red-400 ring-2 ring-red-400/20' : '' }}"
                    />
                    <p class="mt-1 text-center text-xs text-slate-400">Kode ini hanya berlaku selama 10 menit.</p>
                </div>

                <div class="border-t border-slate-200/80 dark:border-slate-700/80 pt-4 space-y-4">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400 text-center">
                        Buat Password Baru
                    </h3>

                    <x-form.input
                        label="Password Baru"
                        name="password"
                        type="password"
                        placeholder="Minimal 8 karakter"
                        required
                        autocomplete="new-password"
                    />

                    <x-form.input
                        label="Konfirmasi Password Baru"
                        name="password_confirmation"
                        type="password"
                        placeholder="Ketik ulang password baru"
                        required
                        autocomplete="new-password"
                    />
                </div>

                <x-button type="submit" variant="primary" class="w-full justify-center">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan Password Baru
                </x-button>
            </form>

            <div class="mt-6 border-t border-slate-200/80 dark:border-slate-700/80 pt-4 flex items-center justify-between text-xs">
                <form method="POST" action="{{ route('password.resend_otp') }}">
                    @csrf
                    <button
                        type="submit"
                        :disabled="cooldown > 0"
                        class="font-medium text-brand-600 hover:text-brand-500 dark:text-brand-400 dark:hover:text-brand-300 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="cooldown > 0">Kirim Ulang OTP (<span x-text="cooldown"></span>s)</span>
                        <span x-show="cooldown <= 0">Kirim Ulang Kode OTP</span>
                    </button>
                </form>

                <a href="{{ route('password.request') }}" class="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition">
                    Ganti Nomor / Akun
                </a>
            </div>
        </div>

        <p class="mt-6 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} SIAKAD NUJA — Yayasan Nurul Jadid Karduluk
        </p>
    </div>
</div>
@endsection
