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

        foreach ($users as $index => $user) {
            $cardSuffix = str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);

            Client::create([
                'user_id' => $user->id,
                'number_card' => '411111111111' . $cardSuffix,
                'name' => $user->name,
                'expiration_date' => '12/28',
            ]);
        }
    }
}
