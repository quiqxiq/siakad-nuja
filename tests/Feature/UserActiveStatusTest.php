<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserActiveStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_user_cannot_access_dashboard(): void
    {
        $inactiveUser = User::factory()->create([
            'role' => 'guru',
            'is_active' => false,
        ]);

        $response = $this->actingAs($inactiveUser)->get(route('dashboard'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
