<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuruSingleAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_single_guru_can_teach_across_mi_and_mts_with_one_account(): void
    {
        $guruUser = User::factory()->create([
            'nama' => 'Aswari, S.Pd.',
            'email' => 'aswari@siakadnuja.sch.id',
            'role' => User::ROLE_GURU,
            'is_active' => true,
        ]);

        $guru = Guru::create([
            'user_id' => $guruUser->id,
            'nip' => '198501012010011002',
            'nama_lengkap' => 'Aswari, S.Pd.',
            'jabatan' => 'Guru Mata Pelajaran',
            'no_hp' => '081234567890',
        ]);

        // Kelas MI & Mapel MI
        $kelasMI = Kelas::create([
            'nama_kelas' => '4',
            'tingkat' => '4',
            'jenjang' => 'MI',
            'tahun_ajaran' => '2026/2027',
            'ruangan' => 'R-4-MI',
        ]);
        $mapelMI = MataPelajaran::create([
            'nama_mapel' => 'Nahwu MI',
            'kode_mapel' => 'NHW-MI',
            'jenjang' => 'MI',
            'kkm' => 75,
        ]);
        $kelasMI->mataPelajaran()->attach($mapelMI->id);

        // Kelas MTs & Mapel MTs
        $kelasMTs = Kelas::create([
            'nama_kelas' => '7A',
            'tingkat' => '7',
            'jenjang' => 'MTs',
            'tahun_ajaran' => '2026/2027',
            'ruangan' => 'R-7-MTs',
        ]);
        $mapelMTs = MataPelajaran::create([
            'nama_mapel' => 'Tasrif MTs',
            'kode_mapel' => 'TSR-MTS',
            'jenjang' => 'MTs',
            'kkm' => 75,
        ]);
        $kelasMTs->mataPelajaran()->attach($mapelMTs->id);

        // Jadwal mengajar guru di MI dan MTs
        JadwalPelajaran::create([
            'kelas_id' => $kelasMI->id,
            'mapel_id' => $mapelMI->id,
            'guru_id' => $guru->id,
            'hari' => 'Sabtu',
            'jam_ke' => 1,
            'jam_mulai' => '07:30',
            'jam_selesai' => '08:40',
            'ruangan' => 'R-4-MI',
            'tahun_ajaran' => '2026/2027',
        ]);

        JadwalPelajaran::create([
            'kelas_id' => $kelasMTs->id,
            'mapel_id' => $mapelMTs->id,
            'guru_id' => $guru->id,
            'hari' => 'Senin',
            'jam_ke' => 2,
            'jam_mulai' => '08:05',
            'jam_selesai' => '08:40',
            'ruangan' => 'R-7-MTs',
            'tahun_ajaran' => '2026/2027',
        ]);

        // Verifikasi relasi guru mencakup kedua jenjang
        $this->assertEquals(2, $guru->jadwal()->count());
        $this->assertContains($kelasMI->id, $guru->teachingKelasIds());
        $this->assertContains($kelasMTs->id, $guru->teachingKelasIds());
        $this->assertTrue($guru->isTeaching($kelasMI->id, $mapelMI->id));
        $this->assertTrue($guru->isTeaching($kelasMTs->id, $mapelMTs->id));

        // Akses halaman sebagai guru: harus melihat jadwal di kedua jenjang
        $responseJadwal = $this->actingAs($guruUser)->get(route('jadwal.index'));
        $responseJadwal->assertOk();
        $responseJadwal->assertSee('Nahwu MI');
        $responseJadwal->assertSee('Tasrif MTs');
        $responseJadwal->assertSee($kelasMI->nama_lengkap);
        $responseJadwal->assertSee($kelasMTs->nama_lengkap);

        // Akses absensi index: filter kelas memuat kelas MI dan MTs milik guru
        $responseAbsensi = $this->actingAs($guruUser)->get(route('absensi.index'));
        $responseAbsensi->assertOk();
        $responseAbsensi->assertSee((string) $kelasMI->id);
        $responseAbsensi->assertSee((string) $kelasMTs->id);

        // Akses nilai index: filter kelas memuat kelas MI dan MTs milik guru
        $responseNilai = $this->actingAs($guruUser)->get(route('nilai.index'));
        $responseNilai->assertOk();
        $responseNilai->assertSee((string) $kelasMI->id);
        $responseNilai->assertSee((string) $kelasMTs->id);
    }

    public function test_database_seeder_produces_exactly_28_canonical_teachers_without_duplicates(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->assertEquals(28, Guru::count());
        $this->assertEquals(28, User::where('role', User::ROLE_GURU)->count());

        // Verifikasi tidak ada nama guru yang kembar/duplikat
        $names = Guru::pluck('nama_lengkap')->all();
        $this->assertCount(28, array_unique($names));

        // Verifikasi guru yang mengajar di kedua jenjang memiliki jadwal di MI dan MTs pada satu akun
        $aswari = Guru::where('nama_lengkap', 'Aswari, S.Pd.')->first();
        $this->assertNotNull($aswari);
        $aswariJenjangs = $aswari->jadwal()->with('kelas')->get()->pluck('kelas.jenjang')->unique()->values()->all();
        $this->assertContains('MI', $aswariJenjangs);
        $this->assertContains('MTs', $aswariJenjangs);

        // Verifikasi wali kelas untuk kelas MI & MTs tidak ada yang duplikat guru yang sama
        $waliIds = Kelas::whereNotNull('wali_kelas_id')->pluck('wali_kelas_id')->all();
        $this->assertCount(count($waliIds), array_unique($waliIds));
    }
}
