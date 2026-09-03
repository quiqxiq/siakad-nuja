<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\KelasRequest;
use App\Models\Guru;
use App\Models\Kelas;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class KelasController extends Controller
{
    public function index(): View
    {
        $user = request()->user();

        $kelas = Kelas::with('waliKelas')
            ->accessibleBy($user)
            ->withCount('siswa')
            ->when(request('q'), fn ($query, string $q) => $query->where('nama_kelas', 'like', "%{$q}%"))
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->paginate(15)
            ->withQueryString();

        return view('kelas.index', compact('kelas'));
    }

    public function create(): View
    {
        $guru = Guru::orderBy('nama_lengkap')->get();

        return view('kelas.create', compact('guru'));
    }

    public function store(KelasRequest $request): RedirectResponse
    {
        Kelas::create($request->validated());

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function show(Kelas $kela): View
    {
        $user = request()->user();
        if ($user?->isGuru()) {
            $accessible = $user->accessibleKelasIds() ?? [];
            if (! in_array($kela->id, $accessible, true)) {
                abort(403, 'Anda tidak memiliki akses ke data kelas ini.');
            }
        }

        $kela->load('waliKelas', 'siswa');

        return view('kelas.show', ['kelas' => $kela]);
    }

    public function edit(Kelas $kela): View
    {
        $guru = Guru::orderBy('nama_lengkap')->get();

        return view('kelas.edit', ['kelas' => $kela, 'guru' => $guru]);
    }

    public function update(KelasRequest $request, Kelas $kela): RedirectResponse
    {
        $kela->update($request->validated());

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kela): RedirectResponse
    {
        $kela->delete();

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil dihapus.');
    }
}
