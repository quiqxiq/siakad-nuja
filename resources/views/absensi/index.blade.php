@extends('layouts.app')

@section('title', 'Absensi Siswa')

@section('content')
@php
    $isAdmin = auth()->user()->isAdmin();
    $hasFilter = request()->hasAny(['q', 'kelas_id', 'mapel_id', 'hari', 'guru_id', 'status', 'tanggal', 'jadwal_id']);
    $badge = [
        'Hadir' => 'success',
        'Izin' => 'info',
        'Sakit' => 'warning',
        'Alpa' => 'danger',
    ];
@endphp

<x-page-header title="Data Absensi Siswa" subtitle="Rekap dan filter kehadiran siswa berdasarkan kelas, mata pelajaran, hari, dan tanggal.">
    <x-slot:actions>
        <x-button :href="route('absensi.create')" variant="primary">
            <x-icon name="plus" class="h-4 w-4" /> Entri Absensi
        </x-button>
    </x-slot:actions>
</x-page-header>

{{-- Kartu Ringkasan Status Kehadiran --}}
<div class="mb-6 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-5">
    <a href="{{ route('absensi.index') }}"
       class="group rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm hover:border-brand-300 hover:shadow-md transition dark:border-slate-700 dark:bg-slate-800 {{ !request('status') && !$hasFilter ? 'ring-2 ring-brand-500/30' : '' }}">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-900/40 dark:text-brand-300 group-hover:scale-105 transition">
                <x-icon name="absensi" class="h-5 w-5" />
            </div>
            <div class="min-w-0">
                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">Total Entri</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white">{{ number_format($summary['total']) }}</p>
            </div>
        </div>
    </a>

    <a href="{{ request()->fullUrlWithQuery(['status' => 'Hadir', 'page' => null]) }}"
       class="group rounded-2xl border border-emerald-200/80 bg-white p-4 shadow-sm hover:border-emerald-400 hover:shadow-md transition dark:border-emerald-800/50 dark:bg-slate-800 {{ request('status') === 'Hadir' ? 'ring-2 ring-emerald-500/30' : '' }}">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-300 group-hover:scale-105 transition">
                <x-icon name="check" class="h-5 w-5" />
            </div>
            <div class="min-w-0">
                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">Hadir</p>
                <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($summary['hadir']) }}</p>
            </div>
        </div>
    </a>

    <a href="{{ request()->fullUrlWithQuery(['status' => 'Izin', 'page' => null]) }}"
       class="group rounded-2xl border border-sky-200/80 bg-white p-4 shadow-sm hover:border-sky-400 hover:shadow-md transition dark:border-sky-800/50 dark:bg-slate-800 {{ request('status') === 'Izin' ? 'ring-2 ring-sky-500/30' : '' }}">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-600 dark:bg-sky-900/40 dark:text-sky-300 group-hover:scale-105 transition">
                <x-icon name="info" class="h-5 w-5" />
            </div>
            <div class="min-w-0">
                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">Izin</p>
                <p class="text-xl font-bold text-sky-600 dark:text-sky-400">{{ number_format($summary['izin']) }}</p>
            </div>
        </div>
    </a>

    <a href="{{ request()->fullUrlWithQuery(['status' => 'Sakit', 'page' => null]) }}"
       class="group rounded-2xl border border-amber-200/80 bg-white p-4 shadow-sm hover:border-amber-400 hover:shadow-md transition dark:border-amber-800/50 dark:bg-slate-800 {{ request('status') === 'Sakit' ? 'ring-2 ring-amber-500/30' : '' }}">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-900/40 dark:text-amber-300 group-hover:scale-105 transition">
                <x-icon name="warning" class="h-5 w-5" />
            </div>
            <div class="min-w-0">
                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">Sakit</p>
                <p class="text-xl font-bold text-amber-600 dark:text-amber-400">{{ number_format($summary['sakit']) }}</p>
            </div>
        </div>
    </a>

    <a href="{{ request()->fullUrlWithQuery(['status' => 'Alpa', 'page' => null]) }}"
       class="group col-span-2 sm:col-span-1 rounded-2xl border border-rose-200/80 bg-white p-4 shadow-sm hover:border-rose-400 hover:shadow-md transition dark:border-rose-800/50 dark:bg-slate-800 {{ request('status') === 'Alpa' ? 'ring-2 ring-rose-500/30' : '' }}">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-900/40 dark:text-rose-300 group-hover:scale-105 transition">
                <x-icon name="x" class="h-5 w-5" />
            </div>
            <div class="min-w-0">
                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">Alpa</p>
                <p class="text-xl font-bold text-rose-600 dark:text-rose-400">{{ number_format($summary['alpa']) }}</p>
            </div>
        </div>
    </a>
</div>

{{-- Bar Pencarian & Multi-Filter --}}
<div class="mb-5 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <form method="GET" action="{{ route('absensi.index') }}" class="space-y-3">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6">

            {{-- Input Pencarian --}}
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Cari Siswa / Keterangan</label>
                <div class="relative">
                    <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama siswa, NIS, keterangan..."
                        class="block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white pl-9 pr-3.5 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
            </div>

            {{-- Filter Kelas --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Kelas</label>
                <select name="kelas_id"
                    class="block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 py-2">
                    <option value="">Semua Kelas</option>
                    @foreach ($kelasList as $k)
                        <option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>{{ $k->nama_kelas }} ({{ $k->jenjang }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Mapel --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Mata Pelajaran</label>
                <select name="mapel_id"
                    class="block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 py-2">
                    <option value="">Semua Mapel</option>
                    @foreach ($mapelList as $m)
                        <option value="{{ $m->id }}" @selected(request('mapel_id') == $m->id)>{{ $m->nama_mapel }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Hari --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Hari</label>
                <select name="hari"
                    class="block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 py-2">
                    <option value="">Semua Hari</option>
                    @foreach ($hariList as $h)
                        <option value="{{ $h }}" @selected(request('hari') == $h)>{{ $h }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Status --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Status Kehadiran</label>
                <select name="status"
                    class="block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 py-2">
                    <option value="">Semua Status</option>
                    @foreach ($statusList as $st)
                        <option value="{{ $st }}" @selected(request('status') == $st)>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Tanggal --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Tanggal Absensi</label>
                <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                    class="block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 py-2">
            </div>

            {{-- Filter Guru (Khusus Admin) --}}
            @if ($isAdmin && $guruList->isNotEmpty())
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Guru Pengampu</label>
                    <select name="guru_id"
                        class="block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 py-2">
                        <option value="">Semua Guru</option>
                        @foreach ($guruList as $g)
                            <option value="{{ $g->id }}" @selected(request('guru_id') == $g->id)>{{ $g->nama_lengkap }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Tombol Aksi Filter --}}
            <div class="flex items-end gap-2 {{ $isAdmin && $guruList->isNotEmpty() ? 'sm:col-span-2 xl:col-span-4' : 'sm:col-span-2 xl:col-span-5' }}">
                <x-button type="submit" variant="primary" class="h-[38px] px-4">
                    <x-icon name="search" class="h-4 w-4" /> Terapkan Filter
                </x-button>
                @if ($hasFilter)
                    <x-button :href="route('absensi.index')" variant="secondary" class="h-[38px] px-3.5 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/30">
                        <x-icon name="x" class="h-4 w-4" /> Reset Filter
                    </x-button>
                @endif
            </div>

        </div>
    </form>
</div>

{{-- Tabel Data Absensi --}}
<x-card padding="p-0">
    @if ($absensi->count())
        {{-- Desktop View --}}
        <div class="hidden md:block">
            <x-table :headers="['Tanggal & Hari', 'Siswa', 'Mapel & Waktu', 'Kelas', 'Guru Pengampu', 'Status', 'Keterangan', 'Aksi']">
                @foreach ($absensi as $a)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">
                            <div class="font-medium text-slate-900 dark:text-white">{{ optional($a->tanggal)->format('d M Y') }}</div>
                            <div class="text-xs text-slate-400">{{ $a->jadwal->hari ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm whitespace-nowrap">
                            <div class="font-semibold text-slate-900 dark:text-white">{{ $a->siswa->nama_lengkap ?? '-' }}</div>
                            <div class="text-xs text-slate-400">NIS: {{ $a->siswa->nis ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">
                            <div class="font-medium text-slate-900 dark:text-white">{{ $a->jadwal->mapel->nama_mapel ?? '-' }}</div>
                            <div class="text-xs text-slate-400">
                                Jam ke-{{ $a->jadwal->jam_ke ?? '-' }}
                                @if ($a->jadwal?->jam_mulai && $a->jadwal?->jam_selesai)
                                    ({{ \Illuminate\Support\Str::substr($a->jadwal->jam_mulai, 0, 5) }}–{{ \Illuminate\Support\Str::substr($a->jadwal->jam_selesai, 0, 5) }})
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">
                            <span class="inline-flex items-center rounded-lg bg-slate-100 dark:bg-slate-700 px-2.5 py-1 text-xs font-semibold text-slate-800 dark:text-slate-200">
                                {{ $a->jadwal->kelas->nama_kelas ?? '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap">
                            {{ $a->jadwal->guru->nama_lengkap ?? '-' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <x-badge :variant="$badge[$a->status] ?? 'slate'">{{ $a->status ?? '-' }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400 max-w-xs truncate">
                            {{ $a->keterangan ?? '-' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center gap-1">
                                <x-button :href="route('absensi.show', $a)" variant="ghost" size="icon" title="Lihat Detail">
                                    <x-icon name="eye" class="h-4 w-4" />
                                </x-button>
                                <x-confirm-delete :action="route('absensi.destroy', $a)" />
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </div>

        {{-- Mobile View --}}
        <div class="divide-y divide-slate-100 dark:divide-slate-700/60 md:hidden">
            @foreach ($absensi as $a)
                <div class="p-4 space-y-2.5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-bold text-slate-900 dark:text-white truncate">{{ $a->siswa->nama_lengkap ?? '-' }}</p>
                            <p class="text-xs text-slate-400">NIS: {{ $a->siswa->nis ?? '-' }} • Kelas {{ $a->jadwal->kelas->nama_kelas ?? '-' }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-1">
                            <x-button :href="route('absensi.show', $a)" variant="ghost" size="icon"><x-icon name="eye" class="h-4 w-4" /></x-button>
                            <x-confirm-delete :action="route('absensi.destroy', $a)" />
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600 dark:text-slate-300">
                        <span class="font-medium text-slate-900 dark:text-white">{{ optional($a->tanggal)->format('d M Y') }}</span>
                        <span>•</span>
                        <span>{{ $a->jadwal->mapel->nama_mapel ?? '-' }} ({{ $a->jadwal->hari ?? '-' }})</span>
                    </div>

                    <div class="flex items-center justify-between gap-2 pt-1 border-t border-slate-100 dark:border-slate-700/50">
                        <div class="flex items-center gap-2">
                            <x-badge :variant="$badge[$a->status] ?? 'slate'">{{ $a->status ?? '-' }}</x-badge>
                            @if ($a->keterangan)
                                <span class="text-xs text-slate-500 truncate max-w-[180px]">{{ $a->keterangan }}</span>
                            @endif
                        </div>
                        <span class="text-[11px] text-slate-400 truncate">{{ $a->jadwal->guru->nama_lengkap ?? '' }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="p-8 text-center">
            @if ($hasFilter)
                <x-empty-state icon="absensi" title="Data Tidak Ditemukan" description="Tidak ada data absensi yang cocok dengan filter pencarian yang diterapkan.">
                    <x-slot:action>
                        <x-button :href="route('absensi.index')" variant="secondary" size="sm">
                            <x-icon name="x" class="h-4 w-4" /> Hapus Semua Filter
                        </x-button>
                    </x-slot:action>
                </x-empty-state>
            @else
                <x-empty-state icon="absensi" title="Belum Ada Data Absensi" description="Belum ada catatan kehadiran yang diinput. Klik tombol di bawah untuk mulai entri absensi.">
                    <x-slot:action>
                        <x-button :href="route('absensi.create')" variant="primary" size="sm">
                            <x-icon name="plus" class="h-4 w-4" /> Entri Absensi Baru
                        </x-button>
                    </x-slot:action>
                </x-empty-state>
            @endif
        </div>
    @endif
</x-card>

@if ($absensi->hasPages())
    <div class="mt-4">{{ $absensi->links() }}</div>
@endif
@endsection
