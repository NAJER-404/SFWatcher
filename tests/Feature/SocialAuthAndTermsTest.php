<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SpectralSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class SocialAuthAndTermsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SpectralSeeder::class);
    }

    public function test_login_page_placeholders_and_google_button(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);

        // Required placeholders
        $response->assertSee('placeholder="Enter email"', false);
        $response->assertSee('placeholder="Enter password"', false);

        // Disallowed phrase
        $response->assertDontSee('Optional for direct email sign-in');

        // Google button present with route
        $response->assertSee('Continue with Google');
        $response->assertSee(route('auth.google'));

        // Removed options
        $response->assertDontSee('Continue with GitHub');
        $response->assertDontSee('Sign in as Administrator');
    }

    public function test_registration_page_placeholders_and_terms_checkbox(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);

        // Required placeholders on registration
        $response->assertSee('placeholder="Enter email"', false);
        $response->assertSee('placeholder="Enter password"', false);

        // Terms of Use and Privacy Policy checkbox
        $response->assertSee('name="terms"', false);
        $response->assertSee('Terms of Use and Privacy Policy');
        $response->assertSee('id="terms-modal"', false);
    }

    public function test_registration_fails_without_terms_accepted(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Test Citizen',
            'email'                 => 'citizen@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            // 'terms' omitted
        ]);

        $response->assertSessionHasErrors('terms');
        $this->assertDatabaseMissing('users', ['email' => 'citizen@example.com']);
    }

    public function test_registration_succeeds_and_strictly_assigns_reporter_role(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'New Observer',
            'email'                 => 'newobserver@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'investigator', // Attempting to inject admin role
            'terms'                 => '1',
        ]);

        $response->assertRedirect('/login');

        $user = User::where('email', 'newobserver@example.com')->first();
        $this->assertNotNull($user);
        // Ensure role is strictly reporter
        $this->assertEquals('reporter', $user->role);
        $this->assertTrue($user->isReporter());
        $this->assertFalse($user->isInvestigator());
    }

    public function test_normal_login_requires_valid_credentials(): void
    {
        $testUser = User::create([
            'name'     => 'Local Reporter',
            'email'    => 'local.reporter@example.com',
            'password' => bcrypt('password123'),
            'role'     => 'reporter',
        ]);

        // 1. Fails without password
        $response = $this->post('/login', [
            'email' => 'local.reporter@example.com',
        ]);
        $response->assertSessionHasErrors('password');

        // 2. Fails with wrong password
        $response = $this->post('/login', [
            'email'    => 'local.reporter@example.com',
            'password' => 'wrongpassword',
        ]);
        $response->assertSessionHasErrors('email');

        // 3. Succeeds with correct credentials
        $response = $this->post('/login', [
            'email'    => 'local.reporter@example.com',
            'password' => 'password123',
        ]);
        $response->assertRedirect(route('spectral.dashboard'));
        $this->assertAuthenticatedAs($testUser);
    }

    public function test_google_redirect_warns_when_credentials_not_configured(): void
    {
        config(['services.google.client_id' => '']);
        config(['services.google.client_secret' => '']);

        $response = $this->get('/auth/google');

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('google');
    }

    public function test_google_oauth_callback_creates_new_reporter_user_with_socialite(): void
    {
        $abstractUser = Mockery::mock(\Laravel\Socialite\Two\User::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-unique-998877');
        $abstractUser->shouldReceive('getEmail')->andReturn('spectrawatch.new@gmail.com');
        $abstractUser->shouldReceive('getName')->andReturn('SpectraWatch Google User');
        $abstractUser->shouldReceive('getNickname')->andReturn(null);

        $provider = Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get('/auth/google/callback?code=mock_authorization_code');

        $response->assertRedirect(route('spectral.dashboard'));
        $this->assertAuthenticated();

        $user = User::where('email', 'spectrawatch.new@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('google-unique-998877', $user->google_id);
        $this->assertEquals('reporter', $user->role);
        $this->assertTrue($user->isReporter());
        $this->assertFalse($user->isInvestigator());
    }

    public function test_google_oauth_callback_links_existing_user_without_duplication(): void
    {
        // Existing user in database without google_id
        $existingUser = User::create([
            'name'     => 'Existing Citizen',
            'email'    => 'existing.citizen@gmail.com',
            'password' => bcrypt('secret123'),
            'role'     => 'reporter',
        ]);

        $abstractUser = Mockery::mock(\Laravel\Socialite\Two\User::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-linked-445566');
        $abstractUser->shouldReceive('getEmail')->andReturn('existing.citizen@gmail.com');
        $abstractUser->shouldReceive('getName')->andReturn('Existing Citizen');
        $abstractUser->shouldReceive('getNickname')->andReturn(null);

        $provider = Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get('/auth/google/callback?code=mock_authorization_code_2');

        $response->assertRedirect(route('spectral.dashboard'));
        $this->assertAuthenticatedAs($existingUser);

        // Ensure no duplicate was created and google_id was updated
        $this->assertEquals(1, User::where('email', 'existing.citizen@gmail.com')->count());
        $this->assertEquals('google-linked-445566', $existingUser->fresh()->google_id);
    }
}
