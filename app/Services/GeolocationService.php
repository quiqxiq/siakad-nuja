<?php

declare(strict_types=1);

namespace App\Services;

class GeolocationService
{
    /**
     * Radius rata-rata bumi dalam satuan meter.
     */
    public const EARTH_RADIUS_METERS = 6371000.0;

    /**
     * Ambil informasi konfigurasi lokasi madrasah.
     *
     * @return array{
     *     nama: string,
     *     alamat: string,
     *     latitude: float,
     *     longitude: float,
     *     radius_meter: int,
     *     maps_url: string
     * }
     */
    public function getMadrasahConfig(): array
    {
        return [
            'nama' => (string) config('absensi.lokasi.nama', 'MI Nurul Jadid Karduluk'),
            'alamat' => (string) config('absensi.lokasi.alamat', 'WP86+5FG, Bapelle, Karduluk, Kec. Pragaan, Kabupaten Sumenep, Jawa Timur 69465'),
            'latitude' => (float) config('absensi.lokasi.latitude', -7.0845556),
            'longitude' => (float) config('absensi.lokasi.longitude', 113.7112031),
            'radius_meter' => (int) config('absensi.lokasi.radius_meter', 50),
            'maps_url' => (string) config('absensi.lokasi.maps_url', 'https://www.google.com/maps/place/MI+NURUL+JADID+KARDULUK/@-7.0845658,113.7109238,21z/data=!4m10!1m2!2m1!1snurul+jadid+karduluk!3m6!1s0x2dd9df86f7544b83:0xfafd1e3515a86246!8m2!3d-7.0845556!4d113.7112031'),
        ];
    }

    /**
     * Hitung jarak antara dua koordinat lintang & bujur menggunakan rumus Haversine.
     *
     * @return float Jarak dalam satuan meter (dibulatkan 2 desimal)
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)
        ));

        return round($angle * self::EARTH_RADIUS_METERS, 2);
    }

    /**
     * Hitung jarak koordinat saat ini ke titik pusat MI Nurul Jadid Karduluk dalam meter.
     */
    public function getDistanceFromMadrasah(float $lat, float $lon): float
    {
        $config = $this->getMadrasahConfig();

        return $this->calculateDistance($lat, $lon, $config['latitude'], $config['longitude']);
    }

    /**
     * Cek apakah koordinat berada dalam batas radius madrasah.
     */
    public function isWithinMadrasahRadius(float $lat, float $lon): bool
    {
        $config = $this->getMadrasahConfig();
        $distance = $this->calculateDistance($lat, $lon, $config['latitude'], $config['longitude']);

        return $distance <= $config['radius_meter'];
    }

    /**
     * Validasi lokasi pengisian absensi dengan respons terstruktur.
     *
     * @return array{
     *     valid: bool,
     *     distance: ?float,
     *     max_radius: int,
     *     target_name: string,
     *     message: ?string
     * }
     */
    public function validateAttendanceLocation(?float $lat, ?float $lon): array
    {
        $config = $this->getMadrasahConfig();

        if ($lat === null || $lon === null || ! is_finite($lat) || ! is_finite($lon)) {
            return [
                'valid' => false,
                'distance' => null,
                'max_radius' => $config['radius_meter'],
                'target_name' => $config['nama'],
                'message' => "Koordinat lokasi GPS diperlukan untuk memverifikasi bahwa Anda berada di {$config['nama']}.",
            ];
        }

        // Cek batasan koordinat bumi
        if ($lat < -90.0 || $lat > 90.0 || $lon < -180.0 || $lon > 180.0) {
            return [
                'valid' => false,
                'distance' => null,
                'max_radius' => $config['radius_meter'],
                'target_name' => $config['nama'],
                'message' => 'Koordinat GPS yang diterima tidak valid.',
            ];
        }

        $distance = $this->calculateDistance($lat, $lon, $config['latitude'], $config['longitude']);

        if ($distance > $config['radius_meter']) {
            $formattedDistance = number_format($distance, 1, ',', '.');

            return [
                'valid' => false,
                'distance' => $distance,
                'max_radius' => $config['radius_meter'],
                'target_name' => $config['nama'],
                'message' => "Absensi ditolak. Anda berada {$formattedDistance} meter dari titik {$config['nama']} (radius maksimal {$config['radius_meter']} meter).",
            ];
        }

        return [
            'valid' => true,
            'distance' => $distance,
            'max_radius' => $config['radius_meter'],
            'target_name' => $config['nama'],
            'message' => null,
        ];
    }
}
