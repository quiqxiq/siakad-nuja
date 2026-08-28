<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\NilaiRequest;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\Siswa;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class NilaiController extends Controller
{
    public function index(): View
    {
        $user = request()->user();

        $nilai = Nilai::with(['siswa', 'mapel', 'kelas'])
            ->when($user?->isGuru(), function ($query) use ($user): void {
                // Guru hanya melihat nilai kelas/mapel yang ia ampu atau ia walikan.
                $guru = $user->guru;
                $kelasMapel = $guru?->jadwal()->get(['kelas_id', 'mapel_id']) ?? collect();
                $kelasWali = $guru?->kelasWali()->pluck('id') ?? collect();

                $query->where(function ($q) use ($kelasMapel, $kelasWali): void {
                    foreach ($kelasMapel as $km) {
                        $q->orWhere(fn ($sub) => $sub->where('kelas_id', $km->kelas_id)->where('mapel_id', $km->mapel_id));
                    }
                    if ($kelasWali->isNotEmpty()) {
                        $q->orWhereIn('kelas_id', $kelasWali->all());
                    }
                    if ($kelasMapel->isEmpty() && $kelasWali->isEmpty()) {
                        $q->whereRaw('1 = 0');
                    }
                });
            })
            ->when(request('kelas_id'), fn ($query, $id) => $query->where('kelas_id', $id))
            ->when(request('search'), function ($query, $search): void {
                $query->whereHas('siswa', function ($q) use ($search): void {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                      ->orWhere('nis', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $kelasList = Kelas::orderBy('nama_kelas')->get();

        return view('nilai.index', compact('nilai', 'kelasList'));
    }

    public function create(): View
    {
        $this->authorize('create', Nilai::class);

        return view('nilai.create', $this->formData());
    }

    public function store(NilaiRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->authorize('create', Nilai::class);
        $this->authorizeScope((int) $validated['kelas_id'], (int) $validated['mapel_id']);

        $validated = $this->withCalculatedGrades($validated);

        Nilai::create($validated);

        return redirect()->route('nilai.index')->with('success', 'Nilai berhasil ditambahkan.');
    }

    public function show(Nilai $nilai): View
    {
        $this->authorize('view', $nilai);

        $nilai->load('siswa', 'mapel', 'kelas');

        return view('nilai.show', compact('nilai'));
    }

    public function edit(Nilai $nilai): View
    {
        $this->authorize('update', $nilai);

        return view('nilai.edit', ['nilai' => $nilai] + $this->formData());
    }

    public function update(NilaiRequest $request, Nilai $nilai): RedirectResponse
    {
        $this->authorize('update', $nilai);

        $validated = $request->validated();
        $this->authorizeScope((int) $validated['kelas_id'], (int) $validated['mapel_id']);

        $validated = $this->withCalculatedGrades($validated);

        $nilai->update($validated);

        return redirect()->route('nilai.index')->with('success', 'Nilai berhasil diperbarui.');
    }

    public function destroy(Nilai $nilai): RedirectResponse
    {
        $this->authorize('delete', $nilai);

        $nilai->delete();

        return redirect()->route('nilai.index')->with('success', 'Nilai berhasil dihapus.');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withCalculatedGrades(array $data): array
    {
        $harian = isset($data['nilai_harian']) && $data['nilai_harian'] !== '' && $data['nilai_harian'] !== null ? (float) $data['nilai_harian'] : null;
        $uts = isset($data['nilai_uts']) && $data['nilai_uts'] !== '' && $data['nilai_uts'] !== null ? (float) $data['nilai_uts'] : null;
        $uas = isset($data['nilai_uas']) && $data['nilai_uas'] !== '' && $data['nilai_uas'] !== null ? (float) $data['nilai_uas'] : null;

        $akhir = Nilai::hitungNilaiAkhir($harian, $uts, $uas);

        $data['nilai_akhir'] = $akhir;
        $data['predikat'] = Nilai::hitungPredikat($akhir);

        return $data;
    }

    /**
     * Pastikan guru hanya menyimpan nilai untuk kelas/mapel miliknya.
     */
    private function authorizeScope(int $kelasId, int $mapelId): void
    {
        $temp = new Nilai(['kelas_id' => $kelasId, 'mapel_id' => $mapelId]);
        $this->authorize('update', $temp);
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'siswa' => Siswa::orderBy('nama_lengkap')->get(),
            'mapel' => MataPelajaran::orderBy('nama_mapel')->get(),
            'kelas' => Kelas::orderBy('nama_kelas')->get(),
        ];
    }
}
