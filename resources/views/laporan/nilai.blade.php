@extends('layouts.app')

@section('title', 'Preview Rekap Nilai')
@section('header', 'Preview Rekap Nilai')

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-8 max-w-5xl mx-auto">
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-slate-800">REKAPITULASI NILAI KELAS</h2>
        <p class="text-slate-600 mt-1">Kelas: <span class="font-semibold">{{ $kelas->nama_kelas }}</span> | Mata Pelajaran: <span class="font-semibold">{{ $mapel->nama_mapel }}</span></p>
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-800 border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3 font-semibold w-12 text-center">No</th>
                    <th class="px-4 py-3 font-semibold">NIS</th>
                    <th class="px-4 py-3 font-semibold">Nama Siswa</th>
                    <th class="px-4 py-3 font-semibold text-center">Tugas/Harian</th>
                    <th class="px-4 py-3 font-semibold text-center">UTS</th>
                    <th class="px-4 py-3 font-semibold text-center">UAS</th>
                    <th class="px-4 py-3 font-semibold text-center text-brand-700 bg-brand-50/50">Nilai Akhir</th>
                    <th class="px-4 py-3 font-semibold text-center">Predikat</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($siswa as $i => $s)
                @php $n = $nilai[$s->id] ?? null; @endphp
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3 text-center">{{ $i + 1 }}</td>
                    <td class="px-4 py-3 font-medium">{{ $s->nis }}</td>
                    <td class="px-4 py-3">{{ $s->nama_lengkap }}</td>
                    <td class="px-4 py-3 text-center">{{ $n ? $n->nilai_harian : '-' }}</td>
                    <td class="px-4 py-3 text-center">{{ $n ? $n->nilai_uts : '-' }}</td>
                    <td class="px-4 py-3 text-center">{{ $n ? $n->nilai_uas : '-' }}</td>
                    <td class="px-4 py-3 text-center font-bold text-brand-700 bg-brand-50/50">{{ $n ? $n->nilai_akhir : '-' }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($n)
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-bold {{ $n->predikat === 'A' ? 'bg-emerald-100 text-emerald-800' : ($n->predikat === 'B' ? 'bg-blue-100 text-blue-800' : ($n->predikat === 'C' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800')) }}">
                                {{ $n->predikat }}
                            </span>
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-slate-500">Belum ada data siswa untuk kelas ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8 flex justify-end gap-3 print:hidden">
        <a href="{{ route('laporan.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-medium hover:bg-slate-50 transition">Kembali</a>
        <a href="{{ route('laporan.nilai', ['kelas_id' => $kelas->id, 'mapel_id' => $mapel->id, 'export' => 'excel']) }}" class="px-5 py-2.5 rounded-xl bg-emerald-600 text-white font-semibold hover:bg-emerald-700 transition shadow-sm flex items-center gap-2">
            <x-icon name="download" class="h-4 w-4" /> Unduh Excel
        </a>
        <a href="{{ route('laporan.nilai', ['kelas_id' => $kelas->id, 'mapel_id' => $mapel->id, 'export' => 'pdf']) }}" class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition shadow-sm flex items-center gap-2">
            <x-icon name="download" class="h-4 w-4" /> Cetak PDF
        </a>
    </div>
</div>
@endsection
