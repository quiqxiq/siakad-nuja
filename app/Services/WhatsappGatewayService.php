<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\NotifikasiWhatsapp;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Kstmostofa\LaravelWhatsApp\Facades\WhatsApp;

class WhatsappGatewayService
{
    /**
     * Normalisasi nomor HP ke format internasional 628xxx (tanpa tanda +).
     */
    public function normalisasiNomor(string $noHp): string
    {
        $noHp = preg_replace('/[^0-9]/', '', $noHp);

        if (str_starts_with($noHp, '0')) {
            $noHp = '62' . substr($noHp, 1);
        }

        return $noHp;
    }

    /**
     * Resolve nomor HP asli jika pengirim menggunakan WhatsApp LID JID (@lid).
     */
    public function resolvePhoneNumber(string $jidOrNumber): ?string
    {
        if (! str_contains($jidOrNumber, '@lid') && ! (is_numeric($jidOrNumber) && strlen($jidOrNumber) >= 14 && ! str_starts_with($jidOrNumber, '62') && ! str_starts_with($jidOrNumber, '08'))) {
            return $this->normalisasiNomor($jidOrNumber);
        }

        $cacheKey = 'wa_lid_resolved_' . md5($jidOrNumber);

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($jidOrNumber) {
            try {
                $client = WhatsApp::web('main')->client();
                $contact = $client->request('GET', 'sessions/main/contacts/' . rawurlencode($jidOrNumber));
                $user = $contact['id']['user'] ?? null;
                if ($user && $user !== 'lid' && ! empty($user)) {
                    return $this->normalisasiNomor((string) $user);
                }
            } catch (\Throwable $e) {
                Log::warning("[WhatsappGatewayService] Gagal resolve LID {$jidOrNumber}: " . $e->getMessage());
            }

            return null;
        });
    }

    /**
     * Format nomor ke JID WhatsApp Web sidecar (e.g., "628123456789@c.us" atau tetap JID jika sudah ada @)
     */
    public function toJid(string $noHp): string
    {
        if (str_contains($noHp, '@')) {
            return $noHp;
        }

        $normalized = $this->normalisasiNomor($noHp);
        return $normalized . '@c.us';
    }

    /**
     * Kirim pesan teks via laravel-whatsapp facade (Web sidecar backend).
     */
    public function send(string $noHp, string $pesan): bool
    {
        try {
            $toJid = $this->toJid($noHp);
            WhatsApp::send($toJid, $pesan, backend: 'web');
            Log::info("[LaravelWhatsApp] Berhasil kirim ke {$toJid}");
            return true;
        } catch (\Exception $e) {
            Log::error("[LaravelWhatsApp] Exception kirim ke {$noHp}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim pesan dan catat ke tabel notifikasi_whatsapp.
     */
    public function sendNotification(
        string $noHp,
        string $pesan,
        string $jenis,
        ?int $orangTuaId = null,
        ?int $siswaId = null
    ): bool {
        $log = NotifikasiWhatsapp::create([
            'orang_tua_id' => $orangTuaId,
            'siswa_id'     => $siswaId,
            'no_tujuan'    => $this->normalisasiNomor($noHp),
            'jenis'        => $jenis,
            'isi_pesan'    => $pesan,
            'status'       => 'pending',
        ]);

        $success = $this->send($noHp, $pesan);

        $log->update([
            'status'        => $success ? 'terkirim' : 'gagal',
            'dikirim_pada'  => $success ? now() : null,
            'error_message' => $success ? null : 'Gagal terkirim (Nomor tidak terdaftar di WhatsApp atau gateway offline)',
        ]);

        return $success;
    }

    /**
     * Cek status koneksi laravel-whatsapp sidecar.
     */
    public function getStatus(): array
    {
        try {
            $sessionState = WhatsApp::web('main')->state();
            $state        = strtolower($sessionState['status'] ?? 'disconnected');

            $statusStr = match ($state) {
                'ready', 'authenticated' => 'CONNECTED',
                'qr'                     => 'SCAN_QR',
                default                  => 'DISCONNECTED',
            };

            return [
                'status'       => $statusStr,
                'is_connected' => in_array($state, ['ready', 'authenticated'], true),
                'is_logged_in' => in_array($state, ['ready', 'authenticated'], true),
                'jid'          => $sessionState['id'] ?? 'main',
                'device_id'    => 'laravel-whatsapp-sidecar',
            ];
        } catch (\Exception $e) {
            Log::warning('[LaravelWhatsApp] tidak bisa cek status sidecar: ' . $e->getMessage());
            return ['status' => 'DISCONNECTED', 'message' => $e->getMessage()];
        }
    }

    /**
     * Trigger QR code login via laravel-whatsapp sidecar.
     */
    public function getQrCode(): ?string
    {
        try {
            $qrData = WhatsApp::web('main')->qr();
            return $qrData['qr'] ?? null;
        } catch (\Exception $e) {
            Log::warning('[LaravelWhatsApp] Tidak bisa ambil QR: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Logout device dari WhatsApp via laravel-whatsapp.
     */
    public function logout(): bool
    {
        try {
            WhatsApp::web('main')->stop();
            return true;
        } catch (\Exception $e) {
            Log::error('[LaravelWhatsApp] Logout error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Reconnect device ke WhatsApp via laravel-whatsapp.
     */
    public function reconnect(): bool
    {
        try {
            WhatsApp::web('main')->start();
            return true;
        } catch (\Exception $e) {
            Log::error('[LaravelWhatsApp] Reconnect error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim ulang notifikasi yang gagal.
     */
    public function resendNotification(NotifikasiWhatsapp $notif): bool
    {
        $success = $this->send($notif->no_tujuan, $notif->isi_pesan);

        $notif->update([
            'status'        => $success ? 'terkirim' : 'gagal',
            'dikirim_pada'  => $success ? now() : null,
            'error_message' => $success ? null : 'Retry gagal pada ' . now(),
        ]);

        return $success;
    }
}
