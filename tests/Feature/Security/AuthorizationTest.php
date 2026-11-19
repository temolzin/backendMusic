<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;
    use CreatesUsers;

    public function test_guest_cannot_access_protected_routes(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
    }

    public function test_client_cannot_access_admin_users(): void
    {
        $this->actingAs($this->userWithRole('cliente'), 'api')
            ->getJson('/api/admin/users')
            ->assertForbidden();
    }

    public function test_client_cannot_access_admin_dashboard(): void
    {
        $this->actingAs($this->userWithRole('cliente'), 'api')
            ->getJson('/api/admin/dashboard-overview')
            ->assertForbidden();
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $this->actingAs($this->userWithRole('administrador'), 'api')
            ->getJson('/api/admin/dashboard-overview')
            ->assertOk();
    }
}
