<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    private function applyShipmentRoleFilters($query)
    {
        $user = request()->user();
        if ($user) {
            if ($user->role === 'worker') {
                $officeIds = $user->offices->pluck('id')->toArray();
                $query->where(function ($q) use ($officeIds) {
                    $q->whereIn('origin_office_id', $officeIds)
                        ->orWhereIn('destination_office_id', $officeIds);
                });
            } elseif ($user->role === 'company') {
                $query->where(function ($q) use ($user) {
                    $q->where('origin_office_id', $user->id)
                        ->orWhere('destination_office_id', $user->id);
                });
            }
        }
        return $query;
    }

    public function index(Request $request)
    {
        $startDate = $request->query('start_date') ? Carbon::parse($request->query('start_date'))->startOfDay() : Carbon::now()->startOfMonth();
        $endDate = $request->query('end_date') ? Carbon::parse($request->query('end_date'))->endOfDay() : Carbon::now()->endOfDay();

        // 1. KPI Stats
        $monthlyRevenue = Invoice::whereBetween('created_at', [$startDate, $endDate])
            ->where('type', 'con')
            ->where('status', '!=', 'Anulada')
            ->whereHas('shipment', fn($q) => $this->applyShipmentRoleFilters($q))
            ->sum('total');

        $totalShipments = $this->applyShipmentRoleFilters(Shipment::whereBetween('created_at', [$startDate, $endDate]))->count();
        $deliveredShipments = $this->applyShipmentRoleFilters(Shipment::where('current_status', 'delivered')->whereBetween('created_at', [$startDate, $endDate]))->count();
        $deliveryEfficiency = $totalShipments > 0 ? round(($deliveredShipments / $totalShipments) * 100, 1) : 0;

        $activeUsers = $this->applyShipmentRoleFilters(Shipment::whereBetween('created_at', [$startDate, $endDate]))->distinct('sender_id')->count('sender_id');

        // 2. Revenue Chart (Aggregate by day or month)
        $diffInDays = $startDate->diffInDays($endDate);

        if ($diffInDays <= 62) { // Allow up to 2 months of daily view before switching to monthly
            // Daily aggregation
            $revenueQuery = Invoice::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as revenue')
            )
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('type', 'con')
                ->where('status', '!=', 'Anulada')
                ->whereHas('shipment', fn($q) => $this->applyShipmentRoleFilters($q))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $revenueChart = $revenueQuery->map(function ($item) {
                return [
                    'label' => Carbon::parse($item->date)->format('d M'),
                    'revenue' => (float)$item->revenue
                ];
            });

            // Shipment volume (Daily)
            $volumeQuery = $this->applyShipmentRoleFilters(Shipment::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as packages')
            )
                ->whereBetween('created_at', [$startDate, $endDate]))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $volumeChart = $volumeQuery->map(function ($item) {
                return [
                    'label' => Carbon::parse($item->date)->format('d M'),
                    'packages' => (int)$item->packages
                ];
            });
        } else {
            // Monthly aggregation
            $revenueQuery = Invoice::select(
                DB::raw('YEAR(created_at) as year, MONTH(created_at) as month'),
                DB::raw('SUM(total) as revenue')
            )
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('type', 'con')
                ->where('status', '!=', 'Anulada')
                ->whereHas('shipment', fn($q) => $this->applyShipmentRoleFilters($q))
                ->groupBy('year', 'month')
                ->orderBy('year')->orderBy('month')
                ->get();

            $revenueChart = $revenueQuery->map(function ($item) {
                $date = Carbon::createFromDate($item->year, $item->month, 1);
                return [
                    'label' => $date->format('M Y'),
                    'revenue' => (float)$item->revenue
                ];
            });

            // Shipment volume (Monthly)
            $volumeQuery = $this->applyShipmentRoleFilters(Shipment::select(
                DB::raw('YEAR(created_at) as year, MONTH(created_at) as month'),
                DB::raw('COUNT(*) as packages')
            )
                ->whereBetween('created_at', [$startDate, $endDate]))
                ->groupBy('year', 'month')
                ->orderBy('year')->orderBy('month')
                ->get();

            $volumeChart = $volumeQuery->map(function ($item) {
                $date = Carbon::createFromDate($item->year, $item->month, 1);
                return [
                    'label' => $date->format('M Y'),
                    'packages' => (int)$item->packages
                ];
            });
        }

        // 3. Status Distribution
        $statusDistribution = $this->applyShipmentRoleFilters(Shipment::select('current_status as browser', DB::raw('count(*) as visitors'))
            ->whereBetween('created_at', [$startDate, $endDate]))
            ->groupBy('current_status')
            ->get()
            ->map(function ($item) {
                return [
                    'browser' => $item->browser,
                    'visitors' => $item->visitors,
                    'fill' => 'var(--color-' . $item->browser . ')'
                ];
            });

        return response()->json([
            'kpi' => [
                'revenue' => $monthlyRevenue,
                'shipments' => $totalShipments,
                'activeUsers' => $activeUsers,
                'efficiency' => $deliveryEfficiency
            ],
            'charts' => [
                'revenue' => $revenueChart,
                'volume' => $volumeChart,
                'status' => $statusDistribution
            ]
        ]);
    }
}
