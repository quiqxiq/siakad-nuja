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
        //
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
