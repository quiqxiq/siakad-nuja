<div x-data="{ role: '{{ old('role', $user->role ?? 'guru') }}' }" class="space-y-6">
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <x-form.input label="Nama Lengkap" name="nama" :value="$user->nama ?? ''" required />
        <x-form.input label="Email" name="email" type="email" :value="$user->email ?? ''" required />

        <x-form.select label="Peran" name="role" required :placeholder="false" x-model="role">
            <option value="admin" @selected(old('role', $user->role ?? 'guru') === 'admin')>Administrator</option>
            <option value="guru" @selected(old('role', $user->role ?? 'guru') === 'guru')>Guru</option>
        </x-form.select>

        <x-form.input label="Password" name="password" type="password" autocomplete="new-password"
            hint="{{ isset($user) ? 'Kosongkan bila tidak diubah' : 'Minimal 8 karakter' }}" />

        <x-form.input label="Nomor HP / WhatsApp" name="no_hp" type="tel" inputmode="numeric" :value="$user->no_hp ?? ''" placeholder="08..." hint="Nomor WhatsApp untuk penerimaan kredensial akun" />

        {{-- Field khusus guru --}}
        <template x-if="role === 'guru'">
            <div class="contents">
                <x-form.input label="NIP" name="nip" inputmode="numeric" pattern="[0-9]*" :value="$user->guru->nip ?? ''" hint="Wajib diisi untuk guru." />
                <x-form.input label="Jabatan" name="jabatan" :value="$user->guru->jabatan ?? ''" />
            </div>
        </template>
    </div>

    <div class="space-y-3">
        <x-form.checkbox label="Akun aktif" name="is_active" :checked="old('is_active', $user->is_active ?? true)"
            hint="Akun nonaktif tidak dapat login." />

        <x-form.checkbox label="Kirim info & kredensial akun via WhatsApp" name="kirim_wa" :checked="old('kirim_wa', true)"
            hint="Kredensial (Email & Password) akan dikirimkan secara otomatis ke nomor WhatsApp di atas." />
    </div>

    <div class="flex items-center gap-3 pt-2">
        <x-button type="submit" variant="primary"><x-icon name="check" class="h-4 w-4" /> Simpan</x-button>
        <x-button variant="secondary" :href="route('users.index')">Batal</x-button>
    </div>
</div>
