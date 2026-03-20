<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Traits\CreatesUsers;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthenticationTest extends TestCase
{
    use CreatesUsers;

    protected bool $seed = true;

    /** @test */
    public function login_page_is_displayed(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('HelpSub');
        $response->assertSee('Número de Identidade');
    }

    /** @test */
    public function users_can_authenticate_with_valid_credentials(): void
    {
        $user = $this->createAdmin([
            'identity_number' => '123456789',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'identity_number' => '123456789',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    /** @test */
    public function users_cannot_authenticate_with_invalid_password(): void
    {
        $user = $this->createAdmin([
            'identity_number' => '123456789',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'identity_number' => '123456789',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors();
    }

    /** @test */
    public function users_cannot_authenticate_with_invalid_identity_number(): void
    {
        $response = $this->post('/login', [
            'identity_number' => '999999999',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors();
    }

    /** @test */
    public function inactive_users_cannot_login(): void
    {
        $user = $this->createAdmin([
            'identity_number' => '123456789',
            'password' => Hash::make('password'),
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'identity_number' => '123456789',
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    /** @test */
    public function authenticated_users_can_logout(): void
    {
        $user = $this->createAdmin();

        $this->actingAs($user);
        $this->assertAuthenticated();

        $response = $this->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    /** @test */
    public function unauthenticated_users_cannot_access_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function inactive_users_are_logged_out_automatically(): void
    {
        $user = $this->createAdmin([
            'is_active' => true,
        ]);

        $this->actingAs($user);
        $this->assertAuthenticated();

        // Desativar usuário
        $user->update(['is_active' => false]);

        // Tentar acessar dashboard
        $response = $this->get('/dashboard');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    /** @test */
    public function root_redirects_to_login_when_unauthenticated(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function root_redirects_to_dashboard_when_authenticated(): void
    {
        $user = $this->createAdmin();

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect('/dashboard');
    }

    /** @test */
    public function login_validation_requires_identity_number(): void
    {
        $response = $this->post('/login', [
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['identity_number']);
    }

    /** @test */
    public function login_validation_requires_password(): void
    {
        $response = $this->post('/login', [
            'identity_number' => '123456789',
        ]);

        $response->assertSessionHasErrors(['password']);
    }
}
