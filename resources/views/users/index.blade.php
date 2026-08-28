@extends('layouts.app')

@section('title', 'Manajemen Akun')

@section('content')
<x-page-header title="Manajemen Akun" subtitle="Kelola akun admin & guru.">
    <x-slot:actions>
        <x-button :href="route('users.create')" variant="primary">
            <x-icon name="plus" class="h-4 w-4" /> Tambah Akun
        </x-button>
    </x-slot:actions>
</x-page-header>

<div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
    <x-search-bar placeholder="Cari nama atau email..." />
    <form method="GET" class="w-full sm:w-auto flex flex-wrap items-center gap-2">
        @foreach (request()->except(['role', 'status', 'page']) as $k => $v)
            @if (! is_array($v)) <input type="hidden" name="{{ $k }}" value="{{ $v }}"> @endif
        @endforeach
        <select name="role" onchange="this.form.submit()"
            class="block w-full sm:w-auto rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
            <option value="">Semua Peran</option>
            <option value="admin" @selected(request('role') === 'admin')>Administrator</option>
            <option value="guru" @selected(request('role') === 'guru')>Guru</option>
        </select>
        <select name="status" onchange="this.form.submit()"
            class="block w-full sm:w-auto rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
            <option value="">Semua Status</option>
            <option value="1" @selected(request('status') === '1')>Aktif</option>
            <option value="0" @selected(request('status') === '0')>Nonaktif</option>
        </select>
    </form>
</div>

<x-card padding="p-0">
    @if ($users->count())
        {{-- Desktop --}}
        <div class="hidden md:block">
            <x-table :headers="['Nama', 'Email', 'Peran', 'No. HP', 'Status', 'Aksi']">
                @foreach ($users as $u)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                        <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-white whitespace-nowrap">{{ $u->nama }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ $u->email }}</td>
                        <td class="px-4 py-3 whitespace-nowrap"><x-badge :variant="$u->isAdmin() ? 'brand' : 'info'">{{ ucfirst($u->role) }}</x-badge></td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ $u->no_hp ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap"><x-badge :variant="$u->is_active ? 'success' : 'slate'">{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge></td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center gap-1">
                                <x-button :href="route('users.edit', $u)" variant="ghost" size="icon" title="Edit"><x-icon name="edit" class="h-4 w-4" /></x-button>
                                <x-confirm-delete :action="route('users.destroy', $u)" />
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </div>
        {{-- Mobile --}}
        <div class="divide-y divide-slate-100 dark:divide-slate-700/60 md:hidden">
            @foreach ($users as $u)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium text-slate-900 dark:text-white truncate">{{ $u->nama }}</p>
                            <p class="text-sm text-slate-500 truncate">{{ $u->email }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-1">
                            <x-button :href="route('users.edit', $u)" variant="ghost" size="icon"><x-icon name="edit" class="h-4 w-4" /></x-button>
                            <x-confirm-delete :action="route('users.destroy', $u)" />
                        </div>
                    </div>
                    <div class="mt-2 flex items-center gap-2">
                        <x-badge :variant="$u->isAdmin() ? 'brand' : 'info'">{{ ucfirst($u->role) }}</x-badge>
                        <x-badge :variant="$u->is_active ? 'success' : 'slate'">{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="p-6"><x-empty-state icon="users" title="Belum ada akun" description="Tambahkan akun admin atau guru." /></div>
    @endif
</x-card>

@if ($users->hasPages())
    <div class="mt-4">{{ $users->links() }}</div>
@endif
@endsection
