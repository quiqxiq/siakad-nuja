<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'nama_kelas',
        'tingkat',
        'jenjang',
        'tahun_ajaran',
        'wali_kelas_id',
        'kapasitas',
    ];

    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'wali_kelas_id');
    }

    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class, 'kelas_id');
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(JadwalPelajaran::class, 'kelas_id');
    }

    public function nilai(): HasMany
    {
        return $this->hasMany(Nilai::class, 'kelas_id');
    }

    /**
     * Label nama lengkap kelas beserta jenjang (contoh: "MI 1" atau "MTs 7").
     */
    public function getNamaLengkapAttribute(): string
    {
        if ($this->jenjang) {
            return "{$this->jenjang} {$this->nama_kelas}";
        }

        return "Kelas {$this->nama_kelas}";
    }

    /**
     * Scope query untuk membatasi kelas hanya pada yang dapat diakses user.
     */
    public function scopeAccessibleBy(\Illuminate\Database\Eloquent\Builder $query, ?User $user): \Illuminate\Database\Eloquent\Builder
    {
        if (! $user || $user->isAdmin()) {
            return $query;
        }

        $accessibleIds = $user->accessibleKelasIds();

        return $query->whereIn('id', $accessibleIds ?: [0]);
    }
}
