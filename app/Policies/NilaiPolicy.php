<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Nilai;
use App\Models\User;

class NilaiPolicy
{
    /**
     * Admin dapat melakukan apa saja.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isGuru();
    }

    public function view(User $user, Nilai $nilai): bool
    {
        return $this->mengampuKelasMapel($user, (int) $nilai->kelas_id, (int) $nilai->mapel_id)
            || $this->isWaliKelas($user, (int) $nilai->kelas_id);
    }

    public function create(User $user): bool
    {
        return $user->isGuru();
    }

    public function update(User $user, Nilai $nilai): bool
    {
        // Hanya guru pengampu mapel yang boleh mengubah nilai
        return $this->mengampuKelasMapel($user, (int) $nilai->kelas_id, (int) $nilai->mapel_id);
    }

    public function delete(User $user, Nilai $nilai): bool
    {
        // Hanya guru pengampu mapel yang boleh menghapus nilai
        return $this->mengampuKelasMapel($user, (int) $nilai->kelas_id, (int) $nilai->mapel_id);
    }

    /**
     * Cek apakah guru mengajar mapel di kelas tersebut sesuai jadwal.
     */
    public function mengampuKelasMapel(User $user, int $kelasId, int $mapelId): bool
    {
        $guru = $user->guru;

        if ($guru === null) {
            return false;
        }

        return $guru->isTeaching($kelasId, $mapelId);
    }

    /**
     * Cek apakah guru adalah wali kelas dari kelas tersebut.
     */
    public function isWaliKelas(User $user, int $kelasId): bool
    {
        $guru = $user->guru;

        if ($guru === null) {
            return false;
        }

        return $guru->isWaliKelasFor($kelasId);
    }
}
