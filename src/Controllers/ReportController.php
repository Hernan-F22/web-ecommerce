<?php

/**
 * Report Controller
 * Provides aggregated dataset for reporting & analytics export
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\MarketingMetric;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Utils\Response;

class ReportController
{
    /**
     * GET /api/reports
     */
    public function index(): void
    {
        $type = filter_input(INPUT_GET, 'type', FILTER_DEFAULT) ?: 'all';
        $startDate = filter_input(INPUT_GET, 'start_date', FILTER_DEFAULT);
        $endDate = filter_input(INPUT_GET, 'end_date', FILTER_DEFAULT);

        if ($startDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            $startDate = null;
        }
        if ($endDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $endDate = null;
        }

        $reportData = [];

        switch ($type) {
            case 'inventory':
                $reportData = Product::getAll();
                break;

            case 'marketing':
                $reportData = MarketingMetric::getRawRecords($startDate, $endDate);
                break;

            case 'orders':
                $reportData = SalesOrder::getAll($startDate, $endDate);
                break;

            case 'all':
            default:
                $reportData = [
                    'marketing' => MarketingMetric::getRawRecords($startDate, $endDate),
                    'inventory' => Product::getAll(),
                    'orders'    => SalesOrder::getAll($startDate, $endDate),
                ];
                break;
        }

        Response::json([
            'report_type' => $type,
            'period'      => [
                'start_date' => $startDate ?: date('Y-m-d', strtotime('-29 days')),
                'end_date'   => $endDate ?: date('Y-m-d')
            ],
            'generated_at' => date('c'),
            'records'     => $reportData
        ], "Report '{$type}' generated successfully");
    }
}
