<div x-data="{ tipe: '{{ old('tipe_action', $rule->tipe_action ?? 'static_text') }}' }" class="space-y-6">
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

        {{-- Keyword --}}
        <x-form.input label="Kata Kunci / Keyword" name="keyword" :value="$rule->keyword ?? ''" required
            placeholder="contoh: 6 atau ESKUL" hint="Angka atau kata kunci tunggal yang diketik oleh pengguna WA." />

        {{-- Judul Menu --}}
        <x-form.input label="Judul Menu (tampilan di WA)" name="judul_menu" :value="$rule->judul_menu ?? ''" required
            placeholder="contoh: 🎨 Info Ekstrakurikuler" hint="Label menu yang akan ditampilkan pada daftar menu utama." />

        {{-- Urutan Tampilan --}}
        <x-form.input label="Urutan Nomor Menu" name="urutan" type="number" min="0" step="1" :value="$rule->urutan ?? 0" required
            hint="Menentukan urutan tampilan daftar menu (0, 1, 2, dst)." />

        {{-- Tipe Action --}}
        <x-form.select label="Tipe Aksi / Respon" name="tipe_action" required :placeholder="false" x-model="tipe">
            <option value="static_text" @selected(old('tipe_action', $rule->tipe_action ?? 'static_text') === 'static_text')>Pesan Teks Bebas (Static Text)</option>
            <option value="system_query" @selected(old('tipe_action', $rule->tipe_action ?? 'static_text') === 'system_query')>Query Data Sistem (System Query)</option>
        </x-form.select>

        {{-- Dynamic Action Key (hanya bila system_query) --}}
        <div x-show="tipe === 'system_query'" class="sm:col-span-2">
            <x-form.select label="Fungsi Query System Bawaan" name="action_key" :selected="old('action_key', $rule->action_key ?? '')" x-bind:disabled="tipe !== 'system_query'">
                <option value="info_nilai" @selected(old('action_key', $rule->action_key ?? '') === 'info_nilai')>📊 Info Nilai Rapor (getNilai)</option>
                <option value="info_kehadiran" @selected(old('action_key', $rule->action_key ?? '') === 'info_kehadiran')>📋 Info Rekap Kehadiran (getKehadiran)</option>
                <option value="info_tagihan" @selected(old('action_key', $rule->action_key ?? '') === 'info_tagihan')>💳 Info Tagihan & Pembayaran (getTagihan)</option>
                <option value="info_agenda" @selected(old('action_key', $rule->action_key ?? '') === 'info_agenda')>📢 Info Agenda Sekolah (getAgenda)</option>
                <option value="cs_contact" @selected(old('action_key', $rule->action_key ?? '') === 'cs_contact')>📞 Hubungi Customer Service (getCsInfo)</option>
            </x-form.select>
        </div>

        {{-- Custom Response Text (hanya bila static_text) --}}
        <div x-show="tipe === 'static_text'" class="sm:col-span-2">
            <x-form.textarea label="Isi Balasan Pesan" name="isi_balasan" :value="$rule->isi_balasan ?? ''" rows="5"
                placeholder="Tuliskan teks pesan balasan otomatis di sini..."
                hint="Gunakan {nama_wali}, {nama_siswa}, dan {kelas} sebagai variabel dinamis."
                x-bind:disabled="tipe !== 'static_text'" />
        </div>

    </div>

    <div>
        <x-form.checkbox label="Rule Aktif" name="is_active" :checked="old('is_active', $rule->is_active ?? true)"
            hint="Rule nonaktif tidak akan ditampilkan di daftar menu dan tidak membalas pesan." />
    </div>

    <div class="flex items-center gap-3 pt-2">
        <x-button type="submit" variant="primary"><x-icon name="check" class="h-4 w-4" /> Simpan Rule</x-button>
        <x-button variant="secondary" :href="route('whatsapp.chatbot-rules')">Batal</x-button>
    </div>
</div>
