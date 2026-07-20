<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserSanction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class LiftExpiredSanctions extends Command
{
    protected $signature = 'sanctions:lift-expired';
    protected $description = 'Revisa sanciones vencidas y reactiva las cuentas de los usuarios automáticamente';

    public function handle()
    {
        $usersToReview = User::where('account_status', 'restricted')->get();
        $count = 0;

        foreach ($usersToReview as $user) {
            $latestSanction = UserSanction::where('user_id', $user->id)
                ->latest('created_at')
                ->first();

            if ($latestSanction && $latestSanction->ends_at && $latestSanction->ends_at <= Carbon::now()) {
                $user->account_status = 'active';
                $user->save();

                Log::info("Cuenta reactivada automáticamente para el usuario ID: {$user->id} (Sanción ID: {$latestSanction->id} expirada).");
                $count++;
            }
        }

        $this->info("Cuentas reactivadas: {$count}");
        return 0;
    }
}
