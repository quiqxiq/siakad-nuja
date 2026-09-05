<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Konfigurasi extends Model
{
    protected $table = 'konfigurasi';
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Helper to get a configuration value easily.
     */
    public static function get(string $key, $default = null)
    {
        $config = self::find($key);
        return $config ? $config->value : $default;
    }

    /**
     * Menghasilkan tahun ajaran aktif secara otomatis.
     * Logika:
     * 1. Setting di tabel konfigurasi (key: 'tahun_ajaran_aktif') jika diset admin.
     * 2. Tahun ajaran dari data kelas terbaru di database.
     * 3. Kalender akademik otomatis:
     *    - Juli - Desember (bulan 7-12) => Y/(Y+1) (contoh: 2026/2027)
     *    - Januari - Juni (bulan 1-6)   => (Y-1)/Y (contoh: 2026/2027)
     */
    public static function tahunAjaranAktif(): string
    {
        try {
            $custom = self::get('tahun_ajaran_aktif');
            if (! empty($custom)) {
                return (string) $custom;
            }

            $fromKelas = Kelas::orderByDesc('id')->value('tahun_ajaran');
            if (! empty($fromKelas)) {
                return (string) $fromKelas;
            }
        } catch (\Throwable) {
            // Fallback jika database belum migrate
        }

        $month = (int) date('n');
        $year = (int) date('Y');

        if ($month >= 7) {
            return $year . '/' . ($year + 1);
        }

        return ($year - 1) . '/' . $year;
    }

    /**
     * Menghasilkan semester aktif secara otomatis (Ganjil jika Juli-Des, Genap jika Jan-Jun).
     */
    public static function semesterAktif(): string
    {
        try {
            $custom = self::get('semester_aktif');
            if (! empty($custom)) {
                return (string) $custom;
            }
        } catch (\Throwable) {
            // Fallback
        }

        $month = (int) date('n');
        return ($month >= 7) ? 'Ganjil' : 'Genap';
    }

    /**
     * Menghasilkan daftar pilihan tahun ajaran (rentang beberapa tahun ke belakang & ke depan).
     *
     * @return array<string>
     */
    public static function daftarTahunAjaran(int $rentangSebelum = 2, int $rentangSesudah = 2): array
    {
        $current = self::tahunAjaranAktif();
        $parts = explode('/', $current);
        $startYear = isset($parts[0]) && is_numeric($parts[0]) ? (int) $parts[0] : (int) date('Y');

        $list = [];
        for ($y = $startYear - $rentangSebelum; $y <= $startYear + $rentangSesudah; $y++) {
            $list[] = $y . '/' . ($y + 1);
        }

        $list = array_unique(array_merge([$current], $list));
        sort($list);

        return array_values($list);
    }
}
