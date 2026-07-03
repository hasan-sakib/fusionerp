<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard) {}

    public function __invoke(Request $request)
    {
        $stats   = $this->dashboard->getStats();
        $charts  = $this->dashboard->getChartData();

        return view('dashboard', compact('stats', 'charts'));
    }
}
