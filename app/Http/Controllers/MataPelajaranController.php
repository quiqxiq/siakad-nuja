<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\MataPelajaranRequest;
use App\Models\MataPelajaran;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class MataPelajaranController extends Controller
{
    public function index(): View
    {
        $user = request()->user();

        $mapel = MataPelajaran::accessibleBy($user)
            ->when(request('q'), function ($query, string $q): void {
                $query->where('nama_mapel', 'like', "%{$q}%")
                    ->orWhere('kode_mapel', 'like', "%{$q}%");
            })
            ->orderBy('nama_mapel')
            ->paginate(15)
            ->withQueryString();

        return view('mata_pelajaran.index', compact('mapel'));
    }

    public function create(): View
    {
        return view('mata_pelajaran.create');
    }

    public function store(MataPelajaranRequest $request): RedirectResponse
    {
        MataPelajaran::create($request->validated());

        return redirect()->route('mata-pelajaran.index')->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function show(MataPelajaran $mataPelajaran): View
    {
        return view('mata_pelajaran.show', ['mapel' => $mataPelajaran]);
    }

    public function edit(MataPelajaran $mataPelajaran): View
    {
        return view('mata_pelajaran.edit', ['mapel' => $mataPelajaran]);
    }

    public function update(MataPelajaranRequest $request, MataPelajaran $mataPelajaran): RedirectResponse
    {
        $mataPelajaran->update($request->validated());

        return redirect()->route('mata-pelajaran.index')->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(MataPelajaran $mataPelajaran): RedirectResponse
    {
        $mataPelajaran->delete();

        return redirect()->route('mata-pelajaran.index')->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
