@extends('layouts.app')

@section('title', 'Entri Nilai Massal (Matrix)')

@section('content')
<x-page-header title="Entri Nilai Massal" subtitle="Input nilai Harian, UTS, dan UAS sekaligus untuk seluruh siswa di satu rombel kelas.">
    <x-slot:actions>
        <div class="flex items-center gap-2">
            <x-button :href="route('nilai.leger', ['kelas_id' => $selectedKelasId, 'semester' => $semester, 'tahun_ajaran' => $tahunAjaran])" variant="secondary">
                <x-icon name="document" class="h-4 w-4" /> Buku Leger &amp; Peringkat
            </x-button>
            <x-button :href="route('nilai.index')" variant="ghost">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali
            </x-button>
        </div>
    </x-slot:actions>
</x-page-header>

{{-- Filter Pemilihan Kelas, Mapel, Semester, Tahun Ajaran --}}
<x-card class="mb-6">
    <form method="GET" action="{{ route('nilai.matrix') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5 items-end">
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Kelas</label>
            <select name="kelas_id" onchange="this.form.submit()" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                @forelse ($kelasList as $k)
                    <option value="{{ $k->id }}" @selected($selectedKelasId == $k->id)>{{ $k->nama_lengkap }}</option>
                @empty
                    <option value="">Tidak ada kelas diampu</option>
                @endforelse
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Mata Pelajaran</label>
            <select name="mapel_id" onchange="this.form.submit()" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                @forelse ($mapelList as $m)
                    <option value="{{ $m->id }}" @selected($selectedMapelId == $m->id)>{{ $m->nama_mapel }} (KKM: {{ $m->kkm ?? 75 }})</option>
                @empty
                    <option value="">Tidak ada mapel di kelas ini</option>
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
                <x-icon name="search" class="h-4 w-4" /> Tampilkan Roster
            </button>
        </div>
    </form>
</x-card>

@if ($selectedKelas && $selectedMapel && $matrixData)
    @php
        $kkmVal = (int) ($selectedMapel->kkm ?? 75);
    @endphp

    <form method="POST" action="{{ route('nilai.matrix.store') }}"
        x-data="{
            kkm: {{ $kkmVal }},
            hitungAkhir(harian, uts, uas) {
                let h = (harian !== '' && harian !== null && !isNaN(harian)) ? parseFloat(harian) : null;
                let u = (uts !== '' && uts !== null && !isNaN(uts)) ? parseFloat(uts) : null;
                let a = (uas !== '' && uas !== null && !isNaN(uas)) ? parseFloat(uas) : null;

                if (h === null && u === null && a === null) return '-';
                if (h !== null && u !== null && a !== null) return ((h * 0.3) + (u * 0.3) + (a * 0.4)).toFixed(2);
                if (h !== null && u !== null && a === null) return ((h * 0.5) + (u * 0.5)).toFixed(2);
                if (h !== null && u === null && a !== null) return ((h * 0.4) + (a * 0.6)).toFixed(2);
                if (h === null && u !== null && a !== null) return ((u * 0.4) + (a * 0.6)).toFixed(2);
                if (h !== null) return h.toFixed(2);
                if (u !== null) return u.toFixed(2);
                return a.toFixed(2);
            },
            hitungPredikat(akhir) {
                if (akhir === '-' || akhir === '' || isNaN(akhir)) return '-';
                let val = parseFloat(akhir);
                let interval = (100 - this.kkm) / 3.0;
                let minB = this.kkm + interval;
                let minA = this.kkm + (2 * interval);

                if (val >= minA) return 'A';
                if (val >= minB) return 'B';
                if (val >= this.kkm) return 'C';
                return 'D';
            },
            isTuntas(akhir) {
                if (akhir === '-' || isNaN(akhir)) return null;
                return parseFloat(akhir) >= this.kkm;
            }
        }">
        @csrf
        <input type="hidden" name="kelas_id" value="{{ $selectedKelasId }}">
        <input type="hidden" name="mapel_id" value="{{ $selectedMapelId }}">
        <input type="hidden" name="semester" value="{{ $semester }}">
        <input type="hidden" name="tahun_ajaran" value="{{ $tahunAjaran }}">

        <x-card padding="p-0">
            <div class="p-4 border-b border-slate-200 dark:border-slate-700/80 bg-slate-50 dark:bg-slate-800/60 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white">
                        {{ $selectedKelas->nama_lengkap }} — {{ $selectedMapel->nama_mapel }}
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        KKM: <span class="font-semibold text-brand-600 dark:text-brand-400">{{ $kkmVal }}</span> • Semester: <span class="font-medium text-slate-700 dark:text-slate-300">{{ $semester }}</span> • Tahun Ajaran: <span class="font-medium text-slate-700 dark:text-slate-300">{{ $tahunAjaran }}</span>
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-slate-500 dark:text-slate-400 hidden sm:inline">Bobot: Harian 30% • UTS 30% • UAS 40%</span>
                    <x-button type="submit" variant="primary">
                        <x-icon name="check" class="h-4 w-4" /> Simpan Semua Nilai
                    </x-button>
                </div>
            </div>

            @if ($matrixData->count())
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                        <thead class="bg-slate-100 dark:bg-slate-800 text-xs uppercase font-semibold text-slate-600 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="px-4 py-3 text-center w-12">No</th>
                                <th class="px-4 py-3 w-28">NIS</th>
                                <th class="px-4 py-3 min-w-[200px]">Nama Siswa</th>
                                <th class="px-4 py-3 w-32 text-center">Nilai Harian (30%)</th>
                                <th class="px-4 py-3 w-32 text-center">Nilai UTS (30%)</th>
                                <th class="px-4 py-3 w-32 text-center">Nilai UAS (40%)</th>
                                <th class="px-4 py-3 w-28 text-center bg-slate-50 dark:bg-slate-700/40">Nilai Akhir</th>
                                <th class="px-4 py-3 w-20 text-center bg-slate-50 dark:bg-slate-700/40">Predikat</th>
                                <th class="px-4 py-3 w-28 text-center">Ketuntasan</th>
                                <th class="px-4 py-3 w-20 text-center">Rank</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach ($matrixData as $index => $row)
                                @php
                                    $s = $row['siswa'];
                                    $hInit = $row['nilai_harian'] !== null ? (float) $row['nilai_harian'] : '';
                                    $uInit = $row['nilai_uts'] !== null ? (float) $row['nilai_uts'] : '';
                                    $aInit = $row['nilai_uas'] !== null ? (float) $row['nilai_uas'] : '';
                                    $akhirInit = $row['nilai_akhir'] !== null ? number_format((float) $row['nilai_akhir'], 2) : '-';
                                    $predikatInit = $row['predikat'] ?? '-';
                                @endphp
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors"
                                    x-data="{
                                        harian: '{{ $hInit }}',
                                        uts: '{{ $uInit }}',
                                        uas: '{{ $aInit }}',
                                        get akhir() { return hitungAkhir(this.harian, this.uts, this.uas); },
                                        get predikat() { return hitungPredikat(this.akhir); },
                                        get tuntas() { return isTuntas(this.akhir); }
                                    }">
                                    <td class="px-4 py-2.5 text-center text-xs text-slate-400 font-medium">{{ $index + 1 }}</td>
                                    <td class="px-4 py-2.5 font-mono text-xs text-slate-500 dark:text-slate-400">{{ $s->nis }}</td>
                                    <td class="px-4 py-2.5 font-medium text-slate-900 dark:text-white">{{ $s->nama_lengkap }}</td>
                                    <td class="px-3 py-2">
                                        <input type="number" step="0.01" min="0" max="100"
                                            name="nilai_harian[{{ $s->id }}]"
                                            x-model="harian"
                                            placeholder="0 - 100"
                                            class="block w-full text-center rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm py-1.5 focus:border-brand-500 focus:ring-brand-500">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" step="0.01" min="0" max="100"
                                            name="nilai_uts[{{ $s->id }}]"
                                            x-model="uts"
                                            placeholder="0 - 100"
                                            class="block w-full text-center rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm py-1.5 focus:border-brand-500 focus:ring-brand-500">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" step="0.01" min="0" max="100"
                                            name="nilai_uas[{{ $s->id }}]"
                                            x-model="uas"
                                            placeholder="0 - 100"
                                            class="block w-full text-center rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm py-1.5 focus:border-brand-500 focus:ring-brand-500">
                                    </td>
                                    <td class="px-4 py-2.5 text-center font-bold text-slate-900 dark:text-white bg-slate-50 dark:bg-slate-700/40" x-text="akhir">
                                        {{ $akhirInit }}
                                    </td>
                                    <td class="px-4 py-2.5 text-center font-bold bg-slate-50 dark:bg-slate-700/40">
                                        <span :class="{
                                            'text-emerald-600 dark:text-emerald-400': predikat === 'A' || predikat === 'B',
                                            'text-sky-600 dark:text-sky-400': predikat === 'C',
                                            'text-rose-600 dark:text-rose-400': predikat === 'D' || predikat === 'E',
                                            'text-slate-400': predikat === '-'
                                        }" x-text="predikat">{{ $predikatInit }}</span>
                                    </td>
                                    <td class="px-4 py-2.5 text-center">
                                        <template x-if="tuntas === true">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">Tuntas</span>
                                        </template>
                                        <template x-if="tuntas === false">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">Remedial</span>
                                        </template>
                                        <template x-if="tuntas === null">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">-</span>
                                        </template>
                                    </td>
                                    <td class="px-4 py-2.5 text-center font-semibold text-slate-700 dark:text-slate-300">
                                        @if ($row['rank'] === 1)
                                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-400 text-amber-950 font-bold text-xs shadow-sm">1</span>
                                        @elseif ($row['rank'] === 2)
                                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-300 text-slate-900 font-bold text-xs shadow-sm">2</span>
                                        @elseif ($row['rank'] === 3)
                                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-700 text-amber-100 font-bold text-xs shadow-sm">3</span>
                                        @else
                                            {{ $row['rank'] }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 flex items-center justify-between">
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Total Siswa: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $matrixData->count() }} orang</span>
                    </p>
                    <x-button type="submit" variant="primary">
                        <x-icon name="check" class="h-4 w-4" /> Simpan Semua Nilai
                    </x-button>
                </div>
            @else
                <div class="p-8 text-center">
                    <x-empty-state icon="siswa" title="Belum ada data siswa" description="Tidak ada siswa terdaftar di kelas ini." />
                </div>
            @endif
        </x-card>
    </form>
@else
    <x-card>
        <div class="p-8 text-center">
            <x-empty-state icon="nilai" title="Pilih Kelas & Mata Pelajaran" description="Silakan tentukan kelas dan mata pelajaran yang ingin diinput nilainya pada filter di atas." />
        </div>
    </x-card>
@endif
@endsection
