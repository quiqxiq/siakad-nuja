<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagihanPeriodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_periode_year_exceeding_current_year_fails_validation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create([
            'nama_kelas' => '7A',
            'tingkat' => '7',
            'jenjang' => 'MTs',
            'tahun_ajaran' => '2024/2025',
        ]);
        $siswa = Siswa::create([
            'nis' => '20240001',
            'nama_lengkap' => 'Ahmad Fulan',
            'kelas_id' => $kelas->id,
            'jenis_kelamin' => 'L',
            'tahun_masuk' => 2024,
        ]);

        $futureYear = ((int) date('Y')) + 1;

        $response = $this->actingAs($admin)->post(route('tagihan.store'), [
            'siswa_id' => $siswa->id,
            'judul' => 'SPP Masa Depan',
            'jenis' => 'SPP',
            'periode' => "Juli {$futureYear}",
            'nominal' => 500000,
        ]);

        $currentYear = date('Y');
        $response->assertSessionHasErrors([
            'periode' => "Tahun pada periode tagihan tidak boleh melebihi tahun saat ini ({$currentYear}).",
        ]);
    }
}
