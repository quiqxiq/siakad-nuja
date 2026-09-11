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

    public function test_student_detail_displays_gender_for_both_kode_and_full_text(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create([
            'nama_kelas' => '7A',
            'tingkat' => '7',
            'jenjang' => 'MTs',
            'tahun_ajaran' => '2024/2025',
        ]);

        $siswaL = \App\Models\Siswa::create([
            'nis' => '1001',
            'nama_lengkap' => 'Santri Satu',
            'kelas_id' => $kelas->id,
            'jenis_kelamin' => 'L',
            'tahun_masuk' => 2024,
        ]);

        $siswaFull = \App\Models\Siswa::create([
            'nis' => '1002',
            'nama_lengkap' => 'Santri Dua',
            'kelas_id' => $kelas->id,
            'jenis_kelamin' => 'Laki-laki',
            'tahun_masuk' => 2024,
        ]);

        $siswaP = \App\Models\Siswa::create([
            'nis' => '1003',
            'nama_lengkap' => 'Santri Tiga',
            'kelas_id' => $kelas->id,
            'jenis_kelamin' => 'Perempuan',
            'tahun_masuk' => 2024,
        ]);

        $this->actingAs($admin)->get(route('siswa.show', $siswaL))
            ->assertOk()
            ->assertSee('Laki-laki');

        $this->actingAs($admin)->get(route('siswa.show', $siswaFull))
            ->assertOk()
            ->assertSee('Laki-laki');

        $this->actingAs($admin)->get(route('siswa.show', $siswaP))
            ->assertOk()
            ->assertSee('Perempuan');
    }

    public function test_siswa_creation_accepts_laki_laki_and_normalizes_to_l(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create([
            'nama_kelas' => '7A',
            'tingkat' => '7',
            'jenjang' => 'MTs',
            'tahun_ajaran' => '2024/2025',
        ]);

        $response = $this->actingAs($admin)->post(route('siswa.store'), [
            'nis' => '20249999',
            'nama_lengkap' => 'Santri Baru',
            'kelas_id' => $kelas->id,
            'jenis_kelamin' => 'Laki-laki',
            'tahun_masuk' => 2024,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('siswa', [
            'nis' => '20249999',
            'jenis_kelamin' => 'L',
        ]);
    }
}

