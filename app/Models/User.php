<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_GURU = 'guru';

    protected $fillable = [
        'nama',
        'email',
        'password',
        'role',
        'no_hp',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function getNameAttribute(): string
    {
        return $this->attributes['nama'] ?? '';
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isGuru(): bool
    {
        return $this->role === self::ROLE_GURU;
    }

    public function isWaliKelas(): bool
    {
        return $this->isGuru() && ($this->guru?->kelasWali()->exists() ?? false);
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function guru(): HasOne
    {
        return $this->hasOne(Guru::class);
    }

    public function pengumuman(): HasMany
    {
        return $this->hasMany(Pengumuman::class, 'dibuat_oleh');
    }

    /**
     * ID seluruh kelas yang dapat diakses oleh user ini.
     *
     * @return array<int>|null Null bila admin (akses semua), array ID bila guru.
     */
    public function accessibleKelasIds(): ?array
    {
        if ($this->isAdmin()) {
            return null;
        }

        return $this->guru?->accessibleKelasIds() ?? [];
    }
}
