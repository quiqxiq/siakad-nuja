{{-- Fitur unggulan — Bento Grid asimetris --}}
<section id="fitur" class="relative py-24 sm:py-32">
    <div class="absolute inset-0 bg-gradient-to-b from-slate-50 via-slate-100/70 to-slate-50 dark:from-slate-950 dark:via-slate-900/80 dark:to-slate-950 -z-10 transition-colors duration-300"></div>

    <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="lp-reveal mx-auto max-w-3xl text-center mb-16">
            <span class="inline-block rounded-full bg-brand-500/15 px-4 py-1.5 text-xs font-bold uppercase tracking-widest text-brand-600 dark:text-brand-400 mb-5">
                Fitur Sistem
            </span>
            <h2 class="text-4xl font-black leading-tight tracking-tight text-slate-900 dark:text-white sm:text-5xl">
                Satu platform,<br>
                <span class="lp-text-gradient">semua yang dibutuhkan sekolah.</span>
            </h2>
            <p class="mt-5 text-lg text-slate-600 dark:text-slate-400">
                Bukan kumpulan fitur asal-asalan — setiap modul dirancang dengan alur kerja nyata guru, admin, dan orang tua.
            </p>
        </div>

        {{-- Bento Grid --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 lg:grid-rows-2">

            {{-- Featured: Nilai (besar, span 2 col) --}}
            <div class="lp-reveal group relative overflow-hidden rounded-3xl border border-brand-500/20 bg-white dark:bg-gradient-to-br dark:from-brand-950/60 dark:via-slate-900 dark:to-slate-900 shadow-xl dark:shadow-2xl p-7 lg:row-span-2 lg:col-span-1 flex flex-col justify-between"
                 style="min-height:380px">
                <div class="absolute -right-10 -top-10 h-48 w-48 rounded-full bg-brand-500/10 blur-3xl pointer-events-none group-hover:bg-brand-500/20 transition duration-700"></div>
                <div>
                    <div class="mb-5 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-500/15 dark:bg-brand-500/20 text-brand-600 dark:text-brand-300 transition-transform group-hover:scale-110 group-hover:-rotate-6">
                        <x-icon name="nilai" class="h-7 w-7" />
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Penilaian Otomatis & Cerdas</h3>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                        Input nilai harian, UTS, dan UAS — nilai akhir dan predikat dihitung otomatis sesuai bobot KKM tiap mata pelajaran. Ekspor ke PDF atau Excel kapan saja.
                    </p>
                </div>
                <div class="mt-6 space-y-2">
                    @foreach (['Hitung otomatis nilai akhir + predikat', 'Bobot komponen dapat dikustomisasi', 'Notifikasi WA otomatis ke orang tua'] as $f)
                        <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                            <div class="h-1.5 w-1.5 rounded-full bg-brand-500 shrink-0"></div>
                            {{ $f }}
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Absensi --}}
            <div class="lp-reveal group relative overflow-hidden rounded-3xl border border-emerald-500/20 bg-white dark:bg-gradient-to-br dark:from-emerald-950/40 dark:to-slate-900 shadow-xl dark:shadow-2xl p-6" style="--lp-delay:80ms">
                <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-emerald-500/10 blur-2xl pointer-events-none"></div>
                <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500/15 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-300 transition-transform group-hover:scale-110 group-hover:-rotate-6">
                    <x-icon name="absensi" class="h-6 w-6" />
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Absensi Massal Kilat</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">Tandai kehadiran seluruh kelas dalam hitungan detik. Status absen → notifikasi WhatsApp otomatis ke orang tua.</p>
            </div>

            {{-- Tagihan --}}
            <div class="lp-reveal group relative overflow-hidden rounded-3xl border border-violet-500/20 bg-white dark:bg-gradient-to-br dark:from-violet-950/40 dark:to-slate-900 shadow-xl dark:shadow-2xl p-6" style="--lp-delay:160ms">
                <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-violet-500/10 blur-2xl pointer-events-none"></div>
                <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-500/15 dark:bg-violet-500/20 text-violet-600 dark:text-violet-300 transition-transform group-hover:scale-110 group-hover:-rotate-6">
                    <x-icon name="tagihan" class="h-6 w-6" />
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">SPP & Verifikasi Pembayaran</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">Tagihan massal, upload bukti transfer, verifikasi admin — semua dalam satu alur yang bersih dan transparan.</p>
            </div>

            {{-- Jadwal --}}
            <div class="lp-reveal group relative overflow-hidden rounded-3xl border border-amber-500/20 bg-white dark:bg-gradient-to-br dark:from-amber-950/30 dark:to-slate-900 shadow-xl dark:shadow-2xl p-6" style="--lp-delay:240ms">
                <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-amber-500/10 blur-2xl pointer-events-none"></div>
                <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-500/15 dark:bg-amber-500/20 text-amber-600 dark:text-amber-300 transition-transform group-hover:scale-110 group-hover:-rotate-6">
                    <x-icon name="jadwal" class="h-6 w-6" />
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Jadwal Tanpa Bentrok</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">Susun jadwal per kelas dengan deteksi konflik guru & ruangan. Tampil jelas di dasbor setiap pengguna.</p>
            </div>

            {{-- WhatsApp & Pengumuman --}}
            <div class="lp-reveal group relative overflow-hidden rounded-3xl border border-sky-500/20 bg-white dark:bg-gradient-to-br dark:from-sky-950/40 dark:to-slate-900 shadow-xl dark:shadow-2xl p-6" style="--lp-delay:320ms">
                <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-sky-500/10 blur-2xl pointer-events-none"></div>
                <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-500/15 dark:bg-sky-500/20 text-sky-600 dark:text-sky-300 transition-transform group-hover:scale-110 group-hover:-rotate-6">
                    <x-icon name="pengumuman" class="h-6 w-6" />
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Notifikasi & Pengumuman</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">Broadcast pengumuman ke seluruh wali kelas via WhatsApp. Riwayat notifikasi tersimpan rapi untuk audit.</p>
            </div>

        </div>
    </div>
</section>
