<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Artist;
use App\Models\ArtistSale;
use App\Models\ArtistRating;
use App\Models\UserSanction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    private function getMonthName(int $month): string
    {
        $months = [
            1 => 'Enero',    2 => 'Febrero',   3 => 'Marzo',
            4 => 'Abril',    5 => 'Mayo',       6 => 'Junio',
            7 => 'Julio',    8 => 'Agosto',     9 => 'Septiembre',
            10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        return $months[$month] ?? 'Mes ' . $month;
    }

    private function formatDate(Carbon $date): string
    {
        $day   = $date->format('d');
        $month = $this->getMonthName((int) $date->format('n'));
        $year  = $date->format('Y');
        return "{$day} de {$month} de {$year}";
    }

    private function calcTrend(float $current, float $previous, string $suffix = 'nuevos'): string
    {
        if ($previous == 0 && $current == 0) {
            return 'Sin datos';
        }
        if ($previous == 0) {
            return '+' . number_format($current, 0) . ' ' . $suffix;
        }
        $change  = (($current - $previous) / $previous) * 100;
        $sign    = $change >= 0 ? '+' : '';
        $rounded = number_format(abs($change), 1);
        return $sign . ($change < 0 ? '-' : '') . $rounded . '%';
    }

    private function calcRatingTrend(float $current, float $previous): string
    {
        if ($previous == 0 && $current == 0) {
            return 'Sin datos';
        }
        if ($previous == 0) {
            return '+' . number_format($current, 1) . ' pts en el período';
        }
        $diff = $current - $previous;
        $sign = $diff >= 0 ? '+' : '';
        return $sign . number_format($diff, 1) . ' pts';
    }

    private function calcIncomeTrend(float $current, float $previous): string
    {
        if ($previous == 0 && $current == 0) {
            return 'Sin datos';
        }
        if ($previous == 0) {
            return '+$' . number_format($current, 2) . ' en el período';
        }
        $change  = (($current - $previous) / $previous) * 100;
        $sign    = $change >= 0 ? '+' : '';
        $rounded = number_format(abs($change), 1);
        return $sign . ($change < 0 ? '-' : '') . $rounded . '%';
    }

    private function calculateNetIncome($sales): float
    {
        return (float) $sales->sum(function ($sale) {
            $amount      = floatval($sale->amount);
            $openpayFee  = floatval($sale->openpay_fee);
            $platformFee = $amount * 0.10;
            return max(0, $amount - $openpayFee - $platformFee);
        });
    }

    private function getChartData(string $filter, int $artistId): array
    {
        $chartLabels = [];
        $chartSeries = [];
        if ($filter === 'Por semana' || strtolower($filter) === 'week') {
            $incomeByWeek = [];
            for ($w = 5; $w >= 0; $w--) {
                $weekKey = Carbon::now()->startOfWeek()->subWeeks($w)->format('Y-W');
                $incomeByWeek[$weekKey] = 0;
            }
            $sixWeeksAgo = Carbon::now()->startOfWeek()->subWeeks(5);
            $salesLastWeeks = ArtistSale::where('artist_id', $artistId)
                ->where('event_status', ArtistSale::EVENT_STATUS_COMPLETED)
                ->where('created_at', '>=', $sixWeeksAgo)
                ->get();
            foreach ($salesLastWeeks as $sale) {
                $weekKey = Carbon::parse($sale->created_at)->format('Y-W');
                if (isset($incomeByWeek[$weekKey])) {
                    $amount      = floatval($sale->amount);
                    $openpayFee  = floatval($sale->openpay_fee);
                    $platformFee = $amount * 0.10;
                    $net         = max(0, $amount - $openpayFee - $platformFee);
                    $incomeByWeek[$weekKey] += $net;
                }
            }
            foreach ($incomeByWeek as $weekKey => $total) {
                list($year, $weekNum) = explode('-', $weekKey);
                $chartLabels[] = 'Semana ' . intval($weekNum);
                $chartSeries[] = round((float) $total, 2);
            }
            return [
                'labels' => $chartLabels,
                'series' => $chartSeries,
            ];
        }
        $incomeByMonth = [];
        for ($m = 5; $m >= 0; $m--) {
            $monthNum = (int) Carbon::now()->startOfMonth()->subMonths($m)->format('n');
            $incomeByMonth[$monthNum] = 0;
        }
        $sixMonthsAgo = Carbon::now()->startOfMonth()->subMonths(5);
        $salesLast6Months = ArtistSale::where('artist_id', $artistId)
            ->where('event_status', ArtistSale::EVENT_STATUS_COMPLETED)
            ->where('created_at', '>=', $sixMonthsAgo)
            ->get();
        foreach ($salesLast6Months as $sale) {
            $monthNum = (int) Carbon::parse($sale->created_at)->format('n');
            if (isset($incomeByMonth[$monthNum])) {
                $amount      = floatval($sale->amount);
                $openpayFee  = floatval($sale->openpay_fee);
                $platformFee = $amount * 0.10;
                $net         = max(0, $amount - $openpayFee - $platformFee);
                $incomeByMonth[$monthNum] += $net;
            }
        }
        foreach ($incomeByMonth as $month => $total) {
            $chartLabels[] = $this->getMonthName((int) $month);
            $chartSeries[] = round((float) $total, 2);
        }
        return [
            'labels' => $chartLabels,
            'series' => $chartSeries,
        ];
    }

    public function getArtistsList()
    {
        try {
            $artists = Artist::select('id', 'name', 'user_id', 'zone')
                ->with('user:id,name')
                ->orderBy('name', 'asc')
                ->get()
                ->map(function ($artist) {
                    return [
                        'id'    => $artist->id,
                        'name'  => $artist->name ?? optional($artist->user)->name ?? 'Artista',
                        'image' => $artist->image ?? '',
                    ];
                });
            return response()->json([
                'success' => true,
                'data'    => $artists,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function getArtistStats(Request $request, $artistId)
    {
        try {
            $artist = Artist::with(['user', 'musicalGenders'])->findOrFail($artistId);
            $isActive = optional($artist->user)->account_status !== 'restricted';
            $currentStart  = Carbon::now()->startOfMonth();
            $currentEnd    = Carbon::now()->endOfMonth();
            $previousStart = Carbon::now()->subMonth()->startOfMonth();
            $previousEnd   = Carbon::now()->subMonth()->endOfMonth();
            $currentRating  = ArtistRating::where('artist_id', $artistId)
                ->whereBetween('created_at', [$currentStart, $currentEnd])
                ->avg('rating') ?? 0;
            $previousRating = ArtistRating::where('artist_id', $artistId)
                ->whereBetween('created_at', [$previousStart, $previousEnd])
                ->avg('rating') ?? 0;
            $averageRating  = ArtistRating::where('artist_id', $artistId)->avg('rating') ?? 0;
            $currentSales  = ArtistSale::where('artist_id', $artistId)
                ->where('event_status', ArtistSale::EVENT_STATUS_COMPLETED)
                ->whereBetween('created_at', [$currentStart, $currentEnd])
                ->get();
            $previousSales = ArtistSale::where('artist_id', $artistId)
                ->where('event_status', ArtistSale::EVENT_STATUS_COMPLETED)
                ->whereBetween('created_at', [$previousStart, $previousEnd])
                ->get();
            $allSales        = ArtistSale::where('artist_id', $artistId)->get();
            $totalIncome     = $this->calculateNetIncome($allSales->where('event_status', ArtistSale::EVENT_STATUS_COMPLETED));
            $completedEvents = $allSales->where('event_status', ArtistSale::EVENT_STATUS_COMPLETED)->count();
            $totalContracts  = $allSales->count();
            $currentIncome   = $this->calculateNetIncome($currentSales);
            $previousIncome  = $this->calculateNetIncome($previousSales);
            $currentEvents   = $currentSales->count();
            $previousEvents  = $previousSales->count();
            $currentContracts  = ArtistSale::where('artist_id', $artistId)
                ->whereBetween('created_at', [$currentStart, $currentEnd])
                ->count();
            $previousContracts = ArtistSale::where('artist_id', $artistId)
                ->whereBetween('created_at', [$previousStart, $previousEnd])
                ->count();
            $sanctionsCount   = UserSanction::where('user_id', $artist->user_id)->count();
            $currentSanctions = UserSanction::where('user_id', $artist->user_id)
                ->whereBetween('created_at', [$currentStart, $currentEnd])
                ->count();
            $filter    = $request->query('filter', 'Por mes');
            $chartData = $this->getChartData($filter, $artistId);
            $upcomingEvents = ArtistSale::where('artist_id', $artistId)
                ->where('approval_status', ArtistSale::APPROVAL_STATUS_ACCEPTED)
                ->where('event_status', ArtistSale::EVENT_STATUS_PENDING)
                ->whereDate('event_date', '>=', Carbon::today())
                ->orderBy('event_date', 'asc')
                ->take(3)
                ->get()
                ->map(function ($event) {
                    $date = Carbon::parse($event->event_date);
                    return [
                        'id'       => $event->id,
                        'title'    => $event->customer_first_name
                            ? 'Evento de ' . $event->customer_first_name
                            : 'Evento',
                        'location' => $event->customer_city ?? 'Por definir',
                        'date'     => $date->format('d') . ' ' . $this->getMonthName((int) $date->format('n')),
                        'status'   => $event->event_status,
                    ];
                });
            return response()->json([
                'success' => true,
                'data'    => [
                    'profile' => [
                        'name'         => $artist->name ?? optional($artist->user)->name ?? 'Artista',
                        'is_active'    => $isActive,
                        'location'     => $artist->zone ?? 'No especificada',
                        'member_since' => $this->formatDate($artist->created_at),
                        'members'      => $artist->members ?? 1,
                        'genders'      => $artist->musicalGenders->pluck('name'),
                        'avatar'       => $artist->image ?? '',
                        'socials'      => $artist->social_media ?? [],
                    ],
                    'kpis' => [
                        'rating'          => round((float) $averageRating, 1),
                        'income'          => number_format((float) $totalIncome, 2),
                        'events'          => $completedEvents,
                        'contracts'       => $totalContracts,
                        'sanctions'       => $sanctionsCount,
                        'rating_trend'    => $this->calcRatingTrend((float) $currentRating, (float) $previousRating),
                        'income_trend'    => $this->calcIncomeTrend((float) $currentIncome, (float) $previousIncome),
                        'events_trend'    => $this->calcTrend((float) $currentEvents, (float) $previousEvents, 'nuevos'),
                        'hires_trend'     => $this->calcTrend((float) $currentContracts, (float) $previousContracts, 'nuevas'),
                        'sanctions_trend' => $currentSanctions . ' nuevas',
                    ],
                    'chart'          => $chartData,
                    'upcoming_events' => $upcomingEvents->values(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
