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
        $expired = ArtistSale::where('approval_status', ArtistSale::APPROVAL_STATUS_PENDING)
            ->whereNotNull('approval_deadline')
            ->where('approval_deadline', '<', Carbon::now())
            ->get();

        $keys = OpenpayKey::first();
        $clientIp = $this->getClientIp();
        $openpay = Openpay::getInstance($keys->openpay_id, $keys->openpay_secret, 'MX', $clientIp);
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

            $sale->status = ArtistSale::PAYMENT_STATUS_CANCELLED;
            $sale->approval_status = ArtistSale::APPROVAL_STATUS_EXPIRED;
            $sale->event_status = ArtistSale::EVENT_STATUS_EXPIRED;
            $sale->approval_responded_at = Carbon::now();
            $sale->save();
            $count++;
        }

        $this->info("Solicitudes expiradas: {$count}");
        return 0;
    }

    protected function getClientIp()
    {
        $headers = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'HTTP_CF_CONNECTING_IP',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                foreach ($ips as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                        return $ip;
                    }
                }
            }
        }

        return '187.188.12.50';
    }
}
