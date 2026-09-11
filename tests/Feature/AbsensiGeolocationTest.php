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
use App\Services\GeolocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbsensiGeolocationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $userGuru;
    private Guru $guru;
    private Kelas $kelas;
    private MataPelajaran $mapel;
    private JadwalPelajaran $jadwal;
    private Siswa $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->userGuru = User::factory()->create(['role' => 'guru']);
        $this->guru = Guru::create([
            'user_id' => $this->userGuru->id,
            'nip' => '198001012020011001',
            'nama_lengkap' => 'Ustadz Abdullah, S.Pd.I',
        ]);

        $this->kelas = Kelas::create([
            'nama_kelas' => '1A',
            'tingkat' => '1',
            'jenjang' => 'MI',
            'tahun_ajaran' => '2024/2025',
        ]);

        $this->mapel = MataPelajaran::factory()->create([
            'nama_mapel' => 'Fiqih MI',
            'jenjang' => 'MI',
        ]);

        $this->jadwal = JadwalPelajaran::create([
            'kelas_id' => $this->kelas->id,
            'mapel_id' => $this->mapel->id,
            'guru_id' => $this->guru->id,
            'hari' => 'Senin',
            'jam_ke' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:20',
            'tahun_ajaran' => '2024/2025',
        ]);

        $this->siswa = Siswa::create([
            'nis' => '12001',
            'nama_lengkap' => 'Muhammad Wildan',
            'kelas_id' => $this->kelas->id,
            'jenis_kelamin' => 'L',
            'status' => 'Aktif',
            'tahun_masuk' => 2024,
        ]);
    }

    public function test_geolocation_service_calculates_accurate_distance(): void
    {
        $service = new GeolocationService();

        // Jarak dari titik madrasah ke dirinya sendiri adalah 0 meter
        $distanceSame = $service->calculateDistance(-7.0845556, 113.7112031, -7.0845556, 113.7112031);
        $this->assertEquals(0.0, $distanceSame);

        // Titik bergeser ~28 meter
        $distanceNear = $service->calculateDistance(-7.0843056, 113.7112031, -7.0845556, 113.7112031);
        $this->assertGreaterThan(20.0, $distanceNear);
        $this->assertLessThan(40.0, $distanceNear);

        // Titik bergeser ~111 meter
        $distanceFar = $service->calculateDistance(-7.0835556, 113.7112031, -7.0845556, 113.7112031);
        $this->assertGreaterThan(100.0, $distanceFar);
    }

    public function test_absensi_store_succeeds_when_within_50m_radius(): void
    {
        // Koordinat tepat di titik pusat MI Nurul Jadid Karduluk
        $response = $this->actingAs($this->admin)->post(route('absensi.store'), [
            'jadwal_id' => $this->jadwal->id,
            'tanggal' => '2026-09-11',
            'latitude' => -7.0845556,
            'longitude' => 113.7112031,
            'status' => [
                $this->siswa->id => 'Hadir',
            ],
            'keterangan' => [
                $this->siswa->id => 'Tepat waktu',
            ],
        ]);

        $response->assertRedirect(route('absensi.index', [
            'jadwal_id' => $this->jadwal->id,
            'tanggal' => '2026-09-11',
        ]));
        $response->assertSessionHas('success');

        $absensi = Absensi::where('siswa_id', $this->siswa->id)->first();
        $this->assertNotNull($absensi);
        $this->assertEquals($this->jadwal->id, $absensi->jadwal_id);
        $this->assertEquals('2026-09-11', $absensi->tanggal->format('Y-m-d'));
        $this->assertEquals('Hadir', $absensi->status);
        $this->assertEquals('Tepat waktu', $absensi->keterangan);
        $this->assertEquals(0, $absensi->jarak_meter);
        $this->assertEquals(-7.0845556, $absensi->latitude);
        $this->assertEquals(113.7112031, $absensi->longitude);
    }

    public function test_absensi_store_succeeds_near_boundary_within_50m(): void
    {
        // Geser sedikit sekitar 28 meter dari titik pusat
        $response = $this->actingAs($this->userGuru)->post(route('absensi.store'), [
            'jadwal_id' => $this->jadwal->id,
            'tanggal' => '2026-09-11',
            'latitude' => -7.0843056,
            'longitude' => 113.7112031,
            'status' => [
                $this->siswa->id => 'Izin',
            ],
            'keterangan' => [
                $this->siswa->id => 'Acara keluarga',
            ],
        ]);

        $response->assertRedirect(route('absensi.index', [
            'jadwal_id' => $this->jadwal->id,
            'tanggal' => '2026-09-11',
        ]));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('absensi', [
            'siswa_id' => $this->siswa->id,
            'jadwal_id' => $this->jadwal->id,
            'status' => 'Izin',
        ]);

        $absensi = Absensi::where('siswa_id', $this->siswa->id)->first();
        $this->assertNotNull($absensi);
        $this->assertLessThanOrEqual(50, $absensi->jarak_meter);
    }

    public function test_absensi_store_fails_when_outside_50m_radius(): void
    {
        // Koordinat berada ~111 meter dari titik madrasah
        $response = $this->actingAs($this->userGuru)->post(route('absensi.store'), [
            'jadwal_id' => $this->jadwal->id,
            'tanggal' => '2026-09-11',
            'latitude' => -7.0835556,
            'longitude' => 113.7112031,
            'status' => [
                $this->siswa->id => 'Hadir',
            ],
        ]);

        $response->assertSessionHasErrors(['lokasi']);
        $this->assertDatabaseMissing('absensi', [
            'siswa_id' => $this->siswa->id,
            'jadwal_id' => $this->jadwal->id,
        ]);
    }

    public function test_admin_is_also_strictly_restricted_by_geofence(): void
    {
        // Admin yang berada di luar radius 50m juga ditolak
        $response = $this->actingAs($this->admin)->post(route('absensi.store'), [
            'jadwal_id' => $this->jadwal->id,
            'tanggal' => '2026-09-11',
            'latitude' => -7.0800000,
            'longitude' => 113.7100000,
            'status' => [
                $this->siswa->id => 'Hadir',
            ],
        ]);

        $response->assertSessionHasErrors(['lokasi']);
        $this->assertDatabaseMissing('absensi', [
            'siswa_id' => $this->siswa->id,
            'jadwal_id' => $this->jadwal->id,
        ]);
    }

    public function test_absensi_store_fails_when_coordinates_are_missing(): void
    {
        $response = $this->actingAs($this->admin)->post(route('absensi.store'), [
            'jadwal_id' => $this->jadwal->id,
            'tanggal' => '2026-09-11',
            'status' => [
                $this->siswa->id => 'Hadir',
            ],
        ]);

        $response->assertSessionHasErrors(['latitude', 'longitude']);
    }

    public function test_create_and_roster_views_render_geofencing_elements(): void
    {
        $createResponse = $this->actingAs($this->admin)->get(route('absensi.create'));
        $createResponse->assertOk();
        $createResponse->assertSee('MI Nurul Jadid Karduluk');
        $createResponse->assertSee('radius maksimal');
        $createResponse->assertSee('50 meter');

        $rosterResponse = $this->actingAs($this->admin)->get(route('absensi.roster', [
            'jadwal_id' => $this->jadwal->id,
            'tanggal' => '2026-09-11',
        ]));
        $rosterResponse->assertOk();
        $rosterResponse->assertSee('MI Nurul Jadid Karduluk');
        $rosterResponse->assertSee('name="latitude"', false);
        $rosterResponse->assertSee('name="longitude"', false);
        $rosterResponse->assertSee('Cek Ulang Lokasi');
    }
}
