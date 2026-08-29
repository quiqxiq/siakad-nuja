{{-- CTA — Dramatic closing section --}}
<section class="relative overflow-hidden py-32 sm:py-40">

    {{-- Background --}}
    <div class="absolute inset-0 bg-slate-50 dark:bg-slate-950 -z-20 transition-colors duration-300"></div>
    <div class="absolute inset-0 -z-10">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-[700px] w-[700px] rounded-full bg-brand-500/10 dark:bg-brand-700/20 blur-[120px]"></div>
        <div class="absolute top-0 left-0 h-full w-full lp-bg-grid opacity-20"></div>
        {{-- Shooting star lines --}}
        <div class="absolute top-1/3 left-0 w-full h-px bg-gradient-to-r from-transparent via-brand-500/20 to-transparent"></div>
        <div class="absolute bottom-1/3 left-0 w-full h-px bg-gradient-to-r from-transparent via-violet-500/15 to-transparent"></div>
    </div>

    <div class="relative mx-auto max-w-4xl px-5 sm:px-6 lg:px-8 text-center">

        {{-- Floating badge --}}
        <div class="lp-pop inline-flex items-center gap-2 rounded-full border border-brand-500/30 bg-brand-500/10 px-4 py-2 text-xs font-bold tracking-widest text-brand-700 dark:text-brand-300 uppercase mb-8">
            <span class="relative flex h-2 w-2">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-brand-500 dark:bg-brand-400 opacity-60"></span>
                <span class="relative inline-flex h-2 w-2 rounded-full bg-brand-600 dark:bg-brand-400"></span>
            </span>
            Siap Digunakan Hari Ini
        </div>

        <h2 class="text-5xl font-black leading-[1.05] tracking-tight text-slate-900 dark:text-white sm:text-6xl lg:text-7xl">
            Waktunya sekolahmu<br>
            <span class="lp-text-gradient">naik level.</span>
        </h2>

        <p class="mx-auto mt-8 max-w-xl text-lg leading-relaxed text-slate-600 dark:text-slate-400">
            Bergabunglah bersama Yayasan Nurul Jadid Karduluk Sumenep dalam mengelola administrasi akademik yang cerdas, transparan, dan modern. Masuk dengan akun yang diberikan administrator.
        </p>

        <div class="mt-12 flex flex-col items-center justify-center gap-5 sm:flex-row">
            <a href="{{ route('login') }}"
               class="group relative inline-flex w-full items-center justify-center gap-3 overflow-hidden rounded-2xl bg-brand-600 px-10 py-5 text-base font-black text-white shadow-2xl shadow-brand-600/40 transition hover:bg-brand-500 hover:shadow-brand-600/60 hover:scale-[1.02] sm:w-auto">
                <span class="relative z-10">Masuk ke Sistem</span>
                <x-icon name="logout" class="relative z-10 h-5 w-5 transition-transform group-hover:translate-x-1" />
                <div class="absolute inset-0 bg-gradient-to-r from-brand-400/0 via-white/10 to-brand-400/0 -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
            </a>
            <a href="#beranda"
               class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition underline-offset-4 hover:underline">
                ↑ Kembali ke atas
            </a>
        </div>

        <p class="mt-8 text-sm text-slate-500 dark:text-slate-600">
            Belum punya akun? Hubungi administrator sekolah untuk mendapatkan akses.
        </p>

    </div>
</section>
