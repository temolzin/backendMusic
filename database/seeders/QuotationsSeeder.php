<?php

namespace Database\Seeders;

use App\Models\Quotations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuotationsSeeder extends Seeder
{
    public function run()
    {
        DB::statement('TRUNCATE TABLE quotations RESTART IDENTITY CASCADE;');

        $quotations = [
            ['artist_id' => 1,  'event_date' => '2026-06-15', 'event_hours' => 3, 'city' => 'CDMX',        'address' => 'Av. Insurgentes 123',    'phone' => '5512345678', 'email' => 'cliente1@gmail.com',  'full_name' => 'Carlos Ramírez',    'price' => 9000],
            ['artist_id' => 1,  'event_date' => '2026-07-04', 'event_hours' => 2, 'city' => 'Guadalajara', 'address' => 'Calle Morelos 45',       'phone' => '3312345678', 'email' => 'cliente2@gmail.com',  'full_name' => 'Ana Martínez',      'price' => 6000],
            ['artist_id' => 2,  'event_date' => '2026-06-20', 'event_hours' => 4, 'city' => 'Monterrey',   'address' => 'Blvd. Díaz Ordaz 88',   'phone' => '8112345678', 'email' => 'cliente3@gmail.com',  'full_name' => 'Luis Torres',       'price' => 12000],
            ['artist_id' => 2,  'event_date' => '2026-07-10', 'event_hours' => 2, 'city' => 'Puebla',      'address' => 'Av. Juárez 200',         'phone' => '2212345678', 'email' => 'cliente4@gmail.com',  'full_name' => 'María López',       'price' => 5000],
            ['artist_id' => 3,  'event_date' => '2026-08-10', 'event_hours' => 5, 'city' => 'Cancún',      'address' => 'Zona Hotelera Km 12',    'phone' => '9981234567', 'email' => 'cliente5@gmail.com',  'full_name' => 'Roberto Sánchez',   'price' => 15000],
            ['artist_id' => 3,  'event_date' => '2026-06-30', 'event_hours' => 3, 'city' => 'Tijuana',     'address' => 'Calle Revolución 500',   'phone' => '6641234567', 'email' => 'cliente6@gmail.com',  'full_name' => 'Sofía Herrera',     'price' => 9000],
            ['artist_id' => 4,  'event_date' => '2026-07-15', 'event_hours' => 2, 'city' => 'León',        'address' => 'Blvd. Torres Landa 90',  'phone' => '4771234567', 'email' => 'cliente7@gmail.com',  'full_name' => 'Diego Flores',      'price' => 6000],
            ['artist_id' => 4,  'event_date' => '2026-08-05', 'event_hours' => 4, 'city' => 'Mérida',      'address' => 'Calle 60 Norte 100',     'phone' => '9991234567', 'email' => 'cliente8@gmail.com',  'full_name' => 'Valentina Cruz',    'price' => 12000],
            ['artist_id' => 5,  'event_date' => '2026-07-22', 'event_hours' => 3, 'city' => 'Querétaro',   'address' => 'Av. Constituyentes 300', 'phone' => '4421234567', 'email' => 'cliente9@gmail.com',  'full_name' => 'Fernando Ruiz',     'price' => 9000],
            ['artist_id' => 5,  'event_date' => '2026-09-01', 'event_hours' => 2, 'city' => 'SLP',         'address' => 'Av. Venustiano 77',      'phone' => '4441234567', 'email' => 'cliente10@gmail.com', 'full_name' => 'Isabela Morales',   'price' => 6000],
            ['artist_id' => 6,  'event_date' => '2026-06-18', 'event_hours' => 5, 'city' => 'Veracruz',    'address' => 'Blvd. Manuel Ávila 55',  'phone' => '2291234567', 'email' => 'cliente11@gmail.com', 'full_name' => 'Andrés Jiménez',    'price' => 15000],
            ['artist_id' => 6,  'event_date' => '2026-07-30', 'event_hours' => 3, 'city' => 'Oaxaca',      'address' => 'Calle Macedonio 120',    'phone' => '9511234567', 'email' => 'cliente12@gmail.com', 'full_name' => 'Camila Vega',       'price' => 9000],
            ['artist_id' => 7,  'event_date' => '2026-08-20', 'event_hours' => 2, 'city' => 'Chihuahua',   'address' => 'Av. Tecnológico 400',    'phone' => '6141234567', 'email' => 'cliente13@gmail.com', 'full_name' => 'Pablo Mendoza',     'price' => 6000],
            ['artist_id' => 7,  'event_date' => '2026-09-10', 'event_hours' => 4, 'city' => 'Hermosillo',  'address' => 'Blvd. Kino 200',         'phone' => '6621234567', 'email' => 'cliente14@gmail.com', 'full_name' => 'Lucía Castillo',    'price' => 12000],
            ['artist_id' => 8,  'event_date' => '2026-07-08', 'event_hours' => 3, 'city' => 'Culiacán',    'address' => 'Calle Ángel Flores 88',  'phone' => '6671234567', 'email' => 'cliente15@gmail.com', 'full_name' => 'Emilio Reyes',      'price' => 9000],
            ['artist_id' => 8,  'event_date' => '2026-08-15', 'event_hours' => 2, 'city' => 'Acapulco',    'address' => 'Costera Miguel Alemán 1', 'phone' => '7441234567', 'email' => 'cliente16@gmail.com', 'full_name' => 'Natalia Vargas',    'price' => 6000],
            ['artist_id' => 9,  'event_date' => '2026-06-25', 'event_hours' => 5, 'city' => 'Toluca',      'address' => 'Av. Solidaridad 350',    'phone' => '7221234567', 'email' => 'cliente17@gmail.com', 'full_name' => 'Sebastián Ortiz',   'price' => 15000],
            ['artist_id' => 9,  'event_date' => '2026-07-18', 'event_hours' => 3, 'city' => 'Tuxtla',      'address' => 'Blvd. Belisario 450',    'phone' => '9611234567', 'email' => 'cliente18@gmail.com', 'full_name' => 'Valeria Medina',    'price' => 9000],
            ['artist_id' => 10, 'event_date' => '2026-08-28', 'event_hours' => 2, 'city' => 'Tampico',     'address' => 'Av. Hidalgo 600',        'phone' => '8331234567', 'email' => 'cliente19@gmail.com', 'full_name' => 'Javier Gutiérrez',  'price' => 6000],
            ['artist_id' => 10, 'event_date' => '2026-09-15', 'event_hours' => 4, 'city' => 'Durango',     'address' => 'Calle Zaragoza 75',      'phone' => '6181234567', 'email' => 'cliente20@gmail.com', 'full_name' => 'Daniela Ramírez',   'price' => 12000],
            ['artist_id' => 11, 'event_date' => '2026-07-12', 'event_hours' => 3, 'city' => 'Morelia',     'address' => 'Av. Lázaro Cárdenas 90', 'phone' => '4431234567', 'email' => 'cliente21@gmail.com', 'full_name' => 'Ricardo Peña',      'price' => 9000],
            ['artist_id' => 11, 'event_date' => '2026-08-22', 'event_hours' => 2, 'city' => 'Tepic',       'address' => 'Blvd. Luis Donaldo 55',  'phone' => '3111234567', 'email' => 'cliente22@gmail.com', 'full_name' => 'Mariana Aguilar',   'price' => 6000],
            ['artist_id' => 12, 'event_date' => '2026-06-22', 'event_hours' => 5, 'city' => 'Zacatecas',   'address' => 'Av. López Velarde 300',  'phone' => '4921234567', 'email' => 'cliente23@gmail.com', 'full_name' => 'Eduardo Navarro',   'price' => 15000],
            ['artist_id' => 12, 'event_date' => '2026-07-28', 'event_hours' => 3, 'city' => 'Colima',      'address' => 'Calle Madero 120',       'phone' => '3121234567', 'email' => 'cliente24@gmail.com', 'full_name' => 'Patricia Rojas',    'price' => 9000],
            ['artist_id' => 13, 'event_date' => '2026-08-18', 'event_hours' => 2, 'city' => 'Xalapa',      'address' => 'Av. Enríquez 450',       'phone' => '2281234567', 'email' => 'cliente25@gmail.com', 'full_name' => 'Alejandro Silva',   'price' => 6000],
            ['artist_id' => 13, 'event_date' => '2026-09-05', 'event_hours' => 4, 'city' => 'Mazatlán',    'address' => 'Av. del Mar 200',        'phone' => '6691234567', 'email' => 'cliente26@gmail.com', 'full_name' => 'Fernanda Ramos',    'price' => 12000],
            ['artist_id' => 14, 'event_date' => '2026-07-02', 'event_hours' => 3, 'city' => 'Irapuato',    'address' => 'Blvd. Insurgentes 180',  'phone' => '4621234567', 'email' => 'cliente27@gmail.com', 'full_name' => 'Miguel Ángel Luna', 'price' => 9000],
            ['artist_id' => 14, 'event_date' => '2026-08-12', 'event_hours' => 2, 'city' => 'Ensenada',    'address' => 'Av. Reforma 95',         'phone' => '6461234567', 'email' => 'cliente28@gmail.com', 'full_name' => 'Gabriela Fuentes',  'price' => 6000],
            ['artist_id' => 15, 'event_date' => '2026-06-28', 'event_hours' => 5, 'city' => 'Los Mochis',  'address' => 'Av. Jiquilpan 400',      'phone' => '6681234567', 'email' => 'cliente29@gmail.com', 'full_name' => 'Héctor Domínguez',  'price' => 15000],
            ['artist_id' => 15, 'event_date' => '2026-09-20', 'event_hours' => 3, 'city' => 'La Paz',      'address' => 'Blvd. Forjadores 600',   'phone' => '6121234567', 'email' => 'cliente30@gmail.com', 'full_name' => 'Ximena Guerrero',   'price' => 9000],
        ];

        foreach ($quotations as $quotation) {
            Quotations::create($quotation);
        }
    }
}
