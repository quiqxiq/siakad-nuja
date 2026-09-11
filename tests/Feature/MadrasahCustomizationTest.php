<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ChatbotLog;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\OrangTua;
use App\Models\Pengumuman;
use App\Models\Siswa;
use App\Models\User;
use App\Services\ChatbotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MadrasahCustomizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_orang_tua_form_and_whatsapp_sync_and_village_job(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create([
            'nama_kelas' => '1A',
            'tingkat' => '1',
            'jenjang' => 'MI',
            'tahun_ajaran' => '2024/2025',
        ]);
        $siswa = Siswa::create([
            'nis' => '1001',
            'nama_lengkap' => 'Ahmad Fulan',
            'kelas_id' => $kelas->id,
            'jenis_kelamin' => 'L',
            'status' => 'Aktif',
            'tahun_masuk' => 2024,
        ]);

        // 1. Render create view and verify Karduluk village jobs & labels
        $createResponse = $this->actingAs($admin)->get(route('orang-tua.create'));
        $createResponse->assertOk();
        $createResponse->assertSee('Nama Siswa');
        $createResponse->assertSee('Nama Orang Tua');
        $createResponse->assertSee('Nomor WhatsApp');
        $createResponse->assertSee('Pengrajin / Tukang Ukir Kayu');
        $createResponse->assertSee('Petani');

        // 2. Submit form with single no_wa and specific village job
        $storeResponse = $this->actingAs($admin)->post(route('orang-tua.store'), [
            'siswa_id' => $siswa->id,
            'nama' => 'Bapak Subhan',
            'hubungan' => 'Ayah',
            'no_wa' => '081234567890',
            'pekerjaan' => 'Pengrajin / Tukang Ukir Kayu',
            'is_kontak_utama' => 1,
        ]);

        $storeResponse->assertRedirect(route('orang-tua.index'));

        // 3. Verify no_hp is synced with no_wa in database
        $this->assertDatabaseHas('orang_tua', [
            'siswa_id' => $siswa->id,
            'nama' => 'Bapak Subhan',
            'no_wa' => '081234567890',
            'no_hp' => '081234567890',
            'pekerjaan' => 'Pengrajin / Tukang Ukir Kayu',
        ]);
    }

    public function test_pengumuman_target_role_excludes_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // 1. Create page should not have option value="admin" as selectable recipient
        $createResponse = $this->actingAs($admin)->get(route('pengumuman.create'));
        $createResponse->assertOk();
        $createResponse->assertSee('Wali Murid / Orang Tua');
        $createResponse->assertSee('Semua (Guru &amp; Wali Murid)', false);
        $createResponse->assertDontSee('<option value="admin"', false);

        // 2. Storing with target_role 'wali' succeeds
        $response = $this->actingAs($admin)->post(route('pengumuman.store'), [
            'judul' => 'Libur Hari Raya Idul Fitri',
            'konten' => 'Diberitahukan kepada seluruh wali murid bahwa madrasah libur.',
            'target_role' => 'wali',
            'tanggal_publish' => now()->toDateString(),
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('pengumuman.index'));
        $this->assertDatabaseHas('pengumuman', [
            'judul' => 'Libur Hari Raya Idul Fitri',
            'target_role' => 'wali',
        ]);
    }

    public function test_chatbot_unregistered_number_rejection(): void
    {
        Queue::fake();

        /** @var ChatbotService $chatbot */
        $chatbot = app(ChatbotService::class);

        // Nomor tidak terdaftar mengirim pesan apapun
        $chatbot->process('089999999999', 'assalamualaikum info nilai');

        $log = ChatbotLog::latest()->first();
        $this->assertNotNull($log);
        $this->assertEquals('GUEST_UNREGISTERED', $log->intent);
        $this->assertStringContainsString('nomor WhatsApp Anda belum terdaftar di sistem SIAKAD NUJA', $log->pesan_keluar);
        $this->assertStringContainsString('hubungi pihak tata usaha / admin madrasah', $log->pesan_keluar);
        $this->assertStringNotContainsString('Halo Orang Tua/Wali', $log->pesan_keluar);
        $this->assertStringNotContainsString('⚠️ Perintah tidak dikenali', $log->pesan_keluar);
    }

    public function test_chatbot_registered_number_welcome_and_menu(): void
    {
        Queue::fake();

        $kelas = Kelas::create([
            'nama_kelas' => '1A',
            'tingkat' => '1',
            'jenjang' => 'MI',
            'tahun_ajaran' => '2024/2025',
        ]);
        $siswa = Siswa::create([
            'nis' => '1002',
            'nama_lengkap' => 'Muhammad Ali',
            'kelas_id' => $kelas->id,
            'jenis_kelamin' => 'L',
            'status' => 'Aktif',
            'tahun_masuk' => 2024,
        ]);
        OrangTua::create([
            'siswa_id' => $siswa->id,
            'nama' => 'Ibu Fatimah',
            'hubungan' => 'Ibu',
            'no_wa' => '081987654321',
            'no_hp' => '081987654321',
            'pekerjaan' => 'Petani',
            'is_kontak_utama' => true,
        ]);

        /** @var ChatbotService $chatbot */
        $chatbot = app(ChatbotService::class);

        // Sapaan awal dari nomor terdaftar
        $chatbot->process('081987654321', 'Halo selamat pagi');

        $log = ChatbotLog::latest()->first();
        $this->assertNotNull($log);
        $this->assertEquals('MENU_UTAMA', $log->intent);
        $this->assertStringContainsString('Selamat datang, Bapak/Ibu *Ibu Fatimah*', $log->pesan_keluar);
        $this->assertStringContainsString('*Muhammad Ali* (MI 1A)', $log->pesan_keluar);
        $this->assertStringContainsString('Silakan ketik nomor atau kata kunci layanan berikut', $log->pesan_keluar);
        $this->assertStringNotContainsString('⚠️ Perintah tidak dikenali', $log->pesan_keluar);
    }

    public function test_jadwal_jam_ke_restricted_to_1_through_4(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create([
            'nama_kelas' => '7A',
            'tingkat' => '7',
            'jenjang' => 'MTs',
            'tahun_ajaran' => '2024/2025',
        ]);
        $mapel = MataPelajaran::factory()->create(['jenjang' => 'MTs']);
        $userGuru = User::factory()->create(['role' => 'guru']);
        $guru = Guru::create(['user_id' => $userGuru->id, 'nip' => '19800101', 'nama_lengkap' => 'Guru Test']);

        // jam_ke = 5 (melebihi batas maksimal jam ke-4)
        $responseMax = $this->actingAs($admin)->post(route('jadwal.store'), [
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $guru->id,
            'hari' => 'Senin',
            'jam_ke' => 5,
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:40',
            'ruangan' => 'R1',
            'tahun_ajaran' => '2024/2025',
        ]);
        $responseMax->assertSessionHasErrors(['jam_ke' => 'Jam pelajaran madrasah dibatasi maksimal sampai jam ke-4.']);

        // jam_ke = 0 (kurang dari 1)
        $responseMin = $this->actingAs($admin)->post(route('jadwal.store'), [
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $guru->id,
            'hari' => 'Senin',
            'jam_ke' => 0,
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:40',
            'ruangan' => 'R1',
            'tahun_ajaran' => '2024/2025',
        ]);
        $responseMin->assertSessionHasErrors(['jam_ke' => 'Jam pelajaran minimal adalah jam ke-1.']);

        // jam_ke = 4 (valid)
        $responseValid = $this->actingAs($admin)->post(route('jadwal.store'), [
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $guru->id,
            'hari' => 'Senin',
            'jam_ke' => 4,
            'jam_mulai' => '10:00',
            'jam_selesai' => '11:20',
            'ruangan' => 'R1',
            'tahun_ajaran' => '2024/2025',
        ]);
        $responseValid->assertRedirect(route('jadwal.index'));
        $this->assertDatabaseHas('jadwal_pelajaran', [
            'kelas_id' => $kelas->id,
            'jam_ke' => 4,
        ]);
    }

    public function test_jadwal_and_nilai_curriculum_and_conflict_validation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $kelas1 = Kelas::create(['nama_kelas' => '1', 'tingkat' => '1', 'jenjang' => 'MI', 'tahun_ajaran' => '2024/2025']);
        $kelas7 = Kelas::create(['nama_kelas' => '7A', 'tingkat' => '7', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);

        $mapelKelas1 = MataPelajaran::factory()->create(['nama_mapel' => 'Al-Quran Hadis MI', 'jenjang' => 'MI']);
        $mapelNahwu = MataPelajaran::factory()->create(['nama_mapel' => 'Nahwu MI', 'jenjang' => 'MI']);
        $mapelMTs = MataPelajaran::factory()->create(['nama_mapel' => 'IPA MTs', 'jenjang' => 'MTs']);

        // Daftarkan Al-Quran Hadis ke kurikulum Kelas 1, Nahwu TIDAK didaftarkan ke Kelas 1
        $kelas1->mataPelajaran()->attach($mapelKelas1->id);

        $userGuru = User::factory()->create(['role' => 'guru']);
        $guru = Guru::create(['user_id' => $userGuru->id, 'nip' => '19820202', 'nama_lengkap' => 'Ustadz Hasan']);

        // 1. Jadwal: Jenjang tidak sesuai (Kelas MI dengan Mapel MTs)
        $respJenjang = $this->actingAs($admin)->post(route('jadwal.store'), [
            'kelas_id' => $kelas1->id,
            'mapel_id' => $mapelMTs->id,
            'guru_id' => $guru->id,
            'hari' => 'Senin',
            'jam_ke' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:20',
            'tahun_ajaran' => '2024/2025',
        ]);
        $respJenjang->assertSessionHasErrors(['mapel_id']);

        // 2. Jadwal: Mapel tidak terdaftar pada kurikulum Kelas 1
        $respKurikulum = $this->actingAs($admin)->post(route('jadwal.store'), [
            'kelas_id' => $kelas1->id,
            'mapel_id' => $mapelNahwu->id,
            'guru_id' => $guru->id,
            'hari' => 'Senin',
            'jam_ke' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:20',
            'tahun_ajaran' => '2024/2025',
        ]);
        $respKurikulum->assertSessionHasErrors(['mapel_id']);

        // 3. Jadwal: Mapel terdaftar berhasil disimpan
        $respSuccess = $this->actingAs($admin)->post(route('jadwal.store'), [
            'kelas_id' => $kelas1->id,
            'mapel_id' => $mapelKelas1->id,
            'guru_id' => $guru->id,
            'hari' => 'Senin',
            'jam_ke' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:20',
            'tahun_ajaran' => '2024/2025',
        ]);
        $respSuccess->assertRedirect(route('jadwal.index'));

        // 4. Nilai: Validasi kurikulum pada modul Nilai
        $siswaKelas1 = Siswa::create([
            'nis' => '1003',
            'nama_lengkap' => 'Aisyah',
            'kelas_id' => $kelas1->id,
            'jenis_kelamin' => 'P',
            'status' => 'Aktif',
            'tahun_masuk' => 2024,
        ]);

        // Simpan nilai dengan mapel Nahwu yang tidak ada di kurikulum Kelas 1 -> Gagal
        $respNilaiKurikulum = $this->actingAs($admin)->post(route('nilai.store'), [
            'kelas_id' => $kelas1->id,
            'mapel_id' => $mapelNahwu->id,
            'siswa_id' => $siswaKelas1->id,
            'semester' => 'Ganjil',
            'tahun_ajaran' => '2024/2025',
            'nilai_harian' => 85,
        ]);
        $respNilaiKurikulum->assertSessionHasErrors(['mapel_id']);

        // Simpan nilai untuk siswa yang bukan di kelas terpilih -> Gagal
        $respNilaiSiswa = $this->actingAs($admin)->post(route('nilai.store'), [
            'kelas_id' => $kelas7->id,
            'mapel_id' => $mapelMTs->id,
            'siswa_id' => $siswaKelas1->id, // Siswa kelas 1 diinput ke kelas 7
            'semester' => 'Ganjil',
            'tahun_ajaran' => '2024/2025',
            'nilai_harian' => 85,
        ]);
        $respNilaiSiswa->assertSessionHasErrors(['siswa_id']);

        // Simpan nilai yang valid
        $respNilaiValid = $this->actingAs($admin)->post(route('nilai.store'), [
            'kelas_id' => $kelas1->id,
            'mapel_id' => $mapelKelas1->id,
            'siswa_id' => $siswaKelas1->id,
            'semester' => 'Ganjil',
            'tahun_ajaran' => '2024/2025',
            'nilai_harian' => 88,
            'nilai_uts' => 85,
            'nilai_uas' => 90,
        ]);
        $respNilaiValid->assertRedirect(route('nilai.index'));

        // 5. Coba duplikasi nilai untuk siswa & mapel yang sama di semester & TA yang sama -> Gagal duplikasi
        $respNilaiDuplikat = $this->actingAs($admin)->post(route('nilai.store'), [
            'kelas_id' => $kelas1->id,
            'mapel_id' => $mapelKelas1->id,
            'siswa_id' => $siswaKelas1->id,
            'semester' => 'Ganjil',
            'tahun_ajaran' => '2024/2025',
            'nilai_harian' => 70,
        ]);
        $respNilaiDuplikat->assertSessionHasErrors(['siswa_id']);
    }
}
