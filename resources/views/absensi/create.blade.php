@extends('layouts.app')

@section('title', 'Entri Absensi')

@section('content')
<x-page-header title="Entri Absensi" subtitle="Pilih jadwal dan tanggal untuk mengisi kehadiran seluruh siswa." />

<x-card>
    @if ($jadwal->isNotEmpty())
        @php
            $jadwalOptions = $jadwal->map(function ($j) {
                $kelasNama = $j->kelas->nama_lengkap ?? $j->kelas->nama_kelas ?? 'Kelas -';
                $mapelNama = $j->mapel->nama_mapel ?? 'Mapel -';
                $guruNama = $j->guru->nama_lengkap ?? null;
                $hariJam = $j->hari . ', Jam ke-' . $j->jam_ke;
                return [
                    'id' => $j->id,
                    'label' => $mapelNama . ' — ' . $kelasNama,
                    'sublabel' => $hariJam . ($guruNama ? ' • ' . $guruNama : '') . ($j->ruangan ? ' • ' . $j->ruangan : ''),
                    'kelas_id' => $j->kelas_id,
                ];
            })->values()->all();
        @endphp

        <form method="GET" action="{{ route('absensi.roster') }}" class="space-y-6" x-data="{ selectedKelas: '' }">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        Filter Kelas (Opsional)
                    </label>
                    <select x-model="selectedKelas" class="block w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-white shadow-sm text-sm px-4 py-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition">
                        <option value="">-- Semua Kelas (Tampilkan Semua) --</option>
                        @foreach ($kelasList ?? [] as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_lengkap }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-400">Pilih kelas untuk mempersempit, atau langsung cari mapel di samping.</p>
                </div>

                <div class="sm:col-span-2">
                    <x-form.searchable-select
                        label="Pilih Mata Pelajaran & Jadwal"
                        name="jadwal_id"
                        :options="$jadwalOptions"
                        :selected="request('jadwal_id')"
                        placeholder="— Cari & Pilih Mata Pelajaran / Jadwal —"
                        searchPlaceholder="Ketik nama mata pelajaran, kelas, hari, atau guru..."
                        emptyText="Tidak ada mata pelajaran / jadwal yang cocok dengan pencarian"
                        :watchKelas="true"
                        required />
                </div>

                <div class="sm:col-span-3">
                    <x-form.input label="Tanggal Absensi" name="tanggal" type="date"
                        :value="request('tanggal', now()->format('Y-m-d'))" required />
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <x-button type="submit" variant="primary">
                    <x-icon name="absensi" class="h-4 w-4" /> Tampilkan Siswa
                </x-button>
                <x-button variant="secondary" :href="route('absensi.index')">Batal</x-button>
            </div>
        </form>
    @else
        <x-empty-state icon="jadwal" title="Tidak ada jadwal tersedia"
            description="Anda belum memiliki jadwal mengajar. Hubungi administrator." />
    @endif
</x-card>
@endsection
