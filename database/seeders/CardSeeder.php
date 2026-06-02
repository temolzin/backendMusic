<?php

namespace Database\Seeders;

use App\Models\Client;
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
        DB::statement('TRUNCATE TABLE clients RESTART IDENTITY CASCADE;');

        $users = User::whereHas('roles', function ($query) {
            $query->where('slug', User::ROLE_CLIENT);
        })->orderBy('id')->get();

        $cards = [
            ['type' => 'Visa', 'number' => '4111111111111111'],
            ['type' => 'Mastercard', 'number' => '5555555555554444'],
            ['type' => 'American Express', 'number' => '378282246310005'],
            ['type' => 'Discover', 'number' => '6011111111111117'],
            ['type' => 'Visa', 'number' => '4012888888881881'],
            ['type' => 'Mastercard', 'number' => '5105105105105100'],
        ];

        foreach ($users as $index => $user) {
            $card = $cards[$index % count($cards)];

            Client::create([
                'user_id' => $user->id,
                'number_card' => $card['number'],
                'name' => $user->name,
                'expiration_date' => '12/28',
            ]);
        }
    }
}
