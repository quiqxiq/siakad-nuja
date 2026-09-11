@extends('layouts.app')

@section('title', 'Tambah Jadwal')

@section('content')
<x-page-header title="Tambah Jadwal" subtitle="Lengkapi data jadwal pelajaran baru." />

@if ($errors->any())
    <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 dark:border-rose-900/50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-200">
        <div class="flex items-center gap-2 font-semibold text-sm mb-1.5 text-rose-700 dark:text-rose-300">
            <x-icon name="exclamation" class="h-5 w-5 shrink-0 text-rose-600 dark:text-rose-400" />
            <span>Peringatan: Jadwal Tidak Dapat Disimpan (Bentrok / Kesalahan Input)</span>
        </div>
        <ul class="list-disc list-inside text-xs space-y-1 text-rose-700 dark:text-rose-300">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<x-card>
    <form method="POST" action="{{ route('jadwal.store') }}" class="space-y-6">
        @csrf
        @include('jadwal._form')
    </form>
</x-card>
@endsection
