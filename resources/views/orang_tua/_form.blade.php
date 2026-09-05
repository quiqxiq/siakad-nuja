@php
    $siswaOptions = $siswa->map(fn($s) => [
        'id' => $s->id,
        'label' => $s->nama_lengkap,
        'sublabel' => 'NIS: ' . $s->nis . ' • ' . ($s->kelas->nama_lengkap ?? '-'),
        'kelas_id' => $s->kelas_id,
    ])->values()->all();
@endphp

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-form.searchable-select
            label="Siswa"
            name="siswa_id"
            :options="$siswaOptions"
            :selected="old('siswa_id', $orangTua->siswa_id ?? '')"
            placeholder="Ketik nama atau NIS untuk mencari siswa..."
            required />
    </div>

    <x-form.input label="Nama" name="nama" :value="$orangTua->nama ?? ''" required />

    <x-form.select label="Hubungan" name="hubungan" :selected="old('hubungan', $orangTua->hubungan ?? '')">
        @foreach (['Ayah', 'Ibu', 'Wali'] as $h)
            <option value="{{ $h }}" @selected(old('hubungan', $orangTua->hubungan ?? '') === $h)>{{ $h }}</option>
        @endforeach
    </x-form.select>

    <x-form.input label="No. HP" name="no_hp" type="tel" inputmode="numeric" :value="$orangTua->no_hp ?? ''" placeholder="08..." />

    <x-form.input label="No. WhatsApp" name="no_wa" type="tel" inputmode="numeric" :value="$orangTua->no_wa ?? ''" placeholder="08..." hint="Nomor utama pengiriman notifikasi WA" />

    <x-form.input label="Pekerjaan" name="pekerjaan" :value="$orangTua->pekerjaan ?? ''" />

    <div class="sm:col-span-2">
        <x-form.textarea label="Alamat" name="alamat" :value="$orangTua->alamat ?? ''" rows="3" />
    </div>

    <div class="sm:col-span-2">
        <x-form.checkbox label="Jadikan kontak utama" name="is_kontak_utama" :checked="old('is_kontak_utama', $orangTua->is_kontak_utama ?? false)" />
    </div>
</div>

<div class="flex items-center gap-3 pt-6">
    <x-button type="submit" variant="primary"><x-icon name="check" class="h-4 w-4" /> Simpan</x-button>
    <x-button variant="secondary" :href="route('orang-tua.index')">Batal</x-button>
</div>
