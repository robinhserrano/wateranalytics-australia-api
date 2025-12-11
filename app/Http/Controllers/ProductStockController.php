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

        // Filter by warehouse if provided (and not '0' which means "All")
        if ($request->has('warehouse_id') && $request->warehouse_id !== '' && $request->warehouse_id !== '0') {
            $query->where('warehouse_id', $request->warehouse_id);
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

        $warehouses = Warehouse::orderBy('name')->get();

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
