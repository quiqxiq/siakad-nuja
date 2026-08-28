<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    public function test_admin_can_login_with_correct_credentials(): void
    {
        $user = User::create([
            'nama' => 'Administrator',
            'email' => 'admin@siakadnuja.sch.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->post(route('login.attempt'), [
            'email' => 'admin@siakadnuja.sch.id',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_incorrect_password(): void
    {
        User::create([
            'nama' => 'Administrator',
            'email' => 'admin@siakadnuja.sch.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->post(route('login.attempt'), [
            'email' => 'admin@siakadnuja.sch.id',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors(['email' => 'Email atau password salah.']);
        $this->assertGuest();
    }

    public function test_admin_can_login_with_username_admin(): void
    {
        $user = User::create([
            'nama' => 'Administrator',
            'email' => 'admin@siakadnuja.sch.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->post(route('login.attempt'), [
            'email' => 'admin',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }
}
