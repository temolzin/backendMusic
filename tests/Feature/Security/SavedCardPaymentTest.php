<?php

namespace Tests\Feature\Security;

use App\Models\Card;
use App\Models\OpenpayKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class SavedCardPaymentTest extends TestCase
{
    use RefreshDatabase;
    use CreatesUsers;

    protected function setUp(): void
    {
        parent::setUp();

        OpenpayKey::create([
            'openpay_id' => 'm_test_merchant',
            'openpay_secret' => 'sk_test_secret',
            'openpay_public_key' => 'pk_test_public',
            'openpay_sandbox_mode' => true,
        ]);
    }

    public function test_client_cannot_pay_with_a_card_from_another_user(): void
    {
        $owner = $this->userWithRole('cliente');
        $intruder = $this->userWithRole('cliente');
        $card = $this->cardFor($owner);

        $this->actingAs($intruder, 'api')
            ->postJson('/api/process-payment', [
                'saved_card_id' => $card->id,
                'cvv' => '123',
            ])
            ->assertNotFound();
    }

    public function test_saved_card_payment_requires_the_cvv(): void
    {
        $client = $this->userWithRole('cliente');
        $card = $this->cardFor($client);

        $this->actingAs($client, 'api')
            ->postJson('/api/process-payment', [
                'saved_card_id' => $card->id,
            ])
            ->assertStatus(422);
    }

    private function cardFor(User $user): Card
    {
        return Card::create([
            'user_id' => $user->id,
            'number_card' => '4111111111111111',
            'card_type' => 'visa',
            'name' => 'Test Holder',
            'expiration_date' => '12/30',
        ]);
    }
}
