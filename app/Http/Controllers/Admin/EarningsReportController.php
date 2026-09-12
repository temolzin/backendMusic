<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\ArtistSale;
use Illuminate\Http\Request;

class EarningsReportController extends Controller
{
    private const PLATFORM_COMMISSION = 0.10;

    private function getMonthName(int $month): string
    {
        $months = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo',
            4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
            7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre',
            10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        return $months[$month] ?? 'Mes ' . $month;
    }

    private function calculateNetIncome(float $amount, float $openpayFee): float
    {
        $platformFee = $amount * self::PLATFORM_COMMISSION;
        return max(0, $amount - $openpayFee - $platformFee);
    }

    private function calculateTrend(float $current, float $previous, string $suffix = 'en el período'): string
    {
        if ($previous == 0 && $current == 0) {
            return 'Sin datos';
        }
        if ($previous == 0) {
            return '+$' . number_format($current, 2) . ' ' . $suffix;
        }
        $change = (($current - $previous) / $previous) * 100;
        $sign = $change >= 0 ? '+' : '-';
        $rounded = number_format(abs($change), 1);
        return $sign . $rounded . '%';
    }

    private function buildBucketsInRange(string $period, Carbon $from, Carbon $to): array
    {
        $buckets = [];
        $cursor = $period === 'week'
            ? $from->copy()->startOfWeek()
            : ($period === 'year' ? $from->copy()->startOfYear() : $from->copy()->startOfMonth());
        $monthWithYear = $period !== 'week' && $period !== 'year' && $from->format('Y') !== $to->format('Y');

        $guard = 0;
        while ($cursor->lte($to) && $guard < 600) {
            $key = $cursor->format($this->resolveBucketKeyFormat($period));
            if (!isset($buckets[$key])) {
                $label = $period === 'week'
                    ? 'Semana ' . intval($cursor->format('W'))
                    : ($period === 'year'
                        ? $cursor->format('Y')
                        : $this->getMonthName((int) $cursor->format('n')) . ($monthWithYear ? ' ' . $cursor->format('Y') : ''));
                $buckets[$key] = [
                    'label' => $label,
                    'net_sales' => 0.0,
                    'platform_earnings' => 0.0,
                    'events' => 0,
                ];
            }
            $stepMethod = $period === 'week'
                ? 'addWeek'
                : ($period === 'year' ? 'addYear' : 'addMonth');
            $cursor->{$stepMethod}();
            $guard++;
        }

        return $buckets;
    }

    private function resolveBucketKeyFormat(string $period): string
    {
        return $period === 'week' ? 'Y-W' : ($period === 'year' ? 'Y' : 'Y-m');
    }

    private function buildReportData(string $period, ?string $from = null, ?string $to = null): array
    {
        $fromDate = $from ? Carbon::parse($from)->startOfDay() : null;
        $toDate = $to ? Carbon::parse($to)->endOfDay() : null;

        if (!$fromDate || !$toDate || $fromDate->gt($toDate)) {
            $fromDate = $period === 'week'
                ? Carbon::now()->startOfWeek()->subWeeks(5)
                : ($period === 'year' ? Carbon::now()->startOfYear()->subYears(4) : Carbon::now()->startOfMonth()->subMonths(5));
            $toDate = Carbon::now();
        }
        $buckets = $this->buildBucketsInRange($period, $fromDate, $toDate);

        $keyFormat = $this->resolveBucketKeyFormat($period);

        $query = ArtistSale::with(['artist.musicalGenders', 'eventType'])
            ->where('status', ArtistSale::PAYMENT_STATUS_COMPLETED)
            ->where('created_at', '>=', $fromDate)
            ->where('created_at', '<=', $toDate);

        $completedSales = $query->get();

        $contractsCount = ArtistSale::where('created_at', '>=', $fromDate)
            ->where('created_at', '<=', $toDate)
            ->count();

        foreach ($completedSales as $sale) {
            $key = Carbon::parse($sale->created_at)->format($keyFormat);
            if (!isset($buckets[$key])) {
                continue;
            }
            $amount = floatval($sale->amount);
            $openpayFee = floatval($sale->openpay_fee);
            $buckets[$key]['net_sales'] += $this->calculateNetIncome($amount, $openpayFee);
            $buckets[$key]['platform_earnings'] += round($amount * self::PLATFORM_COMMISSION, 2);
            $buckets[$key]['events']++;
        }

        $chartLabels = [];
        $netSeries = [];
        $platformSeries = [];
        $breakdown = [];
        $totalNet = 0.0;
        $totalPlatform = 0.0;
        $totalEvents = 0;

        foreach ($buckets as $bucket) {
            $chartLabels[] = $bucket['label'];
            $netSeries[] = round($bucket['net_sales'], 2);
            $platformSeries[] = round($bucket['platform_earnings'], 2);
            $breakdown[] = $bucket;
            $totalNet += $bucket['net_sales'];
            $totalPlatform += $bucket['platform_earnings'];
            $totalEvents += $bucket['events'];
        }

        $values = array_values($buckets);
        $last = $values[count($values) - 1]['net_sales'];
        $previous = count($values) > 1 ? $values[count($values) - 2]['net_sales'] : 0;
        $lastPlatform = $values[count($values) - 1]['platform_earnings'];
        $previousPlatform = count($values) > 1 ? $values[count($values) - 2]['platform_earnings'] : 0;
        $lastEvents = $values[count($values) - 1]['events'];
        $previousEvents = count($values) > 1 ? $values[count($values) - 2]['events'] : 0;

        $topArtists = $completedSales
            ->groupBy('artist_id')
            ->map(function ($group) {
                $net = $group->sum(function ($sale) {
                    return $this->calculateNetIncome(
                        floatval($sale->amount),
                        floatval($sale->openpay_fee)
                    );
                });
                $platform = $group->sum(function ($sale) {
                    return floatval($sale->amount) * self::PLATFORM_COMMISSION;
                });
                return [
                    'id' => $group->first()->artist_id,
                    'name' => optional($group->first()->artist)->name ?? 'Artista',
                    'net_sales' => round($net, 2),
                    'platform_earnings' => round($platform, 2),
                    'events' => $group->count(),
                ];
            })
            ->sortByDesc('net_sales')
            ->take(5)
            ->values();

        $genreMap = [];
        foreach ($completedSales as $sale) {
            $net = $this->calculateNetIncome(
                floatval($sale->amount),
                floatval($sale->openpay_fee)
            );
            $genres = optional($sale->artist)->musicalGenders ?? collect();
            if ($genres->isEmpty()) {
                $genreMap['Sin género'] = ($genreMap['Sin género'] ?? 0) + $net;
                continue;
            }
            $share = $net / $genres->count();
            foreach ($genres as $genre) {
                $genreMap[$genre->name] = ($genreMap[$genre->name] ?? 0) + $share;
            }
        }
        arsort($genreMap);

        $eventTypeMap = [];
        foreach ($completedSales as $sale) {
            $net = $this->calculateNetIncome(
                floatval($sale->amount),
                floatval($sale->openpay_fee)
            );
            $eventTypeName = optional($sale->eventType)->name ?? 'Sin tipo';
            $eventTypeMap[$eventTypeName] = ($eventTypeMap[$eventTypeName] ?? 0) + $net;
        }
        arsort($eventTypeMap);

        return [
            'period' => $period,
            'kpis' => [
                'net_sales' => round($totalNet, 2),
                'platform_earnings' => round($totalPlatform, 2),
                'events' => $totalEvents,
                'contracts' => $contractsCount,
                'avg_ticket' => $totalEvents > 0 ? round($totalNet / $totalEvents, 2) : 0,
                'net_sales_trend' => $this->calculateTrend($last, $previous),
                'platform_earnings_trend' => $this->calculateTrend($lastPlatform, $previousPlatform),
                'events_trend' => $this->calculateTrend((float) $lastEvents, (float) $previousEvents, 'eventos'),
            ],
            'chart' => [
                'labels' => $chartLabels,
                'series' => [
                    'net_sales' => $netSeries,
                    'platform_earnings' => $platformSeries,
                ],
            ],
            'top_artists' => $topArtists,
            'genres' => collect($genreMap)
                ->take(6)
                ->map(function ($net, $name) {
                    return ['name' => $name, 'net_sales' => round($net, 2)];
                })
                ->values(),
            'event_types' => collect($eventTypeMap)
                ->take(6)
                ->map(function ($net, $name) {
                    return ['name' => $name, 'net_sales' => round($net, 2)];
                })
                ->values(),
            'breakdown' => $breakdown,
        ];
    }

    public function getEarnings(Request $request)
    {
        try {
            $period = in_array($request->query('period', 'month'), ['week', 'month', 'year'])
                ? $request->query('period')
                : 'month';
            $from = $request->query('from');
            $to = $request->query('to');

            return response()->json([
                'success' => true,
                'data' => $this->buildReportData($period, $from, $to),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
