@extends('layouts.app')

@section('title', 'Buku Leger & Peringkat Kelas')

@section('content')
<x-page-header title="Buku Leger &amp; Peringkat Kelas" subtitle="Rekapitulasi nilai seluruh mata pelajaran per rombel dan peringkat otomatis (Juara Kelas).">
    <x-slot:actions>
        <div class="flex items-center gap-2">
            @if (!empty($selectedKelasId) && !empty($legerData))
                <a href="{{ route('nilai.leger.export', ['kelas_id' => $selectedKelasId, 'semester' => $semester, 'tahun_ajaran' => $tahunAjaran, 'format' => 'pdf']) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg bg-rose-600 hover:bg-rose-700 text-white shadow-sm transition">
                    <x-icon name="download" class="h-4 w-4" /> Unduh PDF
                </a>
                <a href="{{ route('nilai.leger.export', ['kelas_id' => $selectedKelasId, 'semester' => $semester, 'tahun_ajaran' => $tahunAjaran, 'format' => 'excel']) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition">
                    <x-icon name="download" class="h-4 w-4" /> Unduh Excel
                </a>
            @endif
            <x-button :href="route('nilai.matrix', ['kelas_id' => $selectedKelasId, 'semester' => $semester, 'tahun_ajaran' => $tahunAjaran])" variant="primary">
                <x-icon name="plus" class="h-4 w-4" /> Entri Nilai Massal
            </x-button>
        </div>
    </x-slot:actions>
</x-page-header>

{{-- Filter Kelas, Semester, Tahun Ajaran --}}
<x-card class="mb-6">
    <form method="GET" action="{{ route('nilai.leger') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 items-end">
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Pilih Kelas</label>
            <select name="kelas_id" onchange="this.form.submit()" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                @forelse ($kelasList as $k)
                    <option value="{{ $k->id }}" @selected($selectedKelasId == $k->id)>{{ $k->nama_lengkap }}</option>
                @empty
                    <option value="">Tidak ada kelas diampu</option>
                @endforelse
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Semester</label>
            <select name="semester" onchange="this.form.submit()" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                <option value="Ganjil" @selected($semester === 'Ganjil')>Ganjil</option>
                <option value="Genap" @selected($semester === 'Genap')>Genap</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Tahun Ajaran</label>
            <input type="text" name="tahun_ajaran" value="{{ $tahunAjaran }}" placeholder="2024/2025" onchange="this.form.submit()" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
        </div>

        <div>
            <button type="submit" class="w-full px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white font-medium text-sm rounded-lg transition shadow-sm flex items-center justify-center gap-1.5">
                <x-icon name="search" class="h-4 w-4" /> Tampilkan Leger
            </button>
        </div>
    </form>
</x-card>

@if ($legerData && !empty($legerData['rows']))
    @php
        $kelas = $legerData['kelas'];
        $mapelList = $legerData['mapelList'];
        $rows = $legerData['rows'];
        $mapelStats = $legerData['mapelStats'];
    @endphp

    {{-- Highlight Top 3 Juara Kelas --}}
    @php
        $top3 = $rows->where('rank', '<=', 3)->where('rank', '!=', '-')->take(3);
    @endphp
    @if ($top3->isNotEmpty())
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            @foreach ($top3 as $top)
                @php
                    $badgeStyle = match ((int) $top['rank']) {
                        1 => ['bg' => 'from-amber-500/20 to-amber-500/5 border-amber-400 dark:border-amber-500/40 text-amber-700 dark:text-amber-300', 'badge' => 'bg-amber-400 text-amber-950', 'icon' => '🥇 Juara 1'],
                        2 => ['bg' => 'from-slate-400/20 to-slate-400/5 border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300', 'badge' => 'bg-slate-300 text-slate-900', 'icon' => '🥈 Juara 2'],
                        3 => ['bg' => 'from-amber-700/20 to-amber-700/5 border-amber-600/40 text-amber-800 dark:text-amber-200', 'badge' => 'bg-amber-700 text-white', 'icon' => '🥉 Juara 3'],
                        default => ['bg' => 'bg-slate-50 border-slate-200', 'badge' => 'bg-slate-200 text-slate-800', 'icon' => 'Top'],
                    };
                @endphp
                <div class="rounded-xl border bg-gradient-to-b {{ $badgeStyle['bg'] }} p-4 shadow-sm relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1 font-bold text-xs uppercase px-2 py-0.5 rounded-full {{ $badgeStyle['badge'] }}">
                            {{ $badgeStyle['icon'] }}
                        </span>
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Rata-rata: {{ $top['rata_rata'] ?? '-' }}</span>
                    </div>
                    <div class="mt-3">
                        <h4 class="font-bold text-base text-slate-900 dark:text-white truncate">{{ $top['siswa']->nama_lengkap }}</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">NIS: {{ $top['siswa']->nis }} • Total Nilai: <span class="font-bold text-slate-900 dark:text-white">{{ $top['total_akhir'] ?? '-' }}</span></p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Tabel Leger Matriks --}}
    <x-card padding="p-0">
        <div class="p-4 border-b border-slate-200 dark:border-slate-700/80 bg-slate-50 dark:bg-slate-800/60 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">
                    Leger Nilai — {{ $kelas->nama_lengkap }}
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Wali Kelas: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $kelas->waliKelas->nama_lengkap ?? 'Belum Ditentukan' }}</span> • Semester: <span class="font-medium text-slate-700 dark:text-slate-300">{{ $semester }}</span> • Tahun Ajaran: <span class="font-medium text-slate-700 dark:text-slate-300">{{ $tahunAjaran }}</span>
                </p>
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400">
                Total: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $rows->count() }} Siswa</span> | <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $mapelList->count() }} Mata Pelajaran</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300 border-collapse">
                <thead class="bg-slate-100 dark:bg-slate-800 uppercase font-semibold text-slate-600 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-3 py-3 text-center w-12 border-r border-slate-200 dark:border-slate-700">Rank</th>
                        <th class="px-3 py-3 w-24 border-r border-slate-200 dark:border-slate-700">NIS</th>
                        <th class="px-4 py-3 min-w-[180px] border-r border-slate-200 dark:border-slate-700">Nama Siswa</th>
                        @foreach ($mapelList as $m)
                            <th class="px-2 py-2 text-center min-w-[65px] border-r border-slate-200 dark:border-slate-700" title="{{ $m->nama_mapel }} (KKM: {{ $m->kkm ?? 75 }})">
                                <div class="font-bold truncate max-w-[80px] mx-auto">{{ $m->kode_mapel ?? Str::limit($m->nama_mapel, 6) }}</div>
                                <div class="text-[10px] font-normal text-slate-400">KKM {{ $m->kkm ?? 75 }}</div>
                            </th>
                        @endforeach
                        <th class="px-3 py-3 text-center w-20 border-r border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/40">Total</th>
                        <th class="px-3 py-3 text-center w-20 border-r border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/40">Rerata</th>
                        <th class="px-3 py-3 text-center w-24">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @foreach ($rows as $row)
                        @php
                            $s = $row['siswa'];
                            $rank = $row['rank'];
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors {{ $rank === 1 ? 'bg-amber-50/40 dark:bg-amber-950/10' : '' }}">
                            <td class="px-3 py-2 text-center font-bold border-r border-slate-200 dark:border-slate-700">
                                @if ($rank === 1)
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-400 text-amber-950 font-bold text-[10px] shadow-sm">1</span>
                                @elseif ($rank === 2)
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-slate-300 text-slate-900 font-bold text-[10px] shadow-sm">2</span>
                                @elseif ($rank === 3)
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-700 text-amber-100 font-bold text-[10px] shadow-sm">3</span>
                                @else
                                    <span class="text-slate-500 dark:text-slate-400">{{ $rank }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 font-mono text-slate-500 dark:text-slate-400 border-r border-slate-200 dark:border-slate-700">{{ $s->nis }}</td>
                            <td class="px-4 py-2 font-medium text-slate-900 dark:text-white border-r border-slate-200 dark:border-slate-700">
                                <a href="{{ route('siswa.show', $s) }}" class="hover:text-brand-600 dark:hover:text-brand-400 hover:underline">
                                    {{ $s->nama_lengkap }}
                                </a>
                            </td>
                            @foreach ($mapelList as $m)
                                @php
                                    $sc = $row['scores'][$m->id] ?? null;
                                    $val = $sc['akhir'] ?? null;
                                    $isTuntas = $sc['is_tuntas'] ?? null;
                                @endphp
                                <td class="px-2 py-2 text-center border-r border-slate-200 dark:border-slate-700 {{ $isTuntas === false ? 'bg-rose-50/60 dark:bg-rose-950/20 text-rose-600 font-semibold' : '' }}">
                                    @if ($val !== null)
                                        <span>{{ number_format((float) $val, 1) }}</span>
                                        <span class="text-[10px] text-slate-400 block">{{ $sc['predikat'] ?? '' }}</span>
                                    @else
                                        <span class="text-slate-300 dark:text-slate-600">-</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="px-3 py-2 text-center font-bold text-slate-900 dark:text-white border-r border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-700/30">
                                {{ $row['total_akhir'] !== null ? number_format((float) $row['total_akhir'], 1) : '-' }}
                            </td>
                            <td class="px-3 py-2 text-center font-bold text-brand-600 dark:text-brand-400 border-r border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-700/30">
                                {{ $row['rata_rata'] !== null ? number_format((float) $row['rata_rata'], 2) : '-' }}
                            </td>
                            <td class="px-3 py-2 text-center">
                                @if ($row['total_akhir'] !== null)
                                    @if ($row['belum_tuntas_count'] === 0)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">Tuntas</span>
                                    @else
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">{{ $row['belum_tuntas_count'] }} Rem</span>
                                    @endif
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>

                {{-- Footer Baris Statistik --}}
                <tfoot class="bg-slate-100 dark:bg-slate-800 font-semibold text-slate-700 dark:text-slate-300 border-t-2 border-slate-300 dark:border-slate-600">
                    <tr>
                        <td colspan="3" class="px-4 py-2 text-right border-r border-slate-200 dark:border-slate-700">Rata-rata Kelas:</td>
                        @foreach ($mapelList as $m)
                            <td class="px-2 py-2 text-center border-r border-slate-200 dark:border-slate-700 text-brand-600 dark:text-brand-400">
                                {{ $mapelStats[$m->id]['avg'] ?? '-' }}
                            </td>
                        @endforeach
                        <td colspan="3" class="px-3 py-2 text-center"></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="px-4 py-2 text-right border-r border-slate-200 dark:border-slate-700">Nilai Tertinggi:</td>
                        @foreach ($mapelList as $m)
                            <td class="px-2 py-2 text-center border-r border-slate-200 dark:border-slate-700 text-emerald-600 dark:text-emerald-400">
                                {{ $mapelStats[$m->id]['max'] ?? '-' }}
                            </td>
                        @endforeach
                        <td colspan="3" class="px-3 py-2 text-center"></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="px-4 py-2 text-right border-r border-slate-200 dark:border-slate-700">Nilai Terendah:</td>
                        @foreach ($mapelList as $m)
                            <td class="px-2 py-2 text-center border-r border-slate-200 dark:border-slate-700 text-rose-600 dark:text-rose-400">
                                {{ $mapelStats[$m->id]['min'] ?? '-' }}
                            </td>
                        @endforeach
                        <td colspan="3" class="px-3 py-2 text-center"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-card>
@else
    <x-card>
        <div class="p-8 text-center">
            <x-empty-state icon="nilai" title="Data Leger Belum Tersedia" description="Silakan pilih kelas dan semester pada filter di atas untuk melihat buku leger dan peringkat." />
        </div>
    </x-card>
@endif
@endsection
