@extends('layouts.app')

@section('title', 'Entri Absensi')

@section('content')
<x-page-header title="Entri Absensi" subtitle="Pilih jadwal dan tanggal untuk mengisi kehadiran seluruh siswa." />

<div x-data="{
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
                this.errorMessage = 'Sensor GPS diblokir peramban karena diakses lewat HTTP IP address (bukan HTTPS / localhost). Untuk mengizinkan di Chrome HP: buka chrome://flags/#unsafely-treat-insecure-origin-as-secure lalu masukkan alamat IP http://' + window.location.host + ' dan pilih Enabled, atau buka via localhost / domain HTTPS.';
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
                    this.errorMessage = 'Izin GPS ditolak oleh peramban. Mohon aktifkan izin akses lokasi pada peramban perangkat Anda agar dapat melakukan absensi.';
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
                                        (status === 'detecting' ? 'Mendeteksi Posisi GPS Anda...' : 'Verifikasi Lokasi GPS Diperlukan'))"></h3>
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
                                    Anda berada <strong class="text-emerald-700 dark:text-emerald-300 font-semibold" x-text="distance + ' meter'"></strong> dari titik pusat {{ $lokasiConfig['nama'] }} (Akurasi GPS: ±<span x-text="accuracy"></span>m). Anda dapat melanjutkan pengisian absensi.
                                </p>
                            </template>

                            <template x-if="status === 'out_of_range'">
                                <p>
                                    Jarak Anda saat ini: <strong class="text-rose-700 dark:text-rose-300 font-semibold" x-text="distance + ' meter'"></strong> dari titik pusat madrasah. Absensi hanya dapat diakses saat berada dalam radius maksimal <strong>{{ $lokasiConfig['radius_meter'] }} meter</strong>.
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

    <x-card>
        @if ($jadwal->isNotEmpty())
            @php
                $jadwalOptions = $jadwal->map(function ($j) {
                    $kelasNama = $j->kelas->nama_lengkap ?? $j->kelas->nama_kelas ?? 'Kelas -';
                    $mapelNama = $j->mapel->nama_mapel ?? 'Mapel -';
                    $guruNama = $j->guru->nama_lengkap ?? null;
                    $hariJam = $j->hari . ', Jam ke-' . $j->jam_ke;
                    return [
                        'id' => $j->id,
                        'label' => $mapelNama . ' — ' . $kelasNama,
                        'sublabel' => $hariJam . ($guruNama ? ' • ' . $guruNama : '') . ($j->ruangan ? ' • ' . $j->ruangan : ''),
                        'kelas_id' => $j->kelas_id,
                    ];
                })->values()->all();
            @endphp

            <form method="GET" action="{{ route('absensi.roster') }}" class="space-y-6" x-data="{ selectedKelas: '' }">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            Filter Kelas (Opsional)
                        </label>
                        <select x-model="selectedKelas" class="block w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-white shadow-sm text-sm px-4 py-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition">
                            <option value="">-- Semua Kelas (Tampilkan Semua) --</option>
                            @foreach ($kelasList ?? [] as $k)
                                <option value="{{ $k->id }}">{{ $k->nama_lengkap }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-slate-400">Pilih kelas untuk mempersempit, atau langsung cari mapel di samping.</p>
                    </div>

                    <div class="sm:col-span-2">
                        <x-form.searchable-select
                            label="Pilih Mata Pelajaran & Jadwal"
                            name="jadwal_id"
                            :options="$jadwalOptions"
                            :selected="request('jadwal_id')"
                            placeholder="— Cari & Pilih Mata Pelajaran / Jadwal —"
                            searchPlaceholder="Ketik nama mata pelajaran, kelas, hari, atau guru..."
                            emptyText="Tidak ada mata pelajaran / jadwal yang cocok dengan pencarian"
                            :watchKelas="true"
                            required />
                    </div>

                    <div class="sm:col-span-3">
                        <x-form.input label="Tanggal Absensi" name="tanggal" type="date"
                            :value="request('tanggal', now()->format('Y-m-d'))" required />
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <x-button type="submit" variant="primary">
                        <x-icon name="absensi" class="h-4 w-4" /> Tampilkan Siswa
                    </x-button>
                    <x-button variant="secondary" :href="route('absensi.index')">Batal</x-button>
                </div>
            </form>
        @else
            <x-empty-state icon="jadwal" title="Tidak ada jadwal tersedia"
                description="Anda belum memiliki jadwal mengajar. Hubungi administrator." />
        @endif
    </x-card>
</div>
@endsection
