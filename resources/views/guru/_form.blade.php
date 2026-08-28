<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <x-form.input label="Nama Lengkap" name="nama_lengkap" :value="$guru->nama_lengkap ?? ''" required />
    <x-form.input label="NIP" name="nip" :value="$guru->nip ?? ''" inputmode="numeric" pattern="[0-9]*" placeholder="contoh: 198501012010011001" required />

    <x-form.input label="Email" name="email" type="email" :value="old('email', $guru->user->email ?? '')" required />
    <x-form.input label="Password" name="password" type="password"
        :hint="isset($guru) ? 'Kosongkan bila tidak diubah' : 'Minimal 8 karakter'" />

    <x-form.input label="Jabatan" name="jabatan" :value="$guru->jabatan ?? ''" />
    <x-form.input label="No. HP" name="no_hp" type="tel" inputmode="numeric" :value="$guru->no_hp ?? ''" placeholder="08..." />
</div>

<div class="flex items-center gap-3 pt-6">
    <x-button type="submit" variant="primary"><x-icon name="check" class="h-4 w-4" /> Simpan</x-button>
    <x-button variant="secondary" :href="route('guru.index')">Batal</x-button>
</div>
