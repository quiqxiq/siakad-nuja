@extends('layouts.app')

@section('title', 'Data Siswa')

@section('content')
@php $isAdmin = auth()->user()->isAdmin(); @endphp

<x-page-header title="Data Siswa" subtitle="Kelola data siswa dan kelasnya.">
    @if ($isAdmin)
        <x-slot:actions>
            <x-button :href="route('siswa.create')" variant="primary">
                <x-icon name="plus" class="h-4 w-4" /> Tambah Siswa
            </x-button>
        </x-slot:actions>
    @endif
</x-page-header>

<div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
    <x-search-bar placeholder="Cari nama atau NIS..." />
    <form method="GET" class="w-full sm:w-auto">
        @foreach (request()->except(['kelas_id', 'page']) as $k => $v)
            @if (! is_array($v)) <input type="hidden" name="{{ $k }}" value="{{ $v }}"> @endif
        @endforeach
        <select name="kelas_id" onchange="this.form.submit()"
            class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
            <option value="">Semua Kelas</option>
            @foreach ($kelasList as $k)
                <option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>{{ $k->nama_lengkap }}</option>
            @endforeach
        </select>
    </form>
</div>

<x-card padding="p-0">
    @if ($siswa->count())
        {{-- Desktop --}}
        <div class="hidden md:block">
            <x-table :headers="['NIS', 'Nama', 'Kelas', 'JK', 'Tahun Masuk', 'Status', 'Aksi']">
                @foreach ($siswa as $s)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                        <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400 whitespace-nowrap">{{ $s->nis }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-white whitespace-nowrap">{{ $s->nama_lengkap }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ $s->kelas->nama_lengkap ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ $s->jenis_kelamin ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ $s->tahun_masuk }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @php $stColor = ['Aktif' => 'success', 'Lulus' => 'info', 'Pindah' => 'warning', 'Keluar' => 'danger'][$s->status ?? 'Aktif'] ?? 'slate'; @endphp
                            <x-badge :variant="$stColor">{{ $s->status ?? 'Aktif' }}</x-badge>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center gap-1">
                                <x-button :href="route('siswa.show', $s)" variant="ghost" size="icon" title="Lihat"><x-icon name="eye" class="h-4 w-4" /></x-button>
                                @if ($isAdmin)
                                    <x-button :href="route('siswa.edit', $s)" variant="ghost" size="icon" title="Edit"><x-icon name="edit" class="h-4 w-4" /></x-button>
                                    <x-confirm-delete :action="route('siswa.destroy', $s)" />
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </div>
        {{-- Mobile --}}
        <div class="divide-y divide-slate-100 dark:divide-slate-700/60 md:hidden">
            @foreach ($siswa as $s)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium text-slate-900 dark:text-white truncate">{{ $s->nama_lengkap }}</p>
                            <p class="text-sm text-slate-500">NIS {{ $s->nis }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-1">
                            <x-button :href="route('siswa.show', $s)" variant="ghost" size="icon"><x-icon name="eye" class="h-4 w-4" /></x-button>
                            @if ($isAdmin)
                                <x-button :href="route('siswa.edit', $s)" variant="ghost" size="icon"><x-icon name="edit" class="h-4 w-4" /></x-button>
                                <x-confirm-delete :action="route('siswa.destroy', $s)" />
                            @endif
                        </div>
                    </div>
                    <dl class="mt-2 grid grid-cols-2 gap-x-4 gap-y-1 text-sm">
                        <div><dt class="text-xs text-slate-400">Kelas</dt><dd class="text-slate-700 dark:text-slate-300">{{ $s->kelas->nama_lengkap ?? '-' }}</dd></div>
                        <div><dt class="text-xs text-slate-400">Status</dt><dd class="text-slate-700 dark:text-slate-300">{{ $s->status ?? 'Aktif' }}</dd></div>
                    </dl>
                </div>
            @endforeach
        </div>
    @else
        <div class="p-6"><x-empty-state icon="siswa" title="Belum ada siswa" description="Data siswa akan muncul di sini." /></div>
    @endif
</x-card>

@if ($siswa->hasPages())
    <div class="mt-4">{{ $siswa->links() }}</div>
@endif
@endsection
