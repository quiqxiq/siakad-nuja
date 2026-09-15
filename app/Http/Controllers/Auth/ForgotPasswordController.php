<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Konfigurasi;
use App\Models\User;
use App\Services\WhatsappGatewayService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends Controller
{
    public function __construct(private readonly WhatsappGatewayService $gateway) {}

    /**
     * Tampilkan formulir permintaan lupa password / input identifier.
     */
    public function showLinkRequestForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Kirim kode OTP via WhatsApp ke nomor HP pengguna.
     */
    public function sendOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'identifier' => 'required|string',
        ], [
            'identifier.required' => 'Email, Username, NIP, atau Nomor WhatsApp wajib diisi.',
        ]);

        $input = trim((string) $request->input('identifier'));
        $user = $this->resolveUser($input);

        if ($user === null) {
            return back()->withErrors([
                'identifier' => 'Akun tidak ditemukan. Pastikan email, username, NIP, atau nomor WhatsApp yang Anda masukkan benar.',
            ])->withInput();
        }

        if (! $user->is_active) {
            return back()->withErrors([
                'identifier' => 'Akun Anda berstatus nonaktif. Silakan hubungi administrator sekolah.',
            ])->withInput();
        }

        if (empty($user->no_hp)) {
            return back()->withErrors([
                'identifier' => 'Akun ini belum memiliki nomor WhatsApp terdaftar. Silakan hubungi administrator untuk mendaftarkan nomor Anda.',
            ])->withInput();
        }

        // Cek cooldown pengiriman (minimal jeda 60 detik)
        $sentAt = session('password_reset_sent_at_'.$user->id);
        if ($sentAt instanceof Carbon && $sentAt->diffInSeconds(now()) < 60) {
            $remaining = (int) ceil(60 - $sentAt->diffInSeconds(now()));

            return back()->withErrors([
                'identifier' => "Harap tunggu {$this->formatSecondsToHuman($remaining)} sebelum meminta kode OTP kembali.",
            ])->withInput();
        }

        // Generate 6-digit numeric OTP
        $otp = (string) random_int(100000, 999999);

        // Simpan token hashed ke tabel password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        // Siapkan pesan dari konfigurasi template
        $template = Konfigurasi::get(
            'template_otp',
            "🔐 *Kode OTP Reset Password*\n\nHalo *{nama}*,\n\nKode OTP Anda untuk mengatur ulang password SIAKAD NUJA adalah:\n*{otp}*\n\nKode ini bersifat rahasia dan berlaku selama 10 menit. Jangan berikan kode ini kepada siapapun demi keamanan akun Anda.\n\n— SIAKAD Nurul Jadid Karduluk"
        );

        $pesan = strtr($template, [
            '{nama}' => $user->nama,
            '{otp}' => $otp,
        ]);

        // Catat ke log untuk keperluan audit / debugging lokal jika gateway belum tersambung
        Log::info("[ForgotPassword] OTP untuk user {$user->email} ({$user->no_hp}): {$otp}");

        // Kirim via WhatsApp Gateway
        $sent = $this->gateway->sendNotification(
            $user->no_hp,
            $pesan,
            'otp_reset_password'
        );

        $maskedPhone = $this->maskPhoneNumber($user->no_hp);

        // Simpan state di session untuk form verifikasi
        session([
            'password_reset_user_id' => $user->id,
            'password_reset_email' => $user->email,
            'password_reset_phone' => $maskedPhone,
            'password_reset_raw_phone' => $user->no_hp,
            'password_reset_sent_at_'.$user->id => now(),
        ]);

        $message = "Kode OTP telah dikirimkan ke nomor WhatsApp Anda ({$maskedPhone}). Masukkan kode tersebut di bawah ini.";
        if (! $sent && config('app.debug')) {
            $message .= " (Catatan Dev: Gateway offline, kode OTP tercatat di storage/logs/laravel.log: {$otp})";
        }

        return redirect()->route('password.verify_form')->with('success', $message);
    }

    /**
     * Tampilkan halaman verifikasi kode OTP dan input password baru.
     */
    public function showVerifyForm(): View|RedirectResponse
    {
        $userId = session('password_reset_user_id');
        $email = session('password_reset_email');

        if (! $userId || ! $email) {
            return redirect()->route('password.request')
                ->with('error', 'Sesi reset password telah berakhir. Silakan masukkan kembali identitas Anda.');
        }

        $user = User::find($userId);
        if (! $user) {
            return redirect()->route('password.request');
        }

        $sentAt = session('password_reset_sent_at_'.$user->id);
        $cooldown = 0;
        if ($sentAt instanceof Carbon) {
            $elapsed = $sentAt->diffInSeconds(now());
            if ($elapsed < 60) {
                $cooldown = (int) ceil(60 - $elapsed);
            }
        }

        $resetRecord = DB::table('password_reset_tokens')->where('email', $user->email)->first();
        $expiresIn = 600; // default 10 menit
        if ($resetRecord && $resetRecord->created_at) {
            $createdAt = Carbon::parse($resetRecord->created_at);
            $elapsed = $createdAt->diffInSeconds(now());
            $expiresIn = max(0, (int) ceil(600 - $elapsed));
        }

        $maskedPhone = session('password_reset_phone') ?? $this->maskPhoneNumber($user->no_hp);

        return view('auth.verify-otp', compact('user', 'maskedPhone', 'cooldown', 'expiresIn'));
    }

    /**
     * Kirim ulang kode OTP via WhatsApp.
     */
    public function resendOtp(): RedirectResponse
    {
        $userId = session('password_reset_user_id');
        if (! $userId) {
            return redirect()->route('password.request')
                ->with('error', 'Sesi verifikasi telah berakhir. Silakan mulai kembali.');
        }

        $user = User::find($userId);
        if (! $user || empty($user->no_hp)) {
            return redirect()->route('password.request')
                ->with('error', 'Data akun tidak valid.');
        }

        $sentAt = session('password_reset_sent_at_'.$user->id);
        if ($sentAt instanceof Carbon && $sentAt->diffInSeconds(now()) < 60) {
            $remaining = (int) ceil(60 - $sentAt->diffInSeconds(now()));

            return back()->with('error', "Harap tunggu {$this->formatSecondsToHuman($remaining)} sebelum meminta kode OTP kembali.");
        }

        $otp = (string) random_int(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        $template = Konfigurasi::get(
            'template_otp',
            "🔐 *Kode OTP Reset Password*\n\nHalo *{nama}*,\n\nKode OTP Anda untuk mengatur ulang password SIAKAD NUJA adalah:\n*{otp}*\n\nKode ini bersifat rahasia dan berlaku selama 10 menit. Jangan berikan kode ini kepada siapapun demi keamanan akun Anda.\n\n— SIAKAD Nurul Jadid Karduluk"
        );

        $pesan = strtr($template, [
            '{nama}' => $user->nama,
            '{otp}' => $otp,
        ]);

        Log::info("[ForgotPassword] Resend OTP untuk user {$user->email} ({$user->no_hp}): {$otp}");

        $sent = $this->gateway->sendNotification(
            $user->no_hp,
            $pesan,
            'otp_reset_password'
        );

        session(['password_reset_sent_at_'.$user->id => now()]);

        $msg = 'Kode OTP baru berhasil dikirimkan ke nomor WhatsApp Anda.';
        if (! $sent && config('app.debug')) {
            $msg .= " (Catatan Dev: Gateway offline, OTP tercatat di log: {$otp})";
        }

        return back()->with('success', $msg);
    }

    /**
     * Verifikasi kode OTP WhatsApp.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ], [
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.size' => 'Kode OTP harus berupa 6 digit angka.',
        ]);

        $userId = session('password_reset_user_id');
        $email = session('password_reset_email');

        if (! $userId || ! $email) {
            return redirect()->route('password.request')
                ->with('error', 'Sesi verifikasi kedaluwarsa. Silakan ajukan lupa password kembali.');
        }

        $user = User::find($userId);
        if (! $user) {
            return redirect()->route('password.request')
                ->with('error', 'Pengguna tidak ditemukan.');
        }

        $resetRecord = DB::table('password_reset_tokens')->where('email', $user->email)->first();

        if (! $resetRecord) {
            return redirect()->route('password.request')
                ->with('error', 'Tidak ada permintaan reset password yang aktif. Silakan minta kode OTP baru.');
        }

        // Cek kedaluwarsa (10 menit)
        $createdAt = Carbon::parse($resetRecord->created_at);
        if ($createdAt->addMinutes(10)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();

            return back()->withErrors([
                'otp' => 'Kode OTP telah kedaluwarsa (berlaku maksimal 10 menit). Silakan kirim ulang kode OTP.',
            ]);
        }

        // Verifikasi kecocokan OTP
        if (! Hash::check((string) $request->input('otp'), $resetRecord->token)) {
            return back()->withErrors([
                'otp' => 'Kode OTP yang Anda masukkan salah. Silakan periksa kembali pesan WhatsApp Anda.',
            ])->withInput($request->only('otp'));
        }

        // Tandai bahwa OTP berhasil diverifikasi pada session
        session([
            'password_reset_otp_verified' => true,
            'password_reset_verified_at' => now(),
        ]);

        return redirect()->route('password.reset_form')
            ->with('success', 'Kode OTP berhasil diverifikasi! Silakan buat password baru Anda.');
    }

    /**
     * Tampilkan halaman formulir atur password baru (hanya jika OTP terverifikasi).
     */
    public function showResetForm(): View|RedirectResponse
    {
        $userId = session('password_reset_user_id');
        $email = session('password_reset_email');
        $isVerified = session('password_reset_otp_verified');

        if (! $userId || ! $email || ! $isVerified) {
            return redirect()->route('password.request')
                ->with('error', 'Sesi verifikasi belum selesai atau telah berakhir. Silakan masukkan identitas Anda terlebih dahulu.');
        }

        $user = User::find($userId);
        if (! $user) {
            return redirect()->route('password.request')
                ->with('error', 'Pengguna tidak ditemukan.');
        }

        return view('auth.reset-password', compact('user'));
    }

    /**
     * Simpan pembaruan password baru user setelah OTP diverifikasi.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $userId = session('password_reset_user_id');
        $email = session('password_reset_email');
        $isVerified = session('password_reset_otp_verified');

        if (! $userId || ! $email || ! $isVerified) {
            return redirect()->route('password.request')
                ->with('error', 'Sesi verifikasi kedaluwarsa. Silakan ajukan lupa password kembali.');
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal berjumlah 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password_confirmation.required' => 'Konfirmasi password baru wajib diisi.',
        ]);

        $user = User::find($userId);
        if (! $user) {
            return redirect()->route('password.request')
                ->with('error', 'Pengguna tidak ditemukan.');
        }

        // Update password user
        $user->update([
            'password' => Hash::make((string) $request->input('password')),
        ]);

        // Hapus token yang sudah dipakai
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        // Bersihkan seluruh session terkait reset password
        session()->forget([
            'password_reset_user_id',
            'password_reset_email',
            'password_reset_phone',
            'password_reset_raw_phone',
            'password_reset_sent_at_'.$user->id,
            'password_reset_otp_verified',
            'password_reset_verified_at',
        ]);

        // Kirim konfirmasi perubahan password via WA
        if (! empty($user->no_hp)) {
            $confirmMsg = "✅ *Password Berhasil Diperbarui*\n\n"
                ."Halo *{$user->nama}*,\n\n"
                .'Password akun SIAKAD NUJA Anda telah berhasil diperbarui pada '.now()->translatedFormat('l, d F Y H:i')." WIB.\n\n"
                ."Jika Anda tidak merasa melakukan perubahan ini, segera hubungi pihak sekolah/administrator.\n\n"
                .'— SIAKAD Nurul Jadid Karduluk';

            $this->gateway->sendNotification($user->no_hp, $confirmMsg, 'password_changed');
        }

        return redirect()->route('login')->with('success', 'Password Anda berhasil diperbarui! Silakan masuk dengan password baru.');
    }

    /**
     * Alias untuk kompatibilitas ke belakang jika diperlukan.
     */
    public function verifyAndReset(Request $request): RedirectResponse
    {
        return $this->verifyOtp($request);
    }

    /**
     * Resolusi identifier ke model User (email, username, nip, atau no hp).
     */
    private function resolveUser(string $input): ?User
    {
        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            return User::where('email', $input)->first();
        }

        $lower = strtolower($input);

        if ($lower === 'admin') {
            return User::where('email', 'admin@siakadnuja.sch.id')->first();
        }

        if (preg_match('/^guru\d+$/i', $input)) {
            return User::where('email', $lower.'@siakadnuja.sch.id')->first();
        }

        // Cek jika NIP Guru
        $guru = Guru::where('nip', $input)->first();
        if ($guru !== null && $guru->user !== null) {
            return $guru->user;
        }

        // Cek jika nomor HP / WhatsApp
        $cleanPhone = preg_replace('/[^0-9]/', '', $input);
        if ($cleanPhone !== '') {
            $formatted = $this->gateway->normalisasiNomor($cleanPhone);
            $local = '0'.substr($formatted, 2);

            return User::where(function ($q) use ($cleanPhone, $formatted, $local): void {
                $q->where('no_hp', $cleanPhone)
                    ->orWhere('no_hp', $formatted)
                    ->orWhere('no_hp', $local);
            })->first();
        }

        return null;
    }

    /**
     * Mask nomor telepon untuk tampilan aman (misal 0813-****-**01).
     */
    private function maskPhoneNumber(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        $len = strlen($clean);

        if ($len <= 4) {
            return '****';
        }

        $prefix = substr($clean, 0, 4);
        $suffix = substr($clean, -2);

        return $prefix.'-****-**'.$suffix;
    }

    /**
     * Format detik ke teks manusiawi (contoh: "1 menit", "1 menit 15 detik", atau "45 detik").
     */
    private function formatSecondsToHuman(int|float $seconds): string
    {
        $seconds = (int) round($seconds);

        if ($seconds < 60) {
            return "{$seconds} detik";
        }

        $minutes = intdiv($seconds, 60);
        $remainingSeconds = $seconds % 60;

        if ($remainingSeconds === 0) {
            return "{$minutes} menit";
        }

        return "{$minutes} menit {$remainingSeconds} detik";
    }
}
