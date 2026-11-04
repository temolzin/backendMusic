<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersSubscribeSeeder extends Seeder
{
    /**
     * Run the emails seeds for subscribed users.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['email' => 'dylan@yopmail.com'],
            ['email' => 'fernando@yopmail.com'],
        ];

        DB::table('users_subscribe')->insert($data);
    }
}
