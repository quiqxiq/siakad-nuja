@extends('layouts.app')

@section('title', 'Ruang Perwalian - ' . $kelas->nama_lengkap)

@section('content')
<x-page-header title="Ruang Perwalian: {{ $kelas->nama_lengkap }}" subtitle="Bimbingan rombel, daftar siswa binaan, dan kontak orang tua wali murid.">
    <x-slot:actions>
        <x-button variant="secondary" :href="route('perwalian.index')">
            Daftar Perwalian
        </x-button>
    </x-slot:actions>
</x-page-header>

{{-- Ringkasan Statistik Kelas Perwalian --}}
<div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
    <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 p-4 shadow-sm">
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Siswa Binaan</p>
        <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ $totalSiswa }} <span class="text-xs font-normal text-slate-400">/ {{ $kelas->kapasitas ?? '-' }}</span></p>
    </div>
    <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 p-4 shadow-sm">
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Laki-laki</p>
        <p class="mt-1 text-2xl font-bold text-sky-600 dark:text-sky-400">{{ $totalL }} <span class="text-xs font-normal text-slate-400">siswa</span></p>
    </div>
    <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 p-4 shadow-sm">
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Perempuan</p>
        <p class="mt-1 text-2xl font-bold text-rose-600 dark:text-rose-400">{{ $totalP }} <span class="text-xs font-normal text-slate-400">siswa</span></p>
    </div>
    <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 p-4 shadow-sm">
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Wali Kelas</p>
        <p class="mt-1 text-sm font-bold text-slate-900 dark:text-white truncate" title="{{ $kelas->waliKelas->nama_lengkap ?? '-' }}">{{ $kelas->waliKelas->nama_lengkap ?? '-' }}</p>
        <p class="text-[11px] text-slate-400">T.A. {{ $kelas->tahun_ajaran }}</p>
    </div>
</div>

{{-- Filter & Pencarian Siswa --}}
<div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center justify-between">
    <form method="GET" action="{{ route('perwalian.show', $kelas) }}" class="flex-1 max-w-md">
        <div class="relative">
            <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama atau NIS siswa..."
                class="block w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white pl-9 pr-3.5 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
        </div>
    </form>
    @if(request('q'))
        <a href="{{ route('perwalian.show', $kelas) }}" class="text-xs font-semibold text-rose-600 hover:text-rose-700">
            Reset Pencarian
        </a>
    @endif
</div>

{{-- Tabel Siswa Perwalian --}}
<x-card padding="p-0">
    <x-slot:header>
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-slate-900 dark:text-white">Daftar Siswa Kelas {{ $kelas->nama_lengkap }}</h2>
            <span class="text-xs text-slate-500 dark:text-slate-400">{{ $siswa->count() }} siswa ditemukan</span>
        </div>
    </x-slot:header>

    @if ($siswa->isNotEmpty())
        {{-- Tampilan Desktop --}}
        <div class="hidden md:block overflow-x-auto">
            <x-table :headers="['No', 'NIS & Nama Siswa', 'L/P', 'Kontak Orang Tua / Wali', 'Kehadiran (H/S/I/A)', 'Status']">
                @foreach ($siswa as $i => $s)
                    @php
                        $ortuUtama = $s->orangTua->firstWhere('is_kontak_utama', true) ?? $s->orangTua->first();
                        $noWa = $ortuUtama?->no_wa ?: $ortuUtama?->no_hp;
                        $cleanWa = $noWa ? preg_replace('/^0/', '62', preg_replace('/[^\d]/', '', $noWa)) : null;
                    @endphp
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors">
                        <td class="px-4 py-3 text-sm text-slate-400 text-center w-12">{{ $i + 1 }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300 font-bold text-xs">
                                    {{ strtoupper(substr($s->nama_lengkap, 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white leading-snug">{{ $s->nama_lengkap }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">NIS: {{ $s->nis }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-600 dark:text-slate-300 whitespace-nowrap">
                            @if ($s->jenis_kelamin === 'Laki-laki')
                                <span class="inline-flex items-center gap-1 text-sky-600 font-medium">L</span>
                            @else
                                <span class="inline-flex items-center gap-1 text-rose-600 font-medium">P</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if ($ortuUtama)
                                <div class="leading-tight">
                                    <p class="font-medium text-slate-800 dark:text-slate-200 text-xs">{{ $ortuUtama->nama }} <span class="text-slate-400">({{ $ortuUtama->hubungan }})</span></p>
                                    @if ($cleanWa)
                                        <div class="mt-1 flex items-center gap-2">
                                            <a href="https://wa.me/{{ $cleanWa }}" target="_blank" rel="noopener noreferrer"
                                                class="inline-flex items-center gap-1 rounded-md bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800 hover:bg-emerald-100 transition">
                                                <x-icon name="whatsapp" class="h-3 w-3" /> {{ $noWa }}
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-slate-400 italic">Belum terdata</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center gap-1 text-xs">
                                <span class="rounded px-1.5 py-0.5 bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 font-semibold" title="Hadir">{{ $s->hadir_count }} H</span>
                                <span class="rounded px-1.5 py-0.5 bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 font-semibold" title="Sakit">{{ $s->sakit_count }} S</span>
                                <span class="rounded px-1.5 py-0.5 bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300 font-semibold" title="Izin">{{ $s->izin_count }} I</span>
                                <span class="rounded px-1.5 py-0.5 bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 font-semibold" title="Alpa">{{ $s->alpa_count }} A</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-xs">
                            <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                {{ $s->status ?? 'Aktif' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </div>

        {{-- Tampilan Mobile --}}
        <div class="divide-y divide-slate-100 dark:divide-slate-700/60 md:hidden">
            @foreach ($siswa as $s)
                @php
                    $ortuUtama = $s->orangTua->firstWhere('is_kontak_utama', true) ?? $s->orangTua->first();
                    $noWa = $ortuUtama?->no_wa ?: $ortuUtama?->no_hp;
                    $cleanWa = $noWa ? preg_replace('/^0/', '62', preg_replace('/[^\d]/', '', $noWa)) : null;
                @endphp
                <div class="p-4 space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="font-semibold text-slate-900 dark:text-white">{{ $s->nama_lengkap }}</p>
                            <p class="text-xs text-slate-500">NIS: {{ $s->nis }} • {{ $s->jenis_kelamin }}</p>
                        </div>
                        <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-800">
                            {{ $s->status ?? 'Aktif' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-100 dark:border-slate-700/50">
                        <div>
                            <span class="text-slate-400">Wali Murid: </span>
                            <span class="font-medium text-slate-700 dark:text-slate-300">{{ $ortuUtama->nama ?? '-' }}</span>
                        </div>
                        @if ($cleanWa)
                            <a href="https://wa.me/{{ $cleanWa }}" target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center gap-1 text-emerald-600 font-semibold hover:underline">
                                <x-icon name="whatsapp" class="h-3.5 w-3.5" /> WA
                            </a>
                        @endif
                    </div>

                    <div class="flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-400">
                        <span>Absensi:</span>
                        <span class="font-semibold text-emerald-600">{{ $s->hadir_count }} H</span>
                        <span class="font-semibold text-amber-600">{{ $s->sakit_count }} S</span>
                        <span class="font-semibold text-sky-600">{{ $s->izin_count }} I</span>
                        <span class="font-semibold text-rose-600">{{ $s->alpa_count }} A</span>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="p-8">
            <x-empty-state icon="siswa" title="Belum Ada Siswa Terdaftar" description="Belum ada data siswa di kelas perwalian ini." />
        </div>
    @endif
</x-card>
@endsection
