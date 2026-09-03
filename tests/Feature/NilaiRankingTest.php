<?php

declare(strict_types=1);

namespace Tests\Feature;

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

class NilaiRankingTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_matrix_store_saves_all_students_grades(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $kelas = Kelas::create(['nama_kelas' => '7A', 'tingkat' => '7', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);
        $mapel = MataPelajaran::factory()->create(['kkm' => 75]);

        $siswa1 = Siswa::create(['nis' => '1001', 'nama_lengkap' => 'Ahmad Santoso', 'kelas_id' => $kelas->id, 'tahun_masuk' => 2024, 'jenis_kelamin' => 'Laki-laki']);
        $siswa2 = Siswa::create(['nis' => '1002', 'nama_lengkap' => 'Budi Pratama', 'kelas_id' => $kelas->id, 'tahun_masuk' => 2024, 'jenis_kelamin' => 'Laki-laki']);

        $response = $this->actingAs($admin)->post(route('nilai.matrix.store'), [
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'semester' => 'Ganjil',
            'tahun_ajaran' => '2024/2025',
            'nilai_harian' => [
                $siswa1->id => 90,
                $siswa2->id => 70,
            ],
            'nilai_uts' => [
                $siswa1->id => 90,
                $siswa2->id => 70,
            ],
            'nilai_uas' => [
                $siswa1->id => 90,
                $siswa2->id => 70,
            ],
        ]);

        $response->assertRedirect();

        // Siswa 1: 90 * 0.3 + 90 * 0.3 + 90 * 0.4 = 90
        $this->assertDatabaseHas('nilai', [
            'siswa_id' => $siswa1->id,
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'nilai_akhir' => 90.00,
        ]);

        // Siswa 2: 70 * 0.3 + 70 * 0.3 + 70 * 0.4 = 70
        $this->assertDatabaseHas('nilai', [
            'siswa_id' => $siswa2->id,
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'nilai_akhir' => 70.00,
        ]);
    }

    public function test_automatic_ranking_ranks_students_accurately(): void
    {
        $kelas = Kelas::create(['nama_kelas' => '7A', 'tingkat' => '7', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);
        $mapel1 = MataPelajaran::factory()->create(['nama_mapel' => 'Matematika', 'kkm' => 75]);
        $mapel2 = MataPelajaran::factory()->create(['nama_mapel' => 'Bahasa Indonesia', 'kkm' => 75]);

        $siswaJuara1 = Siswa::create(['nis' => '1001', 'nama_lengkap' => 'Zaidan Juara', 'kelas_id' => $kelas->id, 'tahun_masuk' => 2024, 'jenis_kelamin' => 'Laki-laki']);
        $siswaJuara2 = Siswa::create(['nis' => '1002', 'nama_lengkap' => 'Faris RunnerUp', 'kelas_id' => $kelas->id, 'tahun_masuk' => 2024, 'jenis_kelamin' => 'Laki-laki']);
        $siswaJuara3 = Siswa::create(['nis' => '1003', 'nama_lengkap' => 'Habib Ketiga', 'kelas_id' => $kelas->id, 'tahun_masuk' => 2024, 'jenis_kelamin' => 'Laki-laki']);

        // Nilai Zaidan (Total 95 + 90 = 185, Rata-rata 92.5)
        Nilai::create(['siswa_id' => $siswaJuara1->id, 'kelas_id' => $kelas->id, 'mapel_id' => $mapel1->id, 'semester' => 'Ganjil', 'tahun_ajaran' => '2024/2025', 'nilai_akhir' => 95, 'predikat' => 'A']);
        Nilai::create(['siswa_id' => $siswaJuara1->id, 'kelas_id' => $kelas->id, 'mapel_id' => $mapel2->id, 'semester' => 'Ganjil', 'tahun_ajaran' => '2024/2025', 'nilai_akhir' => 90, 'predikat' => 'B']);

        // Nilai Faris (Total 85 + 85 = 170, Rata-rata 85.0)
        Nilai::create(['siswa_id' => $siswaJuara2->id, 'kelas_id' => $kelas->id, 'mapel_id' => $mapel1->id, 'semester' => 'Ganjil', 'tahun_ajaran' => '2024/2025', 'nilai_akhir' => 85, 'predikat' => 'B']);
        Nilai::create(['siswa_id' => $siswaJuara2->id, 'kelas_id' => $kelas->id, 'mapel_id' => $mapel2->id, 'semester' => 'Ganjil', 'tahun_ajaran' => '2024/2025', 'nilai_akhir' => 85, 'predikat' => 'B']);

        // Nilai Habib (Total 75 + 80 = 155, Rata-rata 77.5)
        Nilai::create(['siswa_id' => $siswaJuara3->id, 'kelas_id' => $kelas->id, 'mapel_id' => $mapel1->id, 'semester' => 'Ganjil', 'tahun_ajaran' => '2024/2025', 'nilai_akhir' => 75, 'predikat' => 'C']);
        Nilai::create(['siswa_id' => $siswaJuara3->id, 'kelas_id' => $kelas->id, 'mapel_id' => $mapel2->id, 'semester' => 'Ganjil', 'tahun_ajaran' => '2024/2025', 'nilai_akhir' => 80, 'predikat' => 'C']);

        $service = app(RankingService::class);
        $leger = $service->getLegerKelas($kelas->id, 'Ganjil', '2024/2025');

        $rows = $leger['rows'];

        $this->assertEquals(1, $rows[0]['rank']);
        $this->assertEquals($siswaJuara1->id, $rows[0]['siswa']->id);
        $this->assertEquals(185.0, $rows[0]['total_akhir']);
        $this->assertEquals(92.5, $rows[0]['rata_rata']);

        $this->assertEquals(2, $rows[1]['rank']);
        $this->assertEquals($siswaJuara2->id, $rows[1]['siswa']->id);
        $this->assertEquals(170.0, $rows[1]['total_akhir']);

        $this->assertEquals(3, $rows[2]['rank']);
        $this->assertEquals($siswaJuara3->id, $rows[2]['siswa']->id);
        $this->assertEquals(155.0, $rows[2]['total_akhir']);
    }

    public function test_predikat_calculated_dynamically_based_on_kkm(): void
    {
        // KKM 75: Interval = 25 / 3 = 8.33
        // C: 75 .. 83.33
        // B: 83.33 .. 91.66
        // A: >= 91.66
        // D: < 75
        $this->assertEquals('A', Nilai::hitungPredikat(93, 75));
        $this->assertEquals('B', Nilai::hitungPredikat(85, 75));
        $this->assertEquals('C', Nilai::hitungPredikat(78, 75));
        $this->assertEquals('D', Nilai::hitungPredikat(70, 75));
    }
}
