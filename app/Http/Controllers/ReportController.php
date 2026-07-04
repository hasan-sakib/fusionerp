<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reports) {}

    public function index(): View
    {
        $stats       = $this->reports->getOverviewStats();
        $trend       = $this->reports->getRevenueTrend(12);
        $byStatus    = $this->reports->getOrdersByStatus();
        $topProducts = $this->reports->getTopProductsByRevenue(8);

        return view('reports.index', compact('stats', 'trend', 'byStatus', 'topProducts'));
    }

    public function sales(Request $request): View
    {
        [$from, $to] = $this->parseDateRange($request);

        $summary      = $this->reports->getSalesSummary($from, $to);
        $trend        = $this->reports->getSalesTrend($from, $to);
        $byStatus     = $this->reports->getOrdersByStatus();
        $topProducts  = $this->reports->getSalesTopProducts($from, $to, 10);
        $topCustomers = $this->reports->getTopCustomers($from, $to, 10);

        return view('reports.sales', compact('summary', 'trend', 'byStatus', 'topProducts', 'topCustomers', 'from', 'to'));
    }

    public function inventory(): View
    {
        $stats      = $this->reports->getInventoryStats();
        $byCategory = $this->reports->getStockByCategory();
        $lowStock   = $this->reports->getLowStockProducts(30);
        $movements  = $this->reports->getRecentMovements(30);

        return view('reports.inventory', compact('stats', 'byCategory', 'lowStock', 'movements'));
    }

    public function export(Request $request, string $type): Response
    {
        return match ($type) {
            'sales'     => $this->exportSales($request),
            'inventory' => $this->exportInventory(),
            default     => abort(404),
        };
    }

    // -------------------------------------------------------------------------

    private function exportSales(Request $request): Response
    {
        [$from, $to] = $this->parseDateRange($request);
        $rows     = $this->reports->buildSalesCsvRows($from, $to);
        $filename = "sales-report-{$from->format('Y-m-d')}-to-{$to->format('Y-m-d')}.csv";

        return $this->csvResponse($filename, $rows);
    }

    private function exportInventory(): Response
    {
        $rows = $this->reports->buildInventoryCsvRows();

        return $this->csvResponse('inventory-report-' . now()->format('Y-m-d') . '.csv', $rows);
    }

    private function csvResponse(string $filename, array $rows): Response
    {
        $handle = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ]);
    }

    private function parseDateRange(Request $request): array
    {
        $from = $request->filled('from')
            ? Carbon::createFromFormat('Y-m-d', $request->input('from'))->startOfDay()
            : now()->subDays(29)->startOfDay();

        $to = $request->filled('to')
            ? Carbon::createFromFormat('Y-m-d', $request->input('to'))->endOfDay()
            : now()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
    }
}
