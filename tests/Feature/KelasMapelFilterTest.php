<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KelasMapelFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_nilai_index_filters_mapel_list_when_kelas_is_selected_for_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $kelasMI = Kelas::create([
            'nama_kelas' => '1',
            'tingkat' => '1',
            'jenjang' => 'MI',
            'tahun_ajaran' => '2024/2025',
        ]);

        $kelasMTs = Kelas::create([
            'nama_kelas' => '7',
            'tingkat' => '7',
            'jenjang' => 'MTs',
            'tahun_ajaran' => '2024/2025',
        ]);

        $mapelMI = MataPelajaran::create([
            'kode_mapel' => 'MI-FQH',
            'nama_mapel' => 'Fiqih Ibtidaiyah',
            'kkm' => 70,
            'jenjang' => 'MI',
        ]);

        $mapelMTs = MataPelajaran::create([
            'kode_mapel' => 'MTS-NHW',
            'nama_mapel' => 'Nahwu Tsanawiyah',
            'kkm' => 75,
            'jenjang' => 'MTs',
        ]);

        $kelasMI->mataPelajaran()->attach($mapelMI->id);
        $kelasMTs->mataPelajaran()->attach($mapelMTs->id);

        // 1. Tanpa filter kelas -> tampilkan semua mapel
        $respAll = $this->actingAs($admin)->get(route('nilai.index'));
        $respAll->assertOk();
        $mapelListAll = $respAll->viewData('mapelList');
        $this->assertTrue($mapelListAll->contains('id', $mapelMI->id));
        $this->assertTrue($mapelListAll->contains('id', $mapelMTs->id));

        // 2. Filter kelas MI -> hanya mapel MI yang tampil
        $respMI = $this->actingAs($admin)->get(route('nilai.index', ['kelas_id' => $kelasMI->id]));
        $respMI->assertOk();
        $mapelListMI = $respMI->viewData('mapelList');
        $this->assertTrue($mapelListMI->contains('id', $mapelMI->id));
        $this->assertFalse($mapelListMI->contains('id', $mapelMTs->id));

        // 3. Filter kelas MTs -> hanya mapel MTs yang tampil
        $respMTs = $this->actingAs($admin)->get(route('nilai.index', ['kelas_id' => $kelasMTs->id]));
        $respMTs->assertOk();
        $mapelListMTs = $respMTs->viewData('mapelList');
        $this->assertFalse($mapelListMTs->contains('id', $mapelMI->id));
        $this->assertTrue($mapelListMTs->contains('id', $mapelMTs->id));
    }

    public function test_absensi_index_filters_mapel_list_when_kelas_is_selected_for_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $kelasMI = Kelas::create([
            'nama_kelas' => '1',
            'tingkat' => '1',
            'jenjang' => 'MI',
            'tahun_ajaran' => '2024/2025',
        ]);

        $kelasMTs = Kelas::create([
            'nama_kelas' => '7',
            'tingkat' => '7',
            'jenjang' => 'MTs',
            'tahun_ajaran' => '2024/2025',
        ]);

        $mapelMI = MataPelajaran::create([
            'kode_mapel' => 'MI-FQH',
            'nama_mapel' => 'Fiqih Ibtidaiyah',
            'kkm' => 70,
            'jenjang' => 'MI',
        ]);

        $mapelMTs = MataPelajaran::create([
            'kode_mapel' => 'MTS-NHW',
            'nama_mapel' => 'Nahwu Tsanawiyah',
            'kkm' => 75,
            'jenjang' => 'MTs',
        ]);

        $kelasMI->mataPelajaran()->attach($mapelMI->id);
        $kelasMTs->mataPelajaran()->attach($mapelMTs->id);

        // Filter kelas MI pada absensi
        $respMI = $this->actingAs($admin)->get(route('absensi.index', ['kelas_id' => $kelasMI->id]));
        $respMI->assertOk();
        $mapelListMI = $respMI->viewData('mapelList');
        $this->assertTrue($mapelListMI->contains('id', $mapelMI->id));
        $this->assertFalse($mapelListMI->contains('id', $mapelMTs->id));

        // Filter kelas MTs pada absensi
        $respMTs = $this->actingAs($admin)->get(route('absensi.index', ['kelas_id' => $kelasMTs->id]));
        $respMTs->assertOk();
        $mapelListMTs = $respMTs->viewData('mapelList');
        $this->assertFalse($mapelListMTs->contains('id', $mapelMI->id));
        $this->assertTrue($mapelListMTs->contains('id', $mapelMTs->id));
    }

    public function test_invalid_mapel_id_is_reset_when_not_in_selected_kelas(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $kelasMI = Kelas::create([
            'nama_kelas' => '1',
            'tingkat' => '1',
            'jenjang' => 'MI',
            'tahun_ajaran' => '2024/2025',
        ]);

        $mapelMI = MataPelajaran::create([
            'kode_mapel' => 'MI-1',
            'nama_mapel' => 'Mapel MI',
            'kkm' => 70,
            'jenjang' => 'MI',
        ]);

        $mapelMTs = MataPelajaran::create([
            'kode_mapel' => 'MTS-1',
            'nama_mapel' => 'Mapel MTs',
            'kkm' => 70,
            'jenjang' => 'MTs',
        ]);

        $kelasMI->mataPelajaran()->attach($mapelMI->id);

        $siswaMI = Siswa::create([
            'nis' => '5001',
            'nama_lengkap' => 'Siswa MI 1',
            'kelas_id' => $kelasMI->id,
            'tahun_masuk' => 2024,
            'jenis_kelamin' => 'Laki-laki',
        ]);

        Nilai::create([
            'siswa_id' => $siswaMI->id,
            'kelas_id' => $kelasMI->id,
            'mapel_id' => $mapelMI->id,
            'semester' => 'Ganjil',
            'tahun_ajaran' => '2024/2025',
            'nilai_akhir' => 88,
            'predikat' => 'B',
        ]);

        // Kirim request nilai.index dengan kelas MI tapi mapel MTs (tidak cocok)
        $respNilai = $this->actingAs($admin)->get(route('nilai.index', [
            'kelas_id' => $kelasMI->id,
            'mapel_id' => $mapelMTs->id,
        ]));
        $respNilai->assertOk();
        // Siswa MI tetap terlihat karena mapel yang tidak cocok di-reset
        $respNilai->assertSee('Siswa MI 1');

        // Kirim request absensi.index dengan kelas MI tapi mapel MTs (tidak cocok)
        $respAbsensi = $this->actingAs($admin)->get(route('absensi.index', [
            'kelas_id' => $kelasMI->id,
            'mapel_id' => $mapelMTs->id,
        ]));
        $respAbsensi->assertOk();
        $this->assertNull(request('mapel_id'));
    }

    public function test_guru_only_sees_taught_mapel_in_selected_class(): void
    {
        $userGuru = User::factory()->create(['role' => 'guru']);
        $guru = Guru::create([
            'user_id' => $userGuru->id,
            'nip' => '199201012020',
            'nama_lengkap' => 'Guru Pengampu Ajar',
        ]);

        $kelas1 = Kelas::create([
            'nama_kelas' => '7A',
            'tingkat' => '7',
            'jenjang' => 'MTs',
            'tahun_ajaran' => '2024/2025',
        ]);

        $kelas2 = Kelas::create([
            'nama_kelas' => '8A',
            'tingkat' => '8',
            'jenjang' => 'MTs',
            'tahun_ajaran' => '2024/2025',
        ]);

        $mapelMatematika = MataPelajaran::create([
            'kode_mapel' => 'MTK',
            'nama_mapel' => 'Matematika',
            'kkm' => 75,
            'jenjang' => 'MTs',
        ]);

        $mapelIPA = MataPelajaran::create([
            'kode_mapel' => 'IPA',
            'nama_mapel' => 'Ilmu Pengetahuan Alam',
            'kkm' => 75,
            'jenjang' => 'MTs',
        ]);

        // Guru HANYA mengajar Matematika di Kelas 7A
        JadwalPelajaran::create([
            'kelas_id' => $kelas1->id,
            'mapel_id' => $mapelMatematika->id,
            'guru_id' => $guru->id,
            'hari' => 'Senin',
            'jam_ke' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:40',
            'tahun_ajaran' => '2024/2025',
        ]);

        // Guru HANYA mengajar IPA di Kelas 8A
        JadwalPelajaran::create([
            'kelas_id' => $kelas2->id,
            'mapel_id' => $mapelIPA->id,
            'guru_id' => $guru->id,
            'hari' => 'Selasa',
            'jam_ke' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:40',
            'tahun_ajaran' => '2024/2025',
        ]);

        // 1. Pada Nilai: saat pilih Kelas 7A, guru hanya melihat Matematika, bukan IPA
        $respNilai = $this->actingAs($userGuru)->get(route('nilai.index', ['kelas_id' => $kelas1->id]));
        $respNilai->assertOk();
        $mapelListNilai = $respNilai->viewData('mapelList');
        $this->assertTrue($mapelListNilai->contains('id', $mapelMatematika->id));
        $this->assertFalse($mapelListNilai->contains('id', $mapelIPA->id));

        // 2. Pada Absensi: saat pilih Kelas 7A, guru hanya melihat Matematika, bukan IPA
        $respAbsensi = $this->actingAs($userGuru)->get(route('absensi.index', ['kelas_id' => $kelas1->id]));
        $respAbsensi->assertOk();
        $mapelListAbsensi = $respAbsensi->viewData('mapelList');
        $this->assertTrue($mapelListAbsensi->contains('id', $mapelMatematika->id));
        $this->assertFalse($mapelListAbsensi->contains('id', $mapelIPA->id));
    }
}
