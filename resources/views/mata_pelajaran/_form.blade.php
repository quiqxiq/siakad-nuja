<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <x-form.input label="Kode Mapel" name="kode_mapel" :value="$mapel->kode_mapel ?? ''" required />
    <x-form.input label="Nama Mapel" name="nama_mapel" :value="$mapel->nama_mapel ?? ''" required />

    <x-form.select label="Jenjang" name="jenjang" :selected="old('jenjang', $mapel->jenjang ?? '')" required>
        @foreach (['MI', 'MTs'] as $j)
            <option value="{{ $j }}" @selected(old('jenjang', $mapel->jenjang ?? '') === $j)>{{ $j }}</option>
        @endforeach
    </x-form.select>

    <x-form.input label="KKM" name="kkm" type="number" :value="$mapel->kkm ?? ''" min="0" max="100" />

    <div class="sm:col-span-2">
        <x-form.textarea label="Deskripsi" name="deskripsi" :value="$mapel->deskripsi ?? ''" rows="3" />
    </div>
</div>

<div class="flex items-center gap-3 pt-6">
    <x-button type="submit" variant="primary"><x-icon name="check" class="h-4 w-4" /> Simpan</x-button>
    <x-button variant="secondary" :href="route('mata-pelajaran.index')">Batal</x-button>
</div>
