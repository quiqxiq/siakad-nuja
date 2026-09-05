<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Konfigurasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutomaticTahunAjaranTest extends TestCase
{
    use RefreshDatabase;

    public function test_automatic_academic_year_logic(): void
    {
        $ta = Konfigurasi::tahunAjaranAktif();
        $this->assertMatchesRegularExpression('/^\d{4}\/\d{4}$/', $ta);

        $sem = Konfigurasi::semesterAktif();
        $this->assertContains($sem, ['Ganjil', 'Genap']);

        $daftar = Konfigurasi::daftarTahunAjaran();
        $this->assertContains($ta, $daftar);
        $this->assertGreaterThanOrEqual(3, count($daftar));
    }

    public function test_kelas_create_has_automatic_tahun_ajaran_preselected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $ta = Konfigurasi::tahunAjaranAktif();

        $response = $this->actingAs($admin)->get(route('kelas.create'));
        $response->assertOk();
        $response->assertSee($ta);
        $response->assertSee('(Aktif)');
    }

    public function test_jadwal_create_has_automatic_tahun_ajaran_preselected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $ta = Konfigurasi::tahunAjaranAktif();

        $response = $this->actingAs($admin)->get(route('jadwal.create'));
        $response->assertOk();
        $response->assertSee($ta);
        $response->assertSee('(Aktif)');
    }

    public function test_nilai_create_has_automatic_tahun_ajaran_and_semester_preselected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $ta = Konfigurasi::tahunAjaranAktif();
        $sem = Konfigurasi::semesterAktif();

        $response = $this->actingAs($admin)->get(route('nilai.create'));
        $response->assertOk();
        $response->assertSee($ta);
        $response->assertSee($sem);
    }
}
