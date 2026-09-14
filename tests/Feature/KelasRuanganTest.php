<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KelasRuanganTest extends TestCase
{
    use RefreshDatabase;

    public function test_kelas_auto_generates_consistent_ruangan_if_empty(): void
    {
        $kelas = Kelas::create([
            'nama_kelas' => '1A',
            'tingkat' => '1',
            'jenjang' => 'MI',
            'tahun_ajaran' => '2024/2025',
        ]);

        $this->assertEquals('R-1A-MI', $kelas->ruangan);
    }

    public function test_cannot_assign_same_ruangan_to_two_classes_in_same_tahun_ajaran(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Kelas 1 sudah menggunakan R-A1
        Kelas::create([
            'nama_kelas' => '7A',
            'tingkat' => '7',
            'jenjang' => 'MTs',
            'tahun_ajaran' => '2024/2025',
            'ruangan' => 'R-A1',
        ]);

        // Percobaan Kelas 2 menggunakan ruangan yang sama R-A1
        $response = $this->actingAs($admin)->post(route('kelas.store'), [
            'nama_kelas' => '7B',
            'tingkat' => '7',
            'jenjang' => 'MTs',
            'tahun_ajaran' => '2024/2025',
            'ruangan' => 'R-A1',
        ]);

        $response->assertSessionHasErrors(['ruangan']);
    }

    public function test_can_use_same_ruangan_in_different_tahun_ajaran(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Kelas::create([
            'nama_kelas' => '7A',
            'tingkat' => '7',
            'jenjang' => 'MTs',
            'tahun_ajaran' => '2024/2025',
            'ruangan' => 'R-A1',
        ]);

        // Tahun ajaran berbeda diperbolehkan
        $response = $this->actingAs($admin)->post(route('kelas.store'), [
            'nama_kelas' => '7A',
            'tingkat' => '7',
            'jenjang' => 'MTs',
            'tahun_ajaran' => '2025/2026',
            'ruangan' => 'R-A1',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('kelas', [
            'nama_kelas' => '7A',
            'tahun_ajaran' => '2025/2026',
            'ruangan' => 'R-A1',
        ]);
    }

    public function test_jadwal_automatically_uses_kelas_ruangan_if_empty(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create([
            'nama_kelas' => '8A',
            'tingkat' => '8',
            'jenjang' => 'MTs',
            'tahun_ajaran' => '2024/2025',
            'ruangan' => 'R-8A-MTs',
        ]);

        $mapel = MataPelajaran::factory()->create();
        $userGuru = User::factory()->create(['role' => 'guru']);
        $guru = Guru::create([
            'user_id' => $userGuru->id,
            'nip' => '198501012022',
            'nama_lengkap' => 'Guru Pengampu',
        ]);

        $response = $this->actingAs($admin)->post(route('jadwal.store'), [
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $guru->id,
            'hari' => 'Senin',
            'jam_ke' => 1,
            'jam_mulai' => '07:30',
            'jam_selesai' => '08:45',
            'ruangan' => '', // Kosongkan, harus otomatis ambil dari kelas
            'tahun_ajaran' => '2024/2025',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('jadwal_pelajaran', [
            'kelas_id' => $kelas->id,
            'ruangan' => 'R-8A-MTs',
        ]);
    }
}
