<?php

/**
 * Sales Order Model
 * Handles multi-channel sales aggregation and order records
 */

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

class SalesOrder
{
    private static array $mockOrders = [
        [
            'id'            => 1,
            'order_number'  => 'ORD-260917-8801',
            'channel'       => 'Shopee',
            'customer_name' => 'Budi Santoso',
            'total_amount'  => 578000.00,
            'profit_margin' => 288000.00,
            'status'        => 'Completed',
            'created_at'    => '2026-09-17 14:22:10'
        ],
        [
            'id'            => 2,
            'order_number'  => 'ORD-260917-8802',
            'channel'       => 'TikTok',
            'customer_name' => 'Siti Rahmawati',
            'total_amount'  => 354000.00,
            'profit_margin' => 174000.00,
            'status'        => 'Processing',
            'created_at'    => '2026-09-17 13:45:00'
        ],
        [
            'id'            => 3,
            'order_number'  => 'ORD-260917-8803',
            'channel'       => 'Direct',
            'customer_name' => 'PT Global Niaga Jaya',
            'total_amount'  => 2890000.00,
            'profit_margin' => 1440000.00,
            'status'        => 'Completed',
            'created_at'    => '2026-09-17 11:10:45'
        ],
        [
            'id'            => 4,
            'order_number'  => 'ORD-260916-8794',
            'channel'       => 'Shopee',
            'customer_name' => 'Ahmad Fadillah',
            'total_amount'  => 289000.00,
            'profit_margin' => 144000.00,
            'status'        => 'Shipped',
            'created_at'    => '2026-09-16 18:30:12'
        ],
        [
            'id'            => 5,
            'order_number'  => 'ORD-260916-8795',
            'channel'       => 'TikTok',
            'customer_name' => 'Devi Anggraini',
            'total_amount'  => 438000.00,
            'profit_margin' => 218000.00,
            'status'        => 'Completed',
            'created_at'    => '2026-09-16 16:15:30'
        ],
        [
            'id'            => 6,
            'order_number'  => 'ORD-260916-8796',
            'channel'       => 'Shopee',
            'customer_name' => 'Rizky Pratama',
            'total_amount'  => 657000.00,
            'profit_margin' => 327000.00,
            'status'        => 'Completed',
            'created_at'    => '2026-09-16 12:05:22'
        ],
        [
            'id'            => 7,
            'order_number'  => 'ORD-260915-8780',
            'channel'       => 'Shopee',
            'customer_name' => 'Dewi Lestari',
            'total_amount'  => 219000.00,
            'profit_margin' => 109000.00,
            'status'        => 'Completed',
            'created_at'    => '2026-09-15 19:40:11'
        ],
        [
            'id'            => 8,
            'order_number'  => 'ORD-260915-8781',
            'channel'       => 'TikTok',
            'customer_name' => 'Hendra Gunawan',
            'total_amount'  => 178000.00,
            'profit_margin' => 89000.00,
            'status'        => 'Completed',
            'created_at'    => '2026-09-15 15:20:00'
        ],
        [
            'id'            => 9,
            'order_number'  => 'ORD-260914-8772',
            'channel'       => 'Direct',
            'customer_name' => 'Klinik Estetika Cantika',
            'total_amount'  => 1980000.00,
            'profit_margin' => 1140000.00,
            'status'        => 'Completed',
            'created_at'    => '2026-09-14 10:14:50'
        ],
        [
            'id'            => 10,
            'order_number'  => 'ORD-260914-8773',
            'channel'       => 'Shopee',
            'customer_name' => 'Maya Indah',
            'total_amount'  => 448000.00,
            'profit_margin' => 224000.00,
            'status'        => 'Shipped',
            'created_at'    => '2026-09-14 14:02:18'
        ]
    ];

    /**
     * Get Sales Summary for date range
     */
    public static function getSummary(?string $startDate = null, ?string $endDate = null): array
    {
        $pdo = Database::getConnection();

        if ($pdo) {
            $sql = "SELECT 
                        COALESCE(SUM(total_amount), 0) as total_sales,
                        COALESCE(SUM(profit_margin), 0) as total_profit,
                        COUNT(id) as total_orders
                    FROM sales_orders WHERE status != 'Cancelled'";
            $params = [];

            if (!empty($startDate)) {
                $sql .= " AND created_at >= :start_date";
                $params[':start_date'] = $startDate . ' 00:00:00';
            }
            if (!empty($endDate)) {
                $sql .= " AND created_at <= :end_date";
                $params[':end_date'] = $endDate . ' 23:59:59';
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $res = $stmt->fetch();
        } else {
            $items = self::$mockOrders;
            if (!empty($startDate)) {
                $items = array_filter($items, fn($o) => substr($o['created_at'], 0, 10) >= $startDate);
            }
            if (!empty($endDate)) {
                $items = array_filter($items, fn($o) => substr($o['created_at'], 0, 10) <= $endDate);
            }

            $res = [
                'total_sales'  => array_sum(array_column($items, 'total_amount')),
                'total_profit' => array_sum(array_column($items, 'profit_margin')),
                'total_orders' => count($items)
            ];
        }

        $sales = (float)$res['total_sales'];
        $profit = (float)$res['total_profit'];
        $ordersCount = (int)$res['total_orders'];

        $marginPercentage = $sales > 0 ? round(($profit / $sales) * 100, 2) : 0.00;
        $aov = $ordersCount > 0 ? round($sales / $ordersCount, 0) : 0.00;

        return [
            'total_sales'       => $sales,
            'total_profit'      => $profit,
            'total_orders'      => $ordersCount,
            'net_margin_pct'    => $marginPercentage,
            'aov'               => $aov
        ];
    }

    /**
     * Get recent orders
     */
    public static function getRecentOrders(int $limit = 10): array
    {
        $pdo = Database::getConnection();

        if ($pdo) {
            $stmt = $pdo->prepare("SELECT * FROM sales_orders ORDER BY created_at DESC LIMIT :limit");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }

        return array_slice(self::$mockOrders, 0, $limit);
    }

    /**
     * Get all raw orders for export
     */
    public static function getAll(?string $startDate = null, ?string $endDate = null): array
    {
        $pdo = Database::getConnection();

        if ($pdo) {
            $sql = "SELECT id, order_number, channel, customer_name, total_amount, profit_margin, status, created_at 
                    FROM sales_orders WHERE 1=1";
            $params = [];
            if (!empty($startDate)) {
                $sql .= " AND created_at >= :start_date";
                $params[':start_date'] = $startDate . ' 00:00:00';
            }
            if (!empty($endDate)) {
                $sql .= " AND created_at <= :end_date";
                $params[':end_date'] = $endDate . ' 23:59:59';
            }
            $sql .= " ORDER BY created_at DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        }

        return self::$mockOrders;
    }

    /**
     * Clear / reset all sales orders
     */
    public static function resetOrders(): bool
    {
        $pdo = Database::getConnection();
        if ($pdo) {
            $pdo->exec("TRUNCATE TABLE sales_orders");
            return true;
        }
        return false;
    }
}
