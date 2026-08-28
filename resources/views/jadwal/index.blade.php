@extends('layouts.app')

@section('title', 'Jadwal Pelajaran')

@section('content')
@php $isAdmin = auth()->user()->isAdmin(); @endphp

<x-page-header title="Jadwal Pelajaran" :subtitle="$isAdmin ? 'Kelola jadwal pelajaran per kelas.' : 'Daftar jadwal mengajar Anda.'">
    @if ($isAdmin)
        <x-slot:actions>
            <x-button :href="route('jadwal.create')" variant="primary">
                <x-icon name="plus" class="h-4 w-4" /> Tambah Jadwal
            </x-button>
        </x-slot:actions>
    @endif
</x-page-header>

<div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center justify-between bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-700 shadow-sm">
    <form method="GET" action="{{ route('jadwal.index') }}" class="w-full flex flex-col gap-3 sm:flex-row sm:items-center">
        {{-- Input Pencarian --}}
        <div class="relative flex-1 min-w-[200px]">
            <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari mapel, guru, kelas, ruangan..."
                class="block w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white pl-9 pr-3.5 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if (isset($guruList) && $guruList->isNotEmpty())
                <select name="guru_id" onchange="this.form.submit()"
                    class="block rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 py-2 px-3">
                    <option value="">Semua Guru</option>
                    @foreach ($guruList as $g)
                        <option value="{{ $g->id }}" @selected(($selectedGuruId ?? request('guru_id')) == $g->id)>{{ $g->nama_lengkap }}</option>
                    @endforeach
                </select>
            @endif

            <select name="kelas_id" onchange="this.form.submit()"
                class="block rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 py-2 px-3">
                <option value="">Semua Kelas</option>
                @foreach ($kelasList as $k)
                    <option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>{{ $k->nama_lengkap }}</option>
                @endforeach
            </select>

            <select name="hari" onchange="this.form.submit()"
                class="block rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 py-2 px-3">
                <option value="">Semua Hari</option>
                @foreach (['Sabtu', 'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis'] as $h)
                    <option value="{{ $h }}" @selected(request('hari') == $h)>{{ $h }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-3.5 py-2 bg-brand-600 hover:bg-brand-700 text-white font-medium text-sm rounded-xl transition shadow-sm flex items-center justify-center gap-1.5">
                Cari
            </button>

            @if(request()->hasAny(['search', 'guru_id', 'kelas_id', 'hari']))
                <a href="{{ route('jadwal.index') }}" class="px-3 py-2 text-xs font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/50 rounded-xl hover:bg-rose-50 dark:hover:bg-rose-900/30 transition">
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>

<x-card padding="p-0">
    @if ($jadwal->count())
        {{-- Desktop --}}
        <div class="hidden md:block">
            <x-table :headers="['Hari', 'Jam', 'Waktu', 'Mapel', 'Kelas', 'Guru', 'Ruang', 'Aksi']">
                @foreach ($jadwal as $j)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                        <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-white whitespace-nowrap">{{ $j->hari }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ $j->jam_ke }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ \Illuminate\Support\Str::substr($j->jam_mulai, 0, 5) }}&ndash;{{ \Illuminate\Support\Str::substr($j->jam_selesai, 0, 5) }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ $j->mapel->nama_mapel ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ $j->kelas->nama_lengkap ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ $j->guru->nama_lengkap ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ $j->ruangan ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center gap-1">
                                <x-button :href="route('jadwal.show', $j)" variant="ghost" size="icon" title="Lihat"><x-icon name="eye" class="h-4 w-4" /></x-button>
                                @if ($isAdmin)
                                    <x-button :href="route('jadwal.edit', $j)" variant="ghost" size="icon" title="Edit"><x-icon name="edit" class="h-4 w-4" /></x-button>
                                    <x-confirm-delete :action="route('jadwal.destroy', $j)" />
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </div>
        {{-- Mobile --}}
        <div class="divide-y divide-slate-100 dark:divide-slate-700/60 md:hidden">
            @foreach ($jadwal as $j)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium text-slate-900 dark:text-white truncate">{{ $j->mapel->nama_mapel ?? '-' }}</p>
                            <p class="text-sm text-slate-500">{{ $j->hari }} &middot; Jam ke-{{ $j->jam_ke }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-1">
                            <x-button :href="route('jadwal.show', $j)" variant="ghost" size="icon"><x-icon name="eye" class="h-4 w-4" /></x-button>
                            @if ($isAdmin)
                                <x-button :href="route('jadwal.edit', $j)" variant="ghost" size="icon"><x-icon name="edit" class="h-4 w-4" /></x-button>
                                <x-confirm-delete :action="route('jadwal.destroy', $j)" />
                            @endif
                        </div>
                    </div>
                    <dl class="mt-2 grid grid-cols-2 gap-x-4 gap-y-1 text-sm">
                        <div><dt class="text-xs text-slate-400">Waktu</dt><dd class="text-slate-700 dark:text-slate-300">{{ \Illuminate\Support\Str::substr($j->jam_mulai, 0, 5) }}&ndash;{{ \Illuminate\Support\Str::substr($j->jam_selesai, 0, 5) }}</dd></div>
                        <div><dt class="text-xs text-slate-400">Kelas</dt><dd class="text-slate-700 dark:text-slate-300">{{ $j->kelas->nama_lengkap ?? '-' }}</dd></div>
                        <div><dt class="text-xs text-slate-400">Guru</dt><dd class="text-slate-700 dark:text-slate-300">{{ $j->guru->nama_lengkap ?? '-' }}</dd></div>
                        <div><dt class="text-xs text-slate-400">Ruang</dt><dd class="text-slate-700 dark:text-slate-300">{{ $j->ruangan ?? '-' }}</dd></div>
                    </dl>
                </div>
            @endforeach
        </div>
    @else
        <div class="p-6"><x-empty-state icon="jadwal" title="Belum ada jadwal" description="Data jadwal pelajaran akan muncul di sini." /></div>
    @endif
</x-card>

@if ($jadwal->hasPages())
    <div class="mt-4">{{ $jadwal->withQueryString()->links() }}</div>
@endif
@endsection
