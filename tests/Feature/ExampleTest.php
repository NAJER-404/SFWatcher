<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SpectralSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SpectralSeeder::class);
    }

    /**
     * Unauthenticated request to '/' should redirect to login.
     */
    public function test_unauthenticated_root_redirects_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    /**
     * Authenticated user can access the spectral dashboard.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $user = User::where('role', 'investigator')->first();
        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
    }
}

