{{--
    Landing page publik SIAKAD NUJA.
    Halaman ini berdiri sendiri (tanpa layout app) dan dapat diakses tanpa login.
    Bagian-bagiannya dipecah ke resources/views/landing/*.blade.php.

    Desain: Ultra-premium portfolio dark theme — bukan SaaS generik.
--}}
<!DOCTYPE html>
<html lang="id" x-data="{ dark: $persist(true), scrolled: false }" :class="{ 'dark': dark }" x-cloak
      @scroll.window="scrolled = window.scrollY > 20">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="SIAKAD NUJA — Sistem Informasi Akademik modern Yayasan Nurul Jadid Karduluk Pragaan Sumenep. Kelola nilai, absensi, tagihan SPP, jadwal, dan notifikasi WhatsApp dalam satu platform elegan.">
    <meta name="keywords" content="SIAKAD, Nurul Jadid Karduluk, Sumenep, Pragaan, sistem akademik pesantren, manajemen sekolah, absensi online, nilai siswa, MIS Nurul Jadid, MTs As-Syafi'ie">
    <meta property="og:title" content="SIAKAD NUJA — Sistem Informasi Akademik Nurul Jadid Karduluk">
    <meta property="og:description" content="Platform akademik digital terpadu untuk Yayasan & Lembaga Pendidikan Nurul Jadid Karduluk, Sumenep.">
    <title>SIAKAD NUJA — Sistem Informasi Akademik · Nurul Jadid Karduluk Sumenep</title>
    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Siakad Nuja" />
    <link rel="manifest" href="/site.webmanifest" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 antialiased selection:bg-brand-500/30 overflow-x-hidden transition-colors duration-300">

    @include('landing.navbar')

    <main>
        @include('landing.hero')
        @include('landing.marquee')
        <!-- @include('landing.roles') -->
        @include('landing.gallery')
        @include('landing.stats')
        <!-- @include('landing.cta') -->
    </main>

    @include('landing.footer')

    {{-- Floating WhatsApp Chatbot Widget --}}
    @include('landing.whatsapp_widget')

    {{-- Back to top --}}
    <button x-show="scrolled" @click="window.scrollTo({top:0,behavior:'smooth'})"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4"
            class="fixed bottom-6 right-6 z-50 flex h-11 w-11 items-center justify-center rounded-full bg-brand-600 text-white shadow-lg shadow-brand-600/40 hover:bg-brand-500 transition" style="display:none">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
        </svg>
    </button>

    {{-- Scroll reveal: aktifkan .is-visible saat elemen .lp-reveal masuk viewport --}}
    <script>
        (function () {
            var reveal = function () {
                var els = document.querySelectorAll('.lp-reveal');
                if (!('IntersectionObserver' in window)) {
                    els.forEach(function (el) { el.classList.add('is-visible'); });
                    return;
                }
                var io = new IntersectionObserver(function (entries) {
                    entries.forEach(function (e) {
                        if (e.isIntersecting) {
                            e.target.classList.add('is-visible');
                            io.unobserve(e.target);
                        }
                    });
                }, { threshold: 0.1 });
                els.forEach(function (el) { io.observe(el); });
            };
            if (document.readyState !== 'loading') { reveal(); }
            else { document.addEventListener('DOMContentLoaded', reveal); }
        })();
    </script>
</body>
</html>
