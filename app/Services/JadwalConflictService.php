<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\JadwalPelajaran;
use Illuminate\Validation\ValidationException;

class JadwalConflictService
{
    /**
     * Standarisasi nama hari (Ahad dinormalisasi ke Minggu).
     */
    public static function normalizeHari(string $hari): string
    {
        return strcasecmp($hari, 'Ahad') === 0 ? 'Minggu' : ucfirst(strtolower($hari));
    }

    /**
     * Periksa bentrok jadwal (Guru, Kelas, Ruangan).
     *
     * @param  array<string, mixed>  $data
     * @param  int|null  $ignoreId ID jadwal yang dikecualikan (misal saat edit)
     * @return array<string, string> Array error bentrok [field => message] jika ada bentrok
     */
    public function checkConflict(array $data, ?int $ignoreId = null): array
    {
        $errors = [];

        $hari = self::normalizeHari((string) ($data['hari'] ?? ''));
        $jamKe = (int) ($data['jam_ke'] ?? 0);
        $jamMulai = (string) ($data['jam_mulai'] ?? '');
        $jamSelesai = (string) ($data['jam_selesai'] ?? '');
        $tahunAjaran = (string) ($data['tahun_ajaran'] ?? '');
        $guruId = (int) ($data['guru_id'] ?? 0);
        $kelasId = (int) ($data['kelas_id'] ?? 0);
        $ruangan = isset($data['ruangan']) && trim((string) $data['ruangan']) !== '' ? trim((string) $data['ruangan']) : null;

        if (! $hari || ! $jamMulai || ! $jamSelesai || ! $tahunAjaran) {
            return $errors;
        }

        // Query semua jadwal di tahun ajaran dan hari yang sama
        $candidateQuery = JadwalPelajaran::with(['guru', 'kelas', 'mapel'])
            ->where('tahun_ajaran', $tahunAjaran)
            ->where(function ($q) use ($hari): void {
                if ($hari === 'Minggu') {
                    $q->whereIn('hari', ['Minggu', 'Ahad']);
                } else {
                    $q->where('hari', $hari);
                }
            })
            ->where(function ($q) use ($jamKe, $jamMulai, $jamSelesai): void {
                // Tumpang tindih waktu: jam_ke sama ATAU jam mulai & selesai saling overlap
                $q->where('jam_ke', $jamKe)
                  ->orWhere(function ($sub) use ($jamMulai, $jamSelesai): void {
                      $sub->where('jam_mulai', '<', $jamSelesai)
                          ->where('jam_selesai', '>', $jamMulai);
                  });
            });

        if ($ignoreId !== null) {
            $candidateQuery->where('id', '!=', $ignoreId);
        }

        $conflicts = $candidateQuery->get();

        foreach ($conflicts as $existing) {
            $existingWaktu = "{$existing->hari} jam {$existing->jam_mulai}-{$existing->jam_selesai} (Jam ke-{$existing->jam_ke})";

            // 1. Cek Bentrok Guru
            if ($existing->guru_id === $guruId && ! isset($errors['guru_id'])) {
                $guruNama = $existing->guru->nama_lengkap ?? 'Guru';
                $kelasNama = $existing->kelas->nama_lengkap ?? "Kelas #{$existing->kelas_id}";
                $errors['guru_id'] = "Bentrok: {$guruNama} sudah memiliki jadwal mengajar di {$kelasNama} pada {$existingWaktu}.";
            }

            // 2. Cek Bentrok Kelas
            if ($existing->kelas_id === $kelasId && ! isset($errors['kelas_id'])) {
                $kelasNama = $existing->kelas->nama_lengkap ?? "Kelas #{$existing->kelas_id}";
                $mapelNama = $existing->mapel->nama_mapel ?? 'Mata Pelajaran';
                $guruNama = $existing->guru->nama_lengkap ?? 'Guru';
                $errors['kelas_id'] = "Bentrok: {$kelasNama} sudah memiliki jadwal pelajaran {$mapelNama} ({$guruNama}) pada {$existingWaktu}.";
            }

            // 3. Cek Bentrok Ruangan (hanya jika ruangan diisi)
            if ($ruangan !== null && ! empty($existing->ruangan) && strcasecmp($existing->ruangan, $ruangan) === 0 && ! isset($errors['ruangan'])) {
                $kelasNama = $existing->kelas->nama_lengkap ?? "Kelas #{$existing->kelas_id}";
                $errors['ruangan'] = "Bentrok: Ruangan '{$ruangan}' sudah digunakan oleh {$kelasNama} pada {$existingWaktu}.";
            }
        }

        return $errors;
    }

    /**
     * Lempar ValidationException jika terjadi bentrok.
     *
     * @param  array<string, mixed>  $data
     * @param  int|null  $ignoreId
     *
     * @throws ValidationException
     */
    public function validateNoConflict(array $data, ?int $ignoreId = null): void
    {
        $errors = $this->checkConflict($data, $ignoreId);

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }
}
