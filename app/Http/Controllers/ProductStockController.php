<?php

namespace App\Http\Controllers;

use App\Models\ProductStock;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductStockController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductStock::with('warehouse');

        // Filter by warehouse. If '0' or not provided, default to "All Warehouses" (odoo_id = 0)
        $selectedWarehouseId = $request->input('warehouse_id', '0');
        
        if ($selectedWarehouseId === '0') {
            $allWarehouse = Warehouse::where('odoo_id', 0)->first();
            if ($allWarehouse) {
                $query->where('warehouse_id', $allWarehouse->id);
            }
        } else {
            $query->where('warehouse_id', $selectedWarehouseId);
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('display_name', 'like', "%{$search}%")
                  ->orWhere('categ_name', 'like', "%{$search}%");
            });
        }

        $stocks = $query->orderBy('display_name', 'asc')
            ->paginate(80)
            ->withQueryString();

        $warehouses = Warehouse::where('odoo_id', '>', 0)->orderBy('name')->get();

        return Inertia::render('Stocks/Index', [
            'stocks' => $stocks,
            'warehouses' => $warehouses,
            'filters' => $request->only(['search', 'warehouse_id']),
        ]);
    }

    public function show(ProductStock $productStock)
    {
        $productStock->load('warehouse');

        return Inertia::render('Stocks/Show', [
            'stock' => $productStock,
        ]);
    }
}
