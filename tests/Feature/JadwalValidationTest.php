<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JadwalValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_jam_selesai_after_jam_mulai_validation_error_message(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create([
            'nama_kelas' => '7A',
            'tingkat' => '7',
            'jenjang' => 'MTs',
            'tahun_ajaran' => '2024/2025',
        ]);
        $mapel = MataPelajaran::factory()->create();
        $guruUser = User::factory()->create(['role' => 'guru']);
        $guru = Guru::create([
            'user_id' => $guruUser->id,
            'nip' => '198501012010011001',
            'nama_lengkap' => 'Ustadz Ahmad, S.Pd.',
        ]);

        $response = $this->actingAs($admin)->post(route('jadwal.store'), [
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $guru->id,
            'hari' => 'Senin',
            'jam_ke' => 1,
            'jam_mulai' => '08:00',
            'jam_selesai' => '07:30', // lebih awal dari jam_mulai
            'ruangan' => 'R1',
            'tahun_ajaran' => '2024/2025',
        ]);

        $response->assertSessionHasErrors([
            'jam_selesai' => 'Jam selesai harus lebih besar (setelah) dari jam mulai.',
        ]);
    }

    public function test_jam_selesai_equal_to_jam_mulai_fails_validation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create([
            'nama_kelas' => '7A',
            'tingkat' => '7',
            'jenjang' => 'MTs',
            'tahun_ajaran' => '2024/2025',
        ]);
        $mapel = MataPelajaran::factory()->create();
        $guruUser = User::factory()->create(['role' => 'guru']);
        $guru = Guru::create([
            'user_id' => $guruUser->id,
            'nip' => '198501012010011001',
            'nama_lengkap' => 'Ustadz Ahmad, S.Pd.',
        ]);

        $response = $this->actingAs($admin)->post(route('jadwal.store'), [
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $guru->id,
            'hari' => 'Senin',
            'jam_ke' => 1,
            'jam_mulai' => '08:00',
            'jam_selesai' => '08:00', // sama dengan jam_mulai
            'ruangan' => 'R1',
            'tahun_ajaran' => '2024/2025',
        ]);

        $response->assertSessionHasErrors([
            'jam_selesai' => 'Jam selesai harus lebih besar (setelah) dari jam mulai.',
        ]);
    }

    public function test_valid_jam_selesai_passes_validation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create([
            'nama_kelas' => '7A',
            'tingkat' => '7',
            'jenjang' => 'MTs',
            'tahun_ajaran' => '2024/2025',
        ]);
        $mapel = MataPelajaran::factory()->create();
        $guruUser = User::factory()->create(['role' => 'guru']);
        $guru = Guru::create([
            'user_id' => $guruUser->id,
            'nip' => '198501012010011001',
            'nama_lengkap' => 'Ustadz Ahmad, S.Pd.',
        ]);

        $response = $this->actingAs($admin)->post(route('jadwal.store'), [
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $guru->id,
            'hari' => 'Senin',
            'jam_ke' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:40', // valid
            'ruangan' => 'R1',
            'tahun_ajaran' => '2024/2025',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('jadwal_pelajaran', [
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:40',
        ]);
    }

    public function test_hari_jumat_fails_validation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create([
            'nama_kelas' => '7A',
            'tingkat' => '7',
            'jenjang' => 'MTs',
            'tahun_ajaran' => '2024/2025',
        ]);
        $mapel = MataPelajaran::factory()->create();
        $guruUser = User::factory()->create(['role' => 'guru']);
        $guru = Guru::create([
            'user_id' => $guruUser->id,
            'nip' => '198501012010011001',
            'nama_lengkap' => 'Ustadz Ahmad, S.Pd.',
        ]);

        $response = $this->actingAs($admin)->post(route('jadwal.store'), [
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $guru->id,
            'hari' => 'Jumat', // Jumat tidak diperbolehkan
            'jam_ke' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:40',
            'ruangan' => 'R1',
            'tahun_ajaran' => '2024/2025',
        ]);

        $response->assertSessionHasErrors(['hari']);
    }

    public function test_tahun_ajaran_exceeds_current_year_fails_validation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create([
            'nama_kelas' => '7A',
            'tingkat' => '7',
            'jenjang' => 'MTs',
            'tahun_ajaran' => '2024/2025',
        ]);
        $mapel = MataPelajaran::factory()->create();
        $guruUser = User::factory()->create(['role' => 'guru']);
        $guru = Guru::create([
            'user_id' => $guruUser->id,
            'nip' => '198501012010011001',
            'nama_lengkap' => 'Ustadz Ahmad, S.Pd.',
        ]);

        $futureStartYear = ((int) date('Y')) + 1;
        $futureTahunAjaran = $futureStartYear . '/' . ($futureStartYear + 1);

        $response = $this->actingAs($admin)->post(route('jadwal.store'), [
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $guru->id,
            'hari' => 'Sabtu',
            'jam_ke' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:40',
            'ruangan' => 'R1',
            'tahun_ajaran' => $futureTahunAjaran,
        ]);

        $currentYear = date('Y');
        $response->assertSessionHasErrors([
            'tahun_ajaran' => "Tahun ajaran tidak boleh melebihi tahun saat ini ({$currentYear}).",
        ]);
    }
}
