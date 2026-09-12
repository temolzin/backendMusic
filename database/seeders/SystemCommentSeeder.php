<?php

namespace Database\Seeders;

use App\Models\ArtistSale;
use App\Models\SystemComment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemCommentSeeder extends Seeder
{
    public function run()
    {
        DB::statement('TRUNCATE TABLE system_comments RESTART IDENTITY CASCADE;');

        $customerIds = ArtistSale::whereIn('status', [
            ArtistSale::PAYMENT_STATUS_AUTHORIZED,
            ArtistSale::PAYMENT_STATUS_COMPLETED,
            ArtistSale::PAYMENT_STATUS_LIQUIDATED,
        ])
            ->pluck('customer_id')
            ->unique()
            ->values()
            ->all();

        if (empty($customerIds)) {
            throw new \RuntimeException('No hay usuarios con ventas para sembrar comentarios.');
        }

        $samples = [
            [5, 'Excelente plataforma, contratar música nunca fue tan fácil. Todo el proceso fue rápido y seguro.'],
            [4, 'Muy buena experiencia, la comunicación con el artista fue directa y el pago estuvo protegido.'],
            [3, 'La opción de cotizar eventos es buena, pero me gustaría ver más información de los grupos.'],
            [2, 'Tardó en aparecer la confirmación del evento, por momentos no sabía si estaba todo en orden.'],
            [5, 'Me encantó la atención, pude coordinar la fecha del evento sin salir de casa.'],
            [1, 'Tuve problemas al registrar mi tarjeta y no se terminó de procesar el pago.'],
            [4, 'La sección de artistas favoritos y la tienda están muy bien organizadas.'],
            [5, 'La reserva fue inmediata y el reembolso por cancelación de COVID funcionó perfecto.'],
            [2, 'El chat tardaba en actualizar, aunque al final todo se resolvió bien.'],
            [4, 'Buen sistema de reseñas, los artistas contestan rápido las cotizaciones.'],
        ];

        foreach ($samples as $index => [$rating, $body]) {
            SystemComment::create([
                'user_id' => $customerIds[$index % count($customerIds)],
                'body'    => $body,
                'rating'  => $rating,
            ]);
        }
    }
}
