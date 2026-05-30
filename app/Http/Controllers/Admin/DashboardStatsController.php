<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\MusicalGender;
use App\Models\Quotations;
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

            $cards = [
                $this->makeCard(
                    'users',
                    'Usuarios registrados',
                    User::count(),
                    [
                        [
                            'label' => 'Verificados',
                            'value' => User::whereNotNull('email_verified_at')->count(),
                        ],
                        [
                            'label' => 'Sin verificar',
                            'value' => User::whereNull('email_verified_at')->count(),
                        ],
                        [
                            'label' => 'Nuevos últimos ' . $periodDays . ' días',
                            'value' => User::where('created_at', '>=', $periodStart)->count(),
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
                    'quotations',
                    'Cotizaciones',
                    Quotations::count(),
                    [
                        [
                            'label' => 'Últimos ' . $periodDays . ' días',
                            'value' => Quotations::where('created_at', '>=', $periodStart)->count(),
                        ],
                        [
                            'label' => 'Este mes',
                            'value' => Quotations::whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->count(),
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
