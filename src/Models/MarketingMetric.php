<?php

/**
 * Marketing Metric Model
 * Aggregates performance data across Shopee Ads, Meta Ads, and TikTok Ads
 */

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

class MarketingMetric
{
    /**
     * Get 30-day mock seed data for offline / fallback execution
     */
    private static function getMockData(): array
    {
        static $data = null;
        if ($data !== null) {
            return $data;
        }

        $platforms = [
            'Shopee Ads' => ['base_spend' => 1450000, 'base_roas' => 5.2, 'base_ctr' => 3.48, 'cr' => 0.029],
            'Meta Ads'   => ['base_spend' => 1100000, 'base_roas' => 4.2, 'base_ctr' => 2.31, 'cr' => 0.027],
            'TikTok Ads' => ['base_spend' => 950000,  'base_roas' => 4.25, 'base_ctr' => 2.66, 'cr' => 0.018],
        ];

        $data = [];
        $today = strtotime('2026-09-17');

        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', $today - ($i * 86400));
            // 9.9 Mega campaign boost factor
            $isPromo = ($date === '2026-09-09');
            $multiplier = $isPromo ? 1.6 : (1.0 + (sin($i) * 0.12));

            foreach ($platforms as $platform => $cfg) {
                $spend = round($cfg['base_spend'] * $multiplier);
                $roas = round($cfg['base_roas'] * ($isPromo ? 1.15 : (1.0 + (cos($i) * 0.05))), 2);
                $rev = round($spend * $roas);
                $imp = round(($spend / 15) * ($isPromo ? 1.4 : 1.0));
                $ctr = round($cfg['base_ctr'] * ($isPromo ? 1.1 : 1.0), 2);
                $clicks = round($imp * ($ctr / 100));
                $orders = max(1, round($clicks * $cfg['cr']));

                $data[] = [
                    'platform'    => $platform,
                    'date'        => $date,
                    'ad_spend'    => (float)$spend,
                    'impressions' => (int)$imp,
                    'clicks'      => (int)$clicks,
                    'orders'      => (int)$orders,
                    'revenue'     => (float)$rev,
                    'ctr'         => (float)$ctr,
                    'roas'        => (float)$roas,
                ];
            }
        }

        return $data;
    }

    /**
     * Get aggregated metrics for date range
     */
    public static function getAggregates(?string $startDate = null, ?string $endDate = null): array
    {
        $pdo = Database::getConnection();

        if ($pdo) {
            $sql = "SELECT 
                        COALESCE(SUM(ad_spend), 0) as total_ad_spend,
                        COALESCE(SUM(revenue), 0) as total_revenue,
                        COALESCE(SUM(impressions), 0) as total_impressions,
                        COALESCE(SUM(clicks), 0) as total_clicks,
                        COALESCE(SUM(orders), 0) as total_orders
                    FROM marketing_metrics WHERE 1=1";
            $params = [];

            if (!empty($startDate)) {
                $sql .= " AND date >= :start_date";
                $params[':start_date'] = $startDate;
            }
            if (!empty($endDate)) {
                $sql .= " AND date <= :end_date";
                $params[':end_date'] = $endDate;
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $summary = $stmt->fetch();
        } else {
            // Mock computation
            $items = self::getMockData();
            if (!empty($startDate)) {
                $items = array_filter($items, fn($r) => $r['date'] >= $startDate);
            }
            if (!empty($endDate)) {
                $items = array_filter($items, fn($r) => $r['date'] <= $endDate);
            }

            $summary = [
                'total_ad_spend'    => array_sum(array_column($items, 'ad_spend')),
                'total_revenue'     => array_sum(array_column($items, 'revenue')),
                'total_impressions' => array_sum(array_column($items, 'impressions')),
                'total_clicks'      => array_sum(array_column($items, 'clicks')),
                'total_orders'      => array_sum(array_column($items, 'orders')),
            ];
        }

        $spend = (float)$summary['total_ad_spend'];
        $rev = (float)$summary['total_revenue'];
        $imp = (int)$summary['total_impressions'];
        $clicks = (int)$summary['total_clicks'];
        $orders = (int)$summary['total_orders'];

        $blendedRoas = $spend > 0 ? round($rev / $spend, 2) : 0.00;
        $blendedCtr = $imp > 0 ? round(($clicks / $imp) * 100, 2) : 0.00;
        $blendedCr = $clicks > 0 ? round(($orders / $clicks) * 100, 2) : 0.00;
        $blendedCpc = $clicks > 0 ? round($spend / $clicks, 0) : 0.00;

        return [
            'total_ad_spend'    => $spend,
            'total_revenue'     => $rev,
            'total_impressions' => $imp,
            'total_clicks'      => $clicks,
            'total_orders'      => $orders,
            'blended_roas'      => $blendedRoas,
            'blended_ctr'       => $blendedCtr,
            'blended_cr'        => $blendedCr,
            'blended_cpc'       => $blendedCpc,
        ];
    }

    /**
     * Get platform-by-platform comparison
     */
    public static function getPlatformComparison(?string $startDate = null, ?string $endDate = null): array
    {
        $pdo = Database::getConnection();

        if ($pdo) {
            $sql = "SELECT 
                        platform,
                        COALESCE(SUM(ad_spend), 0) as total_ad_spend,
                        COALESCE(SUM(revenue), 0) as total_revenue,
                        COALESCE(SUM(impressions), 0) as total_impressions,
                        COALESCE(SUM(clicks), 0) as total_clicks,
                        COALESCE(SUM(orders), 0) as total_orders
                    FROM marketing_metrics WHERE 1=1";
            $params = [];

            if (!empty($startDate)) {
                $sql .= " AND date >= :start_date";
                $params[':start_date'] = $startDate;
            }
            if (!empty($endDate)) {
                $sql .= " AND date <= :end_date";
                $params[':end_date'] = $endDate;
            }

            $sql .= " GROUP BY platform ORDER BY total_revenue DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();
        } else {
            $items = self::getMockData();
            if (!empty($startDate)) {
                $items = array_filter($items, fn($r) => $r['date'] >= $startDate);
            }
            if (!empty($endDate)) {
                $items = array_filter($items, fn($r) => $r['date'] <= $endDate);
            }

            $grouped = [];
            foreach ($items as $item) {
                $p = $item['platform'];
                if (!isset($grouped[$p])) {
                    $grouped[$p] = [
                        'platform'          => $p,
                        'total_ad_spend'    => 0,
                        'total_revenue'     => 0,
                        'total_impressions' => 0,
                        'total_clicks'      => 0,
                        'total_orders'      => 0,
                    ];
                }
                $grouped[$p]['total_ad_spend'] += $item['ad_spend'];
                $grouped[$p]['total_revenue'] += $item['revenue'];
                $grouped[$p]['total_impressions'] += $item['impressions'];
                $grouped[$p]['total_clicks'] += $item['clicks'];
                $grouped[$p]['total_orders'] += $item['orders'];
            }
            $rows = array_values($grouped);
        }

        $defaultPlatforms = [
            'Shopee Ads' => ['platform' => 'Shopee Ads', 'total_ad_spend' => 0.0, 'total_revenue' => 0.0, 'total_impressions' => 0, 'total_clicks' => 0, 'total_orders' => 0],
            'Meta Ads'   => ['platform' => 'Meta Ads',   'total_ad_spend' => 0.0, 'total_revenue' => 0.0, 'total_impressions' => 0, 'total_clicks' => 0, 'total_orders' => 0],
            'TikTok Ads' => ['platform' => 'TikTok Ads', 'total_ad_spend' => 0.0, 'total_revenue' => 0.0, 'total_impressions' => 0, 'total_clicks' => 0, 'total_orders' => 0],
        ];

        foreach ($rows as $row) {
            $p = (string)$row['platform'];
            if (isset($defaultPlatforms[$p])) {
                $defaultPlatforms[$p]['total_ad_spend'] = (float)$row['total_ad_spend'];
                $defaultPlatforms[$p]['total_revenue'] = (float)$row['total_revenue'];
                $defaultPlatforms[$p]['total_impressions'] = (int)$row['total_impressions'];
                $defaultPlatforms[$p]['total_clicks'] = (int)$row['total_clicks'];
                $defaultPlatforms[$p]['total_orders'] = (int)$row['total_orders'];
            }
        }

        $comparison = [];
        foreach ($defaultPlatforms as $row) {
            $spend = (float)$row['total_ad_spend'];
            $rev = (float)$row['total_revenue'];
            $imp = (int)$row['total_impressions'];
            $clicks = (int)$row['total_clicks'];
            $orders = (int)$row['total_orders'];

            $roas = $spend > 0 ? round($rev / $spend, 2) : 0.00;
            $ctr = $imp > 0 ? round(($clicks / $imp) * 100, 2) : 0.00;
            $cpc = $clicks > 0 ? round($spend / $clicks, 0) : 0.00;
            $cr = $clicks > 0 ? round(($orders / $clicks) * 100, 2) : 0.00;

            $comparison[] = [
                'platform'          => (string)$row['platform'],
                'total_ad_spend'    => $spend,
                'total_revenue'     => $rev,
                'total_impressions' => $imp,
                'total_clicks'      => $clicks,
                'total_orders'      => $orders,
                'roas'              => $roas,
                'ctr'               => $ctr,
                'cpc'               => $cpc,
                'cr'                => $cr
            ];
        }

        return $comparison;
    }

    /**
     * Clear / reset all marketing metrics
     */
    public static function resetMetrics(): bool
    {
        $pdo = Database::getConnection();
        if ($pdo) {
            $pdo->exec("TRUNCATE TABLE marketing_metrics");
            return true;
        }
        return false;
    }

    /**
     * Get raw records for reports
     */
    public static function getRawRecords(?string $startDate = null, ?string $endDate = null): array
    {
        $pdo = Database::getConnection();

        if ($pdo) {
            $sql = "SELECT id, platform, date, ad_spend, impressions, clicks, orders, revenue, ctr, roas 
                    FROM marketing_metrics WHERE 1=1";
            $params = [];
            if (!empty($startDate)) {
                $sql .= " AND date >= :start_date";
                $params[':start_date'] = $startDate;
            }
            if (!empty($endDate)) {
                $sql .= " AND date <= :end_date";
                $params[':end_date'] = $endDate;
            }
            $sql .= " ORDER BY date DESC, platform ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        }

        $items = self::getMockData();
        if (!empty($startDate)) {
            $items = array_filter($items, fn($r) => $r['date'] >= $startDate);
        }
        if (!empty($endDate)) {
            $items = array_filter($items, fn($r) => $r['date'] <= $endDate);
        }

        usort($items, fn($a, $b) => strcmp($b['date'], $a['date']));
        return array_values($items);
    }

    /**
     * Record or update daily marketing metrics for a platform
     */
    public static function recordDailyMetric(array $data): array
    {
        $platform = trim($data['platform'] ?? '');
        $date = trim($data['date'] ?? date('Y-m-d'));
        $adSpend = max(0.0, (float)($data['ad_spend'] ?? 0));
        $revenue = max(0.0, (float)($data['revenue'] ?? 0));
        $impressions = max(0, (int)($data['impressions'] ?? 0));
        $clicks = max(0, (int)($data['clicks'] ?? 0));
        $orders = max(0, (int)($data['orders'] ?? 0));

        // Intelligent estimation for impressions/clicks if not explicitly provided
        if ($clicks === 0 && $orders > 0) {
            $clicks = (int)round($orders / 0.025);
        }
        if ($impressions === 0 && $clicks > 0) {
            $impressions = (int)round($clicks / 0.028);
        }

        $ctr = $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0.00;
        $roas = $adSpend > 0 ? round($revenue / $adSpend, 2) : 0.00;

        $pdo = Database::getConnection();
        if ($pdo) {
            $checkStmt = $pdo->prepare("SELECT id FROM marketing_metrics WHERE platform = :platform AND date = :date LIMIT 1");
            $checkStmt->execute([':platform' => $platform, ':date' => $date]);
            $existing = $checkStmt->fetch();

            if ($existing) {
                $stmt = $pdo->prepare("UPDATE marketing_metrics SET 
                    ad_spend = :ad_spend,
                    revenue = :revenue,
                    impressions = :impressions,
                    clicks = :clicks,
                    orders = :orders,
                    ctr = :ctr,
                    roas = :roas
                    WHERE id = :id");
                $stmt->execute([
                    ':ad_spend'    => $adSpend,
                    ':revenue'     => $revenue,
                    ':impressions' => $impressions,
                    ':clicks'      => $clicks,
                    ':orders'      => $orders,
                    ':ctr'         => $ctr,
                    ':roas'        => $roas,
                    ':id'          => $existing['id']
                ]);
                $recordId = (int)$existing['id'];
            } else {
                $stmt = $pdo->prepare("INSERT INTO marketing_metrics 
                    (platform, date, ad_spend, impressions, clicks, orders, revenue, ctr, roas)
                    VALUES (:platform, :date, :ad_spend, :impressions, :clicks, :orders, :revenue, :ctr, :roas)");
                $stmt->execute([
                    ':platform'    => $platform,
                    ':date'        => $date,
                    ':ad_spend'    => $adSpend,
                    ':impressions' => $impressions,
                    ':clicks'      => $clicks,
                    ':orders'      => $orders,
                    ':revenue'     => $revenue,
                    ':ctr'         => $ctr,
                    ':roas'        => $roas
                ]);
                $recordId = (int)$pdo->lastInsertId();
            }
        } else {
            $recordId = rand(1000, 9999);
        }

        return [
            'id'          => $recordId,
            'platform'    => $platform,
            'date'        => $date,
            'ad_spend'    => $adSpend,
            'revenue'     => $revenue,
            'impressions' => $impressions,
            'clicks'      => $clicks,
            'orders'      => $orders,
            'ctr'         => $ctr,
            'roas'        => $roas
        ];
    }
}
