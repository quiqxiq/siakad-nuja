{{-- Statistik — mesh gradient dengan angka animasi --}}
<section id="statistik" class="relative py-24 sm:py-32 overflow-hidden">
    <div class="absolute inset-0 bg-slate-50 dark:bg-slate-950 -z-20 transition-colors duration-300"></div>

    {{-- Mesh gradient background --}}
    <div class="absolute inset-0 -z-10">
        <div class="absolute top-0 left-0 right-0 bottom-0 bg-gradient-to-br from-brand-100/60 via-slate-100/80 to-violet-100/60 dark:from-brand-950 dark:via-slate-900 dark:to-violet-950 transition-colors duration-300"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-[500px] w-[500px] rounded-full bg-brand-600/10 blur-[100px]"></div>
    </div>

    <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="lp-reveal mx-auto max-w-2xl text-center mb-16">
            <span class="inline-block rounded-full bg-slate-200/60 border border-slate-300/60 px-4 py-1.5 text-xs font-bold uppercase tracking-widest text-slate-600 dark:bg-white/5 dark:border-white/10 dark:text-slate-400 mb-5">
                Pencapaian
            </span>
            <h2 class="text-4xl font-black leading-tight tracking-tight text-slate-900 dark:text-white sm:text-5xl">
                Angka yang<br>
                <span class="lp-text-gradient">tidak bohong.</span>
            </h2>
            <!-- <p class="mt-5 text-slate-600 dark:text-slate-400">Data nyata dari seeder awal — tumbuh seiring penggunaan sistem di sekolah Anda.</p> -->
        </div>

        {{-- Stat counters --}}
        <div class="lp-reveal grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $stats = [
                    ['127', 'Siswa Terdaftar', 'siswa', 'brand', '+ peserta didik aktif'],
                    ['38',  'Tenaga Pengajar', 'guru',  'emerald', 'guru & staf berdedikasi'],
                    ['9',   'Rombongan Belajar', 'kelas', 'amber', 'kelas berbagai jenjang'],
                    ['1200+', 'Entri Data', 'nilai', 'violet', 'nilai, absensi & jadwal'],
                ];
                $cols = ['brand' => 'brand', 'emerald' => 'emerald', 'amber' => 'amber', 'violet' => 'violet'];
            @endphp

            @foreach ($stats as $i => [$value, $label, $icon, $color, $sub])
                <div class="lp-reveal text-center" style="--lp-delay: {{ $i * 100 }}ms"
                     x-data="{ shown: 0, target: {{ (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT) }} }"
                     x-intersect.once="let step = Math.max(1, Math.ceil(target/50)); let t = setInterval(() => { shown = Math.min(shown + step, target); if (shown >= target) clearInterval(t); }, 25)">
                    <div class="relative inline-flex flex-col items-center rounded-3xl border border-slate-200/80 bg-white/80 px-8 py-10 shadow-lg dark:shadow-none backdrop-blur-sm w-full dark:border-white/10 dark:bg-white/5">
                        <div class="absolute -top-px left-1/2 -translate-x-1/2 h-px w-24 bg-gradient-to-r from-transparent via-{{ $color }}-500/60 to-transparent"></div>
                        <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-{{ $color }}-500/15 text-{{ $color }}-600 dark:text-{{ $color }}-400">
                            <x-icon name="{{ $icon }}" class="h-7 w-7" />
                        </div>
                        <div class="text-5xl font-black tabular-nums text-slate-900 dark:text-white sm:text-6xl">
                            <span x-text="shown.toLocaleString('id-ID')">0</span>{{ str_contains($value, '+') ? '+' : '' }}
                        </div>
                        <div class="mt-2 text-base font-bold text-{{ $color }}-600 dark:text-{{ $color }}-400">{{ $label }}</div>
                        <div class="mt-1 text-xs text-slate-500">{{ $sub }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Quote --}}
        <div class="lp-reveal mt-16 text-center">
            <blockquote class="relative mx-auto max-w-2xl">
                <div class="text-4xl text-brand-500/30 font-serif leading-none mb-2">"</div>
                <p class="text-lg italic text-slate-700 dark:text-slate-300 leading-relaxed">
                    Teknologi yang baik tidak terasa seperti teknologi — ia terasa seperti solusi.
                </p>
                <footer class="mt-4 text-sm text-slate-500">— Tim Pengembang SIAKAD NUJA</footer>
            </blockquote>
        </div>

    </div>
</section>
