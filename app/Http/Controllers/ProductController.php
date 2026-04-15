<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('default_code', 'like', "%{$search}%")
                  ->orWhere('categ_name', 'like', "%{$search}%");
            });
        }

        // Filter by category if provided
        if ($request->has('category_id') && $request->category_id !== '' && $request->category_id !== '0') {
            $query->where('categ_id', $request->category_id);
        }

        // Filter by type if provided
        if ($request->has('type') && $request->type !== '' && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        $products = $query
            ->with([
                'latestLandingPrice' => function ($landingPriceQuery) {
                    $landingPriceQuery->select([
                        'landing_prices.id',
                        'landing_prices.product_id',
                        'landing_prices.installation_service',
                        'landing_prices.supply_only',
                        'landing_prices.effective_from',
                    ]);
                },
            ])
            ->orderBy('name', 'asc')
            ->paginate(80)
            ->withQueryString();

        // Get unique categories for filter dropdown
        $categories = Product::whereNotNull('categ_id')
            ->select('categ_id', 'categ_name')
            ->distinct()
            ->orderBy('categ_name')
            ->get();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category_id', 'type']),
        ]);
    }

    public function show(Product $product)
    {
        $product->load('landingPrices');
        
        return Inertia::render('Products/Show', [
            'product' => $product,
        ]);
    }

    public function updateLandingPrice(Request $request, Product $product)
    {
        $validated = $request->validate([
            'installation_service' => 'required|numeric|min:0',
            'supply_only' => 'required|numeric|min:0',
            'effective_from' => 'required|date',
            'product_category' => 'nullable|string',
        ]);

        $landingPrice = $product->landingPrices()->create([
            'name' => $product->name,
            'internal_reference' => $product->default_code,
            'product_category' => $validated['product_category'] ?? $product->categ_name,
            'installation_service' => $validated['installation_service'],
            'supply_only' => $validated['supply_only'],
            'effective_from' => $validated['effective_from'],
        ]);

        return back()->with('success', 'Landing price updated successfully');
    }

    public function export(Request $request)
    {
        $filename = $request->query('filename', 'Products Landing Prices.csv');
        if (!str_ends_with(strtolower($filename), '.csv')) {
            $filename .= '.csv';
        }

        $products = Product::with([
            'latestLandingPrice' => function ($landingPriceQuery) {
                $landingPriceQuery->select([
                    'landing_prices.id',
                    'landing_prices.product_id',
                    'landing_prices.installation_service',
                    'landing_prices.supply_only',
                    'landing_prices.effective_from',
                ]);
            },
        ])->orderBy('name')->get();

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = [
            'ID',
            'Product Name',
            'Internal Reference',
            'Category',
            'Type',
            'List Price',
            'On Hand Qty',
            'Latest Install Landing Price',
            'Latest Supply-Only Landing Price',
            'Landing Price Active From',
        ];

        $callback = function () use ($products, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($products as $product) {
                fputcsv($file, [
                    $product->id,
                    $product->name,
                    $product->default_code ?? '',
                    $product->categ_name ?? '',
                    $product->type ?? '',
                    $product->list_price ?? 0,
                    $product->qty_available ?? 0,
                    $product->latestLandingPrice?->installation_service ?? '',
                    $product->latestLandingPrice?->supply_only ?? '',
                    $product->latestLandingPrice?->effective_from?->format('Y-m-d') ?? '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
