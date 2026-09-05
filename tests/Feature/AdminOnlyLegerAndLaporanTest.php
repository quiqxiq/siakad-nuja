<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\Siswa;
use App\Models\User;
use App\Services\RankingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOnlyLegerAndLaporanTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_cannot_access_buku_leger_and_export(): void
    {
        $userGuru = User::factory()->create(['role' => 'guru']);
        $kelas = Kelas::create(['nama_kelas' => '7A', 'tingkat' => '7', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);

        // Akses buku leger oleh guru harus 403
        $response = $this->actingAs($userGuru)->get(route('nilai.leger', ['kelas_id' => $kelas->id]));
        $response->assertForbidden();

        // Ekspor leger oleh guru juga harus 403
        $responseExport = $this->actingAs($userGuru)->get(route('nilai.leger.export', [
            'kelas_id' => $kelas->id,
            'semester' => 'Ganjil',
            'tahun_ajaran' => '2024/2025',
        ]));
        $responseExport->assertForbidden();
    }

    public function test_admin_can_access_buku_leger_and_export(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create(['nama_kelas' => '7A', 'tingkat' => '7', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);

        $response = $this->actingAs($admin)->get(route('nilai.leger', ['kelas_id' => $kelas->id]));
        $response->assertOk();
        $response->assertSee('7A');
    }

    public function test_teacher_cannot_access_laporan_akademik(): void
    {
        $userGuru = User::factory()->create(['role' => 'guru']);

        $response = $this->actingAs($userGuru)->get(route('laporan.index'));
        $response->assertForbidden();
    }

    public function test_admin_can_access_laporan_akademik(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('laporan.index'));
        $response->assertOk();
    }

    public function test_teacher_only_sees_own_schedule_in_absensi(): void
    {
        $user1 = User::factory()->create(['role' => 'guru']);
        $guru1 = Guru::create(['user_id' => $user1->id, 'nama_lengkap' => 'Guru Satu', 'nip' => '1111']);

        $user2 = User::factory()->create(['role' => 'guru']);
        $guru2 = Guru::create(['user_id' => $user2->id, 'nama_lengkap' => 'Guru Dua', 'nip' => '2222']);

        $kelas = Kelas::create(['nama_kelas' => '7A', 'tingkat' => '7', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);

        $mapel1 = MataPelajaran::factory()->create(['nama_mapel' => 'Matematika']);
        $mapel2 = MataPelajaran::factory()->create(['nama_mapel' => 'IPA']);

        $jadwal1 = JadwalPelajaran::create(['kelas_id' => $kelas->id, 'mapel_id' => $mapel1->id, 'guru_id' => $guru1->id, 'hari' => 'Senin', 'jam_ke' => 1, 'jam_mulai' => '07:00:00', 'jam_selesai' => '08:00:00', 'tahun_ajaran' => '2024/2025', 'semester' => 'Ganjil']);
        $jadwal2 = JadwalPelajaran::create(['kelas_id' => $kelas->id, 'mapel_id' => $mapel2->id, 'guru_id' => $guru2->id, 'hari' => 'Selasa', 'jam_ke' => 2, 'jam_mulai' => '07:00:00', 'jam_selesai' => '08:00:00', 'tahun_ajaran' => '2024/2025', 'semester' => 'Ganjil']);

        $siswa = Siswa::create(['nis' => '101', 'nama_lengkap' => 'Ali', 'kelas_id' => $kelas->id, 'tahun_masuk' => 2024, 'jenis_kelamin' => 'Laki-laki']);

        Absensi::create(['siswa_id' => $siswa->id, 'jadwal_id' => $jadwal1->id, 'tanggal' => '2024-09-01', 'status' => 'Hadir']);
        Absensi::create(['siswa_id' => $siswa->id, 'jadwal_id' => $jadwal2->id, 'tanggal' => '2024-09-02', 'status' => 'Izin']);

        $response = $this->actingAs($user1)->get(route('absensi.index'));
        $response->assertOk();
        $response->assertSee('Matematika');
        $response->assertDontSee('IPA');
    }

    public function test_partial_grade_entry_harian_only(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create(['nama_kelas' => '9A', 'tingkat' => '9', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);
        $mapel = MataPelajaran::factory()->create();
        $siswa = Siswa::create(['nis' => '3001', 'nama_lengkap' => 'Santri Baru', 'kelas_id' => $kelas->id, 'tahun_masuk' => 2024, 'jenis_kelamin' => 'Laki-laki']);

        $this->actingAs($admin)->post(route('nilai.matrix.store'), [
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'semester' => 'Ganjil',
            'tahun_ajaran' => '2024/2025',
            'nilai_harian' => [$siswa->id => 85],
            'nilai_uts' => [$siswa->id => ''],
            'nilai_uas' => [$siswa->id => ''],
        ]);

        $nilai = Nilai::where('siswa_id', $siswa->id)->first();
        $this->assertNotNull($nilai);
        $this->assertEquals(85.00, $nilai->nilai_harian);
        $this->assertNull($nilai->nilai_uts);
        $this->assertNull($nilai->nilai_uas);
        $this->assertEquals(85.00, $nilai->nilai_akhir);
        $this->assertEquals('Harian Saja', $nilai->status_tahap);
    }

    public function test_academic_ranking_in_leger(): void
    {
        $kelas = Kelas::create(['nama_kelas' => '8A', 'tingkat' => '8', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);
        $mapel1 = MataPelajaran::factory()->create(['nama_mapel' => 'Fikih']);
        $mapel2 = MataPelajaran::factory()->create(['nama_mapel' => 'Akidah']);

        $siswaA = Siswa::create(['nis' => '2001', 'nama_lengkap' => 'Siswa A', 'kelas_id' => $kelas->id, 'tahun_masuk' => 2024, 'jenis_kelamin' => 'Laki-laki']);
        Nilai::create(['siswa_id' => $siswaA->id, 'kelas_id' => $kelas->id, 'mapel_id' => $mapel1->id, 'semester' => 'Ganjil', 'tahun_ajaran' => '2024/2025', 'nilai_akhir' => 95]);
        Nilai::create(['siswa_id' => $siswaA->id, 'kelas_id' => $kelas->id, 'mapel_id' => $mapel2->id, 'semester' => 'Ganjil', 'tahun_ajaran' => '2024/2025', 'nilai_akhir' => 90]);

        $siswaB = Siswa::create(['nis' => '2002', 'nama_lengkap' => 'Siswa B', 'kelas_id' => $kelas->id, 'tahun_masuk' => 2024, 'jenis_kelamin' => 'Laki-laki']);
        Nilai::create(['siswa_id' => $siswaB->id, 'kelas_id' => $kelas->id, 'mapel_id' => $mapel1->id, 'semester' => 'Ganjil', 'tahun_ajaran' => '2024/2025', 'nilai_akhir' => 80]);
        Nilai::create(['siswa_id' => $siswaB->id, 'kelas_id' => $kelas->id, 'mapel_id' => $mapel2->id, 'semester' => 'Ganjil', 'tahun_ajaran' => '2024/2025', 'nilai_akhir' => 85]);

        $service = app(RankingService::class);
        $leger = $service->getLegerKelas($kelas->id, 'Ganjil', '2024/2025');

        $rows = $leger['rows'];

        // Siswa A (Total 185) Juara 1
        $this->assertEquals($siswaA->id, $rows[0]['siswa']->id);
        $this->assertEquals(1, $rows[0]['rank']);
        $this->assertEquals(185.00, $rows[0]['total_akhir']);
        $this->assertEquals(92.50, $rows[0]['rata_rata']);

        // Siswa B (Total 165) Juara 2
        $this->assertEquals($siswaB->id, $rows[1]['siswa']->id);
        $this->assertEquals(2, $rows[1]['rank']);
        $this->assertEquals(165.00, $rows[1]['total_akhir']);
        $this->assertEquals(82.50, $rows[1]['rata_rata']);
    }
}
