<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <x-form.select label="Kelas" name="kelas_id" :selected="old('kelas_id', $jadwal->kelas_id ?? '')" required>
        @foreach ($kelas as $k)
            <option value="{{ $k->id }}" @selected(old('kelas_id', $jadwal->kelas_id ?? '') == $k->id)>{{ $k->nama_lengkap }}</option>
        @endforeach
    </x-form.select>

    <x-form.select label="Mata Pelajaran" name="mapel_id" :selected="old('mapel_id', $jadwal->mapel_id ?? '')" required>
        @foreach ($mapel as $m)
            <option value="{{ $m->id }}" @selected(old('mapel_id', $jadwal->mapel_id ?? '') == $m->id)>{{ $m->nama_mapel }}</option>
        @endforeach
    </x-form.select>

    <x-form.select label="Guru" name="guru_id" :selected="old('guru_id', $jadwal->guru_id ?? '')" required>
        @foreach ($guru as $g)
            <option value="{{ $g->id }}" @selected(old('guru_id', $jadwal->guru_id ?? '') == $g->id)>{{ $g->nama_lengkap }}</option>
        @endforeach
    </x-form.select>

    <x-form.select label="Hari" name="hari" :selected="old('hari', $jadwal->hari ?? '')" required>
        @foreach (['Sabtu', 'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis'] as $h)
            <option value="{{ $h }}" @selected(old('hari', $jadwal->hari ?? '') === $h)>{{ $h }}</option>
        @endforeach
    </x-form.select>

    <x-form.input label="Jam Ke-" name="jam_ke" type="number" min="1" max="15" :value="$jadwal->jam_ke ?? ''" required />

    <x-form.input label="Ruangan" name="ruangan" :value="$jadwal->ruangan ?? ''" />

    <x-form.input label="Jam Mulai" name="jam_mulai" type="time"
        :value="isset($jadwal) ? \Illuminate\Support\Str::substr($jadwal->jam_mulai, 0, 5) : ''" required />

    <x-form.input label="Jam Selesai" name="jam_selesai" type="time"
        :value="isset($jadwal) ? \Illuminate\Support\Str::substr($jadwal->jam_selesai, 0, 5) : ''" required />

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
