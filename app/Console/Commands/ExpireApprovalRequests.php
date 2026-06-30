<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ArtistSale;
use App\Models\OpenpayKey;
use Carbon\Carbon;
use Openpay\Data\Openpay;
use Illuminate\Support\Facades\Log;

class ExpireApprovalRequests extends Command
{
    protected $signature = 'approvals:expire';
    protected $description = 'Expira solicitudes de aprobación de artista que llevan más de 24h sin respuesta';

    public function handle()
    {
        $expired = ArtistSale::where('approval_status', 'pending_approval')
            ->whereNotNull('approval_deadline')
            ->where('approval_deadline', '<', Carbon::now())
            ->get();

        $keys = OpenpayKey::first();
        $openpay = Openpay::getInstance($keys->openpay_id, $keys->openpay_secret, 'MX');
        Openpay::setProductionMode(false);

        $count = 0;
        foreach ($expired as $sale) {
            if ($sale->payment_method === 'card' && $sale->openpay_transaction_id) {
                try {
                    $charge = $openpay->charges->get($sale->openpay_transaction_id);
                    $charge->refund(['description' => 'Solicitud expirada sin respuesta del artista']);
                } catch (\Exception $e) {
                    Log::warning("No se pudo cancelar autorización OpenPay para sale {$sale->id}: " . $e->getMessage());
                }
            }

            $sale->approval_status = 'expired';
            $sale->event_status = 'expired';
            $sale->save();
            $count++;
        }

        $this->info("Solicitudes expiradas: {$count}");
        return 0;
    }
}
