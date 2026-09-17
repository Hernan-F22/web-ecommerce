<?php

/**
 * Product & Inventory Model
 */

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

class Product
{
    /**
     * Realistic in-memory fallback dataset for seamless offline testing
     */
    private static array $mockProducts = [
        [
            'id' => 1,
            'sku' => 'SKU-ELC-001',
            'name' => 'Wireless Noise-Canceling Earbuds Pro v2',
            'category' => 'Electronics',
            'cost_price' => 145000.00,
            'selling_price' => 289000.00,
            'stock_physical' => 150,
            'stock_reserved' => 24,
            'created_at' => '2026-08-10 10:00:00'
        ],
        [
            'id' => 2,
            'sku' => 'SKU-ELC-002',
            'name' => 'Magnetic Power Bank 10,000mAh Fast Charging',
            'category' => 'Electronics',
            'cost_price' => 110000.00,
            'selling_price' => 219000.00,
            'stock_physical' => 42,
            'stock_reserved' => 38, // Available: 4 (Low Stock)
            'created_at' => '2026-08-12 11:30:00'
        ],
        [
            'id' => 3,
            'sku' => 'SKU-FSH-001',
            'name' => 'Oversized Streetwear Boxy Heavyweight Tee',
            'category' => 'Fashion',
            'cost_price' => 55000.00,
            'selling_price' => 135000.00,
            'stock_physical' => 280,
            'stock_reserved' => 45,
            'created_at' => '2026-08-15 09:15:00'
        ],
        [
            'id' => 4,
            'sku' => 'SKU-FSH-002',
            'name' => 'Cargo Parachute Pants Urban Techwear Black',
            'category' => 'Fashion',
            'cost_price' => 95000.00,
            'selling_price' => 225000.00,
            'stock_physical' => 85,
            'stock_reserved' => 12,
            'created_at' => '2026-08-16 14:20:00'
        ],
        [
            'id' => 5,
            'sku' => 'SKU-BEA-001',
            'name' => 'Hyaluronic Acid Hydrating Glow Serum 30ml',
            'category' => 'Beauty & Care',
            'cost_price' => 42000.00,
            'selling_price' => 99000.00,
            'stock_physical' => 310,
            'stock_reserved' => 68,
            'created_at' => '2026-08-18 16:45:00'
        ],
        [
            'id' => 6,
            'sku' => 'SKU-BEA-002',
            'name' => 'Sunscreen Gel SPF 50+ PA++++ Anti-Pollution',
            'category' => 'Beauty & Care',
            'cost_price' => 38000.00,
            'selling_price' => 89000.00,
            'stock_physical' => 12,
            'stock_reserved' => 12, // Available: 0 (Out of Stock)
            'created_at' => '2026-08-20 08:00:00'
        ],
        [
            'id' => 7,
            'sku' => 'SKU-HOM-001',
            'name' => 'Smart Aroma Diffuser 500ml with Ambient Light',
            'category' => 'Home & Living',
            'cost_price' => 88000.00,
            'selling_price' => 179000.00,
            'stock_physical' => 64,
            'stock_reserved' => 15,
            'created_at' => '2026-08-22 13:10:00'
        ],
        [
            'id' => 8,
            'sku' => 'SKU-HOM-002',
            'name' => 'Ergonomic Memory Foam Lumbar Support Cushion',
            'category' => 'Home & Living',
            'cost_price' => 75000.00,
            'selling_price' => 169000.00,
            'stock_physical' => 18,
            'stock_reserved' => 14, // Available: 4 (Low Stock)
            'created_at' => '2026-08-25 15:30:00'
        ],
        [
            'id' => 9,
            'sku' => 'SKU-ELC-003',
            'name' => 'Braided Nylon Type-C to Lightning Fast Cable',
            'category' => 'Electronics',
            'cost_price' => 18000.00,
            'selling_price' => 49000.00,
            'stock_physical' => 450,
            'stock_reserved' => 52,
            'created_at' => '2026-08-28 10:05:00'
        ],
        [
            'id' => 10,
            'sku' => 'SKU-FSH-003',
            'name' => 'Waterproof Utility Crossbody Sling Bag',
            'category' => 'Fashion',
            'cost_price' => 62000.00,
            'selling_price' => 149000.00,
            'stock_physical' => 110,
            'stock_reserved' => 18,
            'created_at' => '2026-09-01 11:20:00'
        ]
    ];

    /**
     * Get all products with optional filters
     */
    public static function getAll(?string $search = null, ?string $category = null, ?string $statusFilter = null): array
    {
        $pdo = Database::getConnection();

        if ($pdo) {
            $sql = "SELECT id, sku, name, category, cost_price, selling_price, stock_physical, stock_reserved, created_at FROM products WHERE 1=1";
            $params = [];

            if (!empty($search)) {
                $sql .= " AND (sku LIKE :search1 OR name LIKE :search2)";
                $params[':search1'] = '%' . $search . '%';
                $params[':search2'] = '%' . $search . '%';
            }

            if (!empty($category)) {
                $sql .= " AND category = :category";
                $params[':category'] = $category;
            }

            $sql .= " ORDER BY id ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();
        } else {
            // Use mock fallback
            $rows = self::$mockProducts;
            if (!empty($search)) {
                $term = strtolower($search);
                $rows = array_filter(
                    $rows,
                    fn($p) =>
                    str_contains(strtolower($p['sku']), $term) ||
                        str_contains(strtolower($p['name']), $term)
                );
            }
            if (!empty($category)) {
                $rows = array_filter($rows, fn($p) => $p['category'] === $category);
            }
        }

        // Augment rows with computed stock metrics
        $results = [];
        foreach ($rows as $row) {
            $physical = (int)$row['stock_physical'];
            $reserved = (int)$row['stock_reserved'];
            $available = max(0, $physical - $reserved);

            $status = 'Aman';
            if ($available <= 0) {
                $status = 'Out of Stock';
            } elseif ($available <= 20) {
                $status = 'Low Stock';
            }

            if (!empty($statusFilter) && $statusFilter !== 'All' && $status !== $statusFilter) {
                continue;
            }

            $results[] = [
                'id'             => (int)$row['id'],
                'sku'            => (string)$row['sku'],
                'name'           => (string)$row['name'],
                'category'       => (string)$row['category'],
                'cost_price'     => (float)$row['cost_price'],
                'selling_price'  => (float)$row['selling_price'],
                'stock_physical' => $physical,
                'stock_reserved' => $reserved,
                'stock_available' => $available,
                'status'         => $status,
                'created_at'     => (string)$row['created_at']
            ];
        }

        return array_values($results);
    }

    /**
     * Find product by SKU
     */
    public static function findBySku(string $sku): ?array
    {
        $pdo = Database::getConnection();

        if ($pdo) {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE sku = :sku LIMIT 1");
            $stmt->execute([':sku' => $sku]);
            $row = $stmt->fetch();
            return $row ?: null;
        }

        foreach (self::$mockProducts as $product) {
            if (strcasecmp($product['sku'], $sku) === 0) {
                return $product;
            }
        }

        return null;
    }

    /**
     * Create new SKU / Product
     */
    public static function create(array $data): array
    {
        $sku = strtoupper(trim($data['sku'] ?? ''));
        $name = trim($data['name'] ?? '');
        $category = trim($data['category'] ?? 'General');
        $costPrice = (float)($data['cost_price'] ?? 0);
        $sellingPrice = (float)($data['selling_price'] ?? 0);
        $stockPhysical = (int)($data['stock_physical'] ?? 0);
        $stockReserved = (int)($data['stock_reserved'] ?? 0);

        $pdo = Database::getConnection();

        if ($pdo) {
            $stmt = $pdo->prepare("INSERT INTO products (sku, name, category, cost_price, selling_price, stock_physical, stock_reserved)
                VALUES (:sku, :name, :category, :cost_price, :selling_price, :stock_physical, :stock_reserved)");
            $stmt->execute([
                ':sku'            => $sku,
                ':name'           => $name,
                ':category'       => $category,
                ':cost_price'     => $costPrice,
                ':selling_price'  => $sellingPrice,
                ':stock_physical' => $stockPhysical,
                ':stock_reserved' => $stockReserved
            ]);
            $newId = (int)$pdo->lastInsertId();
        } else {
            $newId = count(self::$mockProducts) + 1;
            self::$mockProducts[] = [
                'id'             => $newId,
                'sku'            => $sku,
                'name'           => $name,
                'category'       => $category,
                'cost_price'     => $costPrice,
                'selling_price'  => $sellingPrice,
                'stock_physical' => $stockPhysical,
                'stock_reserved' => $stockReserved,
                'created_at'     => date('Y-m-d H:i:s')
            ];
        }

        return [
            'id'              => $newId,
            'sku'             => $sku,
            'name'            => $name,
            'category'        => $category,
            'cost_price'      => $costPrice,
            'selling_price'   => $sellingPrice,
            'stock_physical'  => $stockPhysical,
            'stock_reserved'  => $stockReserved,
            'stock_available' => max(0, $stockPhysical - $stockReserved)
        ];
    }

    /**
     * Adjust physical and/or reserved stock for a specific SKU
     */
    public static function adjustStock(string $sku, int $newPhysical, ?int $newReserved = null): bool
    {
        $pdo = Database::getConnection();

        if ($pdo) {
            if ($newReserved !== null) {
                $stmt = $pdo->prepare("UPDATE products SET stock_physical = :physical, stock_reserved = :reserved WHERE sku = :sku");
                return $stmt->execute([
                    ':physical' => max(0, $newPhysical),
                    ':reserved' => max(0, $newReserved),
                    ':sku'      => $sku
                ]);
            } else {
                $stmt = $pdo->prepare("UPDATE products SET stock_physical = :physical WHERE sku = :sku");
                return $stmt->execute([
                    ':physical' => max(0, $newPhysical),
                    ':sku'      => $sku
                ]);
            }
        }

        foreach (self::$mockProducts as &$prod) {
            if (strcasecmp($prod['sku'], $sku) === 0) {
                $prod['stock_physical'] = max(0, $newPhysical);
                if ($newReserved !== null) {
                    $prod['stock_reserved'] = max(0, $newReserved);
                }
                return true;
            }
        }

        return false;
    }

    /**
     * Update product details by SKU
     */
    public static function update(string $sku, array $data): bool
    {
        $name = trim($data['name'] ?? '');
        $category = trim($data['category'] ?? 'General');
        $costPrice = isset($data['cost_price']) ? (float)$data['cost_price'] : 0.0;
        $sellingPrice = isset($data['selling_price']) ? (float)$data['selling_price'] : 0.0;
        $stockPhysical = isset($data['stock_physical']) ? (int)$data['stock_physical'] : 0;
        $stockReserved = isset($data['stock_reserved']) ? (int)$data['stock_reserved'] : 0;

        $pdo = Database::getConnection();

        if ($pdo) {
            $stmt = $pdo->prepare("UPDATE products SET 
                name = :name,
                category = :category,
                cost_price = :cost_price,
                selling_price = :selling_price,
                stock_physical = :stock_physical,
                stock_reserved = :stock_reserved
                WHERE sku = :sku");
            return $stmt->execute([
                ':name'           => $name,
                ':category'       => $category,
                ':cost_price'     => max(0, $costPrice),
                ':selling_price'  => max(0, $sellingPrice),
                ':stock_physical' => max(0, $stockPhysical),
                ':stock_reserved' => max(0, $stockReserved),
                ':sku'            => $sku
            ]);
        }

        foreach (self::$mockProducts as &$prod) {
            if (strcasecmp($prod['sku'], $sku) === 0) {
                $prod['name'] = $name;
                $prod['category'] = $category;
                $prod['cost_price'] = max(0, $costPrice);
                $prod['selling_price'] = max(0, $sellingPrice);
                $prod['stock_physical'] = max(0, $stockPhysical);
                $prod['stock_reserved'] = max(0, $stockReserved);
                return true;
            }
        }

        return false;
    }

    /**
     * Delete product by SKU
     */
    public static function delete(string $sku): bool
    {
        $pdo = Database::getConnection();

        if ($pdo) {
            $stmt = $pdo->prepare("DELETE FROM products WHERE sku = :sku");
            return $stmt->execute([':sku' => $sku]);
        }

        foreach (self::$mockProducts as $idx => $prod) {
            if (strcasecmp($prod['sku'], $sku) === 0) {
                unset(self::$mockProducts[$idx]);
                self::$mockProducts = array_values(self::$mockProducts);
                return true;
            }
        }

        return false;
    }
}
