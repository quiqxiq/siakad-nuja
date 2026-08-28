<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class LandingPageTest extends TestCase
{
    public function test_landing_page_renders_with_all_sections(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('id="beranda"', false);
        $response->assertSee('id="tentang"', false);
        $response->assertDontSee('id="fitur"', false);
        $response->assertSee('id="peran"', false);
        $response->assertSee('id="galeri"', false);
        $response->assertSee('id="statistik"', false);
    }
}
