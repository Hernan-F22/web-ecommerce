<?php

/**
 * Inventory Controller
 * Handles SKU listing, search, creation, and stock adjustments
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Product;
use App\Utils\Response;

class InventoryController
{
    /**
     * GET /api/inventory
     */
    public function index(): void
    {
        $search = filter_input(INPUT_GET, 'search', FILTER_DEFAULT);
        $category = filter_input(INPUT_GET, 'category', FILTER_DEFAULT);
        $status = filter_input(INPUT_GET, 'status', FILTER_DEFAULT);

        // Sanitize string parameters
        $search = $search !== null ? trim(strip_tags($search)) : null;
        $category = $category !== null ? trim(strip_tags($category)) : null;
        $status = $status !== null ? trim(strip_tags($status)) : null;

        $products = Product::getAll($search, $category, $status);

        Response::json([
            'count'    => count($products),
            'products' => $products
        ], 'Inventory retrieved successfully');
    }

    /**
     * POST /api/inventory
     * Action parameter defines operation: 'create' or 'adjust_stock'
     */
    public function store(): void
    {
        // Parse JSON payload or form-encoded POST
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        if (!is_array($data)) {
            $data = $_POST;
        }

        $action = $data['action'] ?? 'create';

        if ($action === 'adjust_stock') {
            $this->handleStockAdjustment($data);
            return;
        }

        if ($action === 'update') {
            $this->handleUpdateProduct($data);
            return;
        }

        if ($action === 'delete') {
            $this->handleDeleteProduct($data);
            return;
        }

        $this->handleCreateProduct($data);
    }

    /**
     * Handle updating full product details
     */
    private function handleUpdateProduct(array $data): void
    {
        $sku = strtoupper(trim(strip_tags($data['sku'] ?? '')));
        if (empty($sku)) {
            Response::error('Kode SKU wajib diisi', 422);
            return;
        }

        $product = Product::findBySku($sku);
        if (!$product) {
            Response::error("Produk dengan SKU '{$sku}' tidak ditemukan", 404);
            return;
        }

        $name = trim(strip_tags($data['name'] ?? $product['name']));
        $category = trim(strip_tags($data['category'] ?? $product['category']));
        $costPrice = isset($data['cost_price']) ? (float)$data['cost_price'] : (float)$product['cost_price'];
        $sellingPrice = isset($data['selling_price']) ? (float)$data['selling_price'] : (float)$product['selling_price'];
        $stockPhysical = isset($data['stock_physical']) ? (int)$data['stock_physical'] : (int)$product['stock_physical'];
        $stockReserved = isset($data['stock_reserved']) ? (int)$data['stock_reserved'] : (int)$product['stock_reserved'];

        if (empty($name)) {
            Response::error('Nama produk tidak boleh kosong', 422);
            return;
        }

        if ($costPrice < 0 || $sellingPrice < 0 || $stockPhysical < 0 || $stockReserved < 0) {
            Response::error('Nilai harga dan stok tidak boleh bernilai negatif', 422);
            return;
        }

        $updated = Product::update($sku, [
            'name'           => $name,
            'category'       => $category,
            'cost_price'     => $costPrice,
            'selling_price'  => $sellingPrice,
            'stock_physical' => $stockPhysical,
            'stock_reserved' => $stockReserved
        ]);

        if ($updated) {
            $refreshed = Product::findBySku($sku);
            $available = max(0, ((int)$refreshed['stock_physical']) - ((int)$refreshed['stock_reserved']));
            Response::json([
                'sku'             => $sku,
                'name'            => $refreshed['name'],
                'category'        => $refreshed['category'],
                'cost_price'      => (float)$refreshed['cost_price'],
                'selling_price'   => (float)$refreshed['selling_price'],
                'stock_physical'  => (int)$refreshed['stock_physical'],
                'stock_reserved'  => (int)$refreshed['stock_reserved'],
                'stock_available' => $available
            ], "Data produk '{$sku}' berhasil diperbarui");
        } else {
            Response::error('Gagal memperbarui data produk', 500);
        }
    }

    /**
     * Handle deleting product
     */
    private function handleDeleteProduct(array $data): void
    {
        $sku = strtoupper(trim(strip_tags($data['sku'] ?? '')));
        if (empty($sku)) {
            Response::error('Kode SKU wajib diisi untuk menghapus data', 422);
            return;
        }

        $product = Product::findBySku($sku);
        if (!$product) {
            Response::error("Produk dengan SKU '{$sku}' tidak ditemukan", 404);
            return;
        }

        $deleted = Product::delete($sku);
        if ($deleted) {
            Response::json(['sku' => $sku], "Produk '{$sku}' berhasil dihapus dari inventaris");
        } else {
            Response::error('Gagal menghapus produk', 500);
        }
    }

    /**
     * Handle manual stock adjustment
     */
    private function handleStockAdjustment(array $data): void
    {
        $sku = trim(strip_tags($data['sku'] ?? ''));
        if (empty($sku)) {
            Response::error('SKU is required for stock adjustment', 422);
            return;
        }

        $product = Product::findBySku($sku);
        if (!$product) {
            Response::error("Product with SKU '{$sku}' not found", 404);
            return;
        }

        if (!isset($data['stock_physical'])) {
            Response::error('Field stock_physical is required', 422);
            return;
        }

        $newPhysical = (int)$data['stock_physical'];
        $newReserved = isset($data['stock_reserved']) ? (int)$data['stock_reserved'] : null;

        if ($newPhysical < 0 || ($newReserved !== null && $newReserved < 0)) {
            Response::error('Stock quantities cannot be negative', 422);
            return;
        }

        $updated = Product::adjustStock($sku, $newPhysical, $newReserved);

        if ($updated) {
            $refreshed = Product::findBySku($sku);
            $available = max(0, ((int)$refreshed['stock_physical']) - ((int)$refreshed['stock_reserved']));
            Response::json([
                'sku'             => $sku,
                'stock_physical'  => (int)$refreshed['stock_physical'],
                'stock_reserved'  => (int)$refreshed['stock_reserved'],
                'stock_available' => $available
            ], "Stock for SKU '{$sku}' successfully updated");
        } else {
            Response::error('Failed to update stock', 500);
        }
    }

    /**
     * Handle new SKU creation
     */
    private function handleCreateProduct(array $data): void
    {
        $sku = strtoupper(trim(strip_tags($data['sku'] ?? '')));
        $name = trim(strip_tags($data['name'] ?? ''));
        $category = trim(strip_tags($data['category'] ?? 'General'));
        $costPrice = isset($data['cost_price']) ? (float)$data['cost_price'] : 0.0;
        $sellingPrice = isset($data['selling_price']) ? (float)$data['selling_price'] : 0.0;
        $stockPhysical = isset($data['stock_physical']) ? (int)$data['stock_physical'] : 0;
        $stockReserved = isset($data['stock_reserved']) ? (int)$data['stock_reserved'] : 0;

        // Validations
        $errors = [];
        if (empty($sku)) {
            $errors['sku'] = 'SKU code is mandatory';
        } elseif (!preg_match('/^[A-Z0-9\-_]{3,30}$/i', $sku)) {
            $errors['sku'] = 'SKU must be 3-30 alphanumeric characters (hyphens and underscores allowed)';
        }

        if (empty($name)) {
            $errors['name'] = 'Product name is mandatory';
        }

        if ($costPrice < 0) {
            $errors['cost_price'] = 'Cost price cannot be negative';
        }

        if ($sellingPrice < 0) {
            $errors['selling_price'] = 'Selling price cannot be negative';
        }

        if ($stockPhysical < 0) {
            $errors['stock_physical'] = 'Physical stock cannot be negative';
        }

        if ($stockReserved < 0) {
            $errors['stock_reserved'] = 'Reserved stock cannot be negative';
        }

        // Check for SKU duplication
        if (empty($errors)) {
            $existing = Product::findBySku($sku);
            if ($existing) {
                $errors['sku'] = "SKU '{$sku}' is already registered in the system";
            }
        }

        if (!empty($errors)) {
            Response::error('Validation failed', 422, $errors);
            return;
        }

        $created = Product::create([
            'sku'            => $sku,
            'name'           => $name,
            'category'       => $category,
            'cost_price'     => $costPrice,
            'selling_price'  => $sellingPrice,
            'stock_physical' => $stockPhysical,
            'stock_reserved' => $stockReserved
        ]);

        Response::json($created, "SKU '{$sku}' successfully created", 201);
    }
}
