<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Traits\CreatesUsers;

class AuthorizationTest extends TestCase
{
    use CreatesUsers;

    protected bool $seed = true;

    /** @test */
    public function admin_can_access_admin_routes(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertStatus(200);
    }

    /** @test */
    public function non_admin_cannot_access_admin_routes(): void
    {
        $almoxarife = $this->createUser();

        $response = $this->actingAs($almoxarife)->get('/admin/users');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_access_reports(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/reports');

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_create_users(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/users/create');

        $response->assertStatus(200);
    }

    /** @test */
    public function authenticated_users_can_access_dashboard(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    /** @test */
    public function authenticated_users_can_view_products(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/products');

        $response->assertStatus(200);
    }

    /** @test */
    public function authenticated_users_can_view_stock(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/stock');

        $response->assertStatus(200);
    }

    /** @test */
    public function authenticated_users_can_view_loans(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/loans');

        $response->assertStatus(200);
    }
}
