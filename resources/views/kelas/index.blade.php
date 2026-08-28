@extends('layouts.app')

@section('title', 'Data Kelas')

@section('content')
@php $isAdmin = auth()->user()->isAdmin(); @endphp

<x-page-header title="Data Kelas" subtitle="Kelola data kelas dan wali kelasnya.">
    @if ($isAdmin)
        <x-slot:actions>
            <x-button :href="route('kelas.create')" variant="primary">
                <x-icon name="plus" class="h-4 w-4" /> Tambah Kelas
            </x-button>
        </x-slot:actions>
    @endif
</x-page-header>

<div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
    <x-search-bar placeholder="Cari kelas..." />
</div>

<x-card padding="p-0">
    @if ($kelas->count())
        {{-- Desktop --}}
        <div class="hidden md:block">
            <x-table :headers="['Kelas', 'Tingkat', 'Jenjang', 'Wali Kelas', 'Siswa', 'T.A.', 'Aksi']">
                @foreach ($kelas as $k)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                        <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-white whitespace-nowrap">{{ $k->nama_lengkap }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ $k->tingkat }}</td>
                        <td class="px-4 py-3 whitespace-nowrap"><x-badge variant="info">{{ $k->jenjang }}</x-badge></td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ $k->waliKelas->nama_lengkap ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ $k->siswa_count }}/{{ $k->kapasitas ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ $k->tahun_ajaran }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center gap-1">
                                <x-button :href="route('kelas.show', $k)" variant="ghost" size="icon" title="Lihat"><x-icon name="eye" class="h-4 w-4" /></x-button>
                                @if ($isAdmin)
                                    <x-button :href="route('kelas.edit', $k)" variant="ghost" size="icon" title="Edit"><x-icon name="edit" class="h-4 w-4" /></x-button>
                                    <x-confirm-delete :action="route('kelas.destroy', $k)" />
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </div>
        {{-- Mobile --}}
        <div class="divide-y divide-slate-100 dark:divide-slate-700/60 md:hidden">
            @foreach ($kelas as $k)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium text-slate-900 dark:text-white truncate">{{ $k->nama_lengkap }}</p>
                            <p class="text-sm text-slate-500">{{ $k->tingkat }} • {{ $k->jenjang }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-1">
                            <x-button :href="route('kelas.show', $k)" variant="ghost" size="icon"><x-icon name="eye" class="h-4 w-4" /></x-button>
                            @if ($isAdmin)
                                <x-button :href="route('kelas.edit', $k)" variant="ghost" size="icon"><x-icon name="edit" class="h-4 w-4" /></x-button>
                                <x-confirm-delete :action="route('kelas.destroy', $k)" />
                            @endif
                        </div>
                    </div>
                    <dl class="mt-2 grid grid-cols-2 gap-x-4 gap-y-1 text-sm">
                        <div><dt class="text-xs text-slate-400">Wali Kelas</dt><dd class="text-slate-700 dark:text-slate-300">{{ $k->waliKelas->nama_lengkap ?? '-' }}</dd></div>
                        <div><dt class="text-xs text-slate-400">Siswa</dt><dd class="text-slate-700 dark:text-slate-300">{{ $k->siswa_count }}/{{ $k->kapasitas ?? '-' }}</dd></div>
                        <div><dt class="text-xs text-slate-400">T.A.</dt><dd class="text-slate-700 dark:text-slate-300">{{ $k->tahun_ajaran }}</dd></div>
                    </dl>
                </div>
            @endforeach
        </div>
    @else
        <div class="p-6"><x-empty-state icon="kelas" title="Belum ada kelas" description="Data kelas akan muncul di sini." /></div>
    @endif
</x-card>

@if ($kelas->hasPages())
    <div class="mt-4">{{ $kelas->withQueryString()->links() }}</div>
@endif
@endsection
