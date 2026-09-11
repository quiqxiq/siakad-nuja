@extends('layouts.app')

@section('title', 'Detail Pengumuman')

@section('content')
@php $isAdmin = auth()->user()->isAdmin(); @endphp

<x-page-header title="Detail Pengumuman" subtitle="{{ $pengumuman->judul }}">
    <x-slot:actions>
        <x-button variant="secondary" :href="route('pengumuman.index')">Kembali</x-button>
        @if ($isAdmin)
            <x-button variant="primary" :href="route('pengumuman.edit', $pengumuman)"><x-icon name="edit" class="h-4 w-4" /> Edit</x-button>
        @endif
    </x-slot:actions>
</x-page-header>

<x-card>
    <div class="flex items-start gap-4">
        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand-100 text-brand-600 dark:bg-brand-900/40 dark:text-brand-300">
            <x-icon name="pengumuman" class="h-6 w-6" />
        </div>
        <div class="min-w-0 flex-1">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $pengumuman->judul }}</h2>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <x-badge variant="brand">{{ match ($pengumuman->target_role) { 'wali' => 'Wali Murid', 'guru' => 'Guru', default => 'Semua' } }}</x-badge>
                <x-badge :variant="$pengumuman->is_active ? 'success' : 'slate'">{{ $pengumuman->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
            </div>
        </div>
    </div>

    <dl class="mt-6 grid grid-cols-1 gap-x-6 gap-y-4 border-t border-slate-100 dark:border-slate-700 pt-4 sm:grid-cols-2">
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Tanggal Publish</dt>
            <dd class="mt-1 text-sm text-slate-900 dark:text-white">{{ optional($pengumuman->tanggal_publish)->format('d M Y') ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Dibuat Oleh</dt>
            <dd class="mt-1 text-sm text-slate-900 dark:text-white">{{ $pengumuman->pembuat->nama ?? '-' }}</dd>
        </div>
    </dl>

    <div class="mt-6 border-t border-slate-100 dark:border-slate-700 pt-4">
        <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Konten</dt>
        <div class="prose prose-sm mt-2 max-w-none whitespace-pre-line text-slate-700 dark:text-slate-300">{{ $pengumuman->konten }}</div>
    </div>
</x-card>
@endsection
