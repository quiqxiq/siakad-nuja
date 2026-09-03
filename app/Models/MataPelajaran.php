<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MataPelajaran extends Model
{
    use HasFactory;

    protected $table = 'mata_pelajaran';

    protected $fillable = [
        'kode_mapel',
        'nama_mapel',
        'jenjang',
        'kkm',
        'deskripsi',
    ];

    public function jadwal(): HasMany
    {
        return $this->hasMany(JadwalPelajaran::class, 'mapel_id');
    }

    public function nilai(): HasMany
    {
        return $this->hasMany(Nilai::class, 'mapel_id');
    }

    /**
     * Scope query untuk membatasi mapel hanya pada yang diajar oleh guru.
     */
    public function scopeAccessibleBy(\Illuminate\Database\Eloquent\Builder $query, ?User $user): \Illuminate\Database\Eloquent\Builder
    {
        if (! $user || $user->isAdmin()) {
            return $query;
        }

        $mapelIds = $user->guru?->teachingMapelIds() ?? [];

        return $query->whereIn('id', $mapelIds ?: [0]);
    }
}
