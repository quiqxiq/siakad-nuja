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
        $guru = $user?->guru;
        $guruId = $guru?->id;

        $tab = request('tab', 'saya'); // 'saya', 'perwalian', 'semua'

        $jadwal = JadwalPelajaran::with(['kelas', 'mapel', 'guru'])
            ->when($isGuru, fn ($query) => $query->where('guru_id', $guruId ?? 0))
            ->when(! $isGuru && request('guru_id'), fn ($query, $id) => $query->where('guru_id', $id))
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
            ->orderByRaw("CASE hari WHEN 'Sabtu' THEN 1 WHEN 'Minggu' THEN 2 WHEN 'Ahad' THEN 2 WHEN 'Senin' THEN 3 WHEN 'Selasa' THEN 4 WHEN 'Rabu' THEN 5 WHEN 'Kamis' THEN 6 ELSE 7 END")
            ->orderBy('jam_ke')
            ->paginate(20)
            ->withQueryString();

        if ($isGuru) {
            $teachingKelasIds = $guru?->teachingKelasIds() ?? [];
            $kelasList = Kelas::whereIn('id', $teachingKelasIds ?: [0])->orderBy('nama_kelas')->get();
            $guruList = collect();
        } else {
            $kelasList = Kelas::orderBy('nama_kelas')->get();
            $guruList = Guru::orderBy('nama_lengkap')->get();
        }

        return view('jadwal.index', compact('jadwal', 'kelasList', 'guruList', 'isGuru', 'tab'));
    }

    public function create(): View
    {
        return view('jadwal.create', $this->formData());
    }

    public function store(JadwalPelajaranRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (empty($data['ruangan']) && ! empty($data['kelas_id'])) {
            $kelas = Kelas::find($data['kelas_id']);
            if ($kelas && $kelas->ruangan) {
                $data['ruangan'] = $kelas->ruangan;
            }
        }

        JadwalPelajaran::create($data);

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
        $data = $request->validated();
        if (empty($data['ruangan']) && ! empty($data['kelas_id'])) {
            $kelas = Kelas::find($data['kelas_id']);
            if ($kelas && $kelas->ruangan) {
                $data['ruangan'] = $kelas->ruangan;
            }
        }

        $jadwal->update($data);

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
        $user = request()->user();
        $isGuru = $user?->isGuru();
        $guruModel = $user?->guru;

        if ($isGuru) {
            $teachingKelasIds = $guruModel?->teachingKelasIds() ?? [];
            $kelas = Kelas::whereIn('id', $teachingKelasIds ?: [0])->orderBy('nama_kelas')->get();
            $teachingMapelIds = $guruModel?->teachingMapelIds() ?? [];
            $mapelByKelas = Kelas::getMapelByKelasMapping($teachingMapelIds);
            $mapel = MataPelajaran::whereIn('id', $teachingMapelIds ?: [0])->orderBy('nama_mapel')->get();
            $guru = collect([$guruModel]);
        } else {
            $kelas = Kelas::orderBy('nama_kelas')->get();
            $mapelByKelas = Kelas::getMapelByKelasMapping();
            $mapel = MataPelajaran::orderBy('nama_mapel')->get();
            $guru = Guru::orderBy('nama_lengkap')->get();
        }

        return compact('kelas', 'mapel', 'mapelByKelas', 'guru');
    }
}
