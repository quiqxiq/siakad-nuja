@extends('layouts.app')

@section('title', 'Data Nilai')

@section('content')
<x-page-header title="Data Nilai" subtitle="Kelola nilai siswa per mata pelajaran.">
    <x-slot:actions>
        <x-button :href="route('nilai.create')" variant="primary">
            <x-icon name="plus" class="h-4 w-4" /> Tambah Nilai
        </x-button>
    </x-slot:actions>
</x-page-header>

<div class="mb-4 flex flex-col sm:flex-row items-center justify-between gap-3">
    <form method="GET" class="w-full sm:w-auto flex flex-col sm:flex-row items-center gap-2">
        <div class="relative w-full sm:w-64">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama siswa atau NIS..."
                class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm shadow-sm pl-9 pr-4 py-2 focus:border-brand-500 focus:ring-brand-500">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <x-icon name="search" class="h-4 w-4" />
            </div>
        </div>

        <select name="kelas_id" onchange="this.form.submit()"
            class="block w-full sm:w-44 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm shadow-sm py-2 focus:border-brand-500 focus:ring-brand-500">
            <option value="">Semua Kelas</option>
            @foreach ($kelasList as $k)
                <option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>{{ $k->nama_lengkap }}</option>
            @endforeach
        </select>

        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white font-medium text-sm rounded-lg transition shadow-sm flex items-center justify-center gap-1.5">
            Cari
        </button>

        @if(request('search') || request('kelas_id'))
            <a href="{{ route('nilai.index') }}" class="w-full sm:w-auto px-3 py-2 border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-sm rounded-lg transition text-center">
                Reset
            </a>
        @endif
    </form>
</div>

<x-card padding="p-0">
    @if ($nilai->count())
        {{-- Desktop --}}
        <div class="hidden md:block">
            <x-table :headers="['Siswa', 'Mapel', 'Kelas', 'Smt', 'Harian', 'UTS', 'UAS', 'Akhir', 'Predikat', 'Aksi']">
                @foreach ($nilai as $n)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                        <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-white whitespace-nowrap">{{ $n->siswa->nama_lengkap ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ $n->mapel->nama_mapel ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ $n->kelas->nama_lengkap ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ $n->semester }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ $n->nilai_harian ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ $n->nilai_uts ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ $n->nilai_uas ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-slate-900 dark:text-white whitespace-nowrap">{{ $n->nilai_akhir ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @php $pColor = ['A' => 'success', 'B' => 'success', 'C' => 'info', 'D' => 'warning', 'E' => 'danger'][$n->predikat ?? ''] ?? 'slate'; @endphp
                            <x-badge :variant="$pColor">{{ $n->predikat ?? '-' }}</x-badge>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center gap-1">
                                <x-button :href="route('nilai.show', $n)" variant="ghost" size="icon" title="Lihat"><x-icon name="eye" class="h-4 w-4" /></x-button>
                                <x-button :href="route('nilai.edit', $n)" variant="ghost" size="icon" title="Edit"><x-icon name="edit" class="h-4 w-4" /></x-button>
                                <x-confirm-delete :action="route('nilai.destroy', $n)" />
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </div>
        {{-- Mobile --}}
        <div class="divide-y divide-slate-100 dark:divide-slate-700/60 md:hidden">
            @foreach ($nilai as $n)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium text-slate-900 dark:text-white truncate">{{ $n->siswa->nama_lengkap ?? '-' }}</p>
                            <p class="text-sm text-slate-500">{{ $n->mapel->nama_mapel ?? '-' }} • {{ $n->kelas->nama_lengkap ?? '-' }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-1">
                            <x-button :href="route('nilai.show', $n)" variant="ghost" size="icon"><x-icon name="eye" class="h-4 w-4" /></x-button>
                            <x-button :href="route('nilai.edit', $n)" variant="ghost" size="icon"><x-icon name="edit" class="h-4 w-4" /></x-button>
                            <x-confirm-delete :action="route('nilai.destroy', $n)" />
                        </div>
                    </div>
                    <dl class="mt-2 grid grid-cols-2 gap-x-4 gap-y-1 text-sm">
                        <div><dt class="text-xs text-slate-400">Semester</dt><dd class="text-slate-700 dark:text-slate-300">{{ $n->semester }}</dd></div>
                        <div>
                            <dt class="text-xs text-slate-400">Predikat</dt>
                            <dd class="text-slate-700 dark:text-slate-300">
                                @php $pColor = ['A' => 'success', 'B' => 'success', 'C' => 'info', 'D' => 'warning', 'E' => 'danger'][$n->predikat ?? ''] ?? 'slate'; @endphp
                                <x-badge :variant="$pColor">{{ $n->predikat ?? '-' }}</x-badge>
                            </dd>
                        </div>
                        <div><dt class="text-xs text-slate-400">Harian / UTS / UAS</dt><dd class="text-slate-700 dark:text-slate-300">{{ $n->nilai_harian ?? '-' }} / {{ $n->nilai_uts ?? '-' }} / {{ $n->nilai_uas ?? '-' }}</dd></div>
                        <div><dt class="text-xs text-slate-400">Nilai Akhir</dt><dd class="font-semibold text-slate-900 dark:text-white">{{ $n->nilai_akhir ?? '-' }}</dd></div>
                    </dl>
                </div>
            @endforeach
        </div>
    @else
        <div class="p-6"><x-empty-state icon="nilai" title="Belum ada nilai" description="Data nilai akan muncul di sini." /></div>
    @endif
</x-card>

@if ($nilai->hasPages())
    <div class="mt-4">{{ $nilai->withQueryString()->links() }}</div>
@endif
@endsection
