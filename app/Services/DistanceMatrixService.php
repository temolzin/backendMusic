<?php

namespace App\Services;

use GuzzleHttp\Client;

class DistanceMatrixService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = env('GOOGLE_MAPS_API_KEY', '');
    }

    public function getDrivingDistanceInKm(string $origin, float $destLat, float $destLng): ?float
    {
        if (!$this->apiKey) {
            return null;
        }

        try {
            $response = $this->client->get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                'query' => [
                    'origins'      => $origin,
                    'destinations' => "{$destLat},{$destLng}",
                    'mode'         => 'driving',
                    'language'     => 'es',
                    'key'          => $this->apiKey,
                ],
                'timeout' => 10,
            ]);

            $data = json_decode($response->getBody(), true);

            if (
                ($data['status'] ?? '') !== 'OK' ||
                empty($data['rows'][0]['elements'][0]) ||
                $data['rows'][0]['elements'][0]['status'] !== 'OK'
            ) {
                return null;
            }

            $distanceMeters = $data['rows'][0]['elements'][0]['distance']['value'] ?? null;

            if ($distanceMeters === null) {
                return null;
            }

            return round($distanceMeters / 1000, 2);
        } catch (\Exception $e) {
            \Log::error('Distance Matrix API error: ' . $e->getMessage());
            return null;
        }
    }
}
