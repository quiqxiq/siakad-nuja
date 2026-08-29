{{-- Footer — Premium dark/light portfolio footer --}}
<footer class="border-t border-slate-200/80 bg-white dark:border-white/5 dark:bg-slate-950 transition-colors duration-300">
    <div class="mx-auto max-w-7xl px-5 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-12 md:grid-cols-2 lg:grid-cols-4">

            {{-- Brand --}}
            <div class="lg:col-span-2">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center transition hover:scale-105">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo Yayasan Nurul Jadid Karduluk YANUJA" class="h-full w-full object-contain filter drop-shadow-[0_2px_8px_rgba(0,140,227,0.45)]">
                    </div>
                    <div class="leading-tight">
                        <div class="text-sm font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-1.5">
                            <span>SIAKAD NUJA</span>
                            <span class="rounded bg-sky-500/15 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 px-1.5 py-0.5 text-[9px] font-bold border border-sky-500/30">YANUJA</span>
                        </div>
                        <div class="text-[10px] uppercase tracking-[0.18em] font-semibold text-slate-600 dark:text-slate-300">Nurul Jadid · Karduluk Sumenep</div>
                    </div>
                </div>
                <p class="mt-5 max-w-sm text-sm leading-relaxed text-slate-600 dark:text-slate-500">
                    Sistem Informasi Akademik terpadu Yayasan Nurul Jadid Karduluk (RA, MIS & MTs Nurul Jadid) terintegrasi Notifikasi WhatsApp.
                </p>
                <div class="mt-6">
                    <div class="text-xs text-slate-700 dark:text-slate-400 font-semibold mb-1 flex items-center gap-1">
                        <span>📍 Alamat Sekretariat & Madrasah</span>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        Jl. K. Syafi'ie Itsbat No. 01, Desa Karduluk,<br>
                        Kec. Pragaan, Kab. Sumenep, Jawa Timur 69465
                    </p>
                </div>
            </div>

            {{-- Navigasi --}}
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-4">Navigasi</h4>
                <ul class="space-y-3 text-sm">
                    @foreach ([
                        '#beranda'   => 'Beranda',
                        '#tentang'   => 'Tentang Yayasan',
                        '#galeri'    => 'Galeri & Kegiatan',
                        '#statistik' => 'Pencapaian',
                    ] as $href => $label)
                        <li>
                            <a href="{{ $href }}"
                                class="text-slate-600 transition-colors hover:text-brand-600 dark:text-slate-400 dark:hover:text-white flex items-center gap-2 group">
                                <span class="h-px w-4 bg-slate-300 dark:bg-slate-700 transition-all group-hover:w-6 group-hover:bg-brand-500"></span>
                                {{ $label }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Lembaga Terdaftar --}}
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-4">Lembaga Terdaftar</h4>
                <div class="space-y-2.5">
                    <div class="rounded-xl border border-slate-200/80 bg-slate-50 dark:border-white/5 dark:bg-white/5 p-3">
                        <div class="font-bold text-slate-900 dark:text-white text-xs">RA Nurul Jadid</div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">NPSN: 69749559 (Raudhatul Athfal)</div>
                    </div>
                    <div class="rounded-xl border border-slate-200/80 bg-slate-50 dark:border-white/5 dark:bg-white/5 p-3">
                        <div class="font-bold text-slate-900 dark:text-white text-xs">MIS Nurul Jadid</div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">NPSN: 60720605 (Madrasah Ibtidaiyah)</div>
                    </div>
                    <div class="rounded-xl border border-slate-200/80 bg-slate-50 dark:border-white/5 dark:bg-white/5 p-3">
                        <div class="font-bold text-slate-900 dark:text-white text-xs">MTs Nurul Jadid</div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">Madrasah Tsanawiyah (Kelas 7, 8 & 9)</div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Bottom bar --}}
        <div class="mt-14 flex flex-col items-center justify-between gap-3 border-t border-slate-200/80 dark:border-white/5 pt-8 text-xs text-slate-500 dark:text-slate-600 sm:flex-row">
            <p>&copy; {{ date('Y') }} SIAKAD NUJA — Yayasan Nurul Jadid Karduluk. Seluruh hak cipta dilindungi.</p>
            <p class="flex items-center gap-1">
                Dibuat dengan <span class="text-rose-500">♥</span> untuk kemajuan pendidikan Islam Karduluk Pragaan Sumenep.
            </p>
        </div>
    </div>
</footer>
