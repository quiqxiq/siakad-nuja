{{-- Navbar publik — transparan lalu solid saat scroll --}}
<header class="fixed inset-x-0 top-0 z-50 transition-all duration-500"
        :class="scrolled
            ? 'border-b border-white/10 bg-slate-950/90 backdrop-blur-2xl shadow-lg shadow-black/20'
            : 'border-b border-transparent'">
    <nav class="mx-auto flex h-18 max-w-7xl items-center justify-between gap-4 px-5 sm:px-6 lg:px-8" style="height:70px">

        {{-- Logo --}}
        <a href="#beranda" class="group flex items-center gap-3 select-none">
            <div class="relative flex h-12 w-12 items-center justify-center transition group-hover:scale-105">
                <img src="{{ asset('images/logo.png') }}" alt="Logo Yayasan Nurul Jadid Karduluk YANUJA" class="h-full w-full object-contain filter drop-shadow-[0_2px_8px_rgba(0,140,227,0.45)]">
            </div>
            <div class="leading-tight">
                <div class="text-sm font-black tracking-tight text-white flex items-center gap-1.5">
                    <span>SIAKAD NUJA</span>
                    <span class="rounded-md bg-sky-500/20 px-1.5 py-0.5 text-[9px] font-bold text-sky-300 border border-sky-500/30">YANUJA</span>
                </div>
                <div class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-300">Nurul Jadid · Karduluk Sumenep</div>
            </div>
        </a>

        {{-- Menu desktop --}}
        <div class="hidden items-center gap-1 md:flex">
            @foreach (['#beranda' => 'Beranda', '#tentang' => 'Tentang', '#galeri' => 'Galeri', '#statistik' => 'Pencapaian'] as $href => $label)
                <a href="{{ $href }}"
                   class="rounded-full px-4 py-2 text-sm font-medium text-slate-300 transition-all hover:bg-white/10 hover:text-white">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- Aksi --}}
        <div class="flex items-center gap-2">
            <button @click="dark = !dark"
                    class="rounded-full p-2.5 text-slate-400 transition hover:bg-white/10 hover:text-white"
                    title="Ganti tema" aria-label="Ganti tema">
                <x-icon name="sun" class="hidden h-5 w-5 dark:block" />
                <x-icon name="moon" class="block h-5 w-5 dark:hidden" />
            </button>
            <a href="{{ auth()->check() ? route('dashboard') : route('login') }}"
               class="group relative inline-flex items-center gap-2 overflow-hidden rounded-full bg-brand-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-600/30 transition hover:bg-brand-500 hover:shadow-brand-600/50">
                <span class="relative z-10">{{ auth()->check() ? 'Dashboard' : 'Masuk' }}</span>
                <x-icon name="logout" class="relative z-10 h-4 w-4 transition-transform group-hover:translate-x-0.5" />
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-500"></div>
            </a>
        </div>
    </nav>
</header>
