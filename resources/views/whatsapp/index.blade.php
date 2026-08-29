@extends('layouts.app')

@section('title', 'WhatsApp Gateway')
@section('header', 'WhatsApp Gateway')

@section('content')
<div class="space-y-6 max-w-5xl" x-data="{
    activeTab: '{{ !empty($pairingCode) ? 'code' : 'code' }}',
    phone: '',
    pairingCode: '{{ $pairingCode ?? '' }}',
    loadingCode: false,
    errorMessage: '',
    copied: false,
    async requestPairing() {
        if (!this.phone) {
            this.errorMessage = 'Silakan masukkan nomor WhatsApp Anda.';
            return;
        }
        this.loadingCode = true;
        this.errorMessage = '';
        try {
            const resp = await fetch('{{ route('whatsapp.pairing-code') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ no_hp: this.phone })
            });
            const data = await resp.json();
            if (resp.ok && data.success) {
                this.pairingCode = data.code;
            } else {
                this.errorMessage = data.message || 'Gagal meminta kode pairing. Pastikan sidecar aktif.';
            }
        } catch (e) {
            this.errorMessage = 'Terjadi kesalahan saat menghubungi server.';
        } finally {
            this.loadingCode = false;
        }
    },
    copyCode() {
        if (!this.pairingCode) return;
        navigator.clipboard.writeText(this.pairingCode).then(() => {
            this.copied = true;
            setTimeout(() => { this.copied = false; }, 2500);
        });
    }
}">

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
        <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
        <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/70 p-5 flex items-center gap-4 shadow-sm">
            <div class="h-12 w-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </div>
            <div>
                <div class="text-2xl font-bold text-slate-800 dark:text-white">{{ number_format($totalNotif) }}</div>
                <div class="text-sm text-slate-500 dark:text-slate-400">Total Notifikasi</div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/70 p-5 flex items-center gap-4 shadow-sm">
            <div class="h-12 w-12 rounded-xl bg-red-50 dark:bg-red-950/50 flex items-center justify-center text-red-600 dark:text-red-400">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="text-2xl font-bold text-slate-800 dark:text-white">{{ number_format($totalGagal) }}</div>
                <div class="text-sm text-slate-500 dark:text-slate-400">Notifikasi Gagal</div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/70 p-5 flex items-center gap-4 shadow-sm">
            <div class="h-12 w-12 rounded-xl bg-blue-50 dark:bg-blue-950/50 flex items-center justify-center text-blue-600 dark:text-blue-400">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
            </div>
            <div>
                <div class="text-2xl font-bold text-slate-800 dark:text-white">{{ number_format($totalSesi) }}</div>
                <div class="text-sm text-slate-500 dark:text-slate-400">Sesi Chatbot</div>
            </div>
        </div>
    </div>

    {{-- Status Koneksi Card --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/70 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/60 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
            <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-xl bg-green-100 dark:bg-green-950/60 flex items-center justify-center text-green-600 dark:text-green-400">
                    <x-icon name="whatsapp" class="w-5 h-5" />
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-white">Status Koneksi WhatsApp Gateway</h3>
                    <p class="text-xs text-slate-400">Daemon WhatsApp Web Sidecar & Pengiriman Pesan</p>
                </div>
            </div>
            <button id="btn-refresh" class="text-sm text-brand-600 dark:text-brand-400 hover:underline font-medium flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Refresh
            </button>
        </div>

        <div class="p-6" id="status-container">
            @php
                $statusColor = match($status['status'] ?? 'UNKNOWN') {
                    'CONNECTED' => 'emerald',
                    'DISCONNECTED', 'ERROR' => 'red',
                    'SCAN_QR', 'PAIRING_CODE' => 'amber',
                    default => 'slate',
                };
                $statusLabel = match($status['status'] ?? 'UNKNOWN') {
                    'CONNECTED' => 'Terhubung',
                    'DISCONNECTED' => 'Terputus (Perlu Dihubungkan)',
                    'SCAN_QR' => 'Menunggu Scan QR',
                    'PAIRING_CODE' => 'Menunggu Verifikasi Kode Pairing',
                    'ERROR' => 'Error',
                    default => $status['status'] ?? 'Tidak Diketahui',
                };
                $isConnected = ($status['status'] ?? '') === 'CONNECTED';
            @endphp

            <div class="flex flex-wrap items-center justify-between gap-4 mb-6 pb-5 border-b border-slate-100 dark:border-slate-700/60">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-sm font-semibold bg-{{ $statusColor }}-100 dark:bg-{{ $statusColor }}-950/60 text-{{ $statusColor }}-800 dark:text-{{ $statusColor }}-300 border border-{{ $statusColor }}-200 dark:border-{{ $statusColor }}-800/60">
                        <span class="h-2.5 w-2.5 rounded-full bg-{{ $statusColor }}-500 {{ $isConnected ? 'animate-pulse' : '' }}"></span>
                        <span id="badge-status-label">{{ $statusLabel }}</span>
                    </span>
                    @if(!empty($status['jid']))
                        @php
                            $jidClean = preg_replace('/@.*$/', '', $status['jid']);
                        @endphp
                        <span class="text-sm text-slate-600 dark:text-slate-300">Akun: <strong class="text-slate-800 dark:text-white">{{ $jidClean }}</strong></span>
                    @endif
                    @if(!empty($status['device_id']))
                        <span class="text-xs text-slate-400 bg-slate-100 dark:bg-slate-700/50 px-2.5 py-1 rounded-md">Engine: {{ $status['device_id'] }}</span>
                    @endif
                </div>

                @if($isConnected)
                <div class="flex items-center gap-2">
                    <form action="{{ route('whatsapp.reconnect') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Reconnect
                        </button>
                    </form>

                    <form action="{{ route('whatsapp.logout') }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin logout? WhatsApp bot akan terputus.')">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Logout
                        </button>
                    </form>
                </div>
                @endif
            </div>

            @if(!$isConnected)
            {{-- Tabs Metode Hubungkan --}}
            <div class="space-y-5">
                <div class="flex border-b border-slate-200 dark:border-slate-700">
                    <button type="button" @click="activeTab = 'code'" :class="activeTab === 'code' ? 'border-brand-600 text-brand-600 dark:text-brand-400 font-bold border-b-2' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 font-medium'" class="pb-3 px-4 text-sm flex items-center gap-2 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        Tautkan dengan Nomor HP (Pairing Code)
                        <span class="px-2 py-0.5 text-xs bg-brand-100 text-brand-700 dark:bg-brand-900/60 dark:text-brand-300 rounded-full">Praktis</span>
                    </button>
                    <button type="button" @click="activeTab = 'qr'" :class="activeTab === 'qr' ? 'border-brand-600 text-brand-600 dark:text-brand-400 font-bold border-b-2' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 font-medium'" class="pb-3 px-4 text-sm flex items-center gap-2 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.24M16.12 7.88l1.42-1.42M18.5 12H21m-9 6v-1m0 0h.01m-.01 0h-4m0-6h-.01m.01 4.24V16"/></svg>
                        Scan QR Code
                    </button>
                </div>

                {{-- Tab 1: Pairing Code --}}
                <div x-show="activeTab === 'code'" class="space-y-4">
                    <div class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700/60">
                        <div class="max-w-xl">
                            <h4 class="text-sm font-bold text-slate-800 dark:text-white mb-1 flex items-center gap-2">
                                <span class="h-6 w-6 rounded-full bg-brand-600 text-white flex items-center justify-center text-xs font-bold">1</span>
                                Masukkan Nomor WhatsApp
                            </h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">
                                Masukkan nomor WhatsApp yang akan dijadikan akun bot gateway (contoh: <code class="bg-slate-200 dark:bg-slate-800 px-1 py-0.5 rounded">081234567890</code> atau <code class="bg-slate-200 dark:bg-slate-800 px-1 py-0.5 rounded">6281234567890</code>).
                            </p>

                            <form @submit.prevent="requestPairing" class="flex flex-col sm:flex-row gap-2.5">
                                <div class="relative flex-1">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    </div>
                                    <input
                                        type="tel"
                                        x-model="phone"
                                        placeholder="08xxxxxxxxxx atau 628xxxxxxxxxx"
                                        class="block w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-white shadow-sm text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"
                                        required
                                    >
                                </div>
                                <button
                                    type="submit"
                                    :disabled="loadingCode"
                                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-brand-600 hover:bg-brand-700 disabled:opacity-60 text-white text-sm font-semibold rounded-xl shadow-sm transition shrink-0"
                                >
                                    <template x-if="loadingCode">
                                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    </template>
                                    <span x-text="loadingCode ? 'Meminta Kode...' : 'Dapatkan Kode Tautan'"></span>
                                </button>
                            </form>

                            <template x-if="errorMessage">
                                <div class="mt-2.5 text-xs text-red-600 dark:text-red-400 flex items-center gap-1.5" x-text="errorMessage"></div>
                            </template>
                        </div>
                    </div>

                    {{-- Display Pairing Code & Instructions --}}
                    <template x-if="pairingCode">
                        <div class="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-950/40 dark:to-teal-950/30 border-2 border-emerald-300 dark:border-emerald-700/60 rounded-2xl p-6 shadow-sm">
                            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                                <div class="space-y-2 text-center md:text-left">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-200/80 dark:bg-emerald-900/80 text-emerald-800 dark:text-emerald-200">
                                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-ping"></span>
                                        KODE TAUTAN AKTIF
                                    </span>
                                    <h4 class="text-base font-bold text-slate-800 dark:text-white">Masukkan 8 Digit Kode Ini di WhatsApp HP</h4>
                                    <p class="text-xs text-slate-600 dark:text-slate-300">
                                        Kode berlaku sekitar 3 menit. Status akan otomatis berubah setelah kode dikonfirmasi di HP.
                                    </p>
                                </div>

                                {{-- Code Display Box --}}
                                <div class="flex flex-col items-center gap-2">
                                    <div class="flex items-center gap-2 bg-white dark:bg-slate-900 px-6 py-3.5 rounded-2xl border-2 border-emerald-500 shadow-md">
                                        <span class="font-mono text-2xl sm:text-3xl font-extrabold tracking-widest text-emerald-700 dark:text-emerald-400" x-text="pairingCode.length === 8 ? (pairingCode.slice(0,4) + ' - ' + pairingCode.slice(4)) : pairingCode"></span>
                                        <button
                                            type="button"
                                            @click="copyCode"
                                            class="ml-3 p-2 rounded-lg bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/50 dark:hover:bg-emerald-800 text-emerald-700 dark:text-emerald-300 transition"
                                            title="Salin Kode"
                                        >
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                        </button>
                                    </div>
                                    <span x-show="copied" x-transition class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">✓ Kode berhasil disalin!</span>
                                </div>
                            </div>

                            {{-- Steps Guide --}}
                            <div class="mt-6 pt-5 border-t border-emerald-200/80 dark:border-emerald-800/60">
                                <h5 class="text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300 mb-3">Langkah di Aplikasi WhatsApp HP:</h5>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 text-xs text-slate-700 dark:text-slate-300">
                                    <div class="bg-white/80 dark:bg-slate-900/60 p-3 rounded-xl border border-emerald-200/60 dark:border-emerald-800/40">
                                        <div class="font-bold text-emerald-700 dark:text-emerald-400 mb-1">Langkah 1</div>
                                        Buka WhatsApp di HP Anda.
                                    </div>
                                    <div class="bg-white/80 dark:bg-slate-900/60 p-3 rounded-xl border border-emerald-200/60 dark:border-emerald-800/40">
                                        <div class="font-bold text-emerald-700 dark:text-emerald-400 mb-1">Langkah 2</div>
                                        Tap <strong>Titik Tiga (⋮)</strong> / <strong>Pengaturan</strong> &rarr; <strong>Perangkat Tertaut</strong>.
                                    </div>
                                    <div class="bg-white/80 dark:bg-slate-900/60 p-3 rounded-xl border border-emerald-200/60 dark:border-emerald-800/40">
                                        <div class="font-bold text-emerald-700 dark:text-emerald-400 mb-1">Langkah 3</div>
                                        Tap <strong>Tautkan Perangkat</strong> &rarr; pilih <em>"Tautkan dengan nomor telepon saja"</em> di bawah.
                                    </div>
                                    <div class="bg-white/80 dark:bg-slate-900/60 p-3 rounded-xl border border-emerald-200/60 dark:border-emerald-800/40">
                                        <div class="font-bold text-emerald-700 dark:text-emerald-400 mb-1">Langkah 4</div>
                                        Ketik <strong>8 karakter kode</strong> di atas pada HP Anda.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Tab 2: Scan QR Code --}}
                <div x-show="activeTab === 'qr'" class="space-y-4">
                    <div class="bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 rounded-2xl p-5">
                        <div class="flex flex-col sm:flex-row gap-6 items-center">
                            @if($qrUrl)
                                <img id="qr-image" src="{{ $qrUrl }}" alt="QR Code" class="w-48 h-48 border border-amber-300 dark:border-amber-700 rounded-xl bg-white p-2 shadow-sm shrink-0">
                            @else
                                <div id="qr-placeholder" class="w-48 h-48 border-2 border-dashed border-amber-300 dark:border-amber-700 rounded-xl flex flex-col items-center justify-center text-center p-4 bg-white/60 dark:bg-slate-900/60 shrink-0">
                                    <svg class="w-10 h-10 text-amber-500 animate-spin mb-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <span class="text-xs text-amber-800 dark:text-amber-300 font-medium">Menunggu QR Code...</span>
                                </div>
                            @endif

                            <div class="text-sm text-slate-700 dark:text-slate-300 space-y-2.5">
                                <h4 class="font-bold text-amber-900 dark:text-amber-200 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.24M16.12 7.88l1.42-1.42M18.5 12H21m-9 6v-1m0 0h.01m-.01 0h-4m0-6h-.01m.01 4.24V16"/></svg>
                                    Petunjuk Scan QR Code
                                </h4>
                                <ol class="list-decimal list-inside space-y-1.5 text-xs text-slate-600 dark:text-slate-300">
                                    <li>Buka aplikasi <strong>WhatsApp</strong> di HP Anda</li>
                                    <li>Tap menu <strong>Titik Tiga (⋮)</strong> atau <strong>Pengaturan</strong> &rarr; <strong>Perangkat Tertaut</strong></li>
                                    <li>Tap tombol <strong>Tautkan Perangkat</strong></li>
                                    <li>Arahkan kamera ke QR Code di samping</li>
                                </ol>
                                <div class="pt-2">
                                    <form action="{{ route('whatsapp.login') }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            Inisialisasi Ulang QR
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>

    {{-- Quick Nav --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('whatsapp.chatbot-rules') }}" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/70 p-5 hover:border-brand-300 hover:shadow-sm transition flex items-center gap-3 group">
            <div class="h-10 w-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center group-hover:bg-amber-100 transition">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
                <div class="font-semibold text-slate-800 dark:text-white">Rule Chatbot</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Kelola menu & kata kunci</div>
            </div>
        </a>
        <a href="{{ route('whatsapp.templates') }}" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/70 p-5 hover:border-brand-300 hover:shadow-sm transition flex items-center gap-3 group">
            <div class="h-10 w-10 rounded-xl bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 flex items-center justify-center group-hover:bg-purple-100 transition">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </div>
            <div>
                <div class="font-semibold text-slate-800 dark:text-white">Template Pesan</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Edit template notifikasi</div>
            </div>
        </a>
        <a href="{{ route('whatsapp.log-notifikasi') }}" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/70 p-5 hover:border-brand-300 hover:shadow-sm transition flex items-center gap-3 group">
            <div class="h-10 w-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center group-hover:bg-emerald-100 transition">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <div class="font-semibold text-slate-800 dark:text-white">Log Notifikasi</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Riwayat & retry pesan gagal</div>
            </div>
        </a>
        <a href="{{ route('whatsapp.log-chatbot') }}" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/70 p-5 hover:border-brand-300 hover:shadow-sm transition flex items-center gap-3 group">
            <div class="h-10 w-10 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center group-hover:bg-blue-100 transition">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            </div>
            <div>
                <div class="font-semibold text-slate-800 dark:text-white">Log Chatbot</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Riwayat percakapan</div>
            </div>
        </a>
    </div>

</div>

@push('scripts')
<script>
// Auto-refresh status setiap 4 detik (untuk QR, Pairing code, & connection check)
const statusUrl = "{{ route('whatsapp.status') }}";
let autoRefreshTimer;

async function refreshStatus() {
    try {
        const resp = await fetch(statusUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await resp.json();
        const newStatus = data.status?.status;

        // Auto reload halaman jika status berubah menjadi CONNECTED
        if (newStatus === 'CONNECTED') {
            clearInterval(autoRefreshTimer);
            window.location.reload();
            return;
        }

        // Update QR image jika tersedia
        if (data.qr) {
            const qrImg = document.getElementById('qr-image');
            if (qrImg) {
                qrImg.src = data.qr;
            }
            const placeholder = document.getElementById('qr-placeholder');
            if (placeholder && !qrImg) {
                // If placeholder existed and now we have qr, reload to render img
                window.location.reload();
            }
        }
    } catch (e) {
        console.warn('Status refresh error:', e);
    }
}

document.getElementById('btn-refresh')?.addEventListener('click', () => window.location.reload());

@if(! $isConnected)
autoRefreshTimer = setInterval(refreshStatus, 4000);
@endif
</script>
@endpush
@endsection
