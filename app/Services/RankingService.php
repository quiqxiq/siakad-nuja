<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\Siswa;
use Illuminate\Support\Collection;

class RankingService
{
    /**
     * Hitung peringkat siswa per mata pelajaran di suatu kelas.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getPeringkatMapel(int $kelasId, int $mapelId, string $semester, string $tahunAjaran): Collection
    {
        $siswaList = Siswa::where('kelas_id', $kelasId)->orderBy('nama_lengkap')->get();
        $mapel = MataPelajaran::find($mapelId);

        $nilaiList = Nilai::where('kelas_id', $kelasId)
            ->where('mapel_id', $mapelId)
            ->where('semester', $semester)
            ->where('tahun_ajaran', $tahunAjaran)
            ->get()
            ->keyBy('siswa_id');

        $rows = $siswaList->map(function ($siswa) use ($nilaiList, $mapel) {
            $n = $nilaiList->get($siswa->id);
            $akhir = $n?->nilai_akhir !== null ? (float) $n->nilai_akhir : null;

            return [
                'siswa' => $siswa,
                'nilai' => $n,
                'nilai_harian' => $n?->nilai_harian,
                'nilai_uts' => $n?->nilai_uts,
                'nilai_uas' => $n?->nilai_uas,
                'nilai_akhir' => $akhir,
                'predikat' => $n?->predikat ?? ($akhir !== null ? Nilai::hitungPredikat($akhir, $mapel?->kkm) : null),
                'status_ketuntasan' => $akhir !== null ? ($akhir >= ($mapel?->kkm ?? 75) ? 'Tuntas' : 'Belum Tuntas') : 'Belum Dinilai',
            ];
        });

        // Urutkan nilai_akhir DESC, lalu nama ASC
        $sorted = $rows->sort(function ($a, $b) {
            $valA = $a['nilai_akhir'];
            $valB = $b['nilai_akhir'];

            if ($valA === null && $valB === null) {
                return strcmp($a['siswa']->nama_lengkap, $b['siswa']->nama_lengkap);
            }
            if ($valA === null) {
                return 1;
            }
            if ($valB === null) {
                return -1;
            }

            if ($valA === $valB) {
                return strcmp($a['siswa']->nama_lengkap, $b['siswa']->nama_lengkap);
            }

            return $valA < $valB ? 1 : -1;
        })->values();

        // Assign Rank (dengan penanganan skor sama / tie-rank)
        $prevScore = null;
        $prevRank = null;

        return $sorted->map(function ($item, $index) use (&$prevScore, &$prevRank) {
            if ($item['nilai_akhir'] === null) {
                $item['rank'] = '-';
            } else {
                if ($prevScore !== null && $prevScore == $item['nilai_akhir']) {
                    $item['rank'] = $prevRank;
                } else {
                    $item['rank'] = $index + 1;
                    $prevRank = $item['rank'];
                }
                $prevScore = $item['nilai_akhir'];
            }

            return $item;
        });
    }

    /**
     * Hitung buku leger nilai lengkap per kelas (Matriks Siswa x Mapel + Total + Rata-rata + Peringkat Juara Kelas).
     *
     * @return array<string, mixed>
     */
    public function getLegerKelas(int $kelasId, string $semester, string $tahunAjaran): array
    {
        $kelas = Kelas::with(['waliKelas', 'siswa' => fn ($q) => $q->orderBy('nama_lengkap')])->findOrFail($kelasId);
        $siswaList = $kelas->siswa;

        // Ambil mata pelajaran yang relevan dengan kelas ini (dari jadwal atau yang sudah ada nilainya)
        $mapelIdsFromJadwal = $kelas->jadwal()->pluck('mapel_id')->unique()->all();
        $mapelIdsFromNilai = Nilai::where('kelas_id', $kelasId)
            ->where('semester', $semester)
            ->where('tahun_ajaran', $tahunAjaran)
            ->pluck('mapel_id')
            ->unique()
            ->all();

        $allMapelIds = array_values(array_unique([...$mapelIdsFromJadwal, ...$mapelIdsFromNilai]));
        $mapelList = MataPelajaran::whereIn('id', $allMapelIds)->orderBy('nama_mapel')->get();

        if ($mapelList->isEmpty()) {
            $mapelList = MataPelajaran::orderBy('nama_mapel')->get();
        }

        // Ambil semua nilai di kelas ini
        $nilaiCollection = Nilai::where('kelas_id', $kelasId)
            ->where('semester', $semester)
            ->where('tahun_ajaran', $tahunAjaran)
            ->get();

        $nilaiMatrix = [];
        foreach ($nilaiCollection as $n) {
            $nilaiMatrix[$n->siswa_id][$n->mapel_id] = $n;
        }

        // Susun baris tiap siswa
        $rows = $siswaList->map(function ($siswa) use ($mapelList, $nilaiMatrix) {
            $scores = [];
            $totalAkhir = 0.0;
            $countValid = 0;
            $tuntasCount = 0;
            $belumTuntasCount = 0;

            foreach ($mapelList as $mapel) {
                $n = $nilaiMatrix[$siswa->id][$mapel->id] ?? null;
                $akhir = $n?->nilai_akhir !== null ? (float) $n->nilai_akhir : null;

                $isTuntas = $akhir !== null ? ($akhir >= ($mapel->kkm ?? 75)) : null;

                if ($akhir !== null) {
                    $totalAkhir += $akhir;
                    $countValid++;
                    if ($isTuntas) {
                        $tuntasCount++;
                    } else {
                        $belumTuntasCount++;
                    }
                }

                $scores[$mapel->id] = [
                    'harian' => $n?->nilai_harian,
                    'uts' => $n?->nilai_uts,
                    'uas' => $n?->nilai_uas,
                    'akhir' => $akhir,
                    'predikat' => $n?->predikat ?? ($akhir !== null ? Nilai::hitungPredikat($akhir, $mapel->kkm) : null),
                    'is_tuntas' => $isTuntas,
                ];
            }

            $rataRata = $countValid > 0 ? round($totalAkhir / $countValid, 2) : null;

            return [
                'siswa' => $siswa,
                'scores' => $scores,
                'total_akhir' => $countValid > 0 ? round($totalAkhir, 2) : null,
                'rata_rata' => $rataRata,
                'count_valid' => $countValid,
                'tuntas_count' => $tuntasCount,
                'belum_tuntas_count' => $belumTuntasCount,
            ];
        });

        // Urutkan siswa berdasarkan total_akhir DESC, rata_rata DESC, lalu nama ASC
        $sorted = $rows->sort(function ($a, $b) {
            $totalA = $a['total_akhir'];
            $totalB = $b['total_akhir'];

            if ($totalA === null && $totalB === null) {
                return strcmp($a['siswa']->nama_lengkap, $b['siswa']->nama_lengkap);
            }
            if ($totalA === null) {
                return 1;
            }
            if ($totalB === null) {
                return -1;
            }

            if ($totalA === $totalB) {
                if ($a['rata_rata'] === $b['rata_rata']) {
                    return strcmp($a['siswa']->nama_lengkap, $b['siswa']->nama_lengkap);
                }
                return ($a['rata_rata'] < $b['rata_rata']) ? 1 : -1;
            }

            return ($totalA < $totalB) ? 1 : -1;
        })->values();

        // Assign Peringkat Kelas berdasarkan total_akhir
        $prevTotal = null;
        $prevRank = null;

        $sortedWithRank = $sorted->map(function ($item, $index) use (&$prevTotal, &$prevRank) {
            if ($item['total_akhir'] === null) {
                $item['rank'] = '-';
            } else {
                if ($prevTotal !== null && $prevTotal == $item['total_akhir']) {
                    $item['rank'] = $prevRank;
                } else {
                    $item['rank'] = $index + 1;
                    $prevRank = $item['rank'];
                }
                $prevTotal = $item['total_akhir'];
            }

            return $item;
        });

        // Hitung Statistik Per Mapel
        $mapelStats = [];
        foreach ($mapelList as $mapel) {
            $mapelScores = [];
            foreach ($sortedWithRank as $row) {
                if (isset($row['scores'][$mapel->id]['akhir']) && $row['scores'][$mapel->id]['akhir'] !== null) {
                    $mapelScores[] = $row['scores'][$mapel->id]['akhir'];
                }
            }

            $cnt = count($mapelScores);
            $mapelStats[$mapel->id] = [
                'count' => $cnt,
                'avg' => $cnt > 0 ? round(array_sum($mapelScores) / $cnt, 2) : '-',
                'max' => $cnt > 0 ? max($mapelScores) : '-',
                'min' => $cnt > 0 ? min($mapelScores) : '-',
            ];
        }

        return [
            'kelas' => $kelas,
            'mapelList' => $mapelList,
            'semester' => $semester,
            'tahunAjaran' => $tahunAjaran,
            'rows' => $sortedWithRank,
            'mapelStats' => $mapelStats,
            'totalSiswa' => $siswaList->count(),
        ];
    }
}
