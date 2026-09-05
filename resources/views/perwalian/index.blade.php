@extends('layouts.app')

@section('title', 'Kelas Perwalian')

@section('content')
<x-page-header title="Kelas Perwalian" subtitle="Daftar rombel / kelas yang berada di bawah bimbingan perwalian Anda.">
</x-page-header>

<div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
    @forelse ($kelasList as $k)
        <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 p-6 shadow-sm hover:shadow-md transition">
            <div class="flex items-start justify-between gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-100 dark:bg-brand-900/40 text-brand-600 dark:text-brand-300">
                    <x-icon name="kelas" class="h-6 w-6" />
                </div>
                <x-badge variant="info">{{ $k->jenjang }}</x-badge>
            </div>

            <div class="mt-4">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $k->nama_lengkap }}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">Tingkat {{ $k->tingkat }} • T.A. {{ $k->tahun_ajaran }}</p>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between text-sm">
                <span class="text-slate-500 dark:text-slate-400">Jumlah Siswa:</span>
                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $k->siswa_count }} Siswa</span>
            </div>

            @if ($isAdmin)
                <div class="mt-2 flex items-center justify-between text-xs text-slate-400">
                    <span>Wali:</span>
                    <span class="font-medium text-slate-600 dark:text-slate-300">{{ $k->waliKelas->nama_lengkap ?? 'Belum Ditentukan' }}</span>
                </div>
            @endif

            <div class="mt-5">
                <a href="{{ route('perwalian.show', $k) }}" class="flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 transition">
                    <x-icon name="eye" class="h-4 w-4" /> Masuk Ruang Perwalian
                </a>
            </div>
        </div>
    @empty
        <div class="col-span-full">
            <x-card>
                <x-empty-state icon="kelas" title="Belum Ada Kelas Perwalian" description="Anda belum ditugaskan sebagai wali kelas pada rombel manapun." />
            </x-card>
        </div>
    @endforelse
</div>
@endsection
