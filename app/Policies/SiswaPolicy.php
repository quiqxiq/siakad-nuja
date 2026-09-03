<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Siswa;
use App\Models\User;

class SiswaPolicy
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
        return $user->isAdmin() || $user->isGuru();
    }

    public function view(User $user, Siswa $siswa): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $accessibleIds = $user->accessibleKelasIds() ?? [];

        return in_array((int) $siswa->kelas_id, $accessibleIds, true);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Siswa $siswa): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Siswa $siswa): bool
    {
        return $user->isAdmin();
    }

    public function kirimTeguran(User $user, Siswa $siswa): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $accessibleIds = $user->accessibleKelasIds() ?? [];

        return in_array((int) $siswa->kelas_id, $accessibleIds, true);
    }
}
