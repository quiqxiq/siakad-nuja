<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchableStudentInputTest extends TestCase
{
    use RefreshDatabase;

    public function test_nilai_create_renders_searchable_student_select(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create(['nama_kelas' => '7A', 'tingkat' => '7', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);
        $siswa = Siswa::create(['nis' => '1001', 'nama_lengkap' => 'Ahmad Santoso', 'kelas_id' => $kelas->id, 'tahun_masuk' => 2024, 'jenis_kelamin' => 'Laki-laki']);

        $response = $this->actingAs($admin)->get(route('nilai.create'));
        $response->assertOk();
        $response->assertSee('Ahmad Santoso');
        $response->assertSee('Ketik nama atau NIS untuk mencari siswa...');
    }

    public function test_tagihan_create_renders_searchable_student_select(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create(['nama_kelas' => '7A', 'tingkat' => '7', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);
        $siswa = Siswa::create(['nis' => '1001', 'nama_lengkap' => 'Ahmad Santoso', 'kelas_id' => $kelas->id, 'tahun_masuk' => 2024, 'jenis_kelamin' => 'Laki-laki']);

        $response = $this->actingAs($admin)->get(route('tagihan.create'));
        $response->assertOk();
        $response->assertSee('Ahmad Santoso');
    }

    public function test_orang_tua_create_renders_searchable_student_select(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create(['nama_kelas' => '7A', 'tingkat' => '7', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);
        $siswa = Siswa::create(['nis' => '1001', 'nama_lengkap' => 'Ahmad Santoso', 'kelas_id' => $kelas->id, 'tahun_masuk' => 2024, 'jenis_kelamin' => 'Laki-laki']);

        $response = $this->actingAs($admin)->get(route('orang-tua.create'));
        $response->assertOk();
        $response->assertSee('Ahmad Santoso');
    }

    public function test_matrix_nilai_has_live_student_search(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $guruUser = User::factory()->create(['role' => 'guru']);
        $guru = Guru::create(['user_id' => $guruUser->id, 'nip' => '19900101', 'nama_lengkap' => 'Guru Pengampu']);
        $kelas = Kelas::create(['nama_kelas' => '7A', 'tingkat' => '7', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);
        $mapel = MataPelajaran::factory()->create(['nama_mapel' => 'Fiqih', 'jenjang' => 'MTs']);
        JadwalPelajaran::create([
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $guru->id,
            'hari' => 'Senin',
            'jam_ke' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:00',
            'tahun_ajaran' => '2024/2025',
        ]);
        Siswa::create(['nis' => '1001', 'nama_lengkap' => 'Ahmad Santoso', 'kelas_id' => $kelas->id, 'tahun_masuk' => 2024, 'jenis_kelamin' => 'Laki-laki']);

        $response = $this->actingAs($admin)->get(route('nilai.matrix', [
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'semester' => 'Ganjil',
            'tahun_ajaran' => '2024/2025',
        ]));
        $response->assertOk();
        $response->assertSee('Cari siswa di tabel (nama atau NIS)...');
        $response->assertSee('Ahmad Santoso');
    }
}
