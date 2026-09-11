<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Titik Lokasi & Geofencing Absensi
    |--------------------------------------------------------------------------
    |
    | Menentukan titik pusat lokasi madrasah untuk verifikasi kehadiran.
    | Absensi (oleh guru maupun admin) dibatasi dalam radius tertentu dari titik ini.
    |
    */
    'lokasi' => [
        'nama' => env('ABSENSI_LOKASI_NAMA', 'MI Nurul Jadid Karduluk'),
        'alamat' => env('ABSENSI_LOKASI_ALAMAT', 'WP86+5FG, Bapelle, Karduluk, Kec. Pragaan, Kabupaten Sumenep, Jawa Timur 69465'),
        'latitude' => (float) env('ABSENSI_LATITUDE', -7.0845556),
        'longitude' => (float) env('ABSENSI_LONGITUDE', 113.7112031),
        'radius_meter' => (int) env('ABSENSI_RADIUS_METER', 50),
        'maps_url' => env('ABSENSI_MAPS_URL', 'https://www.google.com/maps/place/MI+NURUL+JADID+KARDULUK/@-7.0845658,113.7109238,21z/data=!4m10!1m2!2m1!1snurul+jadid+karduluk!3m6!1s0x2dd9df86f7544b83:0xfafd1e3515a86246!8m2!3d-7.0845556!4d113.7112031'),
    ],
];
