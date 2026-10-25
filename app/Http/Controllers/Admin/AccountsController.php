<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountsController extends Controller
{
    /**
     * Display accounts, revenue & sales overview with comprehensive filters.
     */
    public function index(Request $request): View
    {
        $filters = $this->resolveFilters($request);
        $data = $this->calculateMetrics($filters);
        $allProducts = Product::select('id', 'name', 'price', 'purchase_price', 'stock_quantity')
            ->orderBy('name')
            ->get();

        return view('admin.accounts.index', array_merge($filters, $data, [
            'allProducts' => $allProducts,
        ]));
    }

    /**
     * Export sales & accounts report to CSV based on current filters.
     */
    public function export(Request $request): StreamedResponse
    {
        $filters = $this->resolveFilters($request);
        $data = $this->calculateMetrics($filters, false);

        $filename = 'sales_accounts_report_'.Carbon::now()->format('Y-m-d_His').'.csv';

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Summary row
            fputcsv($handle, ['Sales & Revenue Overview']);
            fputcsv($handle, ['Generated At', Carbon::now()->toDateTimeString()]);
            fputcsv($handle, ['Total Orders', $data['totalOrdersCount']]);
            fputcsv($handle, ['Total Items Sold', $data['totalItemsCount']]);
            fputcsv($handle, ['Gross Revenue (৳)', number_format($data['grossRevenue'], 2)]);
            fputcsv($handle, ['Cost of Goods Sold (৳)', number_format($data['totalCogs'], 2)]);
            fputcsv($handle, ['Gross Profit (৳)', number_format($data['grossProfit'], 2)]);
            fputcsv($handle, ['Profit Margin (%)', number_format($data['profitMarginPct'], 1).'%']);
            fputcsv($handle, []);

            // Product Performance Breakdown
            fputcsv($handle, ['--- Product Sales Breakdown ---']);
            fputcsv($handle, ['Product ID', 'Product Name', 'Units Sold', 'Revenue (৳)', 'COGS (৳)', 'Net Profit (৳)', 'Margin (%)']);
            foreach ($data['productBreakdown'] as $p) {
                fputcsv($handle, [
                    $p['id'],
                    $p['name'],
                    $p['units_sold'],
                    number_format($p['revenue'], 2),
                    number_format($p['cogs'], 2),
                    number_format($p['profit'], 2),
                    number_format($p['margin_pct'], 1).'%',
                ]);
            }
            fputcsv($handle, []);

            // Detailed Sales Orders
            fputcsv($handle, ['--- Detailed Order Sales ---']);
            fputcsv($handle, ['Order #', 'Date & Time', 'Customer', 'Phone', 'Status', 'Products', 'Revenue (৳)', 'COGS (৳)', 'Profit (৳)']);
            foreach ($data['salesOrders'] as $order) {
                $productSummary = $order->items->map(function ($it) {
                    return $it->product_name.' (x'.$it->quantity.')';
                })->implode('; ');

                $orderCogs = $order->items->sum(function ($it) {
                    $unitCost = (float) ($it->purchase_price ?? ($it->product->purchase_price ?? 0));

                    return $it->quantity * $unitCost;
                });
                $orderRevenue = (float) $order->total_amount;
                $orderProfit = $orderRevenue - $orderCogs;

                fputcsv($handle, [
                    '#ORD-'.str_pad($order->id, 5, '0', STR_PAD_LEFT),
                    $order->created_at->format('Y-m-d H:i'),
                    $order->name,
                    $order->phone,
                    ucfirst($order->status),
                    $productSummary,
                    number_format($orderRevenue, 2),
                    number_format($orderCogs, 2),
                    number_format($orderProfit, 2),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Parse and sanitize incoming query filters.
     *
     * @return array<string, mixed>
     */
    private function resolveFilters(Request $request): array
    {
        $range = $request->input('range', 'this_month');
        $status = $request->input('status', 'valid'); // 'valid' = non-cancelled, or 'all', or specific status
        $productIds = $request->input('product_ids', []);

        if (is_string($productIds)) {
            $productIds = array_filter(explode(',', $productIds));
        }
        $productIds = array_map('intval', (array) $productIds);
        $productIds = array_values(array_filter($productIds));

        $now = Carbon::now();
        $startDate = null;
        $endDate = null;

        switch ($range) {
            case 'today':
                $startDate = $now->copy()->startOfDay();
                $endDate = $now->copy()->endOfDay();
                break;
            case 'yesterday':
                $startDate = $now->copy()->subDay()->startOfDay();
                $endDate = $now->copy()->subDay()->endOfDay();
                break;
            case 'this_week':
                $startDate = $now->copy()->startOfWeek();
                $endDate = $now->copy()->endOfWeek();
                break;
            case 'last_7_days':
                $startDate = $now->copy()->subDays(6)->startOfDay();
                $endDate = $now->copy()->endOfDay();
                break;
            case 'this_month':
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                break;
            case 'last_month':
                $startDate = $now->copy()->subMonth()->startOfMonth();
                $endDate = $now->copy()->subMonth()->endOfMonth();
                break;
            case 'this_year':
                $startDate = $now->copy()->startOfYear();
                $endDate = $now->copy()->endOfYear();
                break;
            case 'custom':
            default:
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
                    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
                    $range = 'custom';
                } else {
                    $range = 'this_month';
                    $startDate = $now->copy()->startOfMonth();
                    $endDate = $now->copy()->endOfMonth();
                }
                break;
        }

        return [
            'range' => $range,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'status' => $status,
            'productIds' => $productIds,
            'search' => $request->input('search'),
        ];
    }

    /**
     * Calculate financial sales & revenue metrics with breakdown.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function calculateMetrics(array $filters, bool $paginateOrders = true): array
    {
        $startDate = $filters['startDate'];
        $endDate = $filters['endDate'];
        $status = $filters['status'];
        $productIds = $filters['productIds'];
        $search = $filters['search'];

        // 1. Base order query
        $ordersQuery = Order::query()
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($status === 'valid') {
            $ordersQuery->whereNotIn('status', ['cancelled', 'refunded']);
        } elseif ($status !== 'all' && ! empty($status)) {
            $ordersQuery->where('status', $status);
        }

        if (! empty($search)) {
            $ordersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            });
        }

        if (! empty($productIds)) {
            $ordersQuery->whereHas('items', function ($q) use ($productIds) {
                $q->whereIn('product_id', $productIds);
            });
        }

        $allMatchingOrders = (clone $ordersQuery)->with(['items.product'])->get();
        $totalOrdersCount = $allMatchingOrders->count();

        // 2. Fetch all matching order items for accurate product & COGS calculation
        $orderIds = $allMatchingOrders->pluck('id');

        $itemsQuery = OrderItem::with('product')
            ->whereIn('order_id', $orderIds);

        if (! empty($productIds)) {
            $itemsQuery->whereIn('product_id', $productIds);
        }

        $items = $itemsQuery->get();

        $totalItemsCount = 0;
        $itemsRevenue = 0.0;
        $totalCogs = 0.0;

        // Group by product for the product breakdown table
        $productAggregates = [];

        foreach ($items as $item) {
            $qty = (int) $item->quantity;
            $lineRevenue = (float) $item->price * $qty;
            // Purchase price: item level first, then product level, fallback 0
            $unitCost = (float) ($item->purchase_price ?? ($item->product?->purchase_price ?? 0.0));
            $lineCost = $unitCost * $qty;

            $totalItemsCount += $qty;
            $itemsRevenue += $lineRevenue;
            $totalCogs += $lineCost;

            $prodId = $item->product_id ?? 0;
            $prodName = $item->product_name ?: ($item->product?->name ?? 'Custom Item');

            if (! isset($productAggregates[$prodId])) {
                $productAggregates[$prodId] = [
                    'id' => $prodId,
                    'name' => $prodName,
                    'thumbnail' => $item->product?->thumbnail?->url ?? $item->product?->image_url ?? null,
                    'units_sold' => 0,
                    'revenue' => 0.0,
                    'cogs' => 0.0,
                    'profit' => 0.0,
                    'margin_pct' => 0.0,
                ];
            }

            $productAggregates[$prodId]['units_sold'] += $qty;
            $productAggregates[$prodId]['revenue'] += $lineRevenue;
            $productAggregates[$prodId]['cogs'] += $lineCost;
        }

        // Calculate profit & margins per product
        foreach ($productAggregates as &$pa) {
            $pa['profit'] = $pa['revenue'] - $pa['cogs'];
            $pa['margin_pct'] = $pa['revenue'] > 0 ? ($pa['profit'] / $pa['revenue']) * 100 : 0.0;
        }
        unset($pa);

        // Sort products by revenue descending
        uasort($productAggregates, function ($a, $b) {
            return $b['revenue'] <=> $a['revenue'];
        });

        // If filtering by specific products, Gross Revenue is item sales for those products.
        // If viewing all products, Gross Revenue includes orders' total_amount (which includes shipping / coupons)
        $deliveryTotal = 0.0;
        $discountTotal = 0.0;
        if (empty($productIds)) {
            $grossRevenue = (float) $allMatchingOrders->sum('total_amount');
            $deliveryTotal = (float) $allMatchingOrders->sum('delivery_cost');
            $discountTotal = (float) $allMatchingOrders->sum('discount_amount');
        } else {
            $grossRevenue = $itemsRevenue;
        }

        $grossProfit = $grossRevenue - $totalCogs;
        $profitMarginPct = $grossRevenue > 0 ? ($grossProfit / $grossRevenue) * 100 : 0.0;
        $averageOrderValue = $totalOrdersCount > 0 ? $grossRevenue / $totalOrdersCount : 0.0;

        // 3. Daily / Periodic Chart Trend Data
        $chartData = $this->buildChartTrend($allMatchingOrders, $startDate, $endDate, $productIds);

        // 4. Paginated Order Sales List
        $ordersListQuery = (clone $ordersQuery)->with(['items.product', 'deliveryMethod'])->latest('created_at');
        $salesOrders = $paginateOrders
            ? $ordersListQuery->paginate(20, ['*'], 'sales_page')->withQueryString()
            : $ordersListQuery->get();

        return [
            'totalOrdersCount' => $totalOrdersCount,
            'totalItemsCount' => $totalItemsCount,
            'grossRevenue' => $grossRevenue,
            'totalCogs' => $totalCogs,
            'grossProfit' => $grossProfit,
            'profitMarginPct' => $profitMarginPct,
            'averageOrderValue' => $averageOrderValue,
            'deliveryTotal' => $deliveryTotal,
            'discountTotal' => $discountTotal,
            'productBreakdown' => array_values($productAggregates),
            'chartData' => $chartData,
            'salesOrders' => $salesOrders,
        ];
    }

    /**
     * Build day-by-day trend data for interactive visual presentation.
     *
     * @param  Collection<int, Order>  $orders
     * @param  list<int>  $productIds
     * @return array<string, mixed>
     */
    private function buildChartTrend($orders, Carbon $startDate, Carbon $endDate, array $productIds): array
    {
        $daysDiff = $startDate->diffInDays($endDate);
        $labels = [];
        $revenues = [];
        $costs = [];
        $profits = [];

        // If span is <= 31 days, group by day; otherwise group by week/month
        if ($daysDiff <= 35) {
            $current = $startDate->copy();
            while ($current->lte($endDate)) {
                $dateKey = $current->format('Y-m-d');
                $labels[] = $current->format('d M');

                $dayOrders = $orders->filter(function ($ord) use ($dateKey) {
                    return $ord->created_at->format('Y-m-d') === $dateKey;
                });

                $dayRev = 0.0;
                $dayCost = 0.0;

                foreach ($dayOrders as $ord) {
                    if (empty($productIds)) {
                        $dayRev += (float) $ord->total_amount;
                    }
                    foreach ($ord->items as $it) {
                        if (! empty($productIds) && ! in_array($it->product_id, $productIds, true)) {
                            continue;
                        }
                        if (! empty($productIds)) {
                            $dayRev += (float) ($it->price * $it->quantity);
                        }
                        $uCost = (float) ($it->purchase_price ?? ($it->product?->purchase_price ?? 0.0));
                        $dayCost += ($uCost * $it->quantity);
                    }
                }

                $revenues[] = round($dayRev, 2);
                $costs[] = round($dayCost, 2);
                $profits[] = round($dayRev - $dayCost, 2);

                $current->addDay();
            }
        } else {
            // Group by month
            $current = $startDate->copy()->startOfMonth();
            while ($current->lte($endDate)) {
                $monthKey = $current->format('Y-m');
                $labels[] = $current->format('M Y');

                $monthOrders = $orders->filter(function ($ord) use ($monthKey) {
                    return $ord->created_at->format('Y-m') === $monthKey;
                });

                $mRev = 0.0;
                $mCost = 0.0;

                foreach ($monthOrders as $ord) {
                    if (empty($productIds)) {
                        $mRev += (float) $ord->total_amount;
                    }
                    foreach ($ord->items as $it) {
                        if (! empty($productIds) && ! in_array($it->product_id, $productIds, true)) {
                            continue;
                        }
                        if (! empty($productIds)) {
                            $mRev += (float) ($it->price * $it->quantity);
                        }
                        $uCost = (float) ($it->purchase_price ?? ($it->product?->purchase_price ?? 0.0));
                        $mCost += ($uCost * $it->quantity);
                    }
                }

                $revenues[] = round($mRev, 2);
                $costs[] = round($mCost, 2);
                $profits[] = round($mRev - $mCost, 2);

                $current->addMonth();
            }
        }

        return [
            'labels' => $labels,
            'revenues' => $revenues,
            'costs' => $costs,
            'profits' => $profits,
        ];
    }
}
