<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\OrangTua;
use App\Models\Pembayaran;
use App\Models\Pengumuman;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    private const TAHUN_AJARAN = '2026/2027';

    public function run(): void
    {
        // Matikan event dispatcher agar seeder berjalan tanpa trigger job WhatsApp di background
        Event::fake();

        $jsonPath = database_path('seeders/data/seeder_dataset.json');
        if (! file_exists($jsonPath)) {
            $this->command->error("File dataset tidak ditemukan: {$jsonPath}");
            return;
        }

        $dataset = json_decode(file_get_contents($jsonPath), true);

        $admin = $this->seedAdmin();
        $guruMap = $this->seedGuru($dataset['teachers'] ?? []);
        $mapelMap = $this->seedMataPelajaran($dataset['mi_mapels'] ?? [], $dataset['mts_mapels'] ?? []);
        $kelasMap = $this->seedKelas($guruMap);
        $siswaList = $this->seedSiswa($dataset['mi_students'] ?? [], $dataset['mts_students'] ?? [], $kelasMap);
        $this->seedOrangTua($siswaList);
        
        $jadwalList = $this->seedJadwal(
            $dataset['mi_schedules'] ?? [],
            $dataset['mts_schedules'] ?? [],
            $kelasMap,
            $mapelMap,
            $guruMap
        );

        $this->seedNilai($siswaList, $mapelMap);
        $this->seedAbsensi($jadwalList, $siswaList);
        $this->seedPengumuman($admin);
        $this->seedTagihan($siswaList, $admin);

        $this->call(ChatbotRuleSeeder::class);

        $this->command->info('Seeding data real MI & MTs selesai. Login admin: admin@siakadnuja.sch.id / password');
    }

    private function seedAdmin(): User
    {
        return User::firstOrCreate(
            ['email' => 'admin@siakadnuja.sch.id'],
            [
                'nama' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'no_hp' => '081200000001',
                'is_active' => true,
            ]
        );
    }

    /**
     * @param  array<int, string>  $teacherNames
     * @return array<string, Guru>  [nama_guru => Guru]
     */
    private function seedGuru(array $teacherNames): array
    {
        $guruMap = [];
        $jabatanList = ['Guru Mata Pelajaran', 'Guru & Wali Kelas', 'Guru Senior', 'Staf Pengajar'];

        foreach ($teacherNames as $i => $nama) {
            $urut = $i + 1;
            $email = 'guru' . $urut . '@siakadnuja.sch.id';

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'nama' => $nama,
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_GURU,
                    'no_hp' => '0813' . str_pad((string) $urut, 8, '0', STR_PAD_LEFT),
                    'is_active' => true,
                ]
            );

            $guru = Guru::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nip' => '1985' . str_pad((string) ($urut * 13), 8, '0', STR_PAD_LEFT),
                    'nama_lengkap' => $nama,
                    'jabatan' => $jabatanList[$i % count($jabatanList)],
                    'no_hp' => $user->no_hp,
                ]
            );

            $guruMap[$nama] = $guru;
        }

        return $guruMap;
    }

    /**
     * @param  array<int, string>  $miMapels
     * @param  array<int, string>  $mtsMapels
     * @return array<string, MataPelajaran>  ["MI_MATEMATIKA" => MataPelajaran]
     */
    private function seedMataPelajaran(array $miMapels, array $mtsMapels): array
    {
        $mapelMap = [];

        // Seed MI Mapels
        foreach ($miMapels as $i => $nama) {
            $slug = Str::slug($nama);
            $kode = strtoupper(substr(str_replace('-', '', $slug), 0, 4)) . '-MI';
            if (MataPelajaran::where('kode_mapel', $kode)->exists()) {
                $kode = strtoupper(substr(str_replace('-', '', $slug), 0, 3)) . ($i + 1) . '-MI';
            }

            $m = MataPelajaran::firstOrCreate(
                ['nama_mapel' => $nama, 'jenjang' => 'MI'],
                [
                    'kode_mapel' => $kode,
                    'kkm' => 75,
                    'deskripsi' => 'Mata pelajaran ' . $nama . ' jenjang MI.',
                ]
            );
            $mapelMap["MI_{$nama}"] = $m;
        }

        // Seed MTs Mapels
        foreach ($mtsMapels as $i => $nama) {
            $slug = Str::slug($nama);
            $kode = strtoupper(substr(str_replace('-', '', $slug), 0, 4)) . '-MTS';
            if (MataPelajaran::where('kode_mapel', $kode)->exists()) {
                $kode = strtoupper(substr(str_replace('-', '', $slug), 0, 3)) . ($i + 1) . '-MTS';
            }

            $m = MataPelajaran::firstOrCreate(
                ['nama_mapel' => $nama, 'jenjang' => 'MTs'],
                [
                    'kode_mapel' => $kode,
                    'kkm' => 75,
                    'deskripsi' => 'Mata pelajaran ' . $nama . ' jenjang MTs.',
                ]
            );
            $mapelMap["MTs_{$nama}"] = $m;
        }

        return $mapelMap;
    }

    /**
     * @param  array<string, Guru>  $guruMap
     * @return array<string, Kelas>  ["MI_1" => Kelas, "MTs_7" => Kelas]
     */
    private function seedKelas(array $guruMap): array
    {
        $kelasMap = [];
        $guruList = array_values($guruMap);

        // MI: 1..6
        for ($k = 1; $k <= 6; $k++) {
            $namaKelas = (string) $k;
            $wali = $guruList[($k - 1) % count($guruList)] ?? null;

            $kelas = Kelas::firstOrCreate(
                ['nama_kelas' => $namaKelas, 'jenjang' => 'MI', 'tahun_ajaran' => self::TAHUN_AJARAN],
                [
                    'tingkat' => (string) $k,
                    'wali_kelas_id' => $wali?->id,
                    'kapasitas' => 32,
                ]
            );
            $kelasMap["MI_{$namaKelas}"] = $kelas;
        }

        // MTs: 7..9
        for ($k = 7; $k <= 9; $k++) {
            $namaKelas = (string) $k;
            $wali = $guruList[($k + 5) % count($guruList)] ?? null;

            $kelas = Kelas::firstOrCreate(
                ['nama_kelas' => $namaKelas, 'jenjang' => 'MTs', 'tahun_ajaran' => self::TAHUN_AJARAN],
                [
                    'tingkat' => (string) $k,
                    'wali_kelas_id' => $wali?->id,
                    'kapasitas' => 32,
                ]
            );
            $kelasMap["MTs_{$namaKelas}"] = $kelas;
        }

        return $kelasMap;
    }

    /**
     * @param  array<int, array>  $miStudents
     * @param  array<int, array>  $mtsStudents
     * @param  array<string, Kelas>  $kelasMap
     * @return array<int, array{siswa: Siswa, data: array}>
     */
    private function seedSiswa(array $miStudents, array $mtsStudents, array $kelasMap): array
    {
        $all = [];

        // MI Students
        foreach ($miStudents as $sData) {
            $kelasKey = "MI_{$sData['kelas']}";
            $kelas = $kelasMap[$kelasKey] ?? null;
            if (! $kelas) continue;

            $siswa = Siswa::firstOrCreate(
                ['nis' => $sData['nis']],
                [
                    'nama_lengkap' => $sData['nama'],
                    'kelas_id' => $kelas->id,
                    'tanggal_lahir' => $sData['tanggal_lahir'] ?: null,
                    'jenis_kelamin' => $sData['jk'],
                    'alamat' => $sData['alamat'],
                    'status' => 'Aktif',
                    'tahun_masuk' => 2025,
                ]
            );

            $all[] = ['siswa' => $siswa, 'data' => $sData];
        }

        // MTs Students
        foreach ($mtsStudents as $sData) {
            $kelasKey = "MTs_{$sData['kelas']}";
            $kelas = $kelasMap[$kelasKey] ?? null;
            if (! $kelas) continue;

            $siswa = Siswa::firstOrCreate(
                ['nis' => $sData['nis']],
                [
                    'nama_lengkap' => $sData['nama'],
                    'kelas_id' => $kelas->id,
                    'tanggal_lahir' => $sData['tanggal_lahir'] ?: null,
                    'jenis_kelamin' => $sData['jk'],
                    'alamat' => $sData['alamat'],
                    'status' => 'Aktif',
                    'tahun_masuk' => 2024,
                ]
            );

            $all[] = ['siswa' => $siswa, 'data' => $sData];
        }

        return $all;
    }

    /**
     * @param  array<int, array{siswa: Siswa, data: array}>  $siswaList
     */
    private function seedOrangTua(array $siswaList): void
    {
        foreach ($siswaList as $item) {
            $siswa = $item['siswa'];
            $data = $item['data'];

            if ($siswa->orangTua()->exists()) {
                continue;
            }

            $namaAyah = ! empty($data['nama_ayah']) ? $data['nama_ayah'] : 'Ayah ' . $siswa->nama_lengkap;
            $namaIbu = ! empty($data['nama_ibu']) ? $data['nama_ibu'] : 'Ibu ' . $siswa->nama_lengkap;

            // Nomor HP & WA selalu acak/random (tidak memakai nomor dari berkas Excel)
            $noHpAyah = '08' . fake()->numerify('##########');
            $noHpIbu  = '08' . fake()->numerify('##########');

            OrangTua::create([
                'siswa_id' => $siswa->id,
                'nama' => $namaAyah,
                'hubungan' => 'Ayah',
                'no_hp' => $noHpAyah,
                'no_wa' => $noHpAyah,
                'alamat' => $siswa->alamat,
                'pekerjaan' => 'Wiraswasta',
                'is_kontak_utama' => true,
            ]);

            OrangTua::create([
                'siswa_id' => $siswa->id,
                'nama' => $namaIbu,
                'hubungan' => 'Ibu',
                'no_hp' => $noHpIbu,
                'no_wa' => $noHpIbu,
                'alamat' => $siswa->alamat,
                'pekerjaan' => 'Ibu Rumah Tangga',
                'is_kontak_utama' => false,
            ]);
        }
    }

    /**
     * @param  array<int, array>  $miSchedules
     * @param  array<int, array>  $mtsSchedules
     * @param  array<string, Kelas>  $kelasMap
     * @param  array<string, MataPelajaran>  $mapelMap
     * @param  array<string, Guru>  $guruMap
     * @return array<int, JadwalPelajaran>
     */
    private function seedJadwal(
        array $miSchedules,
        array $mtsSchedules,
        array $kelasMap,
        array $mapelMap,
        array $guruMap
    ): array {
        $jadwalList = [];
        $combined = array_merge($miSchedules, $mtsSchedules);

        foreach ($combined as $item) {
            $jenjang = $item['jenjang'];
            $kelasKey = "{$jenjang}_{$item['kelas']}";
            $mapelKey = "{$jenjang}_{$item['mapel']}";

            $kelas = $kelasMap[$kelasKey] ?? null;
            $mapel = $mapelMap[$mapelKey] ?? null;
            $guru = $guruMap[$item['guru']] ?? null;

            if (! $kelas || ! $mapel) {
                continue;
            }

            if (! $guru) {
                $guru = $kelas->waliKelas ?: (reset($guruMap) ?: null);
            }

            if (! $guru) {
                continue;
            }

            $jadwal = JadwalPelajaran::create([
                'kelas_id' => $kelas->id,
                'mapel_id' => $mapel->id,
                'guru_id' => $guru->id,
                'hari' => ucfirst(strtolower($item['hari'])),
                'jam_ke' => (int) $item['jam_ke'],
                'jam_mulai' => $item['jam_mulai'],
                'jam_selesai' => $item['jam_selesai'],
                'ruangan' => 'R-' . $kelas->nama_kelas . '-' . $jenjang,
                'tahun_ajaran' => self::TAHUN_AJARAN,
            ]);

            $jadwalList[] = $jadwal;
        }

        return $jadwalList;
    }

    /**
     * @param  array<int, array{siswa: Siswa, data: array}>  $siswaList
     * @param  array<string, MataPelajaran>  $mapelMap
     */
    private function seedNilai(array $siswaList, array $mapelMap): void
    {
        $rows = [];

        foreach ($siswaList as $item) {
            $siswa = $item['siswa'];
            $jenjang = $item['data']['jenjang'];

            $mapelForJenjang = array_filter($mapelMap, fn($k) => str_starts_with($k, "{$jenjang}_"), ARRAY_FILTER_USE_KEY);
            $selectedMapels = array_slice(array_values($mapelForJenjang), 0, 5);

            foreach ($selectedMapels as $mapel) {
                $harian = fake()->numberBetween(70, 95);
                $uts = fake()->numberBetween(65, 95);
                $uas = fake()->numberBetween(65, 98);
                $akhir = Nilai::hitungNilaiAkhir($harian, $uts, $uas);

                $rows[] = [
                    'siswa_id' => $siswa->id,
                    'mapel_id' => $mapel->id,
                    'kelas_id' => $siswa->kelas_id,
                    'semester' => 'Ganjil',
                    'tahun_ajaran' => self::TAHUN_AJARAN,
                    'nilai_harian' => $harian,
                    'nilai_uts' => $uts,
                    'nilai_uas' => $uas,
                    'nilai_akhir' => $akhir,
                    'predikat' => Nilai::hitungPredikat($akhir, $mapel->kkm),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            Nilai::insert($chunk);
        }
    }

    /**
     * @param  array<int, JadwalPelajaran>  $jadwalList
     * @param  array<int, array{siswa: Siswa, data: array}>  $siswaList
     */
    private function seedAbsensi(array $jadwalList, array $siswaList): void
    {
        $jadwalPerKelas = [];
        foreach ($jadwalList as $jadwal) {
            if ($jadwal->jam_ke === 1 && ! isset($jadwalPerKelas[$jadwal->kelas_id])) {
                $jadwalPerKelas[$jadwal->kelas_id] = $jadwal;
            }
        }

        $siswaPerKelas = [];
        foreach ($siswaList as $item) {
            $siswa = $item['siswa'];
            $siswaPerKelas[$siswa->kelas_id][] = $siswa;
        }

        $tanggalList = [];
        for ($i = 1; $i <= 5; $i++) {
            $tanggalList[] = now()->subDays($i)->format('Y-m-d');
        }

        $rows = [];
        foreach ($jadwalPerKelas as $kelasId => $jadwal) {
            foreach ($tanggalList as $tanggal) {
                foreach ($siswaPerKelas[$kelasId] ?? [] as $siswa) {
                    $status = fake()->randomElement(['Hadir', 'Hadir', 'Hadir', 'Hadir', 'Izin', 'Sakit', 'Alpa']);
                    $rows[] = [
                        'siswa_id' => $siswa->id,
                        'jadwal_id' => $jadwal->id,
                        'tanggal' => $tanggal,
                        'status' => $status,
                        'keterangan' => $status === 'Izin' ? 'Ada keperluan keluarga' : null,
                        'created_at' => now(),
                    ];
                }
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            Absensi::insert($chunk);
        }
    }

    private function seedPengumuman(User $admin): void
    {
        $data = [
            ['Penerimaan Rapor Semester Ganjil', 'Pembagian rapor semester ganjil akan dilaksanakan pada hari Sabtu. Mohon kehadiran orang tua/wali murid.', 'semua'],
            ['Rapat Koordinasi Guru MI & MTs', 'Seluruh guru MI dan MTs diharapkan hadir dalam rapat koordinasi persiapan ujian akhir semester di ruang guru.', 'guru'],
            ['Libur Semester Akademik 2026/2027', 'Libur semester ganjil dimulai setelah pembagian rapor. Kegiatan belajar dimulai kembali sesuai kalender akademik.', 'semua'],
        ];

        foreach ($data as $i => [$judul, $konten, $target]) {
            Pengumuman::create([
                'judul'           => $judul,
                'konten'          => $konten,
                'target_role'     => $target,
                'dibuat_oleh'     => $admin->id,
                'tanggal_publish' => now()->subDays($i * 2)->format('Y-m-d'),
                'is_active'       => true,
            ]);
        }
    }

    /**
     * @param  array<int, array{siswa: Siswa, data: array}>  $siswaList
     */
    private function seedTagihan(array $siswaList, User $admin): void
    {
        $bulanList = [
            ['Mei 2026',  '2026-05-31', 3],
            ['Juni 2026', '2026-06-30', 2],
            ['Juli 2026', '2026-07-31', 1],
        ];
        $nominal = 200_000;

        foreach ($siswaList as $item) {
            $siswa = $item['siswa'];
            foreach ($bulanList as [$periode, $jatuhTempo, $bulanLalu]) {
                $isMid    = $bulanLalu === 2;
                $isOldest = $bulanLalu === 3;

                if ($isOldest) {
                    $status = Tagihan::STATUS_LUNAS;
                } elseif ($isMid) {
                    $status = fake()->randomElement([Tagihan::STATUS_LUNAS, Tagihan::STATUS_MENUNGGU]);
                } else {
                    $status = fake()->randomElement([Tagihan::STATUS_BELUM, Tagihan::STATUS_MENUNGGU]);
                }

                $tagihan = Tagihan::create([
                    'siswa_id'    => $siswa->id,
                    'judul'       => 'SPP ' . $periode,
                    'jenis'       => 'SPP',
                    'periode'     => $periode,
                    'nominal'     => $nominal,
                    'jatuh_tempo' => $jatuhTempo,
                    'status'      => $status,
                    'keterangan'  => null,
                ]);

                if ($status === Tagihan::STATUS_LUNAS) {
                    Pembayaran::create([
                        'tagihan_id'        => $tagihan->id,
                        'nominal'           => $nominal,
                        'metode'            => 'Transfer',
                        'bank'              => fake()->randomElement(['BCA', 'BRI', 'BNI', 'Mandiri', 'BSI']),
                        'nama_pengirim'     => $siswa->nama_lengkap,
                        'tanggal_bayar'     => now()->subMonths($bulanLalu)->startOfMonth()->format('Y-m-d'),
                        'bukti'             => null,
                        'status'            => Pembayaran::STATUS_DISETUJUI,
                        'catatan'           => 'Pembayaran telah dikonfirmasi.',
                        'diverifikasi_oleh' => $admin->id,
                        'diverifikasi_pada' => now()->subMonths($bulanLalu)->addDays(3),
                    ]);
                } elseif ($status === Tagihan::STATUS_MENUNGGU) {
                    Pembayaran::create([
                        'tagihan_id'    => $tagihan->id,
                        'nominal'       => $nominal,
                        'metode'        => 'Transfer',
                        'bank'          => fake()->randomElement(['BCA', 'BRI', 'BSI', 'Mandiri']),
                        'nama_pengirim' => $siswa->nama_lengkap,
                        'tanggal_bayar' => now()->subDays(fake()->numberBetween(1, 7))->format('Y-m-d'),
                        'bukti'         => null,
                        'status'        => Pembayaran::STATUS_MENUNGGU,
                        'catatan'       => null,
                    ]);
                }
            }
        }
    }
}
