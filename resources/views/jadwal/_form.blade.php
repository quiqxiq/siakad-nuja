<div class="grid grid-cols-1 gap-5 sm:grid-cols-2"
    x-data="{
        selectedKelas: '{{ old('kelas_id', $jadwal->kelas_id ?? '') }}',
        selectedMapel: '{{ old('mapel_id', $jadwal->mapel_id ?? '') }}',
        ruangan: '{{ old('ruangan', $jadwal->ruangan ?? '') }}',
        mapelByKelas: {{ json_encode($mapelByKelas ?? []) }},
        kelasRuangan: {{ json_encode($kelas->pluck('ruangan', 'id') ?? []) }},
        init() {
            if (this.selectedKelas && !this.ruangan && this.kelasRuangan[this.selectedKelas]) {
                this.ruangan = this.kelasRuangan[this.selectedKelas];
            }
        },
        get availableMapels() {
            if (!this.selectedKelas) return [];
            return this.mapelByKelas[this.selectedKelas] || [];
        },
        onKelasChange() {
            const availableIds = this.availableMapels.map(m => String(m.id));
            if (this.selectedMapel && !availableIds.includes(String(this.selectedMapel))) {
                this.selectedMapel = '';
            }
            if (this.selectedKelas && this.kelasRuangan[this.selectedKelas]) {
                this.ruangan = this.kelasRuangan[this.selectedKelas];
            }
        }
    }">
    <x-form.select label="Kelas" name="kelas_id" x-model="selectedKelas" @change="onKelasChange()" required :placeholder="false">
        <option value="">-- Pilih Kelas Terlebih Dahulu --</option>
        @foreach ($kelas as $k)
            <option value="{{ $k->id }}" @selected(old('kelas_id', $jadwal->kelas_id ?? '') == $k->id)>{{ $k->nama_lengkap }}</option>
        @endforeach
    </x-form.select>

    <x-form.searchable-select
        label="Mata Pelajaran"
        name="mapel_id"
        :options="[]"
        optionsExpr="availableMapels"
        disabledExpr="!selectedKelas"
        placeholder="-- Pilih Mata Pelajaran --"
        disabledPlaceholder="-- Pilih Kelas Terlebih Dahulu --"
        searchPlaceholder="Ketik nama mata pelajaran..."
        emptyText="Tidak ada mata pelajaran yang cocok dengan pencarian"
        disabledHint="Pilih kelas terlebih dahulu untuk melihat daftar mata pelajaran kelas tersebut."
        required />

    @php
        $guruOptions = $guru->map(fn($g) => [
            'id' => $g->id,
            'label' => $g->nama_lengkap,
            'sublabel' => 'NIP: ' . ($g->nip ?: '-') . ($g->jabatan ? ' • ' . $g->jabatan : ''),
        ])->values()->all();
    @endphp

    <x-form.searchable-select
        label="Guru Pengampu"
        name="guru_id"
        :options="$guruOptions"
        :selected="old('guru_id', $jadwal->guru_id ?? '')"
        placeholder="— Cari & Pilih Guru Pengampu —"
        searchPlaceholder="Ketik nama atau NIP guru..."
        emptyText="Tidak ada guru yang cocok dengan pencarian"
        required />

    <x-form.select label="Hari" name="hari" :selected="old('hari', $jadwal->hari ?? '')" required>
        @foreach (['Sabtu', 'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis'] as $h)
            <option value="{{ $h }}" @selected(old('hari', $jadwal->hari ?? '') === $h)>{{ $h }}</option>
        @endforeach
    </x-form.select>

    <x-form.select label="Jam Ke-" name="jam_ke" :selected="old('jam_ke', $jadwal->jam_ke ?? '')" required :placeholder="false">
        <option value="">-- Pilih Jam Pelajaran (1 s.d 4) --</option>
        @foreach ([1 => 'Jam ke-1', 2 => 'Jam ke-2', 3 => 'Jam ke-3', 4 => 'Jam ke-4'] as $jVal => $jLbl)
            <option value="{{ $jVal }}" @selected((int) old('jam_ke', $jadwal->jam_ke ?? 0) === $jVal)>{{ $jLbl }}</option>
        @endforeach
    </x-form.select>

    <x-form.input label="Ruangan" name="ruangan" x-model="ruangan" :value="$jadwal->ruangan ?? ''" placeholder="Contoh: R-1-MI" hint="Otomatis mengikuti 1 ruangan tetap milik kelas yang dipilih." />

    <x-form.input label="Jam Mulai" name="jam_mulai" type="time"
        :value="isset($jadwal) ? \Illuminate\Support\Str::substr($jadwal->jam_mulai, 0, 5) : old('jam_mulai', '07:30')" required />

    <x-form.input label="Jam Selesai" name="jam_selesai" type="time"
        :value="isset($jadwal) ? \Illuminate\Support\Str::substr($jadwal->jam_selesai, 0, 5) : old('jam_selesai', '08:40')" required />

    @php
        $defaultTahunAjaran = old('tahun_ajaran', $jadwal->tahun_ajaran ?? App\Models\Konfigurasi::tahunAjaranAktif());
        $daftarTA = App\Models\Konfigurasi::daftarTahunAjaran();
    @endphp

    <div class="sm:col-span-2">
        <x-form.select label="Tahun Ajaran" name="tahun_ajaran" :selected="$defaultTahunAjaran" required :placeholder="false" hint="Otomatis terisi tahun ajaran aktif">
            @foreach ($daftarTA as $ta)
                <option value="{{ $ta }}" @selected($defaultTahunAjaran === $ta)>
                    {{ $ta }} {{ $ta === App\Models\Konfigurasi::tahunAjaranAktif() ? '(Aktif)' : '' }}
                </option>
            @endforeach
        </x-form.select>
    </div>
</div>

<div class="flex items-center gap-3 pt-6">
    <x-button type="submit" variant="primary"><x-icon name="check" class="h-4 w-4" /> Simpan</x-button>
    <x-button variant="secondary" :href="route('jadwal.index')">Batal</x-button>
</div>
