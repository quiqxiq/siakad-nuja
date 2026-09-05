<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\Siswa;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LaporanController extends Controller
{
    /**
     * Halaman form filter laporan.
     */
    public function index(Request $request): View
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $mapel = MataPelajaran::orderBy('nama_mapel')->get();
        $guru  = Guru::orderBy('nama_lengkap')->get();

        return view('laporan.index', compact('kelas', 'mapel', 'guru'));
    }

    /**
     * Preview / Export Jadwal Pelajaran (Persis Excel 2026-2027)
     */
    public function jadwal(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'tipe'     => 'required|in:keseluruhan,per_kelas,per_guru',
            'jenjang'  => 'nullable|in:MI,MTs,Semua',
            'kelas_id' => 'nullable|exists:kelas,id',
            'guru_id'  => 'nullable|exists:guru,id',
            'export'   => 'nullable|in:pdf,csv,excel',
        ]);

        $tipe    = $validated['tipe'];
        $jenjang = $validated['jenjang'] ?? 'MI';
        $export  = $validated['export'] ?? null;

        if ($user->isGuru()) {
            $guruUser = $user->guru;
            if ($tipe === 'keseluruhan') {
                $tipe = 'per_guru';
            }
            $validated['guru_id'] = $guruUser?->id;
        }

        $hariList = ['Sabtu', 'Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis'];
        $waktuKegiatan = [
            'Sabtu'  => ['jam' => 'Keg.', 'pukul' => '07.00-07.30', 'kegiatan' => 'Pembiasaan : Membaca Juz Amma, Istighosah & Asmaul Husna'],
            'Ahad'   => ['jam' => 'Keg.', 'pukul' => '07.00-07.30', 'kegiatan' => 'Pembiasaan : Membaca Surah Yasin'],
            'Senin'  => ['jam' => 'Keg.', 'pukul' => '07.00-07.30', 'kegiatan' => 'Upacara Bendera'],
            'Selasa' => ['jam' => 'Keg.', 'pukul' => '07.00-07.30', 'kegiatan' => 'Pembiasaan : Membaca Waqiah & Tabarok'],
            'Rabu'   => ['jam' => 'Keg.', 'pukul' => '07.00-07.30', 'kegiatan' => 'Pembiasaan : Pengkajian Kitab Safinah'],
            'Kamis'  => ['jam' => 'Keg.', 'pukul' => '07.00-07.30', 'kegiatan' => 'Pembiasaan : Senam Santri / Pramuka'],
        ];

        $kelasQuery = Kelas::query();
        if ($jenjang !== 'Semua') {
            $kelasQuery->where('jenjang', $jenjang);
        }
        if ($user->isGuru()) {
            $guruUser = $user->guru;
            $kelasIds = $guruUser?->jadwal()->pluck('kelas_id')->unique()->toArray() ?? [];
            $kelasQuery->whereIn('id', $kelasIds);
        }
        $kelas = $kelasQuery->orderBy('nama_kelas')->get();

        $selectedKelas = null;
        $selectedGuru  = null;
        $matrix = [];

        if ($tipe === 'keseluruhan') {
            $jadwalRaw = JadwalPelajaran::with(['kelas', 'mapel', 'guru'])
                ->whereIn('kelas_id', $kelas->pluck('id'))
                ->get();

            foreach ($jadwalRaw as $j) {
                $hKey = strcasecmp($j->hari, 'Minggu') === 0 ? 'Ahad' : $j->hari;
                $matrix[$hKey][$j->jam_ke][$j->kelas_id] = [
                    'mapel'       => $j->mapel->nama_mapel ?? '-',
                    'guru'        => $j->guru->nama_lengkap ?? '-',
                    'jam_mulai'   => $j->jam_mulai,
                    'jam_selesai' => $j->jam_selesai,
                ];
            }
        } elseif ($tipe === 'per_kelas') {
            $selectedKelas = ! empty($validated['kelas_id'])
                ? Kelas::find($validated['kelas_id'])
                : $kelas->first();

            if ($selectedKelas) {
                if ($user->isGuru()) {
                    $guruUser = $user->guru;
                    $kelasIds = $guruUser?->jadwal()->pluck('kelas_id')->unique()->toArray() ?? [];
                    if (! in_array($selectedKelas->id, $kelasIds, true)) {
                        abort(403, 'Anda tidak memiliki akses ke jadwal kelas ini.');
                    }
                }

                $jadwalQuery = JadwalPelajaran::with(['mapel', 'guru'])
                    ->where('kelas_id', $selectedKelas->id);

                if ($user->isGuru()) {
                    $guruUser = $user->guru;
                    $jadwalQuery->where('guru_id', $guruUser?->id);
                }

                $jadwalRaw = $jadwalQuery->get();

                foreach ($jadwalRaw as $j) {
                    $hKey = strcasecmp($j->hari, 'Minggu') === 0 ? 'Ahad' : $j->hari;
                    $matrix[$hKey][$j->jam_ke] = [
                        'mapel'       => $j->mapel->nama_mapel ?? '-',
                        'guru'        => $j->guru->nama_lengkap ?? '-',
                        'jam_mulai'   => $j->jam_mulai,
                        'jam_selesai' => $j->jam_selesai,
                    ];
                }
            }
        } elseif ($tipe === 'per_guru') {
            $guruId = $user->isGuru() ? $user->guru?->id : ($validated['guru_id'] ?? null);
            $selectedGuru = ! empty($guruId)
                ? Guru::find($guruId)
                : ($user->isGuru() ? $user->guru : Guru::first());

            if ($selectedGuru) {
                $jadwalRaw = JadwalPelajaran::with(['kelas', 'mapel'])
                    ->where('guru_id', $selectedGuru->id)
                    ->get();

                foreach ($jadwalRaw as $j) {
                    $hKey = strcasecmp($j->hari, 'Minggu') === 0 ? 'Ahad' : $j->hari;
                    $matrix[$hKey][$j->jam_ke][] = [
                        'kelas'       => $j->kelas->nama_kelas ?? '-',
                        'jenjang'     => $j->kelas->jenjang ?? '-',
                        'mapel'       => $j->mapel->nama_mapel ?? '-',
                        'jam_mulai'   => $j->jam_mulai,
                        'jam_selesai' => $j->jam_selesai,
                        'ruangan'     => $j->ruangan,
                    ];
                }
            }
        }

        $title = 'JADWAL PELAJARAN ' . ($tipe === 'per_kelas' && $selectedKelas ? 'KELAS ' . $selectedKelas->nama_kelas : ($tipe === 'per_guru' && $selectedGuru ? 'GURU ' . strtoupper($selectedGuru->nama_lengkap) : 'MADRASAH (' . $jenjang . ')'));

        if ($export === 'pdf') {
            $pdf = Pdf::loadView('laporan.pdf_jadwal', compact(
                'tipe', 'jenjang', 'kelas', 'selectedKelas', 'selectedGuru',
                'hariList', 'waktuKegiatan', 'matrix', 'title'
            ))->setPaper('a4', 'landscape');

            return $pdf->download(Str::slug($title) . '.pdf');
        }

        if ($export === 'excel' || $export === 'csv') {
            return response()->view('laporan.pdf_jadwal', compact(
                'tipe', 'jenjang', 'kelas', 'selectedKelas', 'selectedGuru',
                'hariList', 'waktuKegiatan', 'matrix', 'title'
            ))
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . Str::slug($title) . '.xls"');
        }

        return view('laporan.jadwal', compact(
            'tipe', 'jenjang', 'kelas', 'selectedKelas', 'selectedGuru',
            'hariList', 'waktuKegiatan', 'matrix', 'title'
        ));
    }

    /**
     * Preview Rekap Kehadiran
     */
    public function kehadiran(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'bulan'    => 'required|date_format:Y-m', // e.g., 2025-07
            'export'   => 'nullable|in:pdf,csv',
        ]);

        $kelas = Kelas::findOrFail($validated['kelas_id']);
        $bulan = $validated['bulan'];
        $user  = $request->user();

        if ($user->isGuru()) {
            $guruUser = $user->guru;
            $kelasIds = $guruUser?->jadwal()->pluck('kelas_id')->unique()->toArray() ?? [];

            if (! in_array($kelas->id, $kelasIds, true)) {
                abort(403, 'Anda tidak memiliki akses ke laporan kehadiran kelas ini.');
            }
        }

        $siswa = Siswa::where('kelas_id', $kelas->id)->orderBy('nama_lengkap')->get();

        $absensi = Absensi::with('jadwal')
            ->whereHas('jadwal', function ($q) use ($kelas, $user): void {
                $q->where('kelas_id', $kelas->id);
                if ($user->isGuru()) {
                    $guruUser = $user->guru;
                    $q->where('guru_id', $guruUser?->id);
                }
            })
            ->where('tanggal', 'like', $bulan . '-%')
            ->get();

        $rekap = [];
        foreach ($siswa as $s) {
            $rekap[$s->id] = ['Hadir' => 0, 'Sakit' => 0, 'Izin' => 0, 'Alpa' => 0];
        }

        foreach ($absensi as $absen) {
            if (isset($rekap[$absen->siswa_id][$absen->status])) {
                $rekap[$absen->siswa_id][$absen->status]++;
            }
        }

        if ($request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('laporan.pdf_kehadiran', compact('kelas', 'bulan', 'siswa', 'rekap'))->setPaper('a4', 'landscape');
            return $pdf->download('Rekap_Kehadiran_'.$kelas->nama_kelas.'_'.$bulan.'.pdf');
        }

        if ($request->input('export') === 'csv') {
            return $this->exportCsvKehadiran($kelas, $bulan, $siswa, $rekap);
        }

        return view('laporan.kehadiran', compact('kelas', 'bulan', 'siswa', 'rekap'));
    }

    /**
     * Preview Rekap Nilai
     */
    public function nilai(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mata_pelajaran,id',
            'export'   => 'nullable|in:pdf,csv',
        ]);

        $kelas = Kelas::findOrFail($validated['kelas_id']);
        $mapel = MataPelajaran::findOrFail($validated['mapel_id']);
        $user  = $request->user();

        if ($user->isGuru()) {
            $guruUser = $user->guru;
            $isTeaches = $guruUser?->jadwal()
                ->where('kelas_id', $kelas->id)
                ->where('mapel_id', $mapel->id)
                ->exists();

            if (! $isTeaches) {
                abort(403, 'Anda tidak memiliki akses ke laporan nilai kelas/mapel ini.');
            }
        }

        $siswa = Siswa::where('kelas_id', $kelas->id)->orderBy('nama_lengkap')->get();
        $nilai = Nilai::where('kelas_id', $kelas->id)
            ->where('mapel_id', $mapel->id)
            ->get()
            ->keyBy('siswa_id');

        if ($request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('laporan.pdf_nilai', compact('kelas', 'mapel', 'siswa', 'nilai'))->setPaper('a4', 'portrait');
            return $pdf->download('Rekap_Nilai_'.$kelas->nama_kelas.'_'.$mapel->kode_mapel.'.pdf');
        }

        if ($request->input('export') === 'csv') {
            return $this->exportCsvNilai($kelas, $mapel, $siswa, $nilai);
        }

        return view('laporan.nilai', compact('kelas', 'mapel', 'siswa', 'nilai'));
    }

    /**
     * Helper Ekspor CSV Kehadiran
     */
    private function exportCsvKehadiran($kelas, $bulan, $siswa, $rekap)
    {
        $filename = 'Rekap_Kehadiran_'.$kelas->nama_kelas.'_'.$bulan.'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($siswa, $rekap) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No', 'NIS', 'Nama Siswa', 'Hadir', 'Sakit', 'Izin', 'Alpa']);
            foreach ($siswa as $i => $s) {
                fputcsv($file, [
                    $i + 1,
                    $s->nis,
                    $s->nama_lengkap,
                    $rekap[$s->id]['Hadir'],
                    $rekap[$s->id]['Sakit'],
                    $rekap[$s->id]['Izin'],
                    $rekap[$s->id]['Alpa'],
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Helper Ekspor CSV Nilai
     */
    private function exportCsvNilai($kelas, $mapel, $siswa, $nilai)
    {
        $filename = 'Rekap_Nilai_'.$kelas->nama_kelas.'_'.$mapel->kode_mapel.'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($siswa, $nilai) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No', 'NIS', 'Nama Siswa', 'Harian', 'UTS', 'UAS', 'Akhir', 'Predikat']);
            foreach ($siswa as $i => $s) {
                $n = $nilai[$s->id] ?? null;
                fputcsv($file, [
                    $i + 1,
                    $s->nis,
                    $s->nama_lengkap,
                    $n ? $n->nilai_harian : '-',
                    $n ? $n->nilai_uts : '-',
                    $n ? $n->nilai_uas : '-',
                    $n ? $n->nilai_akhir : '-',
                    $n ? $n->predikat : '-',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
