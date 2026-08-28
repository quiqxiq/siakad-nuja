<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Models\User;
use Tests\TestCase;

class AbsensiFilterTest extends TestCase
{
    public function test_absensi_index_renders_with_filters_for_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('absensi.index'));

        $response->assertStatus(200);
        $response->assertSee('Data Absensi Siswa');
        $response->assertSee('Cari Siswa / Keterangan');
        $response->assertSee('Semua Kelas');
        $response->assertSee('Semua Mapel');
        $response->assertSee('Semua Hari');
        $response->assertSee('Semua Status');
        $response->assertSee('Tanggal Absensi');
        $response->assertSee('Terapkan Filter');
    }

    public function test_absensi_filter_by_kelas_and_status_and_search(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $kelasA = Kelas::create([
            'nama_kelas' => '7A',
            'tingkat' => '7',
            'jenjang' => 'MTs',
            'tahun_ajaran' => '2024/2025',
        ]);
        $kelasB = Kelas::create([
            'nama_kelas' => '8A',
            'tingkat' => '8',
            'jenjang' => 'MTs',
            'tahun_ajaran' => '2024/2025',
        ]);

        $mapelMatematika = MataPelajaran::create([
            'kode_mapel' => 'MAT-7',
            'nama_mapel' => 'Matematika MTs',
            'kkm' => 75,
            'jenjang' => 'MTs',
        ]);

        $guruUser = User::factory()->create(['role' => 'guru']);
        $guru = Guru::create([
            'user_id' => $guruUser->id,
            'nip' => '198501012010011001',
            'nama_lengkap' => 'Ustadz Zaid',
        ]);

        $jadwalA = JadwalPelajaran::create([
            'kelas_id' => $kelasA->id,
            'mapel_id' => $mapelMatematika->id,
            'guru_id' => $guru->id,
            'hari' => 'Senin',
            'jam_ke' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:40',
            'tahun_ajaran' => '2024/2025',
        ]);

        $jadwalB = JadwalPelajaran::create([
            'kelas_id' => $kelasB->id,
            'mapel_id' => $mapelMatematika->id,
            'guru_id' => $guru->id,
            'hari' => 'Selasa',
            'jam_ke' => 2,
            'jam_mulai' => '08:40',
            'jam_selesai' => '10:00',
            'tahun_ajaran' => '2024/2025',
        ]);

        $siswaA = Siswa::create([
            'nis' => '1001',
            'nama_lengkap' => 'Ahmad Santri 7A',
            'kelas_id' => $kelasA->id,
            'jenis_kelamin' => 'L',
            'tahun_masuk' => 2024,
        ]);

        $siswaB = Siswa::create([
            'nis' => '1002',
            'nama_lengkap' => 'Budi Santri 8A',
            'kelas_id' => $kelasB->id,
            'jenis_kelamin' => 'L',
            'tahun_masuk' => 2024,
        ]);

        Absensi::create([
            'siswa_id' => $siswaA->id,
            'jadwal_id' => $jadwalA->id,
            'tanggal' => '2024-09-01',
            'status' => 'Hadir',
            'keterangan' => 'Tepat waktu',
        ]);

        Absensi::create([
            'siswa_id' => $siswaB->id,
            'jadwal_id' => $jadwalB->id,
            'tanggal' => '2024-09-02',
            'status' => 'Sakit',
            'keterangan' => 'Demam',
        ]);

        // Filter kelas A
        $responseA = $this->actingAs($admin)->get(route('absensi.index', ['kelas_id' => $kelasA->id]));
        $responseA->assertSee('Ahmad Santri 7A');
        $responseA->assertDontSee('Budi Santri 8A');

        // Filter status Sakit
        $responseSakit = $this->actingAs($admin)->get(route('absensi.index', ['status' => 'Sakit']));
        $responseSakit->assertSee('Budi Santri 8A');
        $responseSakit->assertDontSee('Ahmad Santri 7A');

        // Search by nama siswa
        $responseSearch = $this->actingAs($admin)->get(route('absensi.index', ['q' => 'Budi']));
        $responseSearch->assertSee('Budi Santri 8A');
        $responseSearch->assertDontSee('Ahmad Santri 7A');

        // Filter by hari
        $responseHari = $this->actingAs($admin)->get(route('absensi.index', ['hari' => 'Senin']));
        $responseHari->assertSee('Ahmad Santri 7A');
        $responseHari->assertDontSee('Budi Santri 8A');
    }
}
