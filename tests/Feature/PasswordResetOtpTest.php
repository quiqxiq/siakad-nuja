<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\User;
use App\Services\WhatsappGatewayService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class PasswordResetOtpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock WhatsappGatewayService agar tidak memanggil sidecar nyata saat running unit/feature tests
        $mockGateway = Mockery::mock(WhatsappGatewayService::class);
        $mockGateway->shouldReceive('normalisasiNomor')
            ->andReturnUsing(function ($no) {
                $no = preg_replace('/[^0-9]/', '', $no);
                if (str_starts_with($no, '0')) {
                    return '62'.substr($no, 1);
                }

                return $no;
            });
        $mockGateway->shouldReceive('sendNotification')->andReturn(true);
        $mockGateway->shouldReceive('send')->andReturn(true);

        $this->app->instance(WhatsappGatewayService::class, $mockGateway);
    }

    public function test_guest_can_access_forgot_password_page(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertStatus(200);
        $response->assertSee('Lupa Password?');
        $response->assertSee('Kirim Kode OTP via WhatsApp');
    }

    public function test_send_otp_success_with_email(): void
    {
        $user = User::factory()->create([
            'email' => 'guru_test@siakadnuja.sch.id',
            'no_hp' => '081234567890',
            'is_active' => true,
        ]);

        $response = $this->post(route('password.send_otp'), [
            'identifier' => 'guru_test@siakadnuja.sch.id',
        ]);

        $response->assertRedirect(route('password.verify_form'));
        $response->assertSessionHas('password_reset_user_id', $user->id);

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }

    public function test_send_otp_success_with_username_admin(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@siakadnuja.sch.id',
            'no_hp' => '081200000001',
            'is_active' => true,
            'role' => 'admin',
        ]);

        $response = $this->post(route('password.send_otp'), [
            'identifier' => 'admin',
        ]);

        $response->assertRedirect(route('password.verify_form'));
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $admin->email,
        ]);
    }

    public function test_send_otp_success_with_nip(): void
    {
        $user = User::factory()->create([
            'email' => 'guru_nip@siakadnuja.sch.id',
            'no_hp' => '081299998888',
            'is_active' => true,
        ]);

        Guru::create([
            'user_id' => $user->id,
            'nip' => '198901012020',
            'nama_lengkap' => 'Ustadz Testing',
            'no_hp' => $user->no_hp,
        ]);

        $response = $this->post(route('password.send_otp'), [
            'identifier' => '198901012020',
        ]);

        $response->assertRedirect(route('password.verify_form'));
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }

    public function test_send_otp_fails_if_user_not_found(): void
    {
        $response = $this->post(route('password.send_otp'), [
            'identifier' => 'unknown@siakadnuja.sch.id',
        ]);

        $response->assertSessionHasErrors(['identifier']);
    }

    public function test_send_otp_fails_if_user_inactive(): void
    {
        User::factory()->create([
            'email' => 'nonaktif@siakadnuja.sch.id',
            'no_hp' => '081234567890',
            'is_active' => false,
        ]);

        $response = $this->post(route('password.send_otp'), [
            'identifier' => 'nonaktif@siakadnuja.sch.id',
        ]);

        $response->assertSessionHasErrors(['identifier']);
    }

    public function test_send_otp_fails_if_user_has_no_phone(): void
    {
        User::factory()->create([
            'email' => 'tanpa_hp@siakadnuja.sch.id',
            'no_hp' => null,
            'is_active' => true,
        ]);

        $response = $this->post(route('password.send_otp'), [
            'identifier' => 'tanpa_hp@siakadnuja.sch.id',
        ]);

        $response->assertSessionHasErrors(['identifier']);
    }

    public function test_verify_form_redirects_without_session(): void
    {
        $response = $this->get(route('password.verify_form'));

        $response->assertRedirect(route('password.request'));
    }

    public function test_verify_form_shows_otp_input_only(): void
    {
        $user = User::factory()->create([
            'email' => 'guru_screen@siakadnuja.sch.id',
            'no_hp' => '081234567890',
            'is_active' => true,
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('123456'),
            'created_at' => now()->subMinutes(2),
        ]);

        $response = $this->withSession([
            'password_reset_user_id' => $user->id,
            'password_reset_email' => $user->email,
            'password_reset_phone' => '0812-****-**90',
            'password_reset_sent_at_'.$user->id => now()->subSeconds(15),
        ])->get(route('password.verify_form'));

        $response->assertStatus(200);
        $response->assertSee('Kode OTP (6 Digit)');
        $response->assertSee('Verifikasi Kode OTP');
        $response->assertSee('Kode kedaluwarsa dalam:');
        $response->assertSee('Kirim Ulang OTP');
        // Pastikan input password baru TIDAK ADA di halaman verifikasi OTP ini
        $response->assertDontSee('Buat Password Baru');
        $response->assertDontSee('Konfirmasi Password Baru');

        // Pastikan viewData expiresIn bernilai sekitar 480 detik (8 menit tersisa dari 10 menit)
        $expiresIn = $response->viewData('expiresIn');
        $this->assertGreaterThan(450, $expiresIn);
        $this->assertLessThanOrEqual(480, $expiresIn);

        // Pastikan viewData cooldown bernilai sekitar 45 detik (60 - 15)
        $cooldown = $response->viewData('cooldown');
        $this->assertGreaterThan(40, $cooldown);
        $this->assertLessThanOrEqual(45, $cooldown);
    }

    public function test_send_otp_enforces_cooldown_with_formatted_message(): void
    {
        $user = User::factory()->create([
            'email' => 'guru_cooldown@siakadnuja.sch.id',
            'no_hp' => '081234567890',
            'is_active' => true,
        ]);

        $this->post(route('password.send_otp'), [
            'identifier' => $user->email,
        ]);

        // Percobaan kirim kedua langsung setelahnya (dalam 60 detik)
        $response = $this->post(route('password.send_otp'), [
            'identifier' => $user->email,
        ]);

        $response->assertSessionHasErrors(['identifier']);
        $errorMessage = session('errors')->first('identifier');
        $this->assertStringContainsString('Harap tunggu', $errorMessage);
        $this->assertStringContainsString('sebelum meminta kode OTP kembali.', $errorMessage);
        $this->assertTrue(str_contains($errorMessage, 'menit') || str_contains($errorMessage, 'detik'));
    }

    public function test_resend_otp_enforces_cooldown_with_formatted_message(): void
    {
        $user = User::factory()->create([
            'email' => 'guru_resend@siakadnuja.sch.id',
            'no_hp' => '081234567890',
            'is_active' => true,
        ]);

        $response = $this->withSession([
            'password_reset_user_id' => $user->id,
            'password_reset_email' => $user->email,
            'password_reset_sent_at_'.$user->id => now()->subSeconds(20),
        ])->post(route('password.resend_otp'));

        $response->assertSessionHas('error');
        $error = session('error');
        $this->assertStringContainsString('Harap tunggu', $error);
        $this->assertStringContainsString('40 detik', $error);
    }

    public function test_verify_otp_fails_with_wrong_otp(): void
    {
        $user = User::factory()->create([
            'email' => 'guru_reset@siakadnuja.sch.id',
            'no_hp' => '081234567890',
            'is_active' => true,
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('123456'),
            'created_at' => now(),
        ]);

        $response = $this->withSession([
            'password_reset_user_id' => $user->id,
            'password_reset_email' => $user->email,
        ])->post(route('password.verify_otp'), [
            'otp' => '654321', // wrong OTP
        ]);

        $response->assertSessionHasErrors(['otp']);
    }

    public function test_verify_otp_fails_if_otp_expired(): void
    {
        $user = User::factory()->create([
            'email' => 'guru_reset@siakadnuja.sch.id',
            'no_hp' => '081234567890',
            'is_active' => true,
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('123456'),
            'created_at' => Carbon::now()->subMinutes(11), // 11 mins ago (expired)
        ]);

        $response = $this->withSession([
            'password_reset_user_id' => $user->id,
            'password_reset_email' => $user->email,
        ])->post(route('password.verify_otp'), [
            'otp' => '123456',
        ]);

        $response->assertSessionHasErrors(['otp']);
    }

    public function test_verify_otp_succeeds_and_redirects_to_reset_form(): void
    {
        $user = User::factory()->create([
            'email' => 'guru_otp_ok@siakadnuja.sch.id',
            'no_hp' => '081234567890',
            'is_active' => true,
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('123456'),
            'created_at' => now(),
        ]);

        $response = $this->withSession([
            'password_reset_user_id' => $user->id,
            'password_reset_email' => $user->email,
        ])->post(route('password.verify_otp'), [
            'otp' => '123456',
        ]);

        $response->assertRedirect(route('password.reset_form'));
        $response->assertSessionHas('password_reset_otp_verified', true);
    }

    public function test_reset_form_redirects_without_verified_otp_session(): void
    {
        $response = $this->get(route('password.reset_form'));
        $response->assertRedirect(route('password.request'));

        $user = User::factory()->create();
        $response2 = $this->withSession([
            'password_reset_user_id' => $user->id,
            'password_reset_email' => $user->email,
            // belum verified
        ])->get(route('password.reset_form'));

        $response2->assertRedirect(route('password.request'));
    }

    public function test_reset_form_accessible_when_otp_verified(): void
    {
        $user = User::factory()->create([
            'nama' => 'Guru Berjaya',
            'email' => 'guru_berjaya@siakadnuja.sch.id',
            'no_hp' => '081234567890',
            'is_active' => true,
        ]);

        $response = $this->withSession([
            'password_reset_user_id' => $user->id,
            'password_reset_email' => $user->email,
            'password_reset_otp_verified' => true,
        ])->get(route('password.reset_form'));

        $response->assertStatus(200);
        $response->assertSee('Buat Password Baru');
        $response->assertSee('Simpan Password Baru');
    }

    public function test_reset_password_fails_if_confirmation_mismatch(): void
    {
        $user = User::factory()->create([
            'email' => 'guru_reset_fail@siakadnuja.sch.id',
            'no_hp' => '081234567890',
            'is_active' => true,
        ]);

        $response = $this->withSession([
            'password_reset_user_id' => $user->id,
            'password_reset_email' => $user->email,
            'password_reset_otp_verified' => true,
        ])->post(route('password.reset_password'), [
            'password' => 'new_password_123',
            'password_confirmation' => 'mismatched_password',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_reset_password_succeeds_and_redirects_to_login(): void
    {
        $user = User::factory()->create([
            'email' => 'guru_berhasil@siakadnuja.sch.id',
            'password' => Hash::make('old_password'),
            'no_hp' => '081234567890',
            'is_active' => true,
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('889900'),
            'created_at' => now(),
        ]);

        $response = $this->withSession([
            'password_reset_user_id' => $user->id,
            'password_reset_email' => $user->email,
            'password_reset_otp_verified' => true,
        ])->post(route('password.reset_password'), [
            'password' => 'password_baru_123',
            'password_confirmation' => 'password_baru_123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        // Token OTP harus terhapus
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $user->email,
        ]);

        // Cek password baru di DB
        $user->refresh();
        $this->assertTrue(Hash::check('password_baru_123', $user->password));

        // Coba login dengan password baru
        $loginResponse = $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'password_baru_123',
        ]);

        $loginResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }
}
