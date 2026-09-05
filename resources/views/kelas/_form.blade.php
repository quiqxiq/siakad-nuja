<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <x-form.input label="Nama Kelas" name="nama_kelas" :value="$kelas->nama_kelas ?? ''" required />

    <x-form.input label="Tingkat" name="tingkat" :value="$kelas->tingkat ?? ''" required hint="Mis. X / XI / XII" />

    <x-form.select label="Jenjang" name="jenjang" :selected="old('jenjang', $kelas->jenjang ?? '')" required>
        @foreach (['MI', 'MTs'] as $j)
            <option value="{{ $j }}" @selected(old('jenjang', $kelas->jenjang ?? '') === $j)>{{ $j }}</option>
        @endforeach
    </x-form.select>

    @php
        $defaultTahunAjaran = old('tahun_ajaran', $kelas->tahun_ajaran ?? App\Models\Konfigurasi::tahunAjaranAktif());
        $daftarTA = App\Models\Konfigurasi::daftarTahunAjaran();
    @endphp

    <x-form.select label="Tahun Ajaran" name="tahun_ajaran" :selected="$defaultTahunAjaran" required :placeholder="false" hint="Otomatis terisi tahun ajaran aktif">
        @foreach ($daftarTA as $ta)
            <option value="{{ $ta }}" @selected($defaultTahunAjaran === $ta)>
                {{ $ta }} {{ $ta === App\Models\Konfigurasi::tahunAjaranAktif() ? '(Aktif)' : '' }}
            </option>
        @endforeach
    </x-form.select>

    <x-form.select label="Wali Kelas" name="wali_kelas_id" :selected="old('wali_kelas_id', $kelas->wali_kelas_id ?? '')">
        @foreach ($guru as $g)
            <option value="{{ $g->id }}" @selected(old('wali_kelas_id', $kelas->wali_kelas_id ?? '') == $g->id)>{{ $g->nama_lengkap }}</option>
        @endforeach
    </x-form.select>

    <x-form.input label="Kapasitas" name="kapasitas" type="number" :value="$kelas->kapasitas ?? ''" />
</div>

<div class="flex items-center gap-3 pt-6">
    <x-button type="submit" variant="primary"><x-icon name="check" class="h-4 w-4" /> Simpan</x-button>
    <x-button variant="secondary" :href="route('kelas.index')">Batal</x-button>
</div>
