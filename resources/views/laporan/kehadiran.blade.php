@extends('layouts.app')

@section('title', 'Preview Rekap Kehadiran')
@section('header', 'Preview Rekap Kehadiran')

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-8 max-w-5xl mx-auto">
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-slate-800">REKAPITULASI KEHADIRAN KELAS</h2>
        <p class="text-slate-600 mt-1">Kelas: <span class="font-semibold">{{ $kelas->nama_kelas }}</span> | Bulan: <span class="font-semibold">{{ \Carbon\Carbon::parse($bulan)->translatedFormat('F Y') }}</span></p>
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-800 border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3 font-semibold w-12 text-center">No</th>
                    <th class="px-4 py-3 font-semibold">NIS</th>
                    <th class="px-4 py-3 font-semibold">Nama Siswa</th>
                    <th class="px-4 py-3 font-semibold text-center w-20 text-emerald-600">Hadir</th>
                    <th class="px-4 py-3 font-semibold text-center w-20 text-amber-600">Sakit</th>
                    <th class="px-4 py-3 font-semibold text-center w-20 text-blue-600">Izin</th>
                    <th class="px-4 py-3 font-semibold text-center w-20 text-red-600">Alpa</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($siswa as $i => $s)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3 text-center">{{ $i + 1 }}</td>
                    <td class="px-4 py-3 font-medium">{{ $s->nis }}</td>
                    <td class="px-4 py-3">{{ $s->nama_lengkap }}</td>
                    <td class="px-4 py-3 text-center font-bold text-emerald-600 bg-emerald-50/50">{{ $rekap[$s->id]['Hadir'] }}</td>
                    <td class="px-4 py-3 text-center font-bold text-amber-600 bg-amber-50/50">{{ $rekap[$s->id]['Sakit'] }}</td>
                    <td class="px-4 py-3 text-center font-bold text-blue-600 bg-blue-50/50">{{ $rekap[$s->id]['Izin'] }}</td>
                    <td class="px-4 py-3 text-center font-bold text-red-600 bg-red-50/50">{{ $rekap[$s->id]['Alpa'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-slate-500">Belum ada data siswa untuk kelas ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8 flex justify-end gap-3 print:hidden">
        <a href="{{ route('laporan.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-medium hover:bg-slate-50 transition">Kembali</a>
        <a href="{{ route('laporan.kehadiran', ['kelas_id' => $kelas->id, 'bulan' => $bulan, 'export' => 'excel']) }}" class="px-5 py-2.5 rounded-xl bg-emerald-600 text-white font-semibold hover:bg-emerald-700 transition shadow-sm flex items-center gap-2">
            <x-icon name="download" class="h-4 w-4" /> Unduh Excel
        </a>
        <a href="{{ route('laporan.kehadiran', ['kelas_id' => $kelas->id, 'bulan' => $bulan, 'export' => 'pdf']) }}" class="px-5 py-2.5 rounded-xl bg-brand-600 text-white font-semibold hover:bg-brand-700 transition shadow-sm flex items-center gap-2">
            <x-icon name="download" class="h-4 w-4" /> Cetak PDF
        </a>
    </div>
</div>
@endsection
