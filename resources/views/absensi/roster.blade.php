@extends('layouts.app')

@section('title', 'Isi Absensi')

@section('content')
@php
    $statuses = ['Hadir', 'Izin', 'Sakit', 'Alpa'];
    // Literal class strings so Tailwind JIT can detect them (no interpolation).
    $statusClass = [
        'Hadir' => 'peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 dark:peer-checked:bg-emerald-900/40 dark:peer-checked:text-emerald-300',
        'Izin' => 'peer-checked:border-sky-500 peer-checked:bg-sky-50 peer-checked:text-sky-700 dark:peer-checked:bg-sky-900/40 dark:peer-checked:text-sky-300',
        'Sakit' => 'peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700 dark:peer-checked:bg-amber-900/40 dark:peer-checked:text-amber-300',
        'Alpa' => 'peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-700 dark:peer-checked:bg-red-900/40 dark:peer-checked:text-red-300',
    ];
@endphp

<x-page-header title="Isi Absensi" subtitle="{{ $jadwal->kelas->nama_kelas ?? '-' }} — {{ $jadwal->mapel->nama_mapel ?? '-' }}">
    <x-slot:actions>
        <x-button variant="secondary" :href="route('absensi.create')">Ganti Jadwal</x-button>
    </x-slot:actions>
</x-page-header>

<div class="mb-4 flex flex-wrap items-center gap-3 rounded-xl bg-white dark:bg-slate-800 px-5 py-4 shadow-sm ring-1 ring-slate-200/70 dark:ring-slate-700/70">
    <div class="flex items-center gap-2 text-sm">
        <x-icon name="jadwal" class="h-5 w-5 text-brand-500" />
        <span class="font-medium text-slate-900 dark:text-white">{{ $jadwal->hari }}, jam ke-{{ $jadwal->jam_ke }}</span>
    </div>
    <span class="text-slate-300 dark:text-slate-600">•</span>
    <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
        <x-icon name="clock" class="h-4 w-4 text-slate-400" />
        {{ \Illuminate\Support\Str::substr($jadwal->jam_mulai, 0, 5) }}–{{ \Illuminate\Support\Str::substr($jadwal->jam_selesai, 0, 5) }}
    </div>
    <span class="text-slate-300 dark:text-slate-600">•</span>
    <div class="text-sm font-medium text-slate-900 dark:text-white">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}</div>
</div>

@if ($errors->any())
    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-300">
        <div class="flex items-start gap-2">
            <x-icon name="alert-triangle" class="h-5 w-5 shrink-0 text-rose-500 mt-0.5" />
            <div>
                <strong class="font-semibold">Gagal Menyimpan Absensi:</strong>
                <ul class="mt-1 list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

@if ($siswa->isNotEmpty())
    <form method="POST" action="{{ route('absensi.store') }}"
        x-data="{
            searchFilter: '',
            setAll(s) { document.querySelectorAll('input[data-status=\'' + s + '\']').forEach(el => el.checked = true) },
            targetLat: {{ $lokasiConfig['latitude'] ?? -7.0845556 }},
            targetLng: {{ $lokasiConfig['longitude'] ?? 113.7112031 }},
            targetName: '{{ addslashes($lokasiConfig['nama'] ?? 'MI Nurul Jadid Karduluk') }}',
            maxRadius: {{ $lokasiConfig['radius_meter'] ?? 50 }},
            latitude: null,
            longitude: null,
            accuracy: null,
            distance: null,
            status: 'detecting',
            errorMessage: '',
            isChecking: false,

            init() {
                this.detectLocation();
            },

            detectLocation() {
                this.isChecking = true;
                this.status = 'detecting';
                this.errorMessage = '';

                if (!navigator.geolocation) {
                    this.status = 'unsupported';
                    if (!window.isSecureContext) {
                        this.errorMessage = 'Sensor GPS diblokir peramban karena diakses lewat HTTP IP address (bukan HTTPS / localhost). Jika menggunakan Chrome di HP: buka chrome://flags/#unsafely-treat-insecure-origin-as-secure lalu daftarkan alamat IP http://' + window.location.host + ', atau akses menggunakan HTTPS / domain.';
                    } else {
                        this.errorMessage = 'Peramban (browser) Anda tidak mendukung fitur pendeteksi lokasi GPS.';
                    }
                    this.isChecking = false;
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        this.latitude = pos.coords.latitude;
                        this.longitude = pos.coords.longitude;
                        this.accuracy = pos.coords.accuracy ? Math.round(pos.coords.accuracy) : null;
                        this.distance = Math.round(this.calculateDistance(this.latitude, this.longitude, this.targetLat, this.targetLng));
                        this.isChecking = false;

                        if (this.distance <= this.maxRadius) {
                            this.status = 'valid';
                        } else {
                            this.status = 'out_of_range';
                        }
                    },
                    (err) => {
                        this.isChecking = false;
                        if (err.code === 1) {
                            this.status = 'permission_denied';
                            this.errorMessage = 'Izin GPS ditolak oleh peramban. Mohon aktifkan izin akses lokasi pada peramban perangkat Anda.';
                        } else if (err.code === 2) {
                            this.status = 'error';
                            this.errorMessage = 'Sinyal lokasi tidak dapat diperoleh. Pastikan fitur GPS perangkat Anda aktif.';
                        } else if (err.code === 3) {
                            this.status = 'error';
                            this.errorMessage = 'Waktu permintaan lokasi GPS habis. Silakan klik tombol Cek Ulang Lokasi.';
                        } else {
                            this.status = 'error';
                            this.errorMessage = err.message || 'Gagal memverifikasi lokasi.';
                        }
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 12000,
                        maximumAge: 0
                    }
                );
            },

            calculateDistance(lat1, lon1, lat2, lon2) {
                const R = 6371000;
                const toRad = deg => (deg * Math.PI) / 180;
                const dLat = toRad(lat2 - lat1);
                const dLon = toRad(lon2 - lon1);
                const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                          Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
                          Math.sin(dLon / 2) * Math.sin(dLon / 2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                return R * c;
            },

            get isValid() {
                return this.status === 'valid' && this.latitude !== null && this.longitude !== null;
            }
        }">
        @csrf
        <input type="hidden" name="jadwal_id" value="{{ $jadwal->id }}">
        <input type="hidden" name="tanggal" value="{{ $tanggal }}">
        <input type="hidden" name="latitude" :value="latitude">
        <input type="hidden" name="longitude" :value="longitude">

        <!-- Banner Status Verifikasi Geolokasi (Geofencing 50 Meter) -->
        <div class="mb-4 overflow-hidden rounded-2xl border transition-all duration-300 shadow-sm"
            :class="{
                'border-emerald-200 bg-emerald-50/80 dark:border-emerald-800 dark:bg-emerald-950/30': status === 'valid',
                'border-rose-200 bg-rose-50/80 dark:border-rose-800 dark:bg-rose-950/30': status === 'out_of_range',
                'border-amber-200 bg-amber-50/80 dark:border-amber-800 dark:bg-amber-950/30': status === 'permission_denied' || status === 'error' || status === 'unsupported',
                'border-blue-200 bg-blue-50/80 dark:border-blue-800 dark:bg-blue-950/30': status === 'detecting'
            }">
            <div class="p-4 sm:p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-3.5">
                        <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl shadow-xs"
                            :class="{
                                'bg-emerald-600 text-white': status === 'valid',
                                'bg-rose-600 text-white': status === 'out_of_range',
                                'bg-amber-500 text-white': status === 'permission_denied' || status === 'error' || status === 'unsupported',
                                'bg-blue-600 text-white': status === 'detecting'
                            }">
                            <!-- Loading SVG -->
                            <svg x-show="status === 'detecting'" class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <!-- Valid Check SVG -->
                            <svg x-show="status === 'valid'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <!-- Out of Range SVG -->
                            <svg x-show="status === 'out_of_range'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                            <!-- Warning/Lock SVG -->
                            <svg x-show="status === 'permission_denied' || status === 'error' || status === 'unsupported'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                        </div>

                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white"
                                    x-text="status === 'valid' ? 'Lokasi Terverifikasi: Di Lingkungan Madrasah' :
                                            (status === 'out_of_range' ? 'Di Luar Jangkauan Lokasi Madrasah' :
                                            (status === 'detecting' ? 'Mendeteksi Posisi GPS Anda...' : 'Izin Lokasi GPS Diperlukan'))"></h3>
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold"
                                    :class="{
                                        'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300': status === 'valid',
                                        'bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-300': status === 'out_of_range',
                                        'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300': status === 'permission_denied' || status === 'error' || status === 'unsupported',
                                        'bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300': status === 'detecting'
                                    }"
                                    x-text="status === 'valid' ? 'Siap Absensi' :
                                            (status === 'out_of_range' ? 'Terkunci (Maks 50m)' :
                                            (status === 'detecting' ? 'Memindai Sinyal' : 'Akses Dibatasi'))"></span>
                            </div>

                            <div class="mt-1 text-xs text-slate-600 dark:text-slate-300 space-y-1">
                                <template x-if="status === 'detecting'">
                                    <p>Sedang membaca sensor GPS perangkat untuk memastikan Anda berada dalam radius 50 meter dari {{ $lokasiConfig['nama'] }}...</p>
                                </template>

                                <template x-if="status === 'valid'">
                                    <p>
                                        Anda berada <strong class="text-emerald-700 dark:text-emerald-300 font-semibold" x-text="distance + ' meter'"></strong> dari titik pusat {{ $lokasiConfig['nama'] }} (Akurasi GPS: ±<span x-text="accuracy"></span>m). Presensi dapat disimpan.
                                    </p>
                                </template>

                                <template x-if="status === 'out_of_range'">
                                    <p>
                                        Jarak Anda saat ini: <strong class="text-rose-700 dark:text-rose-300 font-semibold" x-text="distance + ' meter'"></strong> dari titik pusat madrasah. Absensi hanya dapat disimpan jika berada dalam radius <strong>maksimal {{ $lokasiConfig['radius_meter'] }} meter</strong>.
                                    </p>
                                </template>

                                <template x-if="status === 'permission_denied' || status === 'error' || status === 'unsupported'">
                                    <p class="text-amber-800 dark:text-amber-300 font-medium" x-text="errorMessage"></p>
                                </template>
                            </div>

                            <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-slate-500 dark:text-slate-400">
                                <span>Titik: <strong>{{ $lokasiConfig['nama'] }}</strong> (WP86+5FG, Karduluk, Pragaan, Sumenep)</span>
                                <span>•</span>
                                <a href="{{ $lokasiConfig['maps_url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-brand-600 dark:text-brand-400 hover:underline font-medium">
                                    <span>Lihat di Google Maps</span>
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 self-start sm:self-center shrink-0">
                        <button type="button" @click="detectLocation()" :disabled="isChecking"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 shadow-xs hover:bg-slate-50 dark:hover:bg-slate-700 transition disabled:opacity-50">
                            <svg class="h-3.5 w-3.5" :class="{'animate-spin': isChecking}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            <span x-text="isChecking ? 'Mengecek...' : 'Cek Ulang Lokasi'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <x-card padding="p-0">
            <x-slot:header>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ $siswa->count() }} Siswa</h2>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-400">Tandai semua:</span>
                        @foreach ($statuses as $st)
                            <button type="button" @click="setAll('{{ $st }}')"
                                class="rounded-lg border border-slate-200 dark:border-slate-600 px-2.5 py-1 text-xs font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">{{ $st }}</button>
                        @endforeach
                    </div>
                </div>
            </x-slot:header>

            <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/40 flex items-center justify-between gap-3">
                <div class="relative w-full max-w-sm">
                    <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input type="search"
                        x-model="searchFilter"
                        placeholder="Cari nama atau NIS siswa di roster..."
                        class="block w-full rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-white pl-9 pr-8 py-2 text-xs shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    <button type="button" x-show="searchFilter" @click="searchFilter = ''" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                        <x-icon name="close" class="h-3.5 w-3.5" />
                    </button>
                </div>
            </div>

            <ul class="divide-y divide-slate-100 dark:divide-slate-700/60">
                @foreach ($siswa as $s)
                    @php $current = old("status.$s->id", $existing[$s->id]->status ?? 'Hadir'); @endphp
                    <li class="px-4 py-4 sm:px-6" x-show="!searchFilter || ('{{ strtolower(addslashes($s->nama_lengkap . ' ' . $s->nis)) }}').includes(searchFilter.toLowerCase().trim())">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-700 text-sm font-semibold text-slate-600 dark:text-slate-300">
                                    {{ strtoupper(substr($s->nama_lengkap, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-slate-900 dark:text-white">{{ $s->nama_lengkap }}</p>
                                    <p class="text-xs text-slate-500">NIS {{ $s->nis }}</p>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                @foreach ($statuses as $st)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="status[{{ $s->id }}]" value="{{ $st }}" data-status="{{ $st }}"
                                            @checked($current === $st) class="peer sr-only">
                                        <span class="inline-flex items-center rounded-lg border border-slate-200 dark:border-slate-600 px-3 py-1.5 text-xs font-medium text-slate-600 dark:text-slate-300 transition {{ $statusClass[$st] }}">{{ $st }}</span>
                                    </label>
                                @endforeach
                                <input type="text" name="keterangan[{{ $s->id }}]" value="{{ old("keterangan.$s->id", $existing[$s->id]->keterangan ?? '') }}"
                                    placeholder="Keterangan (opsional)"
                                    class="w-full lg:w-44 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            <x-slot:footer>
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="text-xs">
                        <template x-if="!isValid">
                            <span class="inline-flex items-center gap-1.5 text-rose-600 dark:text-rose-400 font-medium">
                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                </svg>
                                <span x-text="status === 'detecting' ? 'Menunggu verifikasi sinyal GPS...' : 'Tombol simpan terkunci karena belum memenuhi syarat geolokasi madrasah.'"></span>
                            </span>
                        </template>
                        <template x-if="isValid">
                            <span class="inline-flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-medium">
                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Lokasi terverifikasi di area madrasah. Siap disimpan.
                            </span>
                        </template>
                    </div>

                    <div class="flex items-center gap-3">
                        <x-button variant="secondary" :href="route('absensi.index')">Batal</x-button>
                        <button type="submit"
                            :disabled="!isValid"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 transition disabled:opacity-40 disabled:cursor-not-allowed">
                            <x-icon name="check" class="h-4 w-4" />
                            <span>Simpan Absensi</span>
                        </button>
                    </div>
                </div>
            </x-slot:footer>
        </x-card>
    </form>
@else
    <x-card>
        <x-empty-state icon="siswa" title="Tidak ada siswa di kelas ini" description="Belum ada siswa terdaftar pada kelas jadwal tersebut." />
    </x-card>
@endif
@endsection
