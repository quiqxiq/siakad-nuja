@extends('layouts.app')

@section('title', 'Isi Absensi')

@section('content')
@php
    $statuses = ['Hadir', 'Izin', 'Sakit', 'Alpa'];
    // Literal class strings so Tailwind JIT can detect them (no interpolation).
    $statusClass = [
        'Hadir' => 'peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 dark:peer-checked:bg-emerald-900/40 dark:peer-checked:text-emerald-300',
        'Izin' => 'peer-checked:border-sky-500 peer-checked:bg-sky-50 peer-checked:text-sky-700 dark:peer-checked:bg-sky-900/40 dark:peer-checked:text-sky-300',
        'Sakit' => 'peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700 dark:peer-checked:bg-amber-900/40 dark:peer-checked:text-amber-300',
        'Alpa' => 'peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-700 dark:peer-checked:bg-red-900/40 dark:peer-checked:text-red-300',
    ];
@endphp

<x-page-header title="Isi Absensi" subtitle="{{ $jadwal->kelas->nama_kelas ?? '-' }} — {{ $jadwal->mapel->nama_mapel ?? '-' }}">
    <x-slot:actions>
        <x-button variant="secondary" :href="route('absensi.create')">Ganti Jadwal</x-button>
    </x-slot:actions>
</x-page-header>

<div class="mb-4 flex flex-wrap items-center gap-3 rounded-xl bg-white dark:bg-slate-800 px-5 py-4 shadow-sm ring-1 ring-slate-200/70 dark:ring-slate-700/70">
    <div class="flex items-center gap-2 text-sm">
        <x-icon name="jadwal" class="h-5 w-5 text-brand-500" />
        <span class="font-medium text-slate-900 dark:text-white">{{ $jadwal->hari }}, jam ke-{{ $jadwal->jam_ke }}</span>
    </div>
    <span class="text-slate-300 dark:text-slate-600">•</span>
    <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
        <x-icon name="clock" class="h-4 w-4 text-slate-400" />
        {{ \Illuminate\Support\Str::substr($jadwal->jam_mulai, 0, 5) }}–{{ \Illuminate\Support\Str::substr($jadwal->jam_selesai, 0, 5) }}
    </div>
    <span class="text-slate-300 dark:text-slate-600">•</span>
    <div class="text-sm font-medium text-slate-900 dark:text-white">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}</div>
</div>

@if ($siswa->isNotEmpty())
    <form method="POST" action="{{ route('absensi.store') }}"
        x-data="{
            searchFilter: '',
            setAll(s) { document.querySelectorAll('input[data-status=\'' + s + '\']').forEach(el => el.checked = true) }
        }">
        @csrf
        <input type="hidden" name="jadwal_id" value="{{ $jadwal->id }}">
        <input type="hidden" name="tanggal" value="{{ $tanggal }}">

        <x-card padding="p-0">
            <x-slot:header>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ $siswa->count() }} Siswa</h2>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-400">Tandai semua:</span>
                        @foreach ($statuses as $st)
                            <button type="button" @click="setAll('{{ $st }}')"
                                class="rounded-lg border border-slate-200 dark:border-slate-600 px-2.5 py-1 text-xs font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">{{ $st }}</button>
                        @endforeach
                    </div>
                </div>
            </x-slot:header>

            <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/40 flex items-center justify-between gap-3">
                <div class="relative w-full max-w-sm">
                    <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input type="text"
                        x-model="searchFilter"
                        placeholder="Cari nama atau NIS siswa di roster..."
                        class="block w-full rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-white pl-9 pr-8 py-2 text-xs shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    <button type="button" x-show="searchFilter" @click="searchFilter = ''" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                        <x-icon name="close" class="h-3.5 w-3.5" />
                    </button>
                </div>
            </div>

            <ul class="divide-y divide-slate-100 dark:divide-slate-700/60">
                @foreach ($siswa as $s)
                    @php $current = old("status.$s->id", $existing[$s->id]->status ?? 'Hadir'); @endphp
                    <li class="px-4 py-4 sm:px-6" x-show="!searchFilter || ('{{ strtolower(addslashes($s->nama_lengkap . ' ' . $s->nis)) }}').includes(searchFilter.toLowerCase().trim())">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-700 text-sm font-semibold text-slate-600 dark:text-slate-300">
                                    {{ strtoupper(substr($s->nama_lengkap, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-slate-900 dark:text-white">{{ $s->nama_lengkap }}</p>
                                    <p class="text-xs text-slate-500">NIS {{ $s->nis }}</p>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                @foreach ($statuses as $st)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="status[{{ $s->id }}]" value="{{ $st }}" data-status="{{ $st }}"
                                            @checked($current === $st) class="peer sr-only">
                                        <span class="inline-flex items-center rounded-lg border border-slate-200 dark:border-slate-600 px-3 py-1.5 text-xs font-medium text-slate-600 dark:text-slate-300 transition {{ $statusClass[$st] }}">{{ $st }}</span>
                                    </label>
                                @endforeach
                                <input type="text" name="keterangan[{{ $s->id }}]" value="{{ old("keterangan.$s->id", $existing[$s->id]->keterangan ?? '') }}"
                                    placeholder="Keterangan (opsional)"
                                    class="w-full lg:w-44 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            <x-slot:footer>
                <div class="flex items-center justify-end gap-3">
                    <x-button variant="secondary" :href="route('absensi.index')">Batal</x-button>
                    <x-button type="submit" variant="primary"><x-icon name="check" class="h-4 w-4" /> Simpan Absensi</x-button>
                </div>
            </x-slot:footer>
        </x-card>
    </form>
@else
    <x-card>
        <x-empty-state icon="siswa" title="Tidak ada siswa di kelas ini" description="Belum ada siswa terdaftar pada kelas jadwal tersebut." />
    </x-card>
@endif
@endsection
