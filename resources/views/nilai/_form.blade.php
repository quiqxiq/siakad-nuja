<div class="grid grid-cols-1 gap-5 sm:grid-cols-2"
    x-data="{
        selectedKelas: '{{ old('kelas_id', $nilai->kelas_id ?? '') }}',
        selectedMapel: '{{ old('mapel_id', $nilai->mapel_id ?? '') }}',
        selectedSiswa: '{{ old('siswa_id', $nilai->siswa_id ?? '') }}',
        jadwalMapelByKelas: {{ json_encode($jadwalMapelByKelas ?? null) }},
        isMapelAvailable(mapelId) {
            if (!this.selectedKelas || !this.jadwalMapelByKelas) return true;
            const allowed = this.jadwalMapelByKelas[this.selectedKelas];
            return !allowed || allowed.includes(parseInt(mapelId));
        },
        onKelasChange() {
            if (this.selectedMapel && !this.isMapelAvailable(this.selectedMapel)) {
                this.selectedMapel = '';
            }
        }
    }">
    <x-form.select label="Kelas" name="kelas_id" x-model="selectedKelas" @change="onKelasChange()" :placeholder="false" required>
        <option value="">-- Pilih Kelas Terlebih Dahulu --</option>
        @foreach ($kelas as $k)
            <option value="{{ $k->id }}" @selected(old('kelas_id', $nilai->kelas_id ?? '') == $k->id)>{{ $k->nama_lengkap }}</option>
        @endforeach
    </x-form.select>

    <x-form.select label="Mata Pelajaran" name="mapel_id" x-model="selectedMapel" :placeholder="false" required>
        <option value="">-- Pilih Mata Pelajaran --</option>
        @foreach ($mapel as $m)
            <option value="{{ $m->id }}"
                x-show="isMapelAvailable('{{ $m->id }}')"
                @selected(old('mapel_id', $nilai->mapel_id ?? '') == $m->id)>
                {{ $m->nama_mapel }} (KKM: {{ $m->kkm ?? 75 }})
            </option>
        @endforeach
    </x-form.select>

    @php
        $siswaOptions = $siswa->map(fn($s) => [
            'id' => $s->id,
            'label' => $s->nama_lengkap,
            'sublabel' => 'NIS: ' . $s->nis . ' • ' . ($s->kelas->nama_lengkap ?? 'Tanpa Kelas'),
            'kelas_id' => $s->kelas_id,
        ])->values()->all();
    @endphp

    <div class="sm:col-span-2">
        <x-form.searchable-select
            label="Siswa"
            name="siswa_id"
            :options="$siswaOptions"
            :selected="old('siswa_id', $nilai->siswa_id ?? '')"
            placeholder="Ketik nama atau NIS untuk mencari siswa..."
            :watchKelas="true"
            required />
    </div>

    @php
        $defaultSemester = old('semester', $nilai->semester ?? App\Models\Konfigurasi::semesterAktif());
        $defaultTahunAjaran = old('tahun_ajaran', $nilai->tahun_ajaran ?? App\Models\Konfigurasi::tahunAjaranAktif());
        $daftarTA = App\Models\Konfigurasi::daftarTahunAjaran();
    @endphp

    <x-form.select label="Semester" name="semester" :selected="$defaultSemester" required :placeholder="false">
        @foreach (['Ganjil', 'Genap'] as $sem)
            <option value="{{ $sem }}" @selected($defaultSemester === $sem)>{{ $sem }}</option>
        @endforeach
    </x-form.select>

    <x-form.select label="Tahun Ajaran" name="tahun_ajaran" :selected="$defaultTahunAjaran" required :placeholder="false" hint="Otomatis disesuaikan dengan tahun ajaran aktif">
        @foreach ($daftarTA as $ta)
            <option value="{{ $ta }}" @selected($defaultTahunAjaran === $ta)>
                {{ $ta }} {{ $ta === App\Models\Konfigurasi::tahunAjaranAktif() ? '(Aktif)' : '' }}
            </option>
        @endforeach
    </x-form.select>

    <div class="sm:col-span-2">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            <x-form.input label="Nilai Harian" name="nilai_harian" type="number" step="0.01" min="0" max="100" :value="$nilai->nilai_harian ?? ''" />
            <x-form.input label="Nilai UTS" name="nilai_uts" type="number" step="0.01" min="0" max="100" :value="$nilai->nilai_uts ?? ''" />
            <x-form.input label="Nilai UAS" name="nilai_uas" type="number" step="0.01" min="0" max="100" :value="$nilai->nilai_uas ?? ''" />
        </div>
        <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">Nilai akhir &amp; predikat dihitung otomatis.</p>
    </div>
</div>

<div class="flex items-center gap-3 pt-6">
    <x-button type="submit" variant="primary"><x-icon name="check" class="h-4 w-4" /> Simpan</x-button>
    <x-button variant="secondary" :href="route('nilai.index')">Batal</x-button>
</div>
