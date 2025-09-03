<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Models (add/remove as your app has them)
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Vendor;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;

class AdminController extends Controller
{
    /**
     * Small helper to keep the dashboard resilient.
     */
    protected function safe(callable $fn, $fallback)
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            Log::warning('Admin dashboard metric failed: '.$e->getMessage());
            return $fallback;
        }
    }

    public function dashboard()
    {
        $now = Carbon::now();
        $since30 = $now->copy()->subDays(30);
        $since7  = $now->copy()->subDays(7);

        // ── Product KPIs
        $totalProducts    = $this->safe(fn() => Product::count(), 0);
        $activeProducts   = $this->safe(fn() => Product::where('is_active', true)->count(), 0);
        $outOfStockCount  = $this->safe(fn() => Product::where('stock', 0)->count(), 0);
        $lowStockProducts = $this->safe(fn() => Product::with(['brand','category'])
                                                ->where('stock', '>', 0)
                                                ->where('stock', '<=', 5)
                                                ->orderBy('stock')
                                                ->take(10)->get(), collect());

        // ── Orders KPIs
        $totalOrders      = $this->safe(fn() => Order::count(), 0);
        $ordersByStatus   = $this->safe(function () {
                                return Order::select('status', DB::raw('COUNT(*) as c'))
                                    ->groupBy('status')->pluck('c', 'status');
                            }, collect());
        $ordersLast30     = $this->safe(fn() => Order::where('created_at', '>=', Carbon::now()->subDays(30))->count(), 0);

        // ── Revenue via Payments (Completed only)
        $totalRevenue     = $this->safe(function () {
                                return Payment::whereRaw('LOWER(status) = ?', ['completed'])
                                    ->sum('amount');
                            }, 0.0);
        $revenueLast30    = $this->safe(function () use ($since30) {
                                return Payment::whereRaw('LOWER(status) = ?', ['completed'])
                                    ->where('created_at', '>=', $since30)
                                    ->sum('amount');
                            }, 0.0);

        // ── Recent activity
        $recentOrders     = $this->safe(fn() => Order::with('user')->latest()->take(8)->get(), collect());
        $recentPayments   = $this->safe(fn() => Payment::with('order')->latest()->take(8)->get(), collect());

        // ── Top sellers (by quantity)
        $topProducts      = $this->safe(function () {
                                return OrderItem::select('product_id',
                                        DB::raw('SUM(quantity) as qty'),
                                        DB::raw('SUM(quantity * price) as sales'))
                                    ->groupBy('product_id')
                                    ->orderByDesc('qty')
                                    ->with(['product.images','product.brand'])
                                    ->take(5)
                                    ->get();
                            }, collect());

        // ── Tiny timeseries for charts (last 7 days)
        $ordersDaily = $this->safe(function () use ($since7) {
                            return Order::select(
                                    DB::raw('DATE(created_at) as d'),
                                    DB::raw('COUNT(*) as c')
                                )->where('created_at', '>=', $since7)
                                 ->groupBy('d')->orderBy('d')->get();
                        }, collect());

        $paymentsDaily = $this->safe(function () use ($since7) {
                            return Payment::select(
                                    DB::raw('DATE(created_at) as d'),
                                    DB::raw('SUM(CASE WHEN LOWER(status) = "completed" THEN amount ELSE 0 END) as revenue')
                                )->where('created_at', '>=', $since7)
                                 ->groupBy('d')->orderBy('d')->get();
                        }, collect());

        // ── Optional counts
        $categoriesCount  = $this->safe(fn() => Category::count(), 0);
        $brandsCount      = $this->safe(fn() => Brand::count(), 0);
        $vendorsCount     = $this->safe(fn() => Vendor::count(), 0);

        // Prepare simple arrays for charts
        $ordersDailyLabels = $ordersDaily->pluck('d')->map(fn($d) => (string) $d)->all();
        $ordersDailyCounts = $ordersDaily->pluck('c')->map(fn($x) => (int) $x)->all();
        $paymentsDailyLabels = $paymentsDaily->pluck('d')->map(fn($d) => (string) $d)->all();
        $paymentsDailyRevenue = $paymentsDaily->pluck('revenue')->map(fn($x) => (float) $x)->all();

        return view('admin.dashboard', [
            // KPIs
            'totalProducts'       => $totalProducts,
            'activeProducts'      => $activeProducts,
            'outOfStockCount'     => $outOfStockCount,
            'totalOrders'         => $totalOrders,
            'ordersByStatus'      => $ordersByStatus,
            'ordersLast30'        => $ordersLast30,
            'totalRevenue'        => $totalRevenue,
            'revenueLast30'       => $revenueLast30,
            'categoriesCount'     => $categoriesCount,
            'brandsCount'         => $brandsCount,
            'vendorsCount'        => $vendorsCount,

            // Lists
            'lowStockProducts'    => $lowStockProducts,
            'topProducts'         => $topProducts,
            'recentOrders'        => $recentOrders,
            'recentPayments'      => $recentPayments,

            // Charts
            'ordersDailyLabels'   => $ordersDailyLabels,
            'ordersDailyCounts'   => $ordersDailyCounts,
            'paymentsDailyLabels' => $paymentsDailyLabels,
            'paymentsDailyRevenue'=> $paymentsDailyRevenue,
        ]);
    }
}
