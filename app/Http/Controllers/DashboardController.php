<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\Driver;
use App\Models\Office;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $lastMonth = $now->copy()->subMonth();
        $startOfLastMonth = $lastMonth->copy()->startOfMonth();
        $endOfLastMonth = $lastMonth->copy()->endOfMonth();

        // 1. KPI Stats
        $monthlyShipments = Shipment::whereBetween('created_at', [$startOfMonth, $now])->count();
        $lastMonthShipments = Shipment::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $shipmentsChange = $this->calculateChange($monthlyShipments, $lastMonthShipments);

        $inTransit = Shipment::where('current_status', 'in_transit')->count();
        $delivered = Shipment::where('current_status', 'delivered')->whereBetween('created_at', [$startOfMonth, $now])->count();

        $monthlyRevenue = Invoice::whereBetween('created_at', [$startOfMonth, $now])
            ->where('type', 'con')
            ->where('status', '!=', 'Anulada')
            ->sum('total');
        $lastMonthRevenue = Invoice::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->where('type', 'con')
            ->where('status', '!=', 'Anulada')
            ->sum('total');
        $revenueChange = $this->calculateChange($monthlyRevenue, $lastMonthRevenue);

        // 2. Chart Data (Last 7 months for trend)
        $shipmentsChart = $this->getMonthlyTrend('count');
        $revenueChart = $this->getMonthlyTrend('sum');

        // 3. Status Distribution
        $statusDistribution = Shipment::select('current_status', DB::raw('count(*) as total'))
            ->groupBy('current_status')
            ->get()
            ->mapWithKeys(fn($item) => [$item->current_status => $item->total]);

        // 4. Recent Shipments
        $recentShipments = Shipment::with(['originOffice.city', 'destinationOffice.city', 'sender', 'receiver'])
            ->latest()
            ->limit(5)
            ->get();

        $recentShipmentsResource = \App\Http\Resources\Shipment\ShipmentResource::collection($recentShipments);

        // 5. Office Performance (Shipments sent by office)
        $officePerformance = Office::withCount('shipmentsSent')
            ->orderByDesc('shipments_sent_count')
            ->limit(5)
            ->get()
            ->map(fn($office) => [
                'name' => $office->name,
                'total' => $office->shipments_sent_count,
            ]);

        // 6. Active Drivers Summary
        $activeDrivers = Driver::with('user')->where('status', 'active')->limit(5)->get();
        $activeDriversResource = \App\Http\Resources\Driver\DriverResource::collection($activeDrivers);

        $activeDriversCount = Driver::where('status', 'active')->count();
        $totalOfficesCount = Office::where('status', 'active')->count();

        return response()->json([
            'kpi' => [
                [
                    'label' => 'Total Encomiendas (Mes)',
                    'value' => number_format($monthlyShipments),
                    'change' => $shipmentsChange,
                    'trend' => $shipmentsChange >= 0 ? 'up' : 'down',
                    'icon' => 'Package'
                ],
                [
                    'label' => 'En Tránsito',
                    'value' => number_format($inTransit),
                    'change' => 0, // Simplified
                    'trend' => 'up',
                    'icon' => 'Truck'
                ],
                [
                    'label' => 'Entregadas (Mes)',
                    'value' => number_format($delivered),
                    'change' => 0, // Simplified
                    'trend' => 'up',
                    'icon' => 'CheckCircle2'
                ],
                [
                    'label' => 'Ingresos (Mes)',
                    'value' => 'Bs ' . number_format($monthlyRevenue, 1),
                    'change' => $revenueChange,
                    'trend' => $revenueChange >= 0 ? 'up' : 'down',
                    'icon' => 'TrendingUp'
                ],
            ],
            'charts' => [
                'shipments' => $shipmentsChart,
                'revenue' => $revenueChart,
                'status' => $statusDistribution
            ],
            'recent_shipments' => $recentShipmentsResource,
            'office_performance' => $officePerformance,
            'active_drivers' => $activeDriversResource,
            'active_drivers_count' => $activeDriversCount,
            'total_offices_count' => $totalOfficesCount
        ]);
    }

    private function calculateChange($current, $previous)
    {
        if ($previous == 0) return 0;
        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function getMonthlyTrend($type)
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);

            if ($type === 'sum') {
                $query = Invoice::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->where('type', 'con')
                    ->where('status', '!=', 'Anulada');
                $value = $query->sum('total');
            } else {
                $query = Shipment::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year);
                $value = $query->count();
            }

            $data[] = [
                'month' => $date->format('M'),
                'value' => $value
            ];
        }
        return $data;
    }
}
