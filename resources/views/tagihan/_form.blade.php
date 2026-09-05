{{-- Form fields for tagihan create/edit --}}
<div x-data="{ mode: '{{ old('_mode', 'siswa') }}' }">

    {{-- Mode selector (hanya di create) --}}
    @unless(isset($tagihan->id))
    <div class="mb-6 flex overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-1 dark:border-slate-700 dark:bg-slate-800">
        <button type="button" @click="mode = 'siswa'"
            :class="mode === 'siswa' ? 'bg-white dark:bg-slate-700 shadow-sm text-brand-700 dark:text-brand-300' : 'text-slate-500'"
            class="flex-1 rounded-lg py-2.5 text-sm font-semibold transition">
            <x-icon name="siswa" class="inline h-4 w-4 mr-1.5" />
            Per Siswa
        </button>
        <button type="button" @click="mode = 'massal'"
            :class="mode === 'massal' ? 'bg-white dark:bg-slate-700 shadow-sm text-brand-700 dark:text-brand-300' : 'text-slate-500'"
            class="flex-1 rounded-lg py-2.5 text-sm font-semibold transition">
            <x-icon name="kelas" class="inline h-4 w-4 mr-1.5" />
            Massal per Kelas
        </button>
    </div>
    @endunless

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">

        {{-- Target: Per Siswa --}}
        @php
            $tagihanSiswaOptions = $siswaList->map(fn($s) => [
                'id' => $s->id,
                'label' => $s->nama_lengkap,
                'sublabel' => 'NIS: ' . $s->nis . ' • ' . ($s->kelas->nama_lengkap ?? '-'),
                'kelas_id' => $s->kelas_id,
            ])->values()->all();
        @endphp
        <div x-show="mode === 'siswa'" class="sm:col-span-2">
            <x-form.searchable-select
                label="Siswa"
                name="siswa_id"
                :options="$tagihanSiswaOptions"
                :selected="old('siswa_id', $tagihan->siswa_id ?? '')"
                placeholder="Ketik nama atau NIS untuk mencari siswa..." />
        </div>

        {{-- Target: Massal per Kelas --}}
        <div x-show="mode === 'massal'" class="sm:col-span-2">
            <x-form.select label="Pilih Kelas (tagihan massal)" name="kelas_id_massal" :selected="old('kelas_id_massal', '')" x-bind:disabled="mode === 'siswa'">
                <option value="">— Pilih Kelas —</option>
                @foreach ($kelasList as $k)
                    <option value="{{ $k->id }}" @selected(old('kelas_id_massal') == $k->id)>{{ $k->nama_lengkap }}</option>
                @endforeach
            </x-form.select>
            <p class="mt-2 text-sm text-amber-600 dark:text-amber-400">
                <x-icon name="warning" class="inline h-4 w-4" />
                Tagihan akan dibuat untuk <strong>seluruh siswa</strong> dalam kelas yang dipilih.
            </p>
        </div>

        <input type="hidden" name="_mode" :value="mode">

        {{-- Judul --}}
        <div class="sm:col-span-2">
            <x-form.input label="Judul Tagihan" name="judul" :value="$tagihan->judul ?? ''" required
                :placeholder="'contoh: SPP Bulan Juli ' . date('Y')" />
        </div>

        {{-- Jenis & Periode --}}
        <x-form.select label="Jenis Tagihan" name="jenis" :selected="old('jenis', $tagihan->jenis ?? 'SPP')" required :placeholder="false">
            @foreach (['SPP', 'Uang Gedung', 'Seragam', 'Kegiatan', 'Lainnya'] as $j)
                <option value="{{ $j }}" @selected(old('jenis', $tagihan->jenis ?? 'SPP') === $j)>{{ $j }}</option>
            @endforeach
        </x-form.select>

        <x-form.input label="Periode" name="periode" :value="$tagihan->periode ?? ''"
            :placeholder="'Juli ' . date('Y') . ' / Semester 1 ' . date('Y') . '/' . (date('Y') + 1)" required />

        {{-- Nominal & Jatuh Tempo --}}
        <x-form.input label="Nominal (Rp)" name="nominal" type="number" step="1000" min="0"
            :value="$tagihan->nominal ?? ''" required placeholder="500000" />

        <x-form.input label="Jatuh Tempo" name="jatuh_tempo" type="date"
            :value="isset($tagihan->jatuh_tempo) ? optional($tagihan->jatuh_tempo)->format('Y-m-d') : ''" />

        {{-- Keterangan --}}
        <div class="sm:col-span-2">
            <x-form.textarea label="Keterangan (opsional)" name="keterangan"
                :value="$tagihan->keterangan ?? ''" rows="3"
                placeholder="Informasi tambahan tentang tagihan ini..." />
        </div>

    </div>

    <div class="mt-8 flex items-center gap-3 border-t border-slate-100 pt-6 dark:border-slate-800">
        <x-button type="submit" variant="primary">
            <x-icon name="check" class="h-4 w-4" />
            {{ isset($tagihan->id) ? 'Perbarui Tagihan' : 'Buat Tagihan' }}
        </x-button>
        <x-button variant="secondary" :href="route('tagihan.index')">Batal</x-button>
    </div>

</div>
