@extends('layouts.app')

@section('title', 'Pengumuman')

@section('content')
@php $isAdmin = auth()->user()->isAdmin(); @endphp

<x-page-header title="Pengumuman" subtitle="Kelola pengumuman untuk warga sekolah.">
    @if ($isAdmin)
        <x-slot:actions>
            <x-button :href="route('pengumuman.create')" variant="primary">
                <x-icon name="plus" class="h-4 w-4" /> Buat Pengumuman
            </x-button>
        </x-slot:actions>
    @endif
</x-page-header>

<x-card padding="p-0">
    @if ($pengumuman->count())
        {{-- Desktop --}}
        <div class="hidden md:block">
            <x-table :headers="['Judul', 'Target', 'Tanggal Publish', 'Dibuat Oleh', 'Status', 'Aksi']">
                @foreach ($pengumuman as $p)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                        <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-white whitespace-nowrap">{{ $p->judul }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex flex-col gap-1 items-start">
                                <x-badge variant="brand">{{ match ($p->target_role) { 'wali' => 'Wali Murid', 'guru' => 'Guru', default => 'Semua' } }}</x-badge>
                                @if ($p->kelas)
                                    <x-badge variant="info">Khusus {{ $p->kelas->nama_kelas }}</x-badge>
                                @else
                                    <span class="text-xs text-slate-400">Semua Kelas</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ optional($p->tanggal_publish)->format('d M Y') ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ $p->pembuat->nama ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <x-badge :variant="$p->is_active ? 'success' : 'slate'">{{ $p->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center gap-1">
                                <x-button :href="route('pengumuman.show', $p)" variant="ghost" size="icon" title="Lihat"><x-icon name="eye" class="h-4 w-4" /></x-button>
                                @if ($isAdmin)
                                    <x-button :href="route('pengumuman.edit', $p)" variant="ghost" size="icon" title="Edit"><x-icon name="edit" class="h-4 w-4" /></x-button>
                                    <x-confirm-delete :action="route('pengumuman.destroy', $p)" />
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </div>
        {{-- Mobile --}}
        <div class="divide-y divide-slate-100 dark:divide-slate-700/60 md:hidden">
            @foreach ($pengumuman as $p)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium text-slate-900 dark:text-white truncate">{{ $p->judul }}</p>
                            <p class="text-sm text-slate-500">{{ $p->pembuat->nama ?? '-' }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-1">
                            <x-button :href="route('pengumuman.show', $p)" variant="ghost" size="icon"><x-icon name="eye" class="h-4 w-4" /></x-button>
                            @if ($isAdmin)
                                <x-button :href="route('pengumuman.edit', $p)" variant="ghost" size="icon"><x-icon name="edit" class="h-4 w-4" /></x-button>
                                <x-confirm-delete :action="route('pengumuman.destroy', $p)" />
                            @endif
                        </div>
                    </div>
                    <dl class="mt-2 grid grid-cols-2 gap-x-4 gap-y-1 text-sm">
                        <div><dt class="text-xs text-slate-400">Target</dt><dd class="text-slate-700 dark:text-slate-300">{{ $p->target_role ?? 'semua' }}</dd></div>
                        <div><dt class="text-xs text-slate-400">Tanggal Publish</dt><dd class="text-slate-700 dark:text-slate-300">{{ optional($p->tanggal_publish)->format('d M Y') ?? '-' }}</dd></div>
                        <div><dt class="text-xs text-slate-400">Status</dt><dd class="text-slate-700 dark:text-slate-300">{{ $p->is_active ? 'Aktif' : 'Nonaktif' }}</dd></div>
                    </dl>
                </div>
            @endforeach
        </div>
    @else
        <div class="p-6"><x-empty-state icon="pengumuman" title="Belum ada pengumuman" description="Pengumuman akan muncul di sini." /></div>
    @endif
</x-card>

@if ($pengumuman->hasPages())
    <div class="mt-4">{{ $pengumuman->links() }}</div>
@endif
@endsection
