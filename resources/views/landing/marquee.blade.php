{{-- Marquee — tech stack & fitur chips bergerak --}}
<div class="relative overflow-hidden border-y border-slate-200/80 bg-slate-100/70 py-5 dark:border-white/5 dark:bg-slate-900/50 backdrop-blur-sm transition-colors duration-300">
    {{-- Gradient fade kiri & kanan --}}
    <div class="pointer-events-none absolute inset-y-0 left-0 z-10 w-24 bg-gradient-to-r from-slate-50 dark:from-slate-950 to-transparent"></div>
    <div class="pointer-events-none absolute inset-y-0 right-0 z-10 w-24 bg-gradient-to-l from-slate-50 dark:from-slate-950 to-transparent"></div>

    <div class="flex animate-[marquee_40s_linear_infinite] whitespace-nowrap">
        @php
            $chips = [
                ['🏫', 'Nurul Jadid Karduluk', 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border-emerald-500/20'],
                ['📍', 'Karduluk · Pragaan · Sumenep', 'bg-brand-500/10 text-brand-700 dark:text-brand-300 border-brand-500/20'],
                ['📚', 'RA Nurul Jadid (NPSN: 69749559)', 'bg-amber-500/10 text-amber-700 dark:text-amber-300 border-amber-500/20'],
                ['🎓', 'MIS Nurul Jadid (NPSN: 60720605)', 'bg-sky-500/10 text-sky-700 dark:text-sky-300 border-sky-500/20'],
                ['🕌', 'MTs Nurul Jadid (Madrasah Tsanawiyah)', 'bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border-indigo-500/20'],
                ['🔔', 'Notifikasi WhatsApp Wali', 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border-emerald-500/20'],
                ['📊', 'SIAKAD Real-time', 'bg-violet-500/10 text-violet-700 dark:text-violet-300 border-violet-500/20'],
                ['💳', 'Verifikasi SPP Digital', 'bg-rose-500/10 text-rose-700 dark:text-rose-300 border-rose-500/20'],
                ['📋', 'Rekap Absensi Massal', 'bg-teal-500/10 text-teal-700 dark:text-teal-300 border-teal-500/20'],
                ['🏆', 'Rekap Nilai & Predikat', 'bg-orange-500/10 text-orange-700 dark:text-orange-300 border-orange-500/20'],
                ['📄', 'Cetak Laporan', 'bg-pink-500/10 text-pink-700 dark:text-pink-300 border-pink-500/20'],
                ['📖', 'Tahfidz & Kitab Kuning', 'bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border-indigo-500/20'],
            ];
            $all = array_merge($chips, $chips); // dua putaran
        @endphp
        @foreach ($all as [$em, $label, $cls])
            <span class="mx-3 inline-flex items-center gap-2 rounded-full border px-4 py-1.5 text-xs font-semibold {{ $cls }}">
                {{ $em }} {{ $label }}
            </span>
        @endforeach
    </div>
</div>

<style>
@keyframes marquee {
    0%   { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}
</style>
