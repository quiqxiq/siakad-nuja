<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with('guru')
            ->when(request('q'), function ($query, string $q): void {
                $query->where('nama', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%");
            })
            ->when(request('role'), fn ($query, $role) => $query->where('role', $role))
            ->when(request()->filled('status'), function ($query): void {
                $query->where('is_active', request('status') === '1');
            })
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated): void {
            $user = User::create([
                'nama' => $validated['nama'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'no_hp' => $validated['no_hp'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            if ($validated['role'] === User::ROLE_GURU) {
                Guru::create([
                    'user_id' => $user->id,
                    'nip' => $validated['nip'],
                    'nama_lengkap' => $validated['nama'],
                    'jabatan' => $validated['jabatan'] ?? null,
                    'no_hp' => $validated['no_hp'] ?? null,
                ]);
            }
        });

        if (! empty($validated['kirim_wa']) && ! empty($validated['no_hp'])) {
            $roleLabel = $validated['role'] === User::ROLE_ADMIN ? 'Administrator' : 'Guru';
            $loginUrl  = route('login');

            $pesan  = "🔐 *Informasi Akun SIAKAD Nurul Jadid*\n\n";
            $pesan .= "Yth. Bpk/Ibu *{$validated['nama']}*,\n\n";
            $pesan .= "Berikut adalah informasi akun Anda untuk mengakses sistem SIAKAD Nurul Jadid Karduluk:\n";
            $pesan .= "• *Peran (Role)*: {$roleLabel}\n";
            $pesan .= "• *Email*: {$validated['email']}\n";
            $pesan .= "• *Password*: {$validated['password']}\n";
            $pesan .= "• *URL Login*: {$loginUrl}\n\n";
            $pesan .= "Mohon untuk menjaga kerahasiaan informasi akun Anda.\n\n";
            $pesan .= "— SIAKAD Nurul Jadid Karduluk";

            \App\Jobs\SendWhatsappMessage::dispatch($validated['no_hp'], $pesan);
        }

        return redirect()->route('users.index')->with('success', 'Akun pengguna berhasil dibuat.');
    }

    public function edit(User $user): View
    {
        $user->load('guru');

        return view('users.edit', compact('user'));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($user, $validated): void {
            $data = [
                'nama' => $validated['nama'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'no_hp' => $validated['no_hp'] ?? null,
                'is_active' => $validated['is_active'] ?? false,
            ];

            if (! empty($validated['password'])) {
                $data['password'] = Hash::make($validated['password']);
            }

            $user->update($data);

            if ($validated['role'] === User::ROLE_GURU) {
                $user->guru()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nip' => $validated['nip'],
                        'nama_lengkap' => $validated['nama'],
                        'jabatan' => $validated['jabatan'] ?? null,
                        'no_hp' => $validated['no_hp'] ?? null,
                    ],
                );
            }
        });

        if (! empty($validated['kirim_wa']) && ! empty($validated['no_hp']) && ! empty($validated['password'])) {
            $roleLabel = $validated['role'] === User::ROLE_ADMIN ? 'Administrator' : 'Guru';
            $loginUrl  = route('login');

            $pesan  = "🔐 *Pembaruan Kredensial Akun SIAKAD*\n\n";
            $pesan .= "Yth. Bpk/Ibu *{$validated['nama']}*,\n\n";
            $pesan .= "Kredensial akun SIAKAD Nurul Jadid Karduluk Anda telah diperbarui:\n";
            $pesan .= "• *Email*: {$validated['email']}\n";
            $pesan .= "• *Password Baru*: {$validated['password']}\n";
            $pesan .= "• *URL Login*: {$loginUrl}\n\n";
            $pesan .= "— SIAKAD Nurul Jadid Karduluk";

            \App\Jobs\SendWhatsappMessage::dispatch($validated['no_hp'], $pesan);
        }

        return redirect()->route('users.index')->with('success', 'Akun pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === request()->user()->id) {
            return redirect()->route('users.index')->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        DB::transaction(function () use ($user): void {
            $user->guru?->delete();
            $user->delete();
        });

        return redirect()->route('users.index')->with('success', 'Akun pengguna berhasil dihapus.');
    }
}
