@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<x-page-header title="Profil Saya" subtitle="Kelola informasi akun dan kata sandi Anda." />

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="lg:col-span-1">
        <x-card>
            <div class="flex flex-col items-center text-center">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-brand-600 text-2xl font-bold text-white">
                    {{ strtoupper(substr($user->nama ?? 'U', 0, 1)) }}
                </div>
                <h3 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">{{ $user->nama }}</h3>
                <p class="text-sm text-slate-500">{{ $user->email }}</p>
                <div class="mt-3">
                    <x-badge :variant="$user->isAdmin() ? 'brand' : 'info'">{{ ucfirst($user->role) }}</x-badge>
                </div>
            </div>
        </x-card>
    </div>

    <div class="lg:col-span-2">
        <x-card>
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Informasi Akun</h2>
                    <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <x-form.input label="Nama Lengkap" name="nama" :value="$user->nama" required />
                        <x-form.input label="Email" name="email" type="email" :value="$user->email" required />
                        <x-form.input label="Nomor HP" name="no_hp" type="tel" inputmode="numeric" :value="$user->no_hp ?? ''" placeholder="08..." />
                    </div>
                </div>

                <div class="border-t border-slate-100 dark:border-slate-700 pt-6">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Ganti Kata Sandi</h2>
                    <p class="mt-1 text-xs text-slate-400">Kosongkan bila tidak ingin mengubah kata sandi.</p>
                    <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-form.input label="Password Saat Ini" name="current_password" type="password" autocomplete="current-password" />
                        </div>
                        <x-form.input label="Password Baru" name="password" type="password" autocomplete="new-password" />
                        <x-form.input label="Konfirmasi Password Baru" name="password_confirmation" type="password" autocomplete="new-password" />
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <x-button type="submit" variant="primary"><x-icon name="check" class="h-4 w-4" /> Simpan Perubahan</x-button>
                </div>
            </form>
        </x-card>
    </div>
</div>
@endsection
