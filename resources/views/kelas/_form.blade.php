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

    @php
        $guruOptions = $guru->map(fn($g) => [
            'id' => $g->id,
            'label' => $g->nama_lengkap,
            'sublabel' => 'NIP: ' . ($g->nip ?: '-') . ($g->jabatan ? ' • ' . $g->jabatan : ''),
        ])->values()->all();
    @endphp

    <x-form.searchable-select
        label="Wali Kelas"
        name="wali_kelas_id"
        :options="$guruOptions"
        :selected="old('wali_kelas_id', $kelas->wali_kelas_id ?? '')"
        placeholder="— Cari & Pilih Wali Kelas —"
        searchPlaceholder="Ketik nama atau NIP guru..."
        emptyText="Tidak ada guru yang cocok dengan pencarian" />

    <x-form.input label="Kapasitas" name="kapasitas" type="number" min="1" max="255" step="1" placeholder="Contoh: 30" :value="$kelas->kapasitas ?? ''" onkeydown="if(['e','E','+','-','.'].includes(event.key)) event.preventDefault()" />

    <x-form.input label="Ruangan Kelas" name="ruangan" :value="$kelas->ruangan ?? ''" placeholder="Contoh: R-1-MI" hint="1 ruangan khusus untuk 1 kelas (otomatis terisi jika dikosongkan)." />
</div>

<div class="flex items-center gap-3 pt-6">
    <x-button type="submit" variant="primary"><x-icon name="check" class="h-4 w-4" /> Simpan</x-button>
    <x-button variant="secondary" :href="route('kelas.index')">Batal</x-button>
</div>
