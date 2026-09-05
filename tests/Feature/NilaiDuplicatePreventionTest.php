<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NilaiDuplicatePreventionTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_store_duplicate_grade_for_same_student_subject_semester_and_year(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $kelas = Kelas::create(['nama_kelas' => '7A', 'tingkat' => '7', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);
        $mapel = MataPelajaran::factory()->create(['kkm' => 75]);
        $siswa = Siswa::create(['nis' => '1001', 'nama_lengkap' => 'Ahmad Santoso', 'kelas_id' => $kelas->id, 'tahun_masuk' => 2024, 'jenis_kelamin' => 'Laki-laki']);

        // Nilai pertama berhasil disimpan
        Nilai::create([
            'siswa_id' => $siswa->id,
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'semester' => 'Ganjil',
            'tahun_ajaran' => '2024/2025',
            'nilai_harian' => 80,
            'nilai_uts' => 85,
            'nilai_uas' => 90,
            'nilai_akhir' => 85.5,
            'predikat' => 'B',
        ]);

        // Mencoba input lagi untuk siswa, mapel, semester, dan tahun ajaran yang sama via form store
        $response = $this->actingAs($admin)->post(route('nilai.store'), [
            'siswa_id' => $siswa->id,
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'semester' => 'Ganjil',
            'tahun_ajaran' => '2024/2025',
            'nilai_harian' => 70,
            'nilai_uts' => 75,
            'nilai_uas' => 80,
        ]);

        $response->assertSessionHasErrors(['siswa_id']);
        $this->assertEquals(1, Nilai::where('siswa_id', $siswa->id)->count());
    }

    public function test_can_update_existing_grade_without_unique_conflict(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $kelas = Kelas::create(['nama_kelas' => '7A', 'tingkat' => '7', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);
        $mapel = MataPelajaran::factory()->create(['kkm' => 75]);
        $siswa = Siswa::create(['nis' => '1001', 'nama_lengkap' => 'Ahmad Santoso', 'kelas_id' => $kelas->id, 'tahun_masuk' => 2024, 'jenis_kelamin' => 'Laki-laki']);

        $nilai = Nilai::create([
            'siswa_id' => $siswa->id,
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'semester' => 'Ganjil',
            'tahun_ajaran' => '2024/2025',
            'nilai_harian' => 80,
            'nilai_uts' => 85,
            'nilai_uas' => 90,
            'nilai_akhir' => 85.5,
            'predikat' => 'B',
        ]);

        $response = $this->actingAs($admin)->put(route('nilai.update', $nilai), [
            'siswa_id' => $siswa->id,
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'semester' => 'Ganjil',
            'tahun_ajaran' => '2024/2025',
            'nilai_harian' => 90,
            'nilai_uts' => 90,
            'nilai_uas' => 90,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('nilai.index'));

        $this->assertDatabaseHas('nilai', [
            'id' => $nilai->id,
            'nilai_harian' => 90.00,
            'nilai_akhir' => 90.00,
        ]);
    }

    public function test_database_unique_constraint_rejects_duplicate_inserts(): void
    {
        $kelas = Kelas::create(['nama_kelas' => '7A', 'tingkat' => '7', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);
        $mapel = MataPelajaran::factory()->create(['kkm' => 75]);
        $siswa = Siswa::create(['nis' => '1001', 'nama_lengkap' => 'Ahmad Santoso', 'kelas_id' => $kelas->id, 'tahun_masuk' => 2024, 'jenis_kelamin' => 'Laki-laki']);

        Nilai::create([
            'siswa_id' => $siswa->id,
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'semester' => 'Ganjil',
            'tahun_ajaran' => '2024/2025',
            'nilai_akhir' => 80,
        ]);

        $this->expectException(QueryException::class);

        // Direct DB insert trying to bypass controller validation
        Nilai::insert([
            'siswa_id' => $siswa->id,
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'semester' => 'Ganjil',
            'tahun_ajaran' => '2024/2025',
            'nilai_akhir' => 85,
        ]);
    }
}
