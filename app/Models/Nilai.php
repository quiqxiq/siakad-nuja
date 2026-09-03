<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nilai extends Model
{
    use HasFactory;

    protected $table = 'nilai';

    protected $fillable = [
        'siswa_id',
        'mapel_id',
        'kelas_id',
        'semester',
        'tahun_ajaran',
        'nilai_harian',
        'nilai_uts',
        'nilai_uas',
        'nilai_akhir',
        'predikat',
    ];

    protected function casts(): array
    {
        return [
            'nilai_harian' => 'decimal:2',
            'nilai_uts' => 'decimal:2',
            'nilai_uas' => 'decimal:2',
            'nilai_akhir' => 'decimal:2',
        ];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mapel_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    /**
     * Hitung nilai akhir berbobot adaptif:
     * - Bila Harian, UTS, UAS lengkap: Harian 30%, UTS 30%, UAS 40%.
     * - Bila hanya Harian & UTS (Tengah Semester): Harian 50%, UTS 50%.
     * - Bila hanya Harian & UAS: Harian 40%, UAS 60%.
     * - Bila hanya Harian: Harian 100%.
     * Mengembalikan null bila ketiga komponen kosong.
     */
    public static function hitungNilaiAkhir(float|int|string|null $harian, float|int|string|null $uts, float|int|string|null $uas): ?float
    {
        $h = ($harian !== null && $harian !== '') ? (float) $harian : null;
        $u = ($uts !== null && $uts !== '') ? (float) $uts : null;
        $a = ($uas !== null && $uas !== '') ? (float) $uas : null;

        if ($h === null && $u === null && $a === null) {
            return null;
        }

        if ($h !== null && $u !== null && $a !== null) {
            return round(($h * 0.3) + ($u * 0.3) + ($a * 0.4), 2);
        }

        if ($h !== null && $u !== null && $a === null) {
            return round(($h * 0.5) + ($u * 0.5), 2);
        }

        if ($h !== null && $u === null && $a !== null) {
            return round(($h * 0.4) + ($a * 0.6), 2);
        }

        if ($h === null && $u !== null && $a !== null) {
            return round(($u * 0.4) + ($a * 0.6), 2);
        }

        if ($h !== null) {
            return round($h, 2);
        }

        if ($u !== null) {
            return round($u, 2);
        }

        return round((float) $a, 2);
    }

    /**
     * Tentukan predikat huruf dari nilai akhir berdasarkan KKM mata pelajaran.
     * Interval: (100 - KKM) / 3
     */
    public static function hitungPredikat(float|int|string|null $nilaiAkhir, ?int $kkm = null): ?string
    {
        if ($nilaiAkhir === null || $nilaiAkhir === '') {
            return null;
        }

        $val = (float) $nilaiAkhir;

        if ($kkm !== null && $kkm > 0 && $kkm < 100) {
            $interval = (100 - $kkm) / 3.0;
            $minB = $kkm + $interval;
            $minA = $kkm + (2 * $interval);

            return match (true) {
                $val >= $minA => 'A',
                $val >= $minB => 'B',
                $val >= $kkm  => 'C',
                default       => 'D',
            };
        }

        return match (true) {
            $val >= 90 => 'A',
            $val >= 80 => 'B',
            $val >= 70 => 'C',
            $val >= 60 => 'D',
            default    => 'E',
        };
    }

    /**
     * Status kelulusan / ketuntasan nilai terhadap KKM mapel.
     */
    public function getStatusKetuntasanAttribute(): string
    {
        if ($this->nilai_akhir === null) {
            return 'Belum Dinilai';
        }

        $kkm = $this->mapel?->kkm ?? 75;

        return (float) $this->nilai_akhir >= $kkm ? 'Tuntas' : 'Belum Tuntas';
    }

    /**
     * Cek apakah seluruh komponen nilai (Harian, UTS, UAS) sudah lengkap diisi.
     */
    public function isLengkap(): bool
    {
        return $this->nilai_harian !== null && $this->nilai_uts !== null && $this->nilai_uas !== null;
    }
}
