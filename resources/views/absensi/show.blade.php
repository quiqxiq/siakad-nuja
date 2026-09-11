@extends('layouts.app')

@section('title', 'Detail Absensi')

@section('content')
@php
    $badge = ['Hadir' => 'success', 'Izin' => 'info', 'Sakit' => 'warning', 'Alpa' => 'danger'];
@endphp

<x-page-header title="Detail Absensi" subtitle="{{ $absensi->siswa->nama_lengkap ?? '-' }}">
    <x-slot:actions>
        <x-button variant="secondary" :href="route('absensi.index')">Kembali</x-button>
    </x-slot:actions>
</x-page-header>

<x-card>
    <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Siswa</dt>
            <dd class="mt-1 text-sm font-medium text-slate-900 dark:text-white">{{ $absensi->siswa->nama_lengkap ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Tanggal</dt>
            <dd class="mt-1 text-sm text-slate-700 dark:text-slate-300">{{ optional($absensi->tanggal)->translatedFormat('l, d F Y') }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Mata Pelajaran</dt>
            <dd class="mt-1 text-sm text-slate-700 dark:text-slate-300">{{ $absensi->jadwal->mapel->nama_mapel ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Kelas</dt>
            <dd class="mt-1 text-sm text-slate-700 dark:text-slate-300">{{ $absensi->jadwal->kelas->nama_kelas ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Status</dt>
            <dd class="mt-1"><x-badge :variant="$badge[$absensi->status] ?? 'slate'">{{ $absensi->status ?? '-' }}</x-badge></dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Keterangan</dt>
            <dd class="mt-1 text-sm text-slate-700 dark:text-slate-300">{{ $absensi->keterangan ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Verifikasi Lokasi (GPS)</dt>
            <dd class="mt-1 text-sm text-slate-700 dark:text-slate-300">
                @if ($absensi->latitude && $absensi->longitude)
                    <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-medium">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Terverifikasi di Area Madrasah
                        @if ($absensi->jarak_meter !== null)
                            ({{ $absensi->jarak_meter }} m dari titik pusat)
                        @endif
                    </span>
                    <div class="mt-0.5 text-xs text-slate-400">
                        Koordinat: {{ $absensi->latitude }}, {{ $absensi->longitude }}
                    </div>
                @else
                    <span class="text-slate-400">Data lokasi belum tercatat</span>
                @endif
            </dd>
        </div>
    </dl>
</x-card>
@endsection
