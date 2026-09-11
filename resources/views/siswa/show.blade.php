@extends('layouts.app')

@section('title', 'Detail Siswa')

@section('content')
@php $isAdmin = auth()->user()->isAdmin(); @endphp

<div x-data="{ openTeguran: false }">
    <x-page-header title="Detail Siswa" subtitle="{{ $siswa->nama_lengkap }}">
        <x-slot:actions>
            <x-button variant="secondary" :href="route('siswa.index')">Kembali</x-button>

            {{-- Tombol Kirim Teguran WA --}}
            <x-button type="button" @click="openTeguran = true" variant="warning">
                <x-icon name="bell" class="h-4 w-4" /> Kirim Teguran WA
            </x-button>

            @if ($isAdmin)
                <x-button variant="primary" :href="route('siswa.edit', $siswa)"><x-icon name="edit" class="h-4 w-4" /> Edit</x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
        <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-6 bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
        <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('error') }}
    </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Identitas --}}
        <div class="lg:col-span-1">
            <x-card>
                <div class="flex flex-col items-center text-center">
                    @if ($siswa->foto)
                        <img src="{{ asset('storage/' . $siswa->foto) }}" alt="Foto {{ $siswa->nama_lengkap }}"
                            class="h-24 w-24 rounded-full object-cover ring-2 ring-brand-100 dark:ring-brand-900">
                    @else
                        <div class="flex h-24 w-24 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900/40 text-2xl font-bold text-brand-600 dark:text-brand-300">
                            {{ strtoupper(substr($siswa->nama_lengkap, 0, 1)) }}
                        </div>
                    @endif
                    <h3 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">{{ $siswa->nama_lengkap }}</h3>
                    <p class="text-sm text-slate-500">NIS {{ $siswa->nis }}</p>
                    <div class="mt-3">
                        @php $stColor = ['Aktif' => 'success', 'Lulus' => 'info', 'Pindah' => 'warning', 'Keluar' => 'danger'][$siswa->status ?? 'Aktif'] ?? 'slate'; @endphp
                        <x-badge :variant="$stColor">{{ $siswa->status ?? 'Aktif' }}</x-badge>
                    </div>
                </div>

                <dl class="mt-6 space-y-3 border-t border-slate-100 dark:border-slate-700 pt-4 text-sm">
                    <div class="flex justify-between gap-4"><dt class="text-slate-400">Kelas</dt><dd class="text-slate-900 dark:text-white font-medium">{{ $siswa->kelas->nama_lengkap ?? $siswa->kelas->nama_kelas ?? '-' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-400">Jenis Kelamin</dt><dd class="text-slate-700 dark:text-slate-300 font-medium">{{ $siswa->jenis_kelamin_teks }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-400">Tanggal Lahir</dt><dd class="text-slate-700 dark:text-slate-300">{{ optional($siswa->tanggal_lahir)->format('d M Y') ?? '-' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-400">Tahun Masuk</dt><dd class="text-slate-700 dark:text-slate-300">{{ $siswa->tahun_masuk }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-400">Alamat</dt><dd class="text-right text-slate-700 dark:text-slate-300">{{ $siswa->alamat ?? '-' }}</dd></div>
                </dl>
            </x-card>
        </div>

        {{-- Relasi --}}
        <div class="space-y-6 lg:col-span-2">
            <x-card padding="p-0">
                <x-slot:header>
                    <div class="flex items-center justify-between px-5 py-4 sm:px-6">
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Orang Tua / Wali</h2>
                    </div>
                </x-slot:header>

                @if ($siswa->orangTua->isNotEmpty())
                    <ul class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @foreach ($siswa->orangTua as $ot)
                            <li class="flex items-center justify-between gap-3 px-5 py-3 sm:px-6">
                                <div>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $ot->nama }}</p>
                                    <p class="text-xs text-slate-500">{{ $ot->hubungan ?? '-' }} • {{ $ot->no_hp ?? '-' }}</p>
                                </div>
                                @if ($ot->is_kontak_utama) <x-badge variant="brand">Kontak Utama</x-badge> @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="p-6"><x-empty-state icon="orangtua" title="Belum ada data orang tua" /></div>
                @endif
            </x-card>

            <x-card padding="p-0">
                <x-slot:header><h2 class="text-sm font-semibold text-slate-900 dark:text-white px-5 py-4 sm:px-6">Rekap Nilai</h2></x-slot:header>
                @if ($siswa->nilai->isNotEmpty())
                    <x-table :headers="['Mapel', 'Semester', 'Akhir', 'Predikat']">
                        @foreach ($siswa->nilai as $n)
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-white">{{ $n->mapel->nama_mapel ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">{{ $n->semester }}</td>
                                <td class="px-4 py-3 text-sm font-semibold text-slate-900 dark:text-white">{{ $n->nilai_akhir ?? '-' }}</td>
                                <td class="px-4 py-3"><x-badge variant="brand">{{ $n->predikat ?? '-' }}</x-badge></td>
                            </tr>
                        @endforeach
                    </x-table>
                @else
                    <div class="p-6"><x-empty-state icon="nilai" title="Belum ada nilai" /></div>
                @endif
            </x-card>
        </div>
    </div>

    {{-- Modal Kirim Teguran WA --}}
    <div x-show="openTeguran" x-cloak @click.self="openTeguran = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-4 backdrop-blur-sm" style="display:none">
        <div @click.stop class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60">
            <div class="mb-4 flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-300">
                        <x-icon name="bell" class="h-5 w-5" />
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-slate-900 dark:text-white">Kirim Peringatan / Teguran WA</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Notifikasi khusus untuk Wali dari {{ $siswa->nama_lengkap }}</p>
                    </div>
                </div>
                <button @click="openTeguran = false" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <x-icon name="x" class="h-5 w-5" />
                </button>
            </div>

            <form method="POST" action="{{ route('siswa.teguran', $siswa) }}" class="space-y-4">
                @csrf

                {{-- List Penerima Wali --}}
                <div class="rounded-xl bg-slate-50 p-3.5 dark:bg-slate-700/40">
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">Penerima Pesan (Semua Wali Murid Terdaftar):</p>
                    @if ($siswa->orangTua->isNotEmpty())
                        <div class="space-y-1">
                            @foreach ($siswa->orangTua as $ot)
                                @if ($ot->no_wa || $ot->no_hp)
                                    <div class="flex items-center justify-between text-xs text-slate-700 dark:text-slate-200">
                                        <span class="font-medium">• {{ $ot->nama }} ({{ $ot->hubungan ?? 'Wali' }})</span>
                                        <span class="font-mono text-emerald-600 dark:text-emerald-400 font-semibold">{{ $ot->no_wa ?? $ot->no_hp }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-rose-500 font-medium">⚠️ Siswa belum memiliki data Orang Tua / Wali terdaftar.</p>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                        Perihal Teguran <span class="text-red-500">*</span>
                    </label>
                    <select name="jenis_teguran" required
                        class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                        <option value="Catatan Kedisiplinan & Tata Tertib">Catatan Kedisiplinan & Tata Tertib</option>
                        <option value="Peringatan Ketidakhadiran (Absensi)">Peringatan Ketidakhadiran (Absensi)</option>
                        <option value="Pelanggaran Aturan Sekolah">Pelanggaran Aturan Sekolah</option>
                        <option value="Undangan Panggilan Orang Tua / Wali">Undangan Panggilan Orang Tua / Wali</option>
                        <option value="Catatan Perkembangan Belajar">Catatan Perkembangan Belajar</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                        Catatan / Detail Pelanggaran <span class="text-red-500">*</span>
                    </label>
                    <textarea name="catatan" rows="3" required
                        class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                        placeholder="Tuliskan catatan pelanggaran atau penjelasan khusus..."></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="perlu_ke_sekolah" name="perlu_ke_sekolah" value="1"
                        class="rounded border-slate-300 text-brand-600 focus:ring-brand-500 dark:border-slate-600 dark:bg-slate-700">
                    <label for="perlu_ke_sekolah" class="text-xs text-slate-700 dark:text-slate-300">
                        Minta Orang Tua / Wali untuk hadir berkoordinasi ke sekolah
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-700/60">
                    <x-button type="button" variant="secondary" @click="openTeguran = false">
                        Batal
                    </x-button>
                    <x-button type="submit" variant="warning">
                        <x-icon name="paper-airplane" class="h-4 w-4" /> Kirim Pesan WA
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
