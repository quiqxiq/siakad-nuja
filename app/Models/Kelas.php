<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function mataPelajaran(): BelongsToMany
    {
        return $this->belongsToMany(MataPelajaran::class, 'kelas_mata_pelajaran', 'kelas_id', 'mapel_id')
            ->withTimestamps();
    }

    /**
     * Mengembalikan pemetaan array kelas_id => daftar mata pelajaran untuk form cascading.
     *
     * @param  array<int, int>|null  $accessibleMapelIds
     * @return array<int, array<int, array<string, mixed>>>
     */
    public static function getMapelByKelasMapping(?array $accessibleMapelIds = null): array
    {
        $kelasList = static::with(['mataPelajaran' => fn ($q) => $q->orderBy('nama_mapel')])->get();
        $allMapel = MataPelajaran::orderBy('nama_mapel')->get();

        $mapping = [];
        foreach ($kelasList as $kelas) {
            $mapels = $kelas->mataPelajaran;
            // Fallback ke jenjang jika belum ada relasi eksplisit di kelas_mata_pelajaran
            if ($mapels->isEmpty()) {
                $mapels = $allMapel->where('jenjang', $kelas->jenjang);
            }

            if ($accessibleMapelIds !== null) {
                $mapels = $mapels->whereIn('id', $accessibleMapelIds);
            }

            $mapping[$kelas->id] = $mapels->map(fn ($m) => [
                'id' => $m->id,
                'nama_mapel' => $m->nama_mapel,
                'kode_mapel' => $m->kode_mapel,
                'kkm' => $m->kkm,
                'jenjang' => $m->jenjang,
            ])->values()->all();
        }

        return $mapping;
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
