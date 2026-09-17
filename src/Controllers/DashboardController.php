<?php

/**
 * Dashboard Summary Controller
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Models\MarketingMetric;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Utils\Response;

class DashboardController
{
    /**
     * GET /api/dashboard-summary
     */
    public function summary(): void
    {
        $startDate = filter_input(INPUT_GET, 'start_date', FILTER_DEFAULT);
        $endDate = filter_input(INPUT_GET, 'end_date', FILTER_DEFAULT);

        // Sanitize date format YYYY-MM-DD
        if ($startDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            $startDate = null;
        }
        if ($endDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $endDate = null;
        }

        // 1. Marketing Metrics (Ad spend, attributed revenue, blended ROAS, etc.)
        $marketingSummary = MarketingMetric::getAggregates($startDate, $endDate);
        $adsComparison = MarketingMetric::getPlatformComparison($startDate, $endDate);

        // 2. Sales Orders (Gross sales, profit, orders count)
        $salesSummary = SalesOrder::getSummary($startDate, $endDate);
        $recentOrders = SalesOrder::getRecentOrders(5);

        // 3. Inventory Overview
        $allProducts = Product::getAll();
        $totalSkus = count($allProducts);
        $lowStockCount = count(array_filter($allProducts, fn($p) => $p['status'] === 'Low Stock'));
        $outOfStockCount = count(array_filter($allProducts, fn($p) => $p['status'] === 'Out of Stock'));

        // 4. Combined Metrics Calculation
        // Total Blended Revenue = Marketing Attributed Revenue (or Gross Sales if higher)
        $totalRevenue = max($marketingSummary['total_revenue'], $salesSummary['total_sales']);
        $totalAdSpend = $marketingSummary['total_ad_spend'];
        $blendedRoas = $totalAdSpend > 0 ? round($totalRevenue / $totalAdSpend, 2) : 0.00;

        // Blended Conversion Rate (Orders / Total Traffic Clicks)
        $conversionRate = $marketingSummary['blended_cr'];

        // Financial Model: Gross Margin 52% of Revenue - Ad Spend
        $grossMarginRate = 0.52;
        $grossProfit = $totalRevenue * $grossMarginRate;
        $estimatedNetProfit = max(0, $grossProfit - $totalAdSpend);
        $netMarginPct = $totalRevenue > 0 ? round(($estimatedNetProfit / $totalRevenue) * 100, 2) : 0.00;

        $responsePayload = [
            'period' => [
                'start_date' => $startDate ?: date('Y-m-d', strtotime('-29 days')),
                'end_date'   => $endDate ?: date('Y-m-d')
            ],
            'kpi' => [
                'total_revenue'      => $totalRevenue,
                'blended_ad_spend'   => $totalAdSpend,
                'blended_roas'       => $blendedRoas,
                'conversion_rate'    => $conversionRate,
                'net_margin_pct'     => $netMarginPct,
                'estimated_profit'   => $estimatedNetProfit,
                'total_clicks'       => $marketingSummary['total_clicks'],
                'total_impressions'  => $marketingSummary['total_impressions'],
                'total_orders'       => $marketingSummary['total_orders'] + $salesSummary['total_orders'],
                'blended_cpc'        => $marketingSummary['blended_cpc'],
            ],
            'ads_comparison' => $adsComparison,
            'recent_orders'  => $recentOrders,
            'inventory_health' => [
                'total_skus'        => $totalSkus,
                'low_stock_count'   => $lowStockCount,
                'out_of_stock_count' => $outOfStockCount,
                'healthy_count'     => max(0, $totalSkus - $lowStockCount - $outOfStockCount)
            ],
            'database_status' => [
                'mode'  => Database::isMockMode() ? 'mock_fallback' : 'tidb_connected',
                'error' => Database::getConnectionError()
            ]
        ];

        Response::json($responsePayload, 'Dashboard summary retrieved successfully');
    }

    /**
     * POST /api/marketing
     * Record daily marketing performance metric
     */
    public function recordMetric(): void
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            $data = $_POST;
        }

        if (!is_array($data) || empty($data)) {
            Response::error('Data metrik tidak valid atau kosong', 400);
            return;
        }

        $platform = trim($data['platform'] ?? '');
        $date = trim($data['date'] ?? '');
        $adSpend = isset($data['ad_spend']) ? (float)$data['ad_spend'] : -1;
        $revenue = isset($data['revenue']) ? (float)$data['revenue'] : -1;

        $errors = [];
        $validPlatforms = ['Shopee Ads', 'Meta Ads', 'TikTok Ads'];
        if (!in_array($platform, $validPlatforms, true)) {
            $errors['platform'] = 'Platform iklan harus salah satu dari: ' . implode(', ', $validPlatforms);
        }

        if (empty($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $errors['date'] = 'Format tanggal harus YYYY-MM-DD (contoh: ' . date('Y-m-d') . ')';
        }

        if ($adSpend < 0) {
            $errors['ad_spend'] = 'Biaya iklan (Ad Spend) wajib diisi angka >= 0';
        }

        if ($revenue < 0) {
            $errors['revenue'] = 'Pendapatan (Attributed Revenue) wajib diisi angka >= 0';
        }

        if (!empty($errors)) {
            Response::error('Validasi input gagal', 422, $errors);
            return;
        }

        $result = MarketingMetric::recordDailyMetric($data);
        Response::json($result, 'Metrik performa iklan harian berhasil disimpan!', 201);
    }

    /**
     * POST /api/marketing/reset
     * Reset marketing metrics and sales orders to 0 for real operations
     */
    public function resetMetrics(): void
    {
        MarketingMetric::resetMetrics();
        SalesOrder::resetOrders();
        Response::json(null, 'Seluruh riwayat metrik demo berhasil dikosongkan! Dashboard kini siap untuk data riil toko Anda.');
    }
}
