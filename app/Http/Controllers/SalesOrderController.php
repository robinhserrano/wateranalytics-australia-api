<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalesOrderController extends Controller
{
    protected $calculator;

    public function __construct(\App\Services\CommissionCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    public function index(Request $request)
    {
        $query = SalesOrder::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('partner_name', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%");
            });
        }

        $salesOrders = $query->orderBy('create_date', 'desc')
            ->paginate(80)
            ->withQueryString();

        return Inertia::render('SalesOrders/Index', [
            'salesOrders' => $salesOrders,
            'filters' => $request->only(['search']),
        ]);
    }

    public function show(SalesOrder $salesOrder)
    {
        $calculationError = null;

        // Auto-calculate commission if missing and possible
        if (!$salesOrder->commissionCalculation) {
            try {
                // Only attempt if we have enough info (e.g. user assigned)
                // The calculator handles validation internally usually, or throws exception
                $this->calculator->calculateCommission($salesOrder);
                $salesOrder->refresh(); // Refresh to get the new relation
            } catch (\Exception $e) {
                // Log warning and capture error for UI
                \Log::warning("Auto-calculation failed for order {$salesOrder->id}: " . $e->getMessage());
                $calculationError = $e->getMessage();
            }
        }

        $salesOrder->load([
            'lines.product',
            'lines.landingPrice',
            'commissionCalculation.user',
            'commissionCalculation.salesManager',
            'commissionCalculation.adjustments.adjuster',
            'commissionCalculation.approvals.approver',
        ]);

        return Inertia::render('SalesOrders/Show', [
            'salesOrder' => $salesOrder,
            'calculationError' => $calculationError,
        ]);
    }
}
