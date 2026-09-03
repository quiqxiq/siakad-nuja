<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guru extends Model
{
    use HasFactory;

    protected $table = 'guru';

    protected $fillable = [
        'user_id',
        'nip',
        'nama_lengkap',
        'jabatan',
        'no_hp',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kelasWali(): HasMany
    {
        return $this->hasMany(Kelas::class, 'wali_kelas_id');
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(JadwalPelajaran::class, 'guru_id');
    }

    /**
     * ID seluruh kelas yang dapat diakses guru (kelas ajar + kelas perwalian).
     *
     * @return array<int>
     */
    public function accessibleKelasIds(): array
    {
        $diampu = $this->jadwal()->pluck('kelas_id')->all();
        $diwalikan = $this->kelasWali()->pluck('id')->all();

        return array_values(array_unique([...$diampu, ...$diwalikan]));
    }

    /**
     * ID kelas yang diajar oleh guru ini di jadwal pelajaran.
     *
     * @return array<int>
     */
    public function teachingKelasIds(): array
    {
        return $this->jadwal()->pluck('kelas_id')->unique()->values()->all();
    }

    /**
     * ID mata pelajaran yang diajar oleh guru ini di jadwal pelajaran.
     *
     * @return array<int>
     */
    public function teachingMapelIds(): array
    {
        return $this->jadwal()->pluck('mapel_id')->unique()->values()->all();
    }

    /**
     * ID kelas yang diwalikan oleh guru ini.
     *
     * @return array<int>
     */
    public function waliKelasIds(): array
    {
        return $this->kelasWali()->pluck('id')->values()->all();
    }

    /**
     * Cek apakah guru adalah wali kelas dari kelas tertentu.
     */
    public function isWaliKelasFor(int $kelasId): bool
    {
        return $this->kelasWali()->where('id', $kelasId)->exists();
    }

    /**
     * Cek apakah guru mengajar mapel tertentu di kelas tertentu.
     */
    public function isTeaching(int $kelasId, int $mapelId): bool
    {
        return $this->jadwal()
            ->where('kelas_id', $kelasId)
            ->where('mapel_id', $mapelId)
            ->exists();
    }
}
