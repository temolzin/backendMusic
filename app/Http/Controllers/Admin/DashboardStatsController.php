<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\ArtistSale;
use App\Models\MusicalGender;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardStatsController extends Controller
{
    public function index(Request $request)
    {
        try {
            $periodDays = (int) $request->input('period_days', 30);
            $periodDays = $periodDays > 0 ? $periodDays : 30;
            $periodStart = Carbon::now()->subDays($periodDays);
            $clients = User::whereHas('roles', function ($query) {
                $query->where('roles.id', 3);
            });

            $cards = [
                $this->makeCard(
                    'users',
                    'Clientes registrados',
                    (clone $clients)->count(),
                    [
                        [
                            'label' => 'Nuevos últimos ' . $periodDays . ' días',
                            'value' => (clone $clients)->where('created_at', '>=', $periodStart)->count(),
                        ],
                    ]
                ),
                $this->makeCard(
                    'musical_genders',
                    'Géneros musicales',
                    MusicalGender::count(),
                    [
                        [
                            'label' => 'Con artistas asociados',
                            'value' => MusicalGender::whereHas('artists')->count(),
                        ],
                        [
                            'label' => 'Sin artistas asociados',
                            'value' => MusicalGender::whereDoesntHave('artists')->count(),
                        ],
                        [
                            'label' => 'Creados últimos ' . $periodDays . ' días',
                            'value' => MusicalGender::where('created_at', '>=', $periodStart)->count(),
                        ],
                    ]
                ),
                $this->makeCard(
                    'artists',
                    'Artistas registrados',
                    Artist::count(),
                    [
                        [
                            'label' => 'Con manager',
                            'value' => Artist::has('manager')->count(),
                        ],
                        [
                            'label' => 'Sin manager',
                            'value' => Artist::doesntHave('manager')->count(),
                        ],
                        [
                            'label' => 'Nuevos últimos ' . $periodDays . ' días',
                            'value' => Artist::where('created_at', '>=', $periodStart)->count(),
                        ],
                    ]
                ),
                $this->makeCard(
                    'sales',
                    'Ventas',
                    ArtistSale::count(),
                    [
                        [
                            'label' => 'Monto acumulado',
                            'value' => (float) ArtistSale::sum('amount'),
                        ],
                        [
                            'label' => 'Monto últimos ' . $periodDays . ' días',
                            'value' => (float) ArtistSale::where('created_at', '>=', $periodStart)->sum('amount'),
                        ],
                        [
                            'label' => 'Ticket promedio últimos ' . $periodDays . ' días',
                            'value' => round((float) ArtistSale::where('created_at', '>=', $periodStart)->avg('amount'), 2),
                        ],
                    ]
                ),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'period_days' => $periodDays,
                    'generated_at' => Carbon::now()->toIso8601String(),
                    'cards' => $cards,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function makeCard(string $key, string $label, int $total, array $breakdown): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'total' => $total,
            'breakdown' => $breakdown,
        ];
    }
}
