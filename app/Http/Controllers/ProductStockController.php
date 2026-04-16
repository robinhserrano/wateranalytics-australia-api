<?php

namespace App\Http\Controllers;

use App\Models\ProductStock;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductStockController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $selectedWarehouseId = $request->input('warehouse_id', '0');
        $page = $request->input('page', 1);
        $perPage = 80;

        $dataSource = 'live';

        try {
            $warehouse = null;
            $odooWarehouseId = 0;
            if ($selectedWarehouseId !== '0') {
                $warehouse = Warehouse::find($selectedWarehouseId);
                $odooWarehouseId = $warehouse ? $warehouse->odoo_id : 0;
            }

            // Cache key based on filters
            $cacheKey = "stocks_live_v2_w{$odooWarehouseId}_p{$page}_s" . md5($search);

            $stocks = Cache::remember($cacheKey, 60, function () use ($odooWarehouseId, $search, $page, $perPage, $warehouse) {
                $warehouseName = $warehouse ? $warehouse->name : 'All Warehouses';
                return $this->fetchLiveStocks($odooWarehouseId, $search, $page, $perPage, $warehouseName);
            });

        } catch (\Exception $e) {
            \Log::error("Odoo Live Stock Fetch Failed: " . $e->getMessage());
            $dataSource = 'cached';
            
            // Fallback to Local SQL
            $query = ProductStock::with('warehouse');
            
            if ($selectedWarehouseId === '0') {
                $allWarehouse = Warehouse::where('odoo_id', 0)->first();
                if ($allWarehouse) $query->where('warehouse_id', $allWarehouse->id);
            } else {
                $query->where('warehouse_id', $selectedWarehouseId);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('display_name', 'like', "%{$search}%")
                      ->orWhere('categ_name', 'like', "%{$search}%");
                });
            }

            $stocks = $query->orderBy('display_name', 'asc')->paginate($perPage);
        }

        $warehouses = Warehouse::where('odoo_id', '>', 0)->orderBy('name')->get();

        return Inertia::render('Stocks/Index', [
            'stocks' => $stocks,
            'warehouses' => $warehouses,
            'filters' => $request->only(['search', 'warehouse_id']),
            'dataSource' => $dataSource,
        ]);
    }

    /**
     * Fetch stocks directly from Odoo via HTTPS JSON-RPC
     */
    protected function fetchLiveStocks(int $odooWarehouseId, ?string $search, int $page, int $perPage, string $warehouseName)
    {
        $offset = ($page - 1) * $perPage;
        
        $fields = [
            'display_name', 'qty_available', 'free_qty', 'incoming_qty', 
            'outgoing_qty', 'virtual_available', 'standard_price', 'categ_id'
        ];

        $domain = [['is_storable', '=', true], ['active', '=', true]];
        if ($search) {
            $domain[] = ['name', 'ilike', $search];
        }

        $context = [];
        if ($odooWarehouseId > 0) {
            $context['warehouse_id'] = $odooWarehouseId;
        }

        // 1. Get Count
        $total = $this->callOdooRpc('product.product', 'search_count', [$domain]);

        // 2. Get Data
        $records = $this->callOdooRpc('product.product', 'search_read', [
            $domain
        ], [
            'fields' => $fields,
            'offset' => $offset,
            'limit' => $perPage,
            'order' => 'name asc',
            'context' => $context
        ]);

        $formatted = collect($records)->map(function ($product) use ($warehouseName) {
            $product = (object)$product;
            return [
                'id' => $product->id, 
                'odoo_id' => $product->id,
                'display_name' => $product->display_name,
                'categ_name' => $product->categ_id[1] ?? 'Uncategorized',
                'warehouse' => ['name' => $warehouseName],
                'qty_available' => $product->qty_available ?? 0,
                'free_qty' => $product->free_qty ?? 0,
                'incoming_qty' => $product->incoming_qty ?? 0,
                'outgoing_qty' => $product->outgoing_qty ?? 0,
                'virtual_available' => $product->virtual_available ?? 0,
                'avg_cost' => $product->standard_price ?? 0,
                'total_value' => ($product->standard_price ?? 0) * ($product->qty_available ?? 0),
            ];
        });

        return new LengthAwarePaginator(
            $formatted,
            $total,
            $perPage,
            $page,
            ['path' => route('stocks.index')]
        );
    }

    /**
     * Helper to call Odoo JSON-RPC with dynamic authentication
     */
    protected function callOdooRpc(string $model, string $method, array $args = [], array $kwargs = [])
    {
        $host = rtrim(config('odoo.host'), '/');
        $db = config('odoo.database');
        $username = config('odoo.username');
        $password = config('odoo.password');

        // 1. Get UID (Authenticated ID) - Cache it for 24 hours
        $uid = Cache::remember('odoo_uid_' . md5($username), 86400, function () use ($host, $db, $username, $password) {
            $authResponse = Http::withHeaders(['Content-Type' => 'application/json'])
                ->withoutVerifying()
                ->post("{$host}/jsonrpc", [
                    'jsonrpc' => '2.0',
                    'method' => 'call',
                    'params' => [
                        'service' => 'common',
                        'method' => 'authenticate',
                        'args' => [$db, $username, $password, []]
                    ],
                    'id' => mt_rand(1, 1000)
                ]);

            if ($authResponse->failed()) {
                throw new \Exception("Authentication request to Odoo failed.");
            }

            $result = $authResponse->json();
            $uid = $result['result'] ?? null;

            if (!$uid) {
                throw new \Exception("Odoo Authentication Failed: Access Denied for {$username}");
            }

            return (int) $uid;
        });

        // 2. Execute the actual call
        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->withoutVerifying()
            ->post("{$host}/jsonrpc", [
                'jsonrpc' => '2.0',
                'method' => 'call',
                'params' => [
                    'service' => 'object',
                    'method' => 'execute_kw',
                    'args' => [
                        $db,
                        $uid,
                        $password,
                        $model,
                        $method,
                        $args,
                        $kwargs
                    ]
                ],
                'id' => mt_rand(1, 1000000)
            ]);

        if ($response->failed()) {
            throw new \Exception("HTTPS request to Odoo failed.");
        }

        $result = $response->json();

        if (isset($result['error'])) {
            // If it's an expired session or access error, maybe the UID was purged
            if (strpos(json_encode($result['error']), 'Access Denied') !== false) {
                Cache::forget('odoo_uid_' . md5($username));
            }
            throw new \Exception("Odoo RPC Error: " . json_encode($result['error']));
        }

        return $result['result'];
    }

    public function show($id)
    {
        // Try to find local product first
        $productStock = ProductStock::where('id', $id)->orWhere('odoo_id', $id)->first();

        $dataSource = 'live';

        try {
            // Use local Odoo ID if found, otherwise assume the ID passed IS the Odoo ID
            $odooId = $productStock ? $productStock->odoo_id : (int)$id;

            $fields = [
                'display_name', 'qty_available', 'free_qty', 'incoming_qty', 
                'outgoing_qty', 'virtual_available', 'standard_price', 'categ_id'
            ];

            $records = $this->callOdooRpc('product.product', 'read', [
                [$odooId]
            ], [
                'fields' => $fields
            ]);

            $odooProduct = !empty($records) ? (object)$records[0] : null;

            if ($odooProduct) {
                if (!$productStock) {
                    $productStock = new ProductStock([
                        'odoo_id' => $odooProduct->id,
                        'display_name' => $odooProduct->display_name,
                    ]);
                }
                
                $productStock->qty_available = $odooProduct->qty_available;
                $productStock->free_qty = $odooProduct->free_qty;
                $productStock->incoming_qty = $odooProduct->incoming_qty;
                $productStock->outgoing_qty = $odooProduct->outgoing_qty;
                $productStock->virtual_available = $odooProduct->virtual_available;
                $productStock->avg_cost = $odooProduct->standard_price;
                $productStock->total_value = ($odooProduct->standard_price ?? 0) * ($odooProduct->qty_available ?? 0);
            } else if (!$productStock) {
                abort(404);
            }
        } catch (\Exception $e) {
            $dataSource = 'cached';
            if (!$productStock) abort(404);
        }

        if ($productStock && $productStock->exists) {
            $productStock->load('warehouse');
        }

        return Inertia::render('Stocks/Show', [
            'stock' => $productStock,
            'dataSource' => $dataSource,
        ]);
    }
}
