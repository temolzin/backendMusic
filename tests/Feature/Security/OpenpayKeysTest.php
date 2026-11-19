<?php

namespace Tests\Feature\Security;

use App\Models\OpenpayKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class OpenpayKeysTest extends TestCase
{
    use RefreshDatabase;
    use CreatesUsers;

    private function seedKeys(): void
    {
        OpenpayKey::create([
            'openpay_id' => 'mz_test_merchant',
            'openpay_secret' => 'sk_test_secret_value',
            'openpay_public_key' => 'pk_test_public_value',
            'openpay_sandbox_mode' => true,
        ]);
    }

    public function test_openpay_secret_is_not_exposed_in_get_keys(): void
    {
        $this->seedKeys();

        $admin = $this->userWithRole('administrador');

        $response = $this->actingAs($admin, 'api')
            ->getJson('/api/admin/openpay-keys');

        $response->assertOk();
        $this->assertArrayNotHasKey('openpay_secret', $response->json('data') ?? []);
        $this->assertStringNotContainsString('sk_test_secret_value', $response->getContent());
    }

    public function test_non_admin_cannot_read_openpay_keys(): void
    {
        $this->seedKeys();

        $this->actingAs($this->userWithRole('cliente'), 'api')
            ->getJson('/api/admin/openpay-keys')
            ->assertForbidden();
    }

    public function test_public_keys_endpoint_only_returns_public_values(): void
    {
        $this->seedKeys();

        $response = $this->getJson('/api/openpay-keys/public');

        $response->assertOk()
            ->assertJsonPath('data.openpay_id', 'mz_test_merchant')
            ->assertJsonPath('data.openpay_public_key', 'pk_test_public_value');

        $this->assertArrayNotHasKey('openpay_secret', $response->json('data') ?? []);
        $this->assertStringNotContainsString('sk_test_secret_value', $response->getContent());
    }

    public function test_non_admin_cannot_read_webhook_verification_codes(): void
    {
        $this->actingAs($this->userWithRole('cliente'), 'api')
            ->getJson('/api/admin/webhook-verification-codes')
            ->assertForbidden();
    }

    public function test_admin_can_read_webhook_verification_codes(): void
    {
        $this->actingAs($this->userWithRole('administrador'), 'api')
            ->getJson('/api/admin/webhook-verification-codes')
            ->assertOk();
    }

    public function test_non_admin_cannot_update_openpay_keys(): void
    {
        $this->seedKeys();

        $client = $this->userWithRole('cliente');

        $this->actingAs($client, 'api')
            ->putJson('/api/admin/openpay-keys', [
                'openpay_id' => 'changed',
                'openpay_secret' => 'changed',
                'openpay_public_key' => 'changed',
                'openpay_sandbox_mode' => true,
            ])
            ->assertForbidden();
    }
}
