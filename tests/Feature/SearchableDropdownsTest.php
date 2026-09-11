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

class SearchableDropdownsTest extends TestCase
{
    use RefreshDatabase;

    public function test_absensi_create_renders_searchable_select_for_mapel_and_jadwal(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create(['nama_kelas' => '7A', 'tingkat' => '7', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);
        $mapel = MataPelajaran::factory()->create(['nama_mapel' => 'Al-Quran Hadits', 'jenjang' => 'MTs']);
        $userGuru = User::factory()->create(['role' => 'guru']);
        $guru = Guru::create(['user_id' => $userGuru->id, 'nip' => '19850101', 'nama_lengkap' => 'Ustadz Zaid']);

        $jadwal = JadwalPelajaran::create([
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $guru->id,
            'hari' => 'Senin',
            'jam_ke' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:20',
            'ruangan' => 'R. MTs 7A',
            'tahun_ajaran' => '2024/2025',
        ]);

        $response = $this->actingAs($admin)->get(route('absensi.create'));
        $response->assertOk();
        $response->assertSee('Pilih Mata Pelajaran &amp; Jadwal', false);
        $response->assertSee('Ketik nama mata pelajaran, kelas, hari, atau guru...');
        $response->assertSee('Al-Quran Hadits');
        $response->assertSee('Ustadz Zaid');

        // Test submission to absensi.roster
        $rosterResponse = $this->actingAs($admin)->get(route('absensi.roster', [
            'jadwal_id' => $jadwal->id,
            'tanggal' => now()->toDateString(),
        ]));
        $rosterResponse->assertOk();
    }

    public function test_jadwal_create_renders_searchable_select_for_mapel_and_guru(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create(['nama_kelas' => '1A', 'tingkat' => '1', 'jenjang' => 'MI', 'tahun_ajaran' => '2024/2025']);
        $userGuru = User::factory()->create(['role' => 'guru']);
        $guru = Guru::create(['user_id' => $userGuru->id, 'nip' => '19820202', 'nama_lengkap' => 'Ustadzah Fatimah']);

        $response = $this->actingAs($admin)->get(route('jadwal.create'));
        $response->assertOk();

        // Searchable mapel
        $response->assertSee('Ketik nama mata pelajaran...');
        $response->assertSee('-- Pilih Kelas Terlebih Dahulu --');

        // Searchable guru
        $response->assertSee('Guru Pengampu');
        $response->assertSee('Ketik nama atau NIP guru...');
        $response->assertSee('Ustadzah Fatimah');
    }

    public function test_nilai_create_renders_searchable_mapel_and_siswa(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create(['nama_kelas' => '1A', 'tingkat' => '1', 'jenjang' => 'MI', 'tahun_ajaran' => '2024/2025']);
        $siswa = Siswa::create([
            'nis' => '1005',
            'nama_lengkap' => 'Muhammad Rizqi',
            'kelas_id' => $kelas->id,
            'jenis_kelamin' => 'L',
            'status' => 'Aktif',
            'tahun_masuk' => 2024,
        ]);

        $response = $this->actingAs($admin)->get(route('nilai.create'));
        $response->assertOk();

        // Searchable siswa
        $response->assertSee('Muhammad Rizqi');
        $response->assertSee('Ketik nama atau NIS untuk mencari siswa...');

        // Searchable mapel
        $response->assertSee('Ketik nama mata pelajaran...');
    }

    public function test_kelas_create_renders_searchable_select_for_wali_kelas(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $userGuru = User::factory()->create(['role' => 'guru']);
        $guru = Guru::create(['user_id' => $userGuru->id, 'nip' => '19830303', 'nama_lengkap' => 'Ustadz Abdullah']);

        $response = $this->actingAs($admin)->get(route('kelas.create'));
        $response->assertOk();
        $response->assertSee('Wali Kelas');
        $response->assertSee('Ketik nama atau NIP guru...');
        $response->assertSee('Ustadz Abdullah');
    }

    public function test_siswa_create_renders_searchable_select_for_kelas(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create(['nama_kelas' => '8B', 'tingkat' => '8', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);

        $response = $this->actingAs($admin)->get(route('siswa.create'));
        $response->assertOk();
        $response->assertSee('Kelas');
        $response->assertSee('Ketik nama kelas atau jenjang...');
        $response->assertSee('MTs 8B');
    }

    public function test_tagihan_create_renders_searchable_select_for_kelas_massal(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create(['nama_kelas' => '9A', 'tingkat' => '9', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);

        $response = $this->actingAs($admin)->get(route('tagihan.create'));
        $response->assertOk();
        $response->assertSee('Pilih Kelas (tagihan massal)');
        $response->assertSee('Ketik nama kelas atau jenjang...');
        $response->assertSee('MTs 9A');
    }
}
