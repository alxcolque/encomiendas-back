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
    private $cachedOfficeIds = -1;

    /**
     * Get IDs of the offices directly assigned to the current worker in office_user.
     * Returns null for admin (sees all), [] for unassigned workers.
     */
    private function getTargetOfficeIds()
    {
        if ($this->cachedOfficeIds !== -1) return $this->cachedOfficeIds;

        $user = auth()->user();
        if (!$user) return $this->cachedOfficeIds = [];

        if ($user->role === 'admin') {
            return $this->cachedOfficeIds = null; // Admin sees all
        }

        if ($user->role === 'worker') {
            // Only the offices directly assigned in office_user
            $officeIds = DB::table('office_user')
                ->where('user_id', $user->id)
                ->pluck('office_id')
                ->toArray();

            return $this->cachedOfficeIds = $officeIds;
        }

        return $this->cachedOfficeIds = [];
    }

    /**
     * Apply role-based filters to a shipment query.
     */
    private function applyShipmentRoleFilters($query)
    {
        $user = auth()->user();
        if (!$user) return $query;

        if ($user->role === 'worker') {
            $officeIds = $this->getTargetOfficeIds();

            if (empty($officeIds)) {
                $query->whereRaw('1 = 0');
                return $query;
            }

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

        return $query;
    }


    public function index()
    {
        $user = auth()->user();
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $lastMonth = $now->copy()->subMonth();
        $startOfLastMonth = $lastMonth->copy()->startOfMonth();
        $endOfLastMonth = $lastMonth->copy()->endOfMonth();

        $officeIds = $this->getTargetOfficeIds();
        $cityName = null;
        if ($user->role === 'worker') {
            // Get the name of the assigned office's city (first assigned office)
            $cityName = DB::table('office_user')
                ->join('offices', 'office_user.office_id', '=', 'offices.id')
                ->join('cities', 'offices.city_id', '=', 'cities.id')
                ->where('office_user.user_id', $user->id)
                ->value('cities.name');
        }

        // 1. KPI Stats
        $monthlyShipments = $this->applyShipmentRoleFilters(Shipment::whereBetween('created_at', [$startOfMonth, $now]))->count();
        $lastMonthShipments = $this->applyShipmentRoleFilters(Shipment::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth]))->count();
        $shipmentsChange = $this->calculateChange($monthlyShipments, $lastMonthShipments);

        $inTransit = $this->applyShipmentRoleFilters(Shipment::where('current_status', 'in_transit'))->count();
        $delivered = $this->applyShipmentRoleFilters(Shipment::where('current_status', 'delivered')->whereBetween('created_at', [$startOfMonth, $now]))->count();

        $monthlyRevenue = Invoice::whereBetween('created_at', [$startOfMonth, $now])
            ->where('type', 'con')
            ->where('status', '!=', 'Anulada')
            ->whereHas('shipment', function($q) {
                $this->applyShipmentRoleFilters($q);
            })
            ->sum('total');
        
        $lastMonthRevenue = Invoice::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->where('type', 'con')
            ->where('status', '!=', 'Anulada')
            ->whereHas('shipment', function($q) {
                $this->applyShipmentRoleFilters($q);
            })
            ->sum('total');
        
        $revenueChange = $this->calculateChange($monthlyRevenue, $lastMonthRevenue);

        // 2. Chart Data
        $shipmentsChart = $this->getMonthlyTrend('count');
        $revenueChart = $this->getMonthlyTrend('sum');

        // 3. Status Distribution
        $statusDistribution = $this->applyShipmentRoleFilters(Shipment::select('current_status', DB::raw('count(*) as total')))
            ->groupBy('current_status')
            ->get()
            ->mapWithKeys(fn($item) => [$item->current_status => $item->total]);

        // 4. Recent Shipments
        $recentShipments = $this->applyShipmentRoleFilters(Shipment::with(['originOffice.city', 'destinationOffice.city', 'sender', 'receiver']))
            ->latest()
            ->limit(10)
            ->get();

        $recentShipmentsResource = \App\Http\Resources\Shipment\ShipmentResource::collection($recentShipments);

        // 5. Office Performance (only for relevant offices)
        $performanceQuery = Office::withCount('shipmentsSent');
        if ($officeIds !== null) {
            $performanceQuery->whereIn('id', $officeIds);
        }
        $officePerformance = $performanceQuery->orderByDesc('shipments_sent_count')
            ->limit(5)
            ->get()
            ->map(fn($office) => [
                'name' => $office->name,
                'total' => $office->shipments_sent_count,
            ]);

        // 6. Global Counts (adjust total offices for workers)
        $activeDrivers = Driver::with('user')->where('status', 'active')->limit(5)->get()->map(fn($driver) => [
            'id' => $driver->user_id,
            'name' => $driver->user->name,
            'avatar' => $driver->user->avatar_url, // Assuming this field exists or using default
            'status' => $driver->status,
        ]);
        $activeDriversCount = Driver::where('status', 'active')->count();
        $totalOfficesCount = ($officeIds !== null) ? count($officeIds) : Office::where('status', 'active')->count();

        return response()->json([
            'city_name' => $cityName,
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
                    'change' => 0,
                    'trend' => 'up',
                    'icon' => 'Truck'
                ],
                [
                    'label' => 'Entregadas (Mes)',
                    'value' => number_format($delivered),
                    'change' => 0,
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
            'active_drivers' => $activeDrivers,
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
                    ->where('status', '!=', 'Anulada')
                    ->whereHas('shipment', function($q) {
                        $this->applyShipmentRoleFilters($q);
                    });
                $value = $query->sum('total');
            } else {
                $query = $this->applyShipmentRoleFilters(Shipment::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year));
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
