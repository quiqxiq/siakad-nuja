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

    public function test_verify_and_reset_fails_with_wrong_otp(): void
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
        ])->post(route('password.reset_attempt'), [
            'otp' => '654321', // wrong OTP
            'password' => 'new_password_123',
            'password_confirmation' => 'new_password_123',
        ]);

        $response->assertSessionHasErrors(['otp']);
    }

    public function test_verify_and_reset_fails_if_otp_expired(): void
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
        ])->post(route('password.reset_attempt'), [
            'otp' => '123456',
            'password' => 'new_password_123',
            'password_confirmation' => 'new_password_123',
        ]);

        $response->assertSessionHasErrors(['otp']);
    }

    public function test_verify_and_reset_succeeds_and_updates_password(): void
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
        ])->post(route('password.reset_attempt'), [
            'otp' => '889900',
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
