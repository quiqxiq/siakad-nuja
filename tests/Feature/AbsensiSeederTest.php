<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Models\User;
use Database\Seeders\AbsensiSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbsensiSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_absensi_seeder_populates_attendance_for_one_teacher_and_subject_on_dates_13_and_14(): void
    {
        $user = User::factory()->create([
            'nama' => 'Ustadz Testing',
            'email' => 'ustadz@siakadnuja.sch.id',
            'role' => User::ROLE_GURU,
        ]);

        $guru = Guru::create([
            'user_id' => $user->id,
            'nama_lengkap' => 'Ustadz Testing',
            'nip' => '198501012020',
            'no_hp' => '081234567890',
        ]);

        $mapel = MataPelajaran::create([
            'nama_mapel' => 'FIQIH',
            'kode_mapel' => 'FIQI-MI',
            'jenjang' => 'MI',
            'kkm' => 75,
        ]);

        $kelas = Kelas::create([
            'nama_kelas' => '2',
            'tingkat' => '2',
            'jenjang' => 'MI',
            'tahun_ajaran' => '2026/2027',
            'kapasitas' => 30,
        ]);

        // Buat 5 siswa di kelas ini
        $siswaList = Siswa::factory()->count(5)->create([
            'kelas_id' => $kelas->id,
        ]);

        // Buat 2 jadwal untuk guru ini (jadwal 1 dan jadwal 2)
        $jadwal1 = JadwalPelajaran::create([
            'guru_id' => $guru->id,
            'mapel_id' => $mapel->id,
            'kelas_id' => $kelas->id,
            'hari' => 'Sabtu',
            'jam_ke' => 1,
            'jam_mulai' => '07:30:00',
            'jam_selesai' => '08:40:00',
            'tahun_ajaran' => '2026/2027',
        ]);

        $kelas2 = Kelas::create([
            'nama_kelas' => '3',
            'tingkat' => '3',
            'jenjang' => 'MI',
            'tahun_ajaran' => '2026/2027',
            'kapasitas' => 30,
        ]);
        $siswaList2 = Siswa::factory()->count(3)->create([
            'kelas_id' => $kelas2->id,
        ]);

        $jadwal2 = JadwalPelajaran::create([
            'guru_id' => $guru->id,
            'mapel_id' => $mapel->id,
            'kelas_id' => $kelas2->id,
            'hari' => 'Ahad',
            'jam_ke' => 2,
            'jam_mulai' => '08:40:00',
            'jam_selesai' => '09:50:00',
            'tahun_ajaran' => '2026/2027',
        ]);

        // Jalankan seeder
        $this->seed(AbsensiSeeder::class);

        // Verifikasi record untuk tanggal 2026-09-13 dan 2026-09-14 untuk kedua jadwal
        $absensi13Jadwal1 = Absensi::whereDate('tanggal', '2026-09-13')->where('jadwal_id', $jadwal1->id)->get();
        $absensi14Jadwal1 = Absensi::whereDate('tanggal', '2026-09-14')->where('jadwal_id', $jadwal1->id)->get();
        $absensi13Jadwal2 = Absensi::whereDate('tanggal', '2026-09-13')->where('jadwal_id', $jadwal2->id)->get();
        $absensi14Jadwal2 = Absensi::whereDate('tanggal', '2026-09-14')->where('jadwal_id', $jadwal2->id)->get();

        $this->assertCount(5, $absensi13Jadwal1);
        $this->assertCount(5, $absensi14Jadwal1);
        $this->assertCount(3, $absensi13Jadwal2);
        $this->assertCount(3, $absensi14Jadwal2);

        // Total: (5 + 3) * 2 = 16 record
        $this->assertEquals(16, Absensi::count());

        // Verifikasi semua jadwal guru tersebut terabseni
        $distinctJadwal = Absensi::distinct()->pluck('jadwal_id')->sort()->values()->all();
        $expectedJadwal = [$jadwal1->id, $jadwal2->id];
        sort($expectedJadwal);
        $this->assertEquals($expectedJadwal, $distinctJadwal);

        $distinctDates = Absensi::all()->map(fn ($a) => $a->tanggal->format('Y-m-d'))->unique()->values()->all();
        sort($distinctDates);
        $this->assertEquals(['2026-09-13', '2026-09-14'], $distinctDates);

        // Jalankan lagi untuk menguji idempotensi (tetap tepat 16 record, tidak menduplikasi)
        $this->seed(AbsensiSeeder::class);
        $this->assertEquals(16, Absensi::count());
    }

    public function test_absensi_seeder_removes_old_extraneous_attendance_records(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_GURU]);
        $guru = Guru::create([
            'user_id' => $user->id,
            'nama_lengkap' => 'Guru Pengganti',
            'nip' => '198501019999',
            'no_hp' => '081234567899',
        ]);
        $mapel = MataPelajaran::create([
            'nama_mapel' => 'AQIDAH',
            'kode_mapel' => 'AQID-MI',
            'jenjang' => 'MI',
            'kkm' => 75,
        ]);
        $kelas = Kelas::create([
            'nama_kelas' => '1',
            'tingkat' => '1',
            'jenjang' => 'MI',
            'tahun_ajaran' => '2026/2027',
            'kapasitas' => 30,
        ]);
        $siswa = Siswa::factory()->create(['kelas_id' => $kelas->id]);
        $jadwal = JadwalPelajaran::create([
            'guru_id' => $guru->id,
            'mapel_id' => $mapel->id,
            'kelas_id' => $kelas->id,
            'hari' => 'Ahad',
            'jam_ke' => 1,
            'jam_mulai' => '07:30:00',
            'jam_selesai' => '08:40:00',
            'tahun_ajaran' => '2026/2027',
        ]);

        // Masukkan data dummy absensi tanggal lain (misal tanggal 10 dan 11 September)
        Absensi::create([
            'siswa_id' => $siswa->id,
            'jadwal_id' => $jadwal->id,
            'tanggal' => '2026-09-10',
            'status' => 'Hadir',
        ]);
        Absensi::create([
            'siswa_id' => $siswa->id,
            'jadwal_id' => $jadwal->id,
            'tanggal' => '2026-09-11',
            'status' => 'Hadir',
        ]);

        $this->assertEquals(2, Absensi::count());

        // Jalankan seeder
        $this->seed(AbsensiSeeder::class);

        // Record tanggal 10 dan 11 harus terhapus, HANYA tersisa tanggal 13 dan 14 September
        $this->assertEquals(2, Absensi::count()); // 1 siswa x 2 tanggal
        $this->assertDatabaseMissing('absensi', ['tanggal' => '2026-09-10']);
        $this->assertDatabaseMissing('absensi', ['tanggal' => '2026-09-11']);

        $dates = Absensi::all()->map(fn ($a) => $a->tanggal->format('Y-m-d'))->unique()->values()->all();
        sort($dates);
        $this->assertEquals(['2026-09-13', '2026-09-14'], $dates);
    }
}
