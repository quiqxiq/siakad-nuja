{{-- Galeri — foto kegiatan & mockup UI showcase --}}
<section id="galeri" class="relative overflow-hidden py-24 sm:py-32">
    <div class="absolute inset-0 bg-gradient-to-b from-slate-50 via-slate-100/70 to-slate-50 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 -z-10 transition-colors duration-300"></div>

    <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
        <div class="lp-reveal mx-auto max-w-2xl text-center mb-16">
            <span class="inline-block rounded-full bg-emerald-500/15 px-4 py-1.5 text-xs font-bold uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-5">
                Galeri & Kegiatan
            </span>
            <h2 class="text-4xl font-black leading-tight tracking-tight text-slate-900 dark:text-white sm:text-5xl">
                Suasana Pendidikan &<br>
                <span class="lp-text-gradient">Ekosistem Digital NUJA</span>
            </h2>
            <p class="mt-5 text-slate-600 dark:text-slate-400">Dokumentasi kegiatan santri/siswa Yayasan Nurul Jadid Karduluk Sumenep serta antarmuka SIAKAD modern.</p>
        </div>

        {{-- Section 1: Photos of Nurul Jadid Karduluk --}}
        <div class="mb-16 grid gap-6 sm:grid-cols-3">

            {{-- Photo 1: KBM Digital --}}
            <div class="lp-reveal group relative overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-xl dark:border-white/10 dark:bg-slate-900 dark:shadow-2xl transition hover:border-emerald-500/50" style="--lp-delay:0ms">
                <div class="relative h-60 w-full overflow-hidden">
                    <img src="{{ asset('images/gallery-kbm-digital.png') }}" alt="KBM Digital Nurul Jadid Karduluk" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/20 to-transparent"></div>
                    <span class="absolute top-3 right-3 rounded-full bg-emerald-500/20 border border-emerald-500/40 px-3 py-1 text-[10px] font-bold text-emerald-300 backdrop-blur-md">KBM Digital</span>
                </div>
                <div class="p-5">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Pembelajaran Interaktif</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 leading-relaxed">Siswa/Santri berinteraksi dengan kurikulum modern berbasis peranti digital di kelas.</p>
                </div>
            </div>

            {{-- Photo 2: Pramuka / Ekstrakurikuler --}}
            <div class="lp-reveal group relative overflow-hidden rounded-3xl border border-sky-500/30 bg-white shadow-xl dark:border-sky-500/20 dark:bg-slate-900 dark:shadow-2xl transition hover:border-sky-500/60" style="--lp-delay:120ms">
                <div class="relative h-64 w-full overflow-hidden">
                    <img src="{{ asset('images/gallery-pramuka.png') }}" alt="Giat Pramuka YANUJA Karduluk Sumenep" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/20 to-transparent"></div>
                    <span class="absolute top-3 right-3 rounded-full bg-sky-500/20 border border-sky-500/40 px-3 py-1 text-[10px] font-bold text-sky-300 backdrop-blur-md">Pramuka YANUJA</span>
                </div>
                <div class="p-5">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Giat Pramuka & Prestasi Juara</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 leading-relaxed">Dokumentasi regu Pramuka Yayasan Nurul Jadid (YANUJA) Karduluk Pragaan Sumenep saat meraih tropi juara.</p>
                </div>
            </div>

            {{-- Photo 3: Tahfidz & Kitab --}}
            <div class="lp-reveal group relative overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-xl dark:border-white/10 dark:bg-slate-900 dark:shadow-2xl transition hover:border-amber-500/50" style="--lp-delay:240ms">
                <div class="relative h-60 w-full overflow-hidden">
                    <img src="{{ asset('images/gallery-tahfidz.png') }}" alt="Tahfidz & Kitab Kuning" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/20 to-transparent"></div>
                    <span class="absolute top-3 right-3 rounded-full bg-amber-500/20 border border-amber-500/40 px-3 py-1 text-[10px] font-bold text-amber-300 backdrop-blur-md">Tahfidz & Kitab</span>
                </div>
                <div class="p-5">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Kajian Al-Qur'an & Kitab Kuning</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 leading-relaxed">Pendalaman ilmu agama Islam berlandaskan ajaran Ahlussunnah wal Jama'ah.</p>
                </div>
            </div>

        </div>

    </div>
</section>
