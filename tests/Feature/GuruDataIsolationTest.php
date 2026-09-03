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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuruDataIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_can_only_see_students_in_accessible_classes(): void
    {
        // 2 Kelas: Kelas A & Kelas B
        $kelasA = Kelas::create(['nama_kelas' => '7A', 'tingkat' => '7', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);
        $kelasB = Kelas::create(['nama_kelas' => '7B', 'tingkat' => '7', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);

        $siswaA = Siswa::create(['nis' => '1001', 'nama_lengkap' => 'Ahmad Santoso', 'kelas_id' => $kelasA->id, 'tahun_masuk' => 2024, 'jenis_kelamin' => 'Laki-laki']);
        $siswaB = Siswa::create(['nis' => '1002', 'nama_lengkap' => 'Budi Pratama', 'kelas_id' => $kelasB->id, 'tahun_masuk' => 2024, 'jenis_kelamin' => 'Laki-laki']);

        // Guru 1 hanya mengajar di Kelas A
        $userGuru1 = User::factory()->create(['role' => 'guru']);
        $guru1 = Guru::create(['user_id' => $userGuru1->id, 'nip' => '199001012020', 'nama_lengkap' => 'Ustadz Zaid']);
        $mapel = MataPelajaran::factory()->create();

        JadwalPelajaran::create([
            'kelas_id' => $kelasA->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $guru1->id,
            'hari' => 'Senin',
            'jam_ke' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:40',
            'tahun_ajaran' => '2024/2025',
        ]);

        // Guru 1 akses daftar siswa: hanya boleh melihat Siswa A, tidak boleh melihat Siswa B
        $response = $this->actingAs($userGuru1)->get(route('siswa.index'));
        $response->assertOk();
        $response->assertSee('Ahmad Santoso');
        $response->assertDontSee('Budi Pratama');

        // Guru 1 akses show Siswa B -> 403 Forbidden
        $responseShowB = $this->actingAs($userGuru1)->get(route('siswa.show', $siswaB));
        $responseShowB->assertForbidden();

        // Guru 1 akses show Siswa A -> 200 OK
        $responseShowA = $this->actingAs($userGuru1)->get(route('siswa.show', $siswaA));
        $responseShowA->assertOk();
    }

    public function test_guru_cannot_edit_grades_for_subjects_they_do_not_teach(): void
    {
        $kelas = Kelas::create(['nama_kelas' => '7A', 'tingkat' => '7', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);
        $siswa = Siswa::create(['nis' => '1001', 'nama_lengkap' => 'Ahmad Santoso', 'kelas_id' => $kelas->id, 'tahun_masuk' => 2024, 'jenis_kelamin' => 'Laki-laki']);

        $mapelMatematika = MataPelajaran::factory()->create(['nama_mapel' => 'Matematika']);
        $mapelIpa = MataPelajaran::factory()->create(['nama_mapel' => 'IPA']);

        // Guru 1: Guru Matematika
        $userGuru1 = User::factory()->create(['role' => 'guru']);
        $guru1 = Guru::create(['user_id' => $userGuru1->id, 'nip' => '199001012020', 'nama_lengkap' => 'Guru Matematika']);
        JadwalPelajaran::create([
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapelMatematika->id,
            'guru_id' => $guru1->id,
            'hari' => 'Senin',
            'jam_ke' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:40',
            'tahun_ajaran' => '2024/2025',
        ]);

        // Guru 2: Guru IPA
        $userGuru2 = User::factory()->create(['role' => 'guru']);
        $guru2 = Guru::create(['user_id' => $userGuru2->id, 'nip' => '199001012021', 'nama_lengkap' => 'Guru IPA']);
        JadwalPelajaran::create([
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapelIpa->id,
            'guru_id' => $guru2->id,
            'hari' => 'Selasa',
            'jam_ke' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:40',
            'tahun_ajaran' => '2024/2025',
        ]);

        // Buat record nilai Matematika
        $nilaiMtk = Nilai::create([
            'siswa_id' => $siswa->id,
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapelMatematika->id,
            'semester' => 'Ganjil',
            'tahun_ajaran' => '2024/2025',
            'nilai_harian' => 85,
            'nilai_uts' => 80,
            'nilai_uas' => 90,
            'nilai_akhir' => 85.5,
            'predikat' => 'B',
        ]);

        // Guru 2 (Guru IPA) mencoba mengubah nilai Matematika -> 403 Forbidden
        $response = $this->actingAs($userGuru2)->put(route('nilai.update', $nilaiMtk), [
            'siswa_id' => $siswa->id,
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapelMatematika->id,
            'semester' => 'Ganjil',
            'tahun_ajaran' => '2024/2025',
            'nilai_harian' => 50,
        ]);
        $response->assertForbidden();

        // Guru 1 (Guru Matematika) mengubah nilai Matematika -> Berhasil
        $responseMtk = $this->actingAs($userGuru1)->put(route('nilai.update', $nilaiMtk), [
            'siswa_id' => $siswa->id,
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapelMatematika->id,
            'semester' => 'Ganjil',
            'tahun_ajaran' => '2024/2025',
            'nilai_harian' => 95,
            'nilai_uts' => 90,
            'nilai_uas' => 90,
        ]);
        $responseMtk->assertRedirect(route('nilai.index'));
        $this->assertDatabaseHas('nilai', [
            'id' => $nilaiMtk->id,
            'nilai_harian' => 95,
        ]);
    }
}
