<?php

namespace Tests\Feature\ShoppingCart;

use App\Models\Artist;
use App\Models\Offer;
use App\Models\ShoppingCard;
use App\Models\ShoppingCardDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class CartPriceRefreshTest extends TestCase
{
    use RefreshDatabase;
    use CreatesUsers;

    public function test_cart_total_is_refreshed_with_the_current_price_and_offer(): void
    {
        $client = $this->userWithRole('cliente');

        $artist = Artist::create([
            'user_id' => User::factory()->create()->id,
            'name' => 'Test Artist',
            'slug' => 'test-artist',
            'members' => 1,
            'history' => 'Test history',
            'zone' => 'Test zone',
            'price_hour' => 10000,
            'extra_kilometre' => 0,
            'coverage_radius' => 0,
        ]);

        Offer::create([
            'artist_id' => $artist->id,
            'description' => 'Active offer',
            'discount_percentage' => 15,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'is_active' => true,
        ]);

        $cart = ShoppingCard::create([
            'user_id' => $client->id,
            'status' => ShoppingCard::STATUS_ACTIVE,
            'order_date_start' => now(),
            'total' => 20000,
        ]);

        ShoppingCardDetail::create([
            'shopping_card_id' => $cart->id,
            'artist_id' => $artist->id,
            'hours' => 2,
            'price' => 10000,
        ]);

        $response = $this->actingAs($client, 'api')
            ->getJson('/api/cliente/shopping_card/listShopingCardDetails');

        $response->assertOk();
        $this->assertEquals(17000, $response->json('list_shoping_card_details.0.total'));
        $this->assertEquals(8500, (float) ShoppingCardDetail::first()->price);
    }
}
