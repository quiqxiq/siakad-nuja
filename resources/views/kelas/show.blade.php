@extends('layouts.app')

@section('title', 'Detail Kelas')

@section('content')
@php $isAdmin = auth()->user()->isAdmin(); @endphp

<x-page-header title="Detail Kelas" subtitle="{{ $kelas->nama_kelas }}">
    <x-slot:actions>
        <x-button variant="secondary" :href="route('kelas.index')">Kembali</x-button>
        @if ($isAdmin)
            <x-button variant="primary" :href="route('kelas.edit', $kelas)"><x-icon name="edit" class="h-4 w-4" /> Edit</x-button>
        @endif
    </x-slot:actions>
</x-page-header>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    {{-- Identitas kelas --}}
    <div class="lg:col-span-1">
        <x-card>
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-brand-100 dark:bg-brand-900/40 text-brand-600 dark:text-brand-300">
                    <x-icon name="kelas" class="h-6 w-6" />
                </div>
                <div class="min-w-0">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white truncate">{{ $kelas->nama_kelas }}</h3>
                    <p class="text-sm text-slate-500">{{ $kelas->tingkat }} • {{ $kelas->jenjang }}</p>
                </div>
            </div>

            <dl class="mt-6 space-y-3 border-t border-slate-100 dark:border-slate-700 pt-4 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-400">Tingkat</dt><dd class="text-slate-700 dark:text-slate-300">{{ $kelas->tingkat }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-400">Jenjang</dt><dd class="text-slate-700 dark:text-slate-300">{{ $kelas->jenjang }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-400">Ruangan</dt><dd class="text-brand-600 dark:text-brand-400 font-semibold">{{ $kelas->ruangan ?? '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-400">Tahun Ajaran</dt><dd class="text-slate-700 dark:text-slate-300">{{ $kelas->tahun_ajaran }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-400">Wali Kelas</dt><dd class="text-slate-900 dark:text-white font-medium">{{ $kelas->waliKelas->nama_lengkap ?? '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-400">Kapasitas</dt><dd class="text-slate-700 dark:text-slate-300">{{ $kelas->kapasitas ?? '-' }}</dd></div>
            </dl>
        </x-card>
    </div>

    {{-- Daftar siswa --}}
    <div class="space-y-6 lg:col-span-2">
        <x-card padding="p-0">
            <x-slot:header>
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Daftar Siswa</h2>
                    <x-badge variant="slate">{{ $kelas->siswa->count() }} siswa</x-badge>
                </div>
            </x-slot:header>
            @if ($kelas->siswa->isNotEmpty())
                <x-table :headers="['NIS', 'Nama']">
                    @foreach ($kelas->siswa as $s)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                            <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400 whitespace-nowrap">{{ $s->nis }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-white">{{ $s->nama_lengkap }}</td>
                        </tr>
                    @endforeach
                </x-table>
            @else
                <div class="p-6"><x-empty-state icon="siswa" title="Belum ada siswa" description="Siswa di kelas ini akan muncul di sini." /></div>
            @endif
        </x-card>
    </div>
</div>
@endsection
