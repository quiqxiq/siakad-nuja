<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

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
        'ruangan',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $kelas): void {
            if (empty($kelas->ruangan)) {
                $kelas->ruangan = 'R-'.$kelas->nama_kelas.'-'.($kelas->jenjang ?: 'KLS');
            }
        });
    }

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
     * Mengambil daftar mata pelajaran untuk suatu kelas (dan guru tertentu jika ada).
     *
     * @return Collection<int, MataPelajaran>
     */
    public static function getMapelsForKelas(int|self $kelas, ?Guru $guru = null): Collection
    {
        $kelasModel = $kelas instanceof self ? $kelas : self::find($kelas);
        if (! $kelasModel) {
            return new Collection;
        }

        if ($guru !== null) {
            $teachingMapelIds = $guru->jadwal()
                ->where('kelas_id', $kelasModel->id)
                ->pluck('mapel_id')
                ->unique()
                ->values()
                ->all();

            return MataPelajaran::whereIn('id', $teachingMapelIds ?: [0])
                ->orderBy('nama_mapel')
                ->get();
        }

        $fromJadwal = JadwalPelajaran::where('kelas_id', $kelasModel->id)->pluck('mapel_id');
        $fromPivot = DB::table('kelas_mata_pelajaran')->where('kelas_id', $kelasModel->id)->pluck('mapel_id');
        $combinedIds = $fromJadwal->merge($fromPivot)->unique()->filter()->values()->all();

        if (! empty($combinedIds)) {
            return MataPelajaran::whereIn('id', $combinedIds)->orderBy('nama_mapel')->get();
        }

        // Fallback ke jenjang jika kelas baru belum memiliki jadwal atau data pivot kurikulum
        return MataPelajaran::where(function ($q) use ($kelasModel): void {
            $q->where('jenjang', $kelasModel->jenjang)->orWhere('jenjang', 'Semua');
        })->orderBy('nama_mapel')->get();
    }

    /**
     * Mengembalikan pemetaan array kelas_id => daftar mata pelajaran untuk form cascading.
     *
     * @param  array<int, int>|null  $accessibleMapelIds
     * @return array<int, array<int, array<string, mixed>>>
     */
    public static function getMapelByKelasMapping(?array $accessibleMapelIds = null, ?Guru $guru = null): array
    {
        $kelasList = static::orderBy('nama_kelas')->get();

        $mapping = [];
        foreach ($kelasList as $kelas) {
            $mapels = static::getMapelsForKelas($kelas, $guru);

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
    public function scopeAccessibleBy(Builder $query, ?User $user): Builder
    {
        if (! $user || $user->isAdmin()) {
            return $query;
        }

        $accessibleIds = $user->accessibleKelasIds();

        return $query->whereIn('id', $accessibleIds ?: [0]);
    }
}
