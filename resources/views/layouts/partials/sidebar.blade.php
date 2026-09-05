@php
    $user = auth()->user();
    $isAdmin = $user?->isAdmin() ?? false;
    $isWaliKelas = $user?->isWaliKelas() ?? false;

    // Definisi menu navigasi berdasarkan peran
    $navSections = [
        [
            'label' => null,
            'items' => [
                ['route' => 'dashboard', 'match' => 'dashboard', 'icon' => 'dashboard', 'label' => 'Dashboard', 'show' => true],
            ],
        ],
        [
            'label' => 'Akademik',
            'items' => [
                ['route' => 'nilai.index', 'match' => 'nilai.index*', 'icon' => 'nilai', 'label' => 'Nilai', 'show' => true],
                ['route' => 'absensi.index', 'match' => 'absensi.*', 'icon' => 'absensi', 'label' => 'Absensi', 'show' => true],
                ['route' => 'jadwal.index', 'match' => 'jadwal.*', 'icon' => 'jadwal', 'label' => 'Jadwal', 'show' => true],
                ['route' => 'nilai.leger', 'match' => 'nilai.leger*', 'icon' => 'document', 'label' => 'Buku Leger & Peringkat', 'show' => $isAdmin],
                ['route' => 'laporan.index', 'match' => 'laporan.*', 'icon' => 'download', 'label' => 'Laporan Akademik', 'show' => $isAdmin],
            ],
        ],
        [
            'label' => 'Perwalian',
            'items' => [
                ['route' => 'perwalian.index', 'match' => 'perwalian.*', 'icon' => 'kelas', 'label' => 'Kelas Perwalian', 'show' => $isWaliKelas || $isAdmin],
            ],
        ],
        [
            'label' => 'Data Master',
            'items' => [
                ['route' => 'siswa.index', 'match' => 'siswa.*', 'icon' => 'siswa', 'label' => 'Siswa', 'show' => $isAdmin],
                ['route' => 'guru.index', 'match' => 'guru.*', 'icon' => 'guru', 'label' => 'Guru', 'show' => $isAdmin],
                ['route' => 'kelas.index', 'match' => 'kelas.*', 'icon' => 'kelas', 'label' => 'Kelas', 'show' => $isAdmin],
                ['route' => 'mata-pelajaran.index', 'match' => 'mata-pelajaran.*', 'icon' => 'mapel', 'label' => 'Mata Pelajaran', 'show' => $isAdmin],
                ['route' => 'orang-tua.index', 'match' => 'orang-tua.*', 'icon' => 'orangtua', 'label' => 'Orang Tua', 'show' => $isAdmin],
            ],
        ],
        [
            'label' => 'Keuangan',
            'items' => [
                ['route' => 'tagihan.index', 'match' => 'tagihan.*', 'icon' => 'tagihan', 'label' => 'Tagihan & Pembayaran', 'show' => $isAdmin],
            ],
        ],
        [
            'label' => 'WhatsApp System',
            'items' => [
                ['route' => 'whatsapp.index', 'match' => 'whatsapp.index', 'icon' => 'whatsapp', 'label' => 'Status & Gateway', 'show' => $isAdmin],
                ['route' => 'whatsapp.chatbot-rules', 'match' => 'whatsapp.chatbot-rules*', 'icon' => 'bot', 'label' => 'Rule Chatbot', 'show' => $isAdmin],
                ['route' => 'whatsapp.templates', 'match' => 'whatsapp.templates*', 'icon' => 'template', 'label' => 'Template Notifikasi', 'show' => $isAdmin],
                ['route' => 'whatsapp.log-notifikasi', 'match' => 'whatsapp.log-notifikasi*', 'icon' => 'log', 'label' => 'Log Notifikasi', 'show' => $isAdmin],
                ['route' => 'whatsapp.log-chatbot', 'match' => 'whatsapp.log-chatbot*', 'icon' => 'chat', 'label' => 'Log Chatbot', 'show' => $isAdmin],
            ],
        ],
        [
            'label' => 'Lainnya',
            'items' => [
                ['route' => 'pengumuman.index', 'match' => 'pengumuman.*', 'icon' => 'pengumuman', 'label' => 'Pengumuman', 'show' => true],
                ['route' => 'users.index', 'match' => 'users.*', 'icon' => 'users', 'label' => 'Manajemen Akun', 'show' => $isAdmin],
            ],
        ],
    ];
@endphp

<nav x-data class="flex h-full flex-col bg-slate-900 text-slate-300">
    <a href="{{ route('landing') }}" class="group flex items-center gap-2.5 px-5 h-16 border-b border-slate-800 shrink-0 hover:bg-slate-800/60 transition-colors" title="Kembali ke Landing Page">
        <div class="relative flex h-9 w-9 items-center justify-center transition-transform group-hover:scale-105">
            <img src="{{ asset('images/logo.png') }}" alt="Logo Yayasan Nurul Jadid Karduluk" class="h-full w-full object-contain filter drop-shadow-[0_2px_6px_rgba(0,140,227,0.4)]">
        </div>
        <div class="leading-tight">
            <div class="font-bold text-white transition-colors group-hover:text-brand-400">SIAKAD NUJA</div>
            <div class="text-[10px] uppercase tracking-wider text-slate-400">Nurul Jadid</div>
        </div>
    </a>

    <div class="flex-1 overflow-y-auto px-3 py-4 space-y-6">
        @foreach ($navSections as $section)
            @php $visible = collect($section['items'])->where('show', true); @endphp
            @if ($visible->isNotEmpty())
                <div>
                    @if ($section['label'])
                        <p class="px-3 mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $section['label'] }}</p>
                    @endif
                    <ul class="space-y-1">
                        @foreach ($visible as $item)
                            @php $active = request()->routeIs($item['match']); @endphp
                            <li>
                                <a href="{{ route($item['route']) }}"
                                    class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition
                                    {{ $active ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <x-icon :name="$item['icon']" class="h-5 w-5 shrink-0" />
                                    {{ $item['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endforeach
    </div>
</nav>
