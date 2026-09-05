<?php

declare(strict_types=1);

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\JadwalPelajaranController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MataPelajaranController;
use App\Http\Controllers\NilaiController;
use App\Http\Controllers\OrangTuaController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\PerwalianController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WhatsappController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('landing');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil & ganti password (semua peran)
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');

    /*
    |--------------------------------------------------------------------------
    | Modul akademik guru: input Nilai & Absensi (admin + guru)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin,guru')->group(function (): void {
        // Buku Leger Nilai & Peringkat Kelas (Khusus Admin)
        Route::get('nilai/leger', [NilaiController::class, 'leger'])->name('nilai.leger')->middleware('role:admin');
        Route::get('nilai/leger/export', [NilaiController::class, 'exportLeger'])->name('nilai.leger.export')->middleware('role:admin');

        // Entri Nilai Massal (Matrix Form) untuk Guru Pengampu & Admin
        Route::get('nilai/matrix', [NilaiController::class, 'matrix'])->name('nilai.matrix');
        Route::post('nilai/matrix', [NilaiController::class, 'storeMatrix'])->name('nilai.matrix.store');

        Route::resource('nilai', NilaiController::class);

        // Absensi: alur entri massal (bukan resource standar)
        Route::get('absensi', [AbsensiController::class, 'index'])->name('absensi.index');
        Route::get('absensi/create', [AbsensiController::class, 'create'])->name('absensi.create');
        Route::get('absensi/roster', [AbsensiController::class, 'roster'])->name('absensi.roster');
        Route::post('absensi', [AbsensiController::class, 'store'])->name('absensi.store');
        Route::get('absensi/{absensi}', [AbsensiController::class, 'show'])->name('absensi.show');
        Route::delete('absensi/{absensi}', [AbsensiController::class, 'destroy'])->name('absensi.destroy');

        // Kirim Teguran WA per Siswa ke Wali
        Route::post('siswa/{siswa}/teguran', [SiswaController::class, 'kirimTeguran'])->name('siswa.teguran');
    });

    /*
    |--------------------------------------------------------------------------
    | Manajemen data master, laporan akademik & akun (admin saja)
    |--------------------------------------------------------------------------
    | Didaftarkan SEBELUM rute read-only agar `.../create` & `.../{id}/edit`
    | tidak tertangkap oleh rute show (`.../{id}`) yang berpola sama.
    */
    Route::middleware('role:admin')->group(function (): void {
        // Laporan Akademik (Khusus Admin)
        Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('laporan/kehadiran', [LaporanController::class, 'kehadiran'])->name('laporan.kehadiran');
        Route::get('laporan/nilai', [LaporanController::class, 'nilai'])->name('laporan.nilai');
        Route::get('laporan/jadwal', [LaporanController::class, 'jadwal'])->name('laporan.jadwal');

        Route::resource('siswa', SiswaController::class);
        Route::resource('kelas', KelasController::class);
        Route::resource('mata-pelajaran', MataPelajaranController::class)
            ->parameters(['mata-pelajaran' => 'mataPelajaran']);
        Route::resource('jadwal', JadwalPelajaranController::class)->except(['index', 'show']);
        Route::resource('pengumuman', PengumumanController::class)->except(['index', 'show']);

        Route::resource('guru', GuruController::class);
        Route::resource('orang-tua', OrangTuaController::class)->parameters([
            'orang-tua' => 'orangTua',
        ]);
        Route::resource('users', UserController::class)->except(['show']);

        // Tagihan & Pembayaran (admin only)
        Route::resource('tagihan', TagihanController::class);
        Route::post('tagihan/pembayaran/{pembayaran}/verifikasi', [TagihanController::class, 'verifikasi'])
            ->name('tagihan.verifikasi');
        Route::post('tagihan/pembayaran/{pembayaran}/tolak', [TagihanController::class, 'tolak'])
            ->name('tagihan.tolak');

        // WhatsApp Admin Panel (admin only)
        Route::prefix('whatsapp')->name('whatsapp.')->group(function (): void {
            Route::get('/', [WhatsappController::class, 'index'])->name('index');
            Route::get('/status', [WhatsappController::class, 'statusAjax'])->name('status');
            Route::post('/login', [WhatsappController::class, 'login'])->name('login');
            Route::post('/pairing-code', [WhatsappController::class, 'pairingCode'])->name('pairing-code');
            Route::post('/logout', [WhatsappController::class, 'logout'])->name('logout');
            Route::post('/reconnect', [WhatsappController::class, 'reconnect'])->name('reconnect');
            Route::get('/templates', [WhatsappController::class, 'templates'])->name('templates');
            Route::put('/templates', [WhatsappController::class, 'updateTemplates'])->name('templates.update');
            Route::get('/templates/{key}/edit', [WhatsappController::class, 'editTemplate'])->name('templates.edit');
            Route::put('/templates/{key}', [WhatsappController::class, 'updateSingleTemplate'])->name('templates.single-update');
            Route::get('/log-notifikasi', [WhatsappController::class, 'logNotifikasi'])->name('log-notifikasi');
            Route::post('/resend/{notifikasi}', [WhatsappController::class, 'resend'])->name('resend');
            Route::get('/log-chatbot', [WhatsappController::class, 'logChatbot'])->name('log-chatbot');

            // Chatbot Rules Management
            Route::get('/chatbot-rules', [WhatsappController::class, 'chatbotRules'])->name('chatbot-rules');
            Route::get('/chatbot-rules/create', [WhatsappController::class, 'createChatbotRule'])->name('chatbot-rules.create');
            Route::post('/chatbot-rules', [WhatsappController::class, 'storeChatbotRule'])->name('chatbot-rules.store');
            Route::get('/chatbot-rules/{rule}/edit', [WhatsappController::class, 'editChatbotRule'])->name('chatbot-rules.edit');
            Route::put('/chatbot-rules/{rule}', [WhatsappController::class, 'updateChatbotRule'])->name('chatbot-rules.update');
            Route::delete('/chatbot-rules/{rule}', [WhatsappController::class, 'destroyChatbotRule'])->name('chatbot-rules.destroy');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Data operasional guru: Jadwal, Pengumuman & Ruang Perwalian (Wali Kelas)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin,guru')->group(function (): void {
        Route::resource('jadwal', JadwalPelajaranController::class)->only(['index', 'show']);
        Route::resource('pengumuman', PengumumanController::class)->only(['index', 'show']);

        // Ruang Perwalian (Wali Kelas & Admin)
        Route::get('perwalian', [PerwalianController::class, 'index'])->name('perwalian.index');
        Route::get('perwalian/{kelas}', [PerwalianController::class, 'show'])->name('perwalian.show');
    });
});
