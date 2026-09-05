<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Kelas;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PerwalianController extends Controller
{
    /**
     * Menampilkan daftar kelas perwalian guru atau pengalihan langsung jika hanya 1 kelas.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user?->isAdmin()) {
            $kelasList = Kelas::with('waliKelas')
                ->withCount('siswa')
                ->orderBy('tingkat')
                ->orderBy('nama_kelas')
                ->get();

            return view('perwalian.index', ['kelasList' => $kelasList, 'isAdmin' => true]);
        }

        $guru = $user?->guru;
        $kelasWali = $guru ? $guru->kelasWali()->withCount('siswa')->get() : collect();

        if ($kelasWali->isEmpty()) {
            abort(403, 'Anda saat ini belum ditugaskan sebagai wali kelas pada rombel manapun.');
        }

        // Jika hanya 1 kelas perwalian (umumnya guru hanya membina 1 kelas), langsung buka kelas tersebut
        if ($kelasWali->count() === 1) {
            return redirect()->route('perwalian.show', $kelasWali->first());
        }

        return view('perwalian.index', ['kelasList' => $kelasWali, 'isAdmin' => false]);
    }

    /**
     * Menampilkan Ruang Perwalian untuk kelas tertentu: profil kelas, daftar siswa binaan, kontak orang tua.
     */
    public function show(Request $request, Kelas $kelas): View
    {
        $user = $request->user();

        // Otorisasi: Khusus Admin atau Wali Kelas dari kelas tersebut
        if (! $user?->isAdmin()) {
            $guru = $user?->guru;
            if (! $guru || ! $guru->isWaliKelasOf($kelas->id)) {
                abort(403, 'Anda tidak memiliki hak akses sebagai wali kelas di rombel ini.');
            }
        }

        $kelas->load(['waliKelas']);

        $siswa = $kelas->siswa()
            ->with(['orangTua'])
            ->withCount([
                'absensi as hadir_count' => fn ($q) => $q->where('status', 'Hadir'),
                'absensi as sakit_count' => fn ($q) => $q->where('status', 'Sakit'),
                'absensi as izin_count'  => fn ($q) => $q->where('status', 'Izin'),
                'absensi as alpa_count'  => fn ($q) => $q->where('status', 'Alpa'),
            ])
            ->when($request->filled('q'), function ($query) use ($request): void {
                $q = $request->string('q')->toString();
                $query->where(function ($sub) use ($q): void {
                    $sub->where('nama_lengkap', 'like', "%{$q}%")
                        ->orWhere('nis', 'like', "%{$q}%");
                });
            })
            ->orderBy('nama_lengkap')
            ->get();

        $totalSiswa = $siswa->count();
        $totalL = $siswa->where('jenis_kelamin', 'Laki-laki')->count();
        $totalP = $siswa->where('jenis_kelamin', 'Perempuan')->count();

        return view('perwalian.show', [
            'kelas' => $kelas,
            'siswa' => $siswa,
            'totalSiswa' => $totalSiswa,
            'totalL' => $totalL,
            'totalP' => $totalP,
        ]);
    }
}
