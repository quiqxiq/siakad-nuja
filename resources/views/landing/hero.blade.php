{{-- HERO — Full-screen immersive portfolio opening --}}
<section id="beranda" class="relative min-h-screen overflow-hidden flex flex-col items-center justify-center pt-20 pb-16">

    {{-- Animated mesh background --}}
    <div class="pointer-events-none absolute inset-0 -z-20">
        {{-- Base dark --}}
        <div class="absolute inset-0 bg-slate-950"></div>
        {{-- Gradient orbs --}}
        <div class="absolute -top-32 -left-32 h-[600px] w-[600px] rounded-full bg-brand-600/20 blur-[140px] animate-pulse" style="animation-duration:8s"></div>
        <div class="absolute top-1/2 -right-48 h-[500px] w-[500px] rounded-full bg-violet-600/15 blur-[120px] animate-pulse" style="animation-duration:10s;animation-delay:3s"></div>
        <div class="absolute -bottom-24 left-1/3 h-[400px] w-[400px] rounded-full bg-sky-500/15 blur-[100px] animate-pulse" style="animation-duration:12s;animation-delay:6s"></div>
    </div>

    {{-- Grid overlay --}}
    <div class="pointer-events-none absolute inset-0 -z-10 lp-bg-grid opacity-30"></div>

    {{-- Noise texture --}}
    <div class="pointer-events-none absolute inset-0 -z-10 opacity-[0.03]"
         style="background-image: url(\"data:image/svg+xml,%3Csvg viewBox='0 0 512 512' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E\")"></div>

    <div class="relative mx-auto max-w-7xl px-5 sm:px-6 lg:px-8 w-full">
        <div class="grid items-center gap-16 lg:grid-cols-2">

            {{-- Teks --}}
            <div class="text-center lg:text-left">

                {{-- Badge --}}
                <div class="lp-pop inline-flex items-center gap-2.5 rounded-full border border-brand-500/30 bg-brand-500/10 px-4 py-2 text-xs font-semibold tracking-wide text-brand-300 backdrop-blur-sm mb-8">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-brand-400 opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-brand-400"></span>
                    </span>
                    Sistem Informasi Akademik · Yayasan Nurul Jadid Karduluk
                </div>

                {{-- Headline --}}
                <h1 class="text-5xl font-black leading-[1.0] tracking-tighter text-white sm:text-6xl xl:text-7xl">
                    Pendidikan<br>
                    <span class="relative">
                        <span class="lp-text-gradient">Unggul & Islami,</span>
                    </span><br>
                    <span class="text-slate-300 font-light italic">Nurul Jadid Karduluk.</span>
                </h1>

                <p class="mx-auto mt-7 max-w-xl text-base leading-relaxed text-slate-400 lg:mx-0 sm:text-lg">
                    SIAKAD NUJA adalah ekosistem digital terpadu <span class="text-white font-semibold">Yayasan Nurul Jadid Karduluk, Sumenep</span> yang menyatukan
                    <span class="text-slate-200 font-medium">data siswa, nilai, absensi, jadwal, dan SPP</span>
                    dalam satu platform modern dengan notifikasi WhatsApp otomatis bagi orang tua.
                </p>

                <div class="mt-10 flex flex-col items-center gap-4 sm:flex-row lg:items-start lg:justify-start justify-center">
                    <a href="{{ auth()->check() ? route('dashboard') : route('login') }}"
                       class="group relative inline-flex items-center gap-2.5 overflow-hidden rounded-2xl bg-brand-600 px-8 py-4 text-base font-bold text-white shadow-2xl shadow-brand-600/40 transition hover:bg-brand-500 hover:shadow-brand-600/60 w-full sm:w-auto justify-center">
                        {{ auth()->check() ? 'Buka Dashboard' : 'Akses Sistem' }}
                        <x-icon name="logout" class="h-5 w-5 transition-transform group-hover:translate-x-1" />
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/15 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
                    </a>
                    <a href="#tentang"
                       class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-8 py-4 text-base font-semibold text-slate-300 backdrop-blur transition hover:bg-white/10 hover:text-white hover:border-white/20 w-full sm:w-auto justify-center">
                        Pelajari Lebih
                        <x-icon name="chevron-down" class="h-5 w-5 opacity-60" />
                    </a>
                </div>

                {{-- Trust badges --}}
                <div class="mt-10 flex flex-wrap items-center justify-center gap-6 lg:justify-start">
                    @foreach ([
                        ['check', 'RA · MIS · MTs Nurul Jadid Karduluk'],
                        ['moon', 'Notifikasi WhatsApp Wali'],
                        ['users', 'Integrasi Sistem Terpusat'],
                    ] as [$ic, $txt])
                        <div class="flex items-center gap-2 text-sm text-slate-400">
                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-400">
                                <x-icon name="{{ $ic }}" class="h-3.5 w-3.5" />
                            </div>
                            {{ $txt }}
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Visual: Campus Building & Dashboard preview --}}
            <div class="relative lg:pl-4">
                <div class="lp-float relative mx-auto max-w-lg">

                    {{-- Card visual gedung pesantren --}}
                    <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-slate-900/90 p-3 shadow-2xl backdrop-blur-2xl ring-1 ring-white/5 mb-4">
                        <div class="relative h-48 w-full overflow-hidden rounded-2xl border border-white/10">
                            <img src="{{ asset('images/hero-building.png') }}" alt="Gedung Nurul Jadid Karduluk" class="h-full w-full object-cover transition-transform duration-700 hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/20 to-transparent"></div>
                            <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <img src="{{ asset('images/logo.png') }}" alt="Logo YANUJA" class="h-9 w-9 object-contain filter drop-shadow-[0_2px_6px_rgba(0,140,227,0.5)]">
                                    <div>
                                        <div class="text-xs font-bold text-white leading-tight flex items-center gap-1">
                                            <span>Yayasan Nurul Jadid Karduluk</span>
                                            <span class="text-[9px] text-sky-400 font-extrabold">(YANUJA)</span>
                                        </div>
                                        <div class="text-[10px] text-slate-300">Jl. K. Syafi'ie Itsbat No. 01, Sumenep</div>
                                    </div>
                                </div>
                                <span class="rounded-full bg-emerald-500/20 border border-emerald-500/40 px-2 py-0.5 text-[10px] font-semibold text-emerald-300">Aktif</span>
                            </div>
                        </div>
                    </div>

                    {{-- Main dashboard card --}}
                    <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-slate-900/90 p-5 shadow-2xl backdrop-blur-2xl ring-1 ring-white/5">

                        {{-- Window controls --}}
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <div class="flex gap-1.5">
                                    <div class="h-3 w-3 rounded-full bg-rose-500"></div>
                                    <div class="h-3 w-3 rounded-full bg-amber-500"></div>
                                    <div class="h-3 w-3 rounded-full bg-emerald-500"></div>
                                </div>
                                <span class="ml-2 text-xs font-medium text-slate-400">siakad-nuja.sch.id · Dashboard</span>
                            </div>
                            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-600 text-white">
                                <x-icon name="dashboard" class="h-3.5 w-3.5" />
                            </div>
                        </div>

                        {{-- Stats grid --}}
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            @php
                                $mockStats = [
                                    ['Siswa Aktif', '168+', 'siswa', 'brand'],
                                    ['Tenaga Pengajar', '24', 'guru', 'emerald'],
                                    ['Rombongan Belajar', '8', 'kelas', 'amber'],
                                    ['SPP Terverifikasi', '94%', 'tagihan', 'sky'],
                                ];
                                $statBg = ['brand' => 'bg-brand-500/20 text-brand-300', 'emerald' => 'bg-emerald-500/20 text-emerald-300', 'amber' => 'bg-amber-500/20 text-amber-300', 'sky' => 'bg-sky-500/20 text-sky-300'];
                            @endphp
                            @foreach ($mockStats as [$label, $val, $ic, $col])
                                <div class="rounded-2xl border border-white/5 bg-white/5 p-3 backdrop-blur">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <div class="flex h-6 w-6 items-center justify-center rounded-lg {{ $statBg[$col] }}">
                                            <x-icon name="{{ $ic }}" class="h-3.5 w-3.5" />
                                        </div>
                                    </div>
                                    <div class="text-lg font-black text-white">{{ $val }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $label }}</div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Chart bars --}}
                        <div class="rounded-2xl border border-white/5 bg-white/5 p-3.5">
                            <div class="flex items-center justify-between text-xs text-slate-400 mb-2">
                                <span class="font-medium">Kehadiran Santri/Siswa</span>
                                <span class="font-bold text-emerald-400">95.8%</span>
                            </div>
                            <div class="flex h-16 items-end gap-1.5">
                                @foreach ([75, 88, 82, 95, 90, 100, 92] as $h)
                                    <div class="flex-1 rounded-t-sm bg-gradient-to-t from-brand-600 to-brand-400 opacity-90 transition-all" style="height: {{ $h }}%"></div>
                                @endforeach
                            </div>
                            <div class="mt-2 flex justify-between text-[10px] text-slate-500">
                                @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $d)
                                    <span>{{ $d }}</span>
                                @endforeach
                            </div>
                        </div>

                        {{-- Decorative gradient overlay --}}
                        <div class="absolute -bottom-4 -right-4 h-32 w-32 rounded-full bg-brand-600/20 blur-2xl pointer-events-none"></div>
                    </div>

                    {{-- Floating notification chip --}}
                    <div class="lp-float-slow absolute -left-8 top-32 hidden rounded-2xl border border-white/10 bg-slate-900/95 px-4 py-2.5 shadow-2xl backdrop-blur-xl sm:block" style="animation-delay:-2s">
                        <div class="flex items-center gap-2.5 text-xs font-medium text-white">
                            <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-400">
                                <x-icon name="check" class="h-4 w-4" />
                            </span>
                            <div>
                                <div class="font-semibold">Nilai Tersimpan & WA Sent</div>
                                <div class="text-slate-400 text-[10px]">RA, MIS & MTs Nurul Jadid Karduluk</div>
                            </div>
                        </div>
                    </div>

                    {{-- Floating payment chip --}}
                    <div class="lp-float-slow absolute -right-6 bottom-6 hidden rounded-2xl border border-white/10 bg-slate-900/95 px-4 py-2.5 shadow-2xl backdrop-blur-xl sm:block" style="animation-delay:-4s">
                        <div class="flex items-center gap-2.5 text-xs font-medium text-white">
                            <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-amber-500/20 text-amber-400">
                                <x-icon name="tagihan" class="h-4 w-4" />
                            </span>
                            <div>
                                <div class="font-semibold">SPP Terverifikasi</div>
                                <div class="text-slate-400 text-[10px]">Karduluk · Pragaan</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    {{-- Scroll indicator --}}
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 text-slate-500 animate-bounce">
        <span class="text-xs tracking-widest uppercase">Scroll</span>
        <x-icon name="chevron-down" class="h-4 w-4" />
    </div>

</section>

{{-- About section --}}
<section id="tentang" class="relative overflow-hidden py-24 sm:py-32">
    <div class="absolute inset-0 bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950 -z-10"></div>
    <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
        <div class="grid items-center gap-16 lg:grid-cols-2">

            {{-- Kiri: Teks --}}
            <div class="lp-reveal">
                <span class="inline-block rounded-full bg-brand-500/15 px-4 py-1.5 text-xs font-bold uppercase tracking-widest text-brand-400 mb-5">
                    Tentang Yayasan
                </span>
                <h2 class="text-4xl font-black leading-tight tracking-tight text-white sm:text-5xl">
                    Membangun Generasi<br>
                    <span class="lp-text-gradient italic">Berakhlak & Berprestasi.</span>
                </h2>
                <p class="mt-6 text-lg leading-relaxed text-slate-400">
                    Yayasan Nurul Jadid Karduluk mendidik santri dan siswa dengan memadukan kurikulum madrasah terpadu, tahfidz Al-Qur'an, kajian kitab kuning, serta pemanfaatan teknologi informasi SIAKAD modern.
                </p>
                <div class="mt-8 grid grid-cols-2 gap-4">
                    @foreach ([
                        ['🎓', 'Lembaga Pendidikan', 'RA, MIS & MTs Nurul Jadid Karduluk.'],
                        ['📖', 'Tahfidz & Kitab', 'Pembinaan karakter santri berasrama berlandaskan Ahlussunnah.'],
                        ['🔔', 'Integrasi WhatsApp', 'Orang tua mendapatkan laporan absensi & nilai secara gratis.'],
                        ['📊', 'Transparansi SPP', 'Pencatatan tagihan dan bukti pembayaran online yang real-time.'],
                    ] as [$emoji, $title, $desc])
                        <div class="rounded-2xl border border-white/5 bg-white/3 p-4 backdrop-blur">
                            <div class="text-2xl mb-2">{{ $emoji }}</div>
                            <h4 class="text-sm font-bold text-white mb-1">{{ $title }}</h4>
                            <p class="text-xs text-slate-400 leading-relaxed">{{ $desc }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Kanan: Visual cards --}}
            <div class="lp-reveal relative" style="--lp-delay:150ms">
                <div class="space-y-4">
                    @foreach ([
                        ['Yayasan Nurul Jadid Karduluk', 'Karduluk, Pragaan, Sumenep', 'Jl. K. Syafi\'ie Itsbat No. 01, Kab. Sumenep, Madura.', '🏫'],
                        ['Lembaga Pendidikan Terdaftar', 'RA · MIS · MTs Nurul Jadid Karduluk', 'RA (NPSN: 69749559), MIS (NPSN: 60720605) & MTs Karduluk.', '👨‍🎓'],
                        ['SIAKAD WA Integration', 'Laravel · Alpine.js · WhatsApp API', 'Platform akademik digital terdepan untuk wali murid.', '⚡'],
                    ] as [$t1, $t2, $desc, $emoji])
                        <div class="flex items-center gap-4 rounded-2xl border border-white/5 bg-white/3 p-5 backdrop-blur">
                            <div class="text-3xl shrink-0">{{ $emoji }}</div>
                            <div>
                                <div class="font-bold text-white text-sm">{{ $t1 }}</div>
                                <div class="text-brand-400 text-xs font-medium">{{ $t2 }}</div>
                                <div class="text-slate-500 text-xs mt-1">{{ $desc }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</section>
