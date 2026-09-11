<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Absensi;
use App\Models\Nilai;
use App\Models\Pengumuman;
use App\Models\Tagihan;
use App\Observers\AbsensiObserver;
use App\Observers\NilaiObserver;
use App\Observers\PengumumanObserver;
use App\Observers\TagihanObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\Illuminate\Foundation\Vite::class, function () {
            return new class extends \Illuminate\Foundation\Vite {
                protected function hotAsset($asset)
                {
                    $url = rtrim(file_get_contents($this->hotFile()));

                    // Ganti host agar sesuai dengan alamat yang sedang diakses (IP LAN atau localhost)
                    $requestHost = request()->getHost();
                    if ($requestHost) {
                        $parsed = parse_url($url);
                        $port = $parsed['port'] ?? 5173;
                        $scheme = $parsed['scheme'] ?? 'http';
                        $url = "{$scheme}://{$requestHost}:{$port}";
                    } elseif (str_contains($url, '[::]') || str_contains($url, '0.0.0.0')) {
                        $url = str_replace(['[::]', '0.0.0.0'], 'localhost', $url);
                    }

                    return $url.'/'.$asset;
                }
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useTailwind();

        // Register Policies
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Siswa::class, \App\Policies\SiswaPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Nilai::class, \App\Policies\NilaiPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Absensi::class, \App\Policies\AbsensiPolicy::class);

        // Register Observers untuk Notifikasi WhatsApp Otomatis
        Absensi::observe(AbsensiObserver::class);
        Nilai::observe(NilaiObserver::class);
        Tagihan::observe(TagihanObserver::class);
        Pengumuman::observe(PengumumanObserver::class);

        if (class_exists(\Kstmostofa\LaravelWhatsApp\Events\Web\MessageReceived::class)) {
            \Illuminate\Support\Facades\Event::listen(
                \Kstmostofa\LaravelWhatsApp\Events\Web\MessageReceived::class,
                \App\Listeners\WhatsappMessageListener::class
            );
        }
    }
}
