<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\JadwalPelajaranRequest;
use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class JadwalPelajaranController extends Controller
{
    public function index(): View
    {
        $user = request()->user();
        $isGuru = $user?->isGuru();
        $userGuruId = $user?->guru?->id;

        $hasFilter = request()->hasAny(['search', 'guru_id', 'kelas_id', 'hari']);
        $selectedGuruId = request('guru_id');

        if (! $hasFilter && $isGuru && $userGuruId) {
            $selectedGuruId = (string) $userGuruId;
        }

        $jadwal = JadwalPelajaran::with(['kelas', 'mapel', 'guru'])
            ->when($selectedGuruId, fn ($query, $id) => $query->where('guru_id', $id))
            ->when(request('search'), function ($query, $search): void {
                $query->where(function ($q) use ($search): void {
                    $q->whereHas('mapel', fn ($sub) => $sub->where('nama_mapel', 'like', "%{$search}%"))
                      ->orWhereHas('kelas', fn ($sub) => $sub->where('nama_kelas', 'like', "%{$search}%")->orWhere('jenjang', 'like', "%{$search}%"))
                      ->orWhereHas('guru', fn ($sub) => $sub->where('nama_lengkap', 'like', "%{$search}%"))
                      ->orWhere('ruangan', 'like', "%{$search}%");
                });
            })
            ->when(request('kelas_id'), fn ($query, $id) => $query->where('kelas_id', $id))
            ->when(request('hari'), fn ($query, $hari) => $query->where('hari', $hari))
            ->orderByRaw("FIELD(hari, 'Sabtu','Minggu','Senin','Selasa','Rabu','Kamis')")
            ->orderBy('jam_ke')
            ->paginate(20)
            ->withQueryString();

        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $guruList = Guru::orderBy('nama_lengkap')->get();

        return view('jadwal.index', compact('jadwal', 'kelasList', 'guruList', 'selectedGuruId'));
    }

    public function create(): View
    {
        return view('jadwal.create', $this->formData());
    }

    public function store(JadwalPelajaranRequest $request): RedirectResponse
    {
        JadwalPelajaran::create($request->validated());

        return redirect()->route('jadwal.index')->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function show(JadwalPelajaran $jadwal): View
    {
        $jadwal->load('kelas', 'mapel', 'guru');

        return view('jadwal.show', compact('jadwal'));
    }

    public function edit(JadwalPelajaran $jadwal): View
    {
        return view('jadwal.edit', ['jadwal' => $jadwal] + $this->formData());
    }

    public function update(JadwalPelajaranRequest $request, JadwalPelajaran $jadwal): RedirectResponse
    {
        $jadwal->update($request->validated());

        return redirect()->route('jadwal.index')->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(JadwalPelajaran $jadwal): RedirectResponse
    {
        $jadwal->delete();

        return redirect()->route('jadwal.index')->with('success', 'Jadwal berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'kelas' => Kelas::orderBy('nama_kelas')->get(),
            'mapel' => MataPelajaran::orderBy('nama_mapel')->get(),
            'guru' => Guru::orderBy('nama_lengkap')->get(),
        ];
    }
}
