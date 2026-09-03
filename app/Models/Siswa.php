<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';

    protected $fillable = [
        'nis',
        'nama_lengkap',
        'kelas_id',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'foto',
        'status',
        'tahun_masuk',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function orangTua(): HasMany
    {
        return $this->hasMany(OrangTua::class, 'siswa_id');
    }

    public function nilai(): HasMany
    {
        return $this->hasMany(Nilai::class, 'siswa_id');
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class, 'siswa_id');
    }

    /**
     * Dapatkan kontak utama wali murid untuk pengiriman notifikasi WA.
     * Mengutamakan yang di-flag is_kontak_utama = true, jika tidak ada mengambil kontak pertama yang punya no_wa/no_hp.
     */
    public function getKontakUtamaWali(): ?OrangTua
    {
        return OrangTua::where('siswa_id', $this->id)
            ->where(function ($q): void {
                $q->whereNotNull('no_wa')->orWhereNotNull('no_hp');
            })
            ->orderByDesc('is_kontak_utama')
            ->first();
    }

    /**
     * Scope query untuk membatasi siswa hanya pada kelas yang dapat diakses oleh user.
     */
    public function scopeAccessibleBy(\Illuminate\Database\Eloquent\Builder $query, ?User $user): \Illuminate\Database\Eloquent\Builder
    {
        if (! $user || $user->isAdmin()) {
            return $query;
        }

        $accessibleIds = $user->accessibleKelasIds();

        return $query->whereIn('kelas_id', $accessibleIds ?: [0]);
    }
}
