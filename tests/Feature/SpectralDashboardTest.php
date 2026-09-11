<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SpectralSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpectralDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SpectralSeeder::class);
        $this->user = User::where('role', 'investigator')->first();
    }

    public function test_spectral_dashboard_renders_successfully(): void
    {
        $response = $this->actingAs($this->user)->get('/spectral');

        $response->assertStatus(200);
        $response->assertSee('Spectra');
        $response->assertSee('Incident');
        $response->assertSee('San Francisco, Agusan del Sur');
        $response->assertSee('id="spectral-map"', false);
        $response->assertSee('id="location-picker-hud"', false);
        $response->assertSee('id="report-modal"', false);
        $response->assertSee('css/spectral.css');
        $response->assertSee('js/spectral.js');
    }

    public function test_ecto_alias_redirects_to_spectral(): void
    {
        // Authenticated: /ecto should redirect to spectral.dashboard
        $response = $this->actingAs($this->user)->get('/ecto');
        $response->assertRedirect(route('spectral.dashboard'));
    }

    public function test_unauthenticated_access_redirects_to_login(): void
    {
        $this->get('/spectral')->assertRedirect('/login');
        $this->get('/')->assertRedirect('/login');
    }
}

