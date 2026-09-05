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

    public function test_guru_cannot_access_data_master_directly(): void
    {
        $userGuru = User::factory()->create(['role' => 'guru']);
        $userAdmin = User::factory()->create(['role' => 'admin']);

        // Guru akses master siswa & kelas -> 403 Forbidden
        $this->actingAs($userGuru)->get(route('siswa.index'))->assertForbidden();
        $this->actingAs($userGuru)->get(route('kelas.index'))->assertForbidden();

        // Admin akses master siswa & kelas -> 200 OK
        $this->actingAs($userAdmin)->get(route('siswa.index'))->assertOk();
        $this->actingAs($userAdmin)->get(route('kelas.index'))->assertOk();
    }

    public function test_wali_kelas_can_only_access_assigned_homeroom_space(): void
    {
        $userWaliA = User::factory()->create(['role' => 'guru']);
        $guruWaliA = Guru::create(['user_id' => $userWaliA->id, 'nip' => '199001012020', 'nama_lengkap' => 'Ustadz Wali A']);

        $userGuruBiasa = User::factory()->create(['role' => 'guru']);
        $guruBiasa = Guru::create(['user_id' => $userGuruBiasa->id, 'nip' => '199001012021', 'nama_lengkap' => 'Ustadz Biasa']);

        $kelasA = Kelas::create(['nama_kelas' => '7A', 'tingkat' => '7', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025', 'wali_kelas_id' => $guruWaliA->id]);
        $kelasB = Kelas::create(['nama_kelas' => '7B', 'tingkat' => '7', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);

        $siswaA = Siswa::create(['nis' => '1001', 'nama_lengkap' => 'Ahmad Santoso', 'kelas_id' => $kelasA->id, 'tahun_masuk' => 2024, 'jenis_kelamin' => 'Laki-laki']);
        $siswaB = Siswa::create(['nis' => '1002', 'nama_lengkap' => 'Budi Pratama', 'kelas_id' => $kelasB->id, 'tahun_masuk' => 2024, 'jenis_kelamin' => 'Laki-laki']);

        // Wali A buka kelas perwaliannya sendiri (Kelas A) -> 200 OK & tampil siswa binaannya
        $responseA = $this->actingAs($userWaliA)->get(route('perwalian.show', $kelasA));
        $responseA->assertOk();
        $responseA->assertSee('Ahmad Santoso');
        $responseA->assertDontSee('Budi Pratama');

        // Wali A buka kelas lain yang bukan perwaliannya (Kelas B) -> 403 Forbidden
        $this->actingAs($userWaliA)->get(route('perwalian.show', $kelasB))->assertForbidden();

        // Guru biasa (bukan wali kelas manapun) buka perwalian index -> 403 Forbidden
        $this->actingAs($userGuruBiasa)->get(route('perwalian.index'))->assertForbidden();

        // Wali A buka perwalian index -> redirected to their single homeroom class show
        $responseIndex = $this->actingAs($userWaliA)->get(route('perwalian.index'));
        $responseIndex->assertRedirect(route('perwalian.show', $kelasA));
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

    public function test_guru_who_is_wali_kelas_only_sees_taught_subjects_in_nilai_index(): void
    {
        // Kelas Ajar (Kelas 7A) & Kelas Perwalian (Kelas 7B)
        $kelasAjar = Kelas::create(['nama_kelas' => '7A', 'tingkat' => '7', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);
        $kelasWali = Kelas::create(['nama_kelas' => '7B', 'tingkat' => '7', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);

        $mapelMtk = MataPelajaran::factory()->create(['nama_mapel' => 'Matematika']);
        $mapelIpa = MataPelajaran::factory()->create(['nama_mapel' => 'IPA']);

        $userGuru = User::factory()->create(['role' => 'guru']);
        $guru = Guru::create(['user_id' => $userGuru->id, 'nip' => '199501012020', 'nama_lengkap' => 'Guru Wali']);

        // Guru menjadi wali kelas di Kelas 7B
        $kelasWali->update(['wali_kelas_id' => $guru->id]);

        // Guru HANYA mengajar Matematika di Kelas 7A
        JadwalPelajaran::create([
            'kelas_id' => $kelasAjar->id,
            'mapel_id' => $mapelMtk->id,
            'guru_id' => $guru->id,
            'hari' => 'Senin',
            'jam_ke' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:40',
            'tahun_ajaran' => '2024/2025',
        ]);

        $siswaAjar = Siswa::create(['nis' => '3001', 'nama_lengkap' => 'Siswa Ajar', 'kelas_id' => $kelasAjar->id, 'tahun_masuk' => 2024, 'jenis_kelamin' => 'Laki-laki']);
        $siswaWali = Siswa::create(['nis' => '3002', 'nama_lengkap' => 'Siswa Wali', 'kelas_id' => $kelasWali->id, 'tahun_masuk' => 2024, 'jenis_kelamin' => 'Perempuan']);

        // Nilai Matematika di Kelas 7A (diampu)
        Nilai::create([
            'siswa_id' => $siswaAjar->id,
            'kelas_id' => $kelasAjar->id,
            'mapel_id' => $mapelMtk->id,
            'semester' => 'Ganjil',
            'tahun_ajaran' => '2024/2025',
            'nilai_akhir' => 90,
            'predikat' => 'A',
        ]);

        // Nilai IPA di Kelas 7B (kelas perwalian, tetapi diajar guru lain)
        Nilai::create([
            'siswa_id' => $siswaWali->id,
            'kelas_id' => $kelasWali->id,
            'mapel_id' => $mapelIpa->id,
            'semester' => 'Ganjil',
            'tahun_ajaran' => '2024/2025',
            'nilai_akhir' => 80,
            'predikat' => 'B',
        ]);

        // 1. Pada halaman nilai.index, guru HANYA boleh melihat nilai Matematika, TIDAK boleh melihat nilai IPA
        $responseIndex = $this->actingAs($userGuru)->get(route('nilai.index'));
        $responseIndex->assertOk();
        $responseIndex->assertSee('Siswa Ajar');
        $responseIndex->assertSee('Matematika');
        $responseIndex->assertDontSee('Siswa Wali');
        $responseIndex->assertDontSee('IPA');

        // 2. Pada form nilai.create, dropdown kelas HANYA memuat Kelas 7A (Kelas Ajar), BUKAN Kelas 7B (Kelas Wali)
        $responseCreate = $this->actingAs($userGuru)->get(route('nilai.create'));
        $responseCreate->assertOk();
        $kelasList = $responseCreate->viewData('kelas');
        $this->assertTrue($kelasList->contains('id', $kelasAjar->id));
        $this->assertFalse($kelasList->contains('id', $kelasWali->id));
    }
}
