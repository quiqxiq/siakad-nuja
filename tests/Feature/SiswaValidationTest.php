<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiswaValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_jenis_kelamin_is_required_when_creating_siswa(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create([
            'nama_kelas' => '7A',
            'tingkat' => '7',
            'jenjang' => 'MTs',
            'tahun_ajaran' => '2024/2025',
        ]);

        $response = $this->actingAs($admin)->post(route('siswa.store'), [
            'nis' => '20240001',
            'nama_lengkap' => 'Ahmad Fulan',
            'kelas_id' => $kelas->id,
            'jenis_kelamin' => '', // kosong
            'tahun_masuk' => 2024,
        ]);

        $response->assertSessionHasErrors(['jenis_kelamin']);
        $errors = session('errors')->get('jenis_kelamin');
        $this->assertStringContainsString('jenis kelamin', $errors[0]);
    }

    public function test_valid_siswa_with_jenis_kelamin_persists_to_database(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create([
            'nama_kelas' => '7A',
            'tingkat' => '7',
            'jenjang' => 'MTs',
            'tahun_ajaran' => '2024/2025',
        ]);

        $response = $this->actingAs($admin)->post(route('siswa.store'), [
            'nis' => '20240001',
            'nama_lengkap' => 'Ahmad Fulan',
            'kelas_id' => $kelas->id,
            'jenis_kelamin' => 'L',
            'tahun_masuk' => 2024,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('siswa', [
            'nis' => '20240001',
            'jenis_kelamin' => 'L',
        ]);
    }

    public function test_non_numeric_nis_fails_validation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create([
            'nama_kelas' => '7A',
            'tingkat' => '7',
            'jenjang' => 'MTs',
            'tahun_ajaran' => '2024/2025',
        ]);

        $response = $this->actingAs($admin)->post(route('siswa.store'), [
            'nis' => 'ABC-STRING-NIS',
            'nama_lengkap' => 'Ahmad Fulan',
            'kelas_id' => $kelas->id,
            'jenis_kelamin' => 'L',
            'tahun_masuk' => 2024,
        ]);

        $response->assertSessionHasErrors(['nis']);
    }

    public function test_future_tahun_masuk_fails_validation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create([
            'nama_kelas' => '7A',
            'tingkat' => '7',
            'jenjang' => 'MTs',
            'tahun_ajaran' => '2024/2025',
        ]);

        $futureYear = ((int) date('Y')) + 1;

        $response = $this->actingAs($admin)->post(route('siswa.store'), [
            'nis' => '20240002',
            'nama_lengkap' => 'Ahmad Fulan',
            'kelas_id' => $kelas->id,
            'jenis_kelamin' => 'L',
            'tahun_masuk' => $futureYear,
        ]);

        $response->assertSessionHasErrors(['tahun_masuk']);
    }
}
