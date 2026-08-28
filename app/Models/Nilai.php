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
     * Hitung nilai akhir berbobot: harian 30%, UTS 30%, UAS 40%.
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

        $hVal = $h ?? 0.0;
        $uVal = $u ?? 0.0;
        $aVal = $a ?? 0.0;

        return round(($hVal * 0.3) + ($uVal * 0.3) + ($aVal * 0.4), 2);
    }

    /**
     * Tentukan predikat huruf dari nilai akhir.
     */
    public static function hitungPredikat(float|int|string|null $nilaiAkhir): ?string
    {
        if ($nilaiAkhir === null || $nilaiAkhir === '') {
            return null;
        }

        $val = (float) $nilaiAkhir;

        return match (true) {
            $val >= 90 => 'A',
            $val >= 80 => 'B',
            $val >= 70 => 'C',
            $val >= 60 => 'D',
            default => 'E',
        };
    }
}
