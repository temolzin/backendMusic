<?php

namespace Database\Seeders;

use App\Models\Card;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('TRUNCATE TABLE cards RESTART IDENTITY CASCADE;');

        $users = User::whereHas('roles', function ($query) {
            $query->where('slug', User::ROLE_CLIENT);
        })->orderBy('id')->get();

        $cards = [
            ['type' => 'Visa', 'number' => '4111 - 1111 - 1111 - 1111'],
            ['type' => 'Mastercard', 'number' => '5555 - 5555 - 5555 - 4444'],
            ['type' => 'American Express', 'number' => '3782 - 822463 - 10005'],
            ['type' => 'Discover', 'number' => '6011 - 1111 - 1111 - 1117'],
            ['type' => 'Visa', 'number' => '4012 - 8888 - 8888 - 1881'],
            ['type' => 'Mastercard', 'number' => '5105 - 1051 - 0510 - 5100'],
        ];

        foreach ($users as $index => $user) {
            $card = $cards[$index % count($cards)];

            Card::create([
                'user_id' => $user->id,
                'number_card' => $card['number'],
                'card_type' => $card['type'],
                'name' => $user->name,
                'expiration_date' => '12/28',
            ]);
        }
    }
}
