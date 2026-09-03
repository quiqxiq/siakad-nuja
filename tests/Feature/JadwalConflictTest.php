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

class JadwalConflictTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_assign_same_teacher_to_different_classes_at_overlapping_time(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $kelasA = Kelas::create(['nama_kelas' => '7A', 'tingkat' => '7', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);
        $kelasB = Kelas::create(['nama_kelas' => '7B', 'tingkat' => '7', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);

        $mapel1 = MataPelajaran::factory()->create(['nama_mapel' => 'Matematika']);
        $mapel2 = MataPelajaran::factory()->create(['nama_mapel' => 'Fisika']);

        $userGuru = User::factory()->create(['role' => 'guru']);
        $guru = Guru::create(['user_id' => $userGuru->id, 'nip' => '198501012010', 'nama_lengkap' => 'Ustadz Ahmad']);

        // Jadwal 1: Guru Ahmad mengajar Kelas 7A di hari Senin jam 07:00 - 08:40 (jam_ke 1)
        JadwalPelajaran::create([
            'kelas_id' => $kelasA->id,
            'mapel_id' => $mapel1->id,
            'guru_id' => $guru->id,
            'hari' => 'Senin',
            'jam_ke' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:40',
            'tahun_ajaran' => '2024/2025',
        ]);

        // Percobaan Jadwal 2: Guru Ahmad dijadwalkan mengajar Kelas 7B di hari Senin jam 08:00 - 09:30 (overlap dengan jadwal 1)
        $response = $this->actingAs($admin)->post(route('jadwal.store'), [
            'kelas_id' => $kelasB->id,
            'mapel_id' => $mapel2->id,
            'guru_id' => $guru->id,
            'hari' => 'Senin',
            'jam_ke' => 2,
            'jam_mulai' => '08:00',
            'jam_selesai' => '09:30',
            'tahun_ajaran' => '2024/2025',
        ]);

        $response->assertSessionHasErrors(['guru_id']);
    }

    public function test_cannot_assign_two_subjects_to_same_class_at_overlapping_time(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create(['nama_kelas' => '7A', 'tingkat' => '7', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);

        $mapel1 = MataPelajaran::factory()->create(['nama_mapel' => 'Matematika']);
        $mapel2 = MataPelajaran::factory()->create(['nama_mapel' => 'Bahasa Arab']);

        $userGuru1 = User::factory()->create(['role' => 'guru']);
        $guru1 = Guru::create(['user_id' => $userGuru1->id, 'nip' => '198501012010', 'nama_lengkap' => 'Ustadz Ahmad']);

        $userGuru2 = User::factory()->create(['role' => 'guru']);
        $guru2 = Guru::create(['user_id' => $userGuru2->id, 'nip' => '198501012011', 'nama_lengkap' => 'Ustadz Mahmud']);

        // Jadwal 1
        JadwalPelajaran::create([
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel1->id,
            'guru_id' => $guru1->id,
            'hari' => 'Selasa',
            'jam_ke' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:30',
            'tahun_ajaran' => '2024/2025',
        ]);

        // Percobaan Jadwal 2 untuk kelas yang sama di jam yang sama
        $response = $this->actingAs($admin)->post(route('jadwal.store'), [
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel2->id,
            'guru_id' => $guru2->id,
            'hari' => 'Selasa',
            'jam_ke' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:30',
            'tahun_ajaran' => '2024/2025',
        ]);

        $response->assertSessionHasErrors(['kelas_id']);
    }

    public function test_cannot_assign_same_room_at_overlapping_time(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $kelasA = Kelas::create(['nama_kelas' => '7A', 'tingkat' => '7', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);
        $kelasB = Kelas::create(['nama_kelas' => '7B', 'tingkat' => '7', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);

        $mapel1 = MataPelajaran::factory()->create();
        $mapel2 = MataPelajaran::factory()->create();

        $userGuru1 = User::factory()->create(['role' => 'guru']);
        $guru1 = Guru::create(['user_id' => $userGuru1->id, 'nip' => '198501012010', 'nama_lengkap' => 'Ustadz Ahmad']);

        $userGuru2 = User::factory()->create(['role' => 'guru']);
        $guru2 = Guru::create(['user_id' => $userGuru2->id, 'nip' => '198501012011', 'nama_lengkap' => 'Ustadz Mahmud']);

        // Jadwal 1 pakai Lab Komputer
        JadwalPelajaran::create([
            'kelas_id' => $kelasA->id,
            'mapel_id' => $mapel1->id,
            'guru_id' => $guru1->id,
            'hari' => 'Rabu',
            'jam_ke' => 3,
            'jam_mulai' => '09:00',
            'jam_selesai' => '10:30',
            'ruangan' => 'Lab Komputer',
            'tahun_ajaran' => '2024/2025',
        ]);

        // Percobaan Jadwal 2 pakai Lab Komputer di jam yang sama
        $response = $this->actingAs($admin)->post(route('jadwal.store'), [
            'kelas_id' => $kelasB->id,
            'mapel_id' => $mapel2->id,
            'guru_id' => $guru2->id,
            'hari' => 'Rabu',
            'jam_ke' => 3,
            'jam_mulai' => '09:00',
            'jam_selesai' => '10:30',
            'ruangan' => 'Lab Komputer',
            'tahun_ajaran' => '2024/2025',
        ]);

        $response->assertSessionHasErrors(['ruangan']);
    }
}
