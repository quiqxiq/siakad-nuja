<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Seeder absensi khusus:
 * - HANYA tanggal 13 dan 14 September 2026.
 * - Untuk SEMUA jadwal yang diampu oleh guru tersebut (Hasib, S.Pd.I).
 *
 * Menghapus data absensi lain agar database bersih sesuai kebutuhan.
 */
class AbsensiSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Bersihkan seluruh data absensi yang ada agar HANYA ada tanggal 13 dan 14 September
        // untuk guru tersebut ("tidak lebih dari itu").
        Absensi::query()->delete();

        // 2. Cari guru target (Hasib, S.Pd.I atau guru aktif yang memiliki jadwal dan siswa)
        $guru = Guru::where('nama_lengkap', 'like', '%Hasib%')->first()
            ?? JadwalPelajaran::whereHas('kelas.siswa')->first()?->guru
            ?? Guru::first();

        if (! $guru) {
            $this->command?->error('Data guru tidak ditemukan. Jalankan DatabaseSeeder terlebih dahulu.');
            return;
        }

        // 3. Ambil seluruh jadwal yang diampu oleh guru tersebut yang memiliki kelas dan siswa
        $jadwalList = JadwalPelajaran::where('guru_id', $guru->id)
            ->with(['guru.user', 'mapel', 'kelas.siswa'])
            ->whereHas('kelas.siswa')
            ->orderBy('hari')
            ->orderBy('jam_ke')
            ->get();

        // Fallback jika guru belum memiliki jadwal sama sekali
        if ($jadwalList->isEmpty()) {
            $this->command?->warn('Jadwal pelajaran untuk guru belum tersedia. Mencoba membuat jadwal minimal...');
            $fallbackJadwal = $this->createFallbackJadwal($guru);
            if ($fallbackJadwal) {
                $jadwalList = collect([$fallbackJadwal->load(['guru.user', 'mapel', 'kelas.siswa'])]);
            }
        }

        if ($jadwalList->isEmpty()) {
            $this->command?->error('Tidak ditemukan jadwal dengan data siswa untuk guru ini.');
            return;
        }

        $targetDates = [
            '2026-09-13' => 'Kemarin (13 September 2026)',
            '2026-09-14' => 'Sekarang (14 September 2026)',
        ];

        $lat = (float) config('absensi.lokasi.latitude', -7.0845556);
        $lon = (float) config('absensi.lokasi.longitude', 113.7112031);

        $totalSeeded = 0;
        $records = [];

        foreach ($targetDates as $tanggal => $label) {
            foreach ($jadwalList as $jadwal) {
                $siswaList = $jadwal->kelas->siswa;
                $jamMulai = $jadwal->jam_mulai ?: '07:30:00';
                $createdAt = Carbon::parse("{$tanggal} {$jamMulai}")->format('Y-m-d H:i:s');

                $index = 0;
                foreach ($siswaList as $siswa) {
                    // Pola kehadiran realistis:
                    // Sebagian besar hadir, siswa tertentu izin, sakit, alpa
                    $mod = $index % 10;
                    if ($mod === 7) {
                        $status = 'Sakit';
                        $keterangan = 'Sakit demam, istirahat di rumah';
                    } elseif ($mod === 8) {
                        $status = 'Izin';
                        $keterangan = 'Izin ada acara keluarga di luar kota';
                    } elseif ($mod === 9) {
                        $status = 'Alpa';
                        $keterangan = null;
                    } else {
                        $status = 'Hadir';
                        $keterangan = null;
                    }

                    // Jarak acak wajar dalam radius 50m (5 - 25 meter)
                    $jarakMeter = 5 + ($index % 20);

                    $records[] = [
                        'siswa_id'    => $siswa->id,
                        'jadwal_id'   => $jadwal->id,
                        'tanggal'     => $tanggal,
                        'status'      => $status,
                        'keterangan'  => $keterangan,
                        'latitude'    => $lat,
                        'longitude'   => $lon,
                        'jarak_meter' => $jarakMeter,
                        'created_at'  => $createdAt,
                    ];

                    $index++;
                    $totalSeeded++;
                }
            }
        }

        // Insert massal dalam chunk 200 baris agar cepat dan optimal
        foreach (array_chunk($records, 200) as $chunk) {
            Absensi::insert($chunk);
        }

        $this->command?->info('====================================================');
        $this->command?->info('  AbsensiSeeder Berhasil Dijalankan!');
        $this->command?->info('====================================================');
        $this->command?->info("Guru         : {$guru->nama_lengkap} (NIP: {$guru->nip})");
        $this->command?->info("Total Jadwal : {$jadwalList->count()} jadwal mengajar");
        foreach ($jadwalList as $j) {
            $this->command?->info("  - Jadwal ID {$j->id}: {$j->hari} Jam ke-{$j->jam_ke} | {$j->mapel->nama_mapel} | Kelas {$j->kelas->nama_kelas} ({$j->kelas->siswa->count()} siswa)");
        }
        $this->command?->info("Tanggal      : 2026-09-13 (Kemarin) & 2026-09-14 (Sekarang)");
        $this->command?->info("Total Record : {$totalSeeded} baris absensi berhasil disimpan");
        $this->command?->info('====================================================');
    }

    /**
     * Fallback untuk membuat 1 jadwal jika database belum memiliki jadwal untuk guru.
     */
    private function createFallbackJadwal(Guru $guru): ?JadwalPelajaran
    {
        $mapel = MataPelajaran::first();
        $kelas = Kelas::whereHas('siswa')->first() ?? Kelas::first();

        if (! $mapel || ! $kelas) {
            return null;
        }

        return JadwalPelajaran::firstOrCreate(
            [
                'kelas_id' => $kelas->id,
                'mapel_id' => $mapel->id,
                'guru_id'  => $guru->id,
                'hari'     => 'Ahad',
                'jam_ke'   => 1,
            ],
            [
                'jam_mulai'    => '07:30:00',
                'jam_selesai'  => '08:40:00',
                'ruangan'      => $kelas->ruangan ?? 'R-1',
                'tahun_ajaran' => $kelas->tahun_ajaran ?? '2026/2027',
            ]
        );
    }
}
