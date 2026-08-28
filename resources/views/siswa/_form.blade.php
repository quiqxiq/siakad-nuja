<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <x-form.input label="NIS" name="nis" :value="$siswa->nis ?? ''" required />
    <x-form.input label="Nama Lengkap" name="nama_lengkap" :value="$siswa->nama_lengkap ?? ''" required />

    <x-form.select label="Kelas" name="kelas_id" :selected="old('kelas_id', $siswa->kelas_id ?? '')" required>
        @foreach ($kelas as $k)
            <option value="{{ $k->id }}" @selected(old('kelas_id', $siswa->kelas_id ?? '') == $k->id)>{{ $k->nama_kelas }}</option>
        @endforeach
    </x-form.select>

    <x-form.input label="Tanggal Lahir" name="tanggal_lahir" type="date"
        :value="isset($siswa) ? optional($siswa->tanggal_lahir)->format('Y-m-d') : ''" />

    <x-form.select label="Jenis Kelamin" name="jenis_kelamin" :selected="old('jenis_kelamin', $siswa->jenis_kelamin ?? '')" required>
        <option value="L" @selected(old('jenis_kelamin', $siswa->jenis_kelamin ?? '') === 'L')>Laki-laki</option>
        <option value="P" @selected(old('jenis_kelamin', $siswa->jenis_kelamin ?? '') === 'P')>Perempuan</option>
    </x-form.select>

    <x-form.input label="Tahun Masuk" name="tahun_masuk" type="number" :value="$siswa->tahun_masuk ?? date('Y')" required />

    <x-form.select label="Status" name="status" :selected="old('status', $siswa->status ?? 'Aktif')" :placeholder="false">
        @foreach (['Aktif', 'Lulus', 'Pindah', 'Keluar'] as $st)
            <option value="{{ $st }}" @selected(old('status', $siswa->status ?? 'Aktif') === $st)>{{ $st }}</option>
        @endforeach
    </x-form.select>

    <div class="sm:col-span-2">
        <x-form.textarea label="Alamat" name="alamat" :value="$siswa->alamat ?? ''" rows="3" />
    </div>

    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Foto</label>
        @if (! empty($siswa->foto))
            <div class="mb-2">
                <img src="{{ asset('storage/' . $siswa->foto) }}" alt="Foto {{ $siswa->nama_lengkap }}"
                    class="h-20 w-20 rounded-lg object-cover ring-1 ring-slate-200 dark:ring-slate-700">
            </div>
        @endif
        <input type="file" name="foto" accept="image/*"
            class="block w-full text-sm text-slate-600 dark:text-slate-300 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-900/40 dark:file:text-brand-300">
        @error('foto') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
    </div>
</div>

<div class="flex items-center gap-3 pt-6">
    <x-button type="submit" variant="primary"><x-icon name="check" class="h-4 w-4" /> Simpan</x-button>
    <x-button variant="secondary" :href="route('siswa.index')">Batal</x-button>
</div>
