<div class="grid grid-cols-1 gap-5 sm:grid-cols-2"
    x-data="{
        selectedKelas: '{{ old('kelas_id', $nilai->kelas_id ?? '') }}',
        selectedSiswa: '{{ old('siswa_id', $nilai->siswa_id ?? '') }}'
    }">
    <x-form.select label="Kelas" name="kelas_id" x-model="selectedKelas" required>
        <option value="">-- Pilih Kelas Terlebih Dahulu --</option>
        @foreach ($kelas as $k)
            <option value="{{ $k->id }}" @selected(old('kelas_id', $nilai->kelas_id ?? '') == $k->id)>{{ $k->nama_lengkap }}</option>
        @endforeach
    </x-form.select>

    <x-form.select label="Mata Pelajaran" name="mapel_id" :selected="old('mapel_id', $nilai->mapel_id ?? '')" required>
        <option value="">-- Pilih Mata Pelajaran --</option>
        @foreach ($mapel as $m)
            <option value="{{ $m->id }}" @selected(old('mapel_id', $nilai->mapel_id ?? '') == $m->id)>{{ $m->nama_mapel }} (KKM: {{ $m->kkm ?? 75 }})</option>
        @endforeach
    </x-form.select>

    <div class="sm:col-span-2">
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Siswa <span class="text-rose-500">*</span></label>
        <select name="siswa_id" x-model="selectedSiswa" required
            class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
            <option value="">-- Pilih Siswa --</option>
            @foreach ($siswa as $s)
                <option value="{{ $s->id }}"
                    x-show="!selectedKelas || selectedKelas == '{{ $s->kelas_id }}'"
                    @selected(old('siswa_id', $nilai->siswa_id ?? '') == $s->id)>
                    {{ $s->nama_lengkap }} (NIS: {{ $s->nis }}) — {{ $s->kelas->nama_lengkap ?? 'Tanpa Kelas' }}
                </option>
            @endforeach
        </select>
        @error('siswa_id')
            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <x-form.select label="Semester" name="semester" :selected="old('semester', $nilai->semester ?? '')" required>
        @foreach (['Ganjil', 'Genap'] as $sem)
            <option value="{{ $sem }}" @selected(old('semester', $nilai->semester ?? '') === $sem)>{{ $sem }}</option>
        @endforeach
    </x-form.select>

    <x-form.input label="Tahun Ajaran" name="tahun_ajaran" :value="$nilai->tahun_ajaran ?? ''" placeholder="2024/2025" required />

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
