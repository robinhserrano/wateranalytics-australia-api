<?php

namespace App\Http\Controllers;

use App\Models\ProductStock;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductStockController extends Controller
{
    public function index(Request $request)
    {
        $search        = $request->input('search');
        $warehouseId   = $request->input('warehouse_id', '0');
        $categories    = array_map('intval', array_filter((array) $request->input('categories', [])));
        $page          = (int) $request->input('page', 1);
        $perPage       = 80;
        $dataSource    = 'live';

        // --- Active (non-excluded) warehouses for the sidebar ---
        $activeWarehouses = Warehouse::where('odoo_id', '>', 0)
            ->where('is_excluded', false)
            ->orderBy('name')
            ->get();

        // Build the list of active stock location paths for "All Warehouses" context
        $activeLocationPaths = $activeWarehouses->map(fn($w) => "{$w->code}/Stock")->values()->all();

        // Resolve the selected warehouse
        $selectedWarehouse = null;
        if ($warehouseId !== '0') {
            $selectedWarehouse = $activeWarehouses->firstWhere('id', $warehouseId);
            // If someone passed an excluded or invalid warehouse ID, fall back to all
            if (!$selectedWarehouse) {
                $warehouseId = '0';
            }
        }

        // Build Odoo context location
        $locationContext = $selectedWarehouse
            ? ["{$selectedWarehouse->code}/Stock"]
            : $activeLocationPaths;

        // --- Categories for the sidebar: use Odoo's search_panel_select_multi_range ---
        // This is exactly what Odoo's own sidebar uses: returns only categories that have
        // matching storable products, with id+display_name. Cached 1 hour.
        $availableCategories = Cache::remember('stock_categories_v2', 3600, function () {
            try {
                $cats = $this->callOdooRpc('product.product', 'search_panel_select_multi_range', [
                    'categ_id'
                ], [
                    'category_domain'  => [],
                    'comodel_domain'   => [],
                    'context'          => [
                        'lang'                => 'en_US',
                        'tz'                  => 'Australia/Sydney',
                        'default_is_storable' => true,
                    ],
                    'enable_counters'  => false,
                    'filter_domain'    => [],
                    'expand'           => false,
                    'group_by'         => false,
                    'group_domain'     => [],
                    'limit'            => 200,
                    'search_domain'    => [['is_storable', '=', true]],
                ]);
                // Result is an array of {id, display_name, ...}, sort by display_name
                $values = collect($cats['values'] ?? $cats)
                    ->map(fn($c) => ['id' => $c['id'], 'name' => $c['display_name']])
                    ->sortBy('name')
                    ->values()
                    ->all();
                return $values;
            } catch (\Exception $e) {
                Log::warning('Category panel fetch failed, falling back to local DB: ' . $e->getMessage());
                return ProductStock::whereNotNull('categ_name')
                    ->distinct()->orderBy('categ_name')
                    ->pluck('categ_name')
                    ->map(fn($n, $i) => ['id' => $i, 'name' => $n])
                    ->values()->all();
            }
        });

        try {
            // Cache key includes warehouse, page, search, and categories
            $cacheKey = 'stocks_v3_w' . $warehouseId
                . '_p' . $page
                . '_s' . md5((string) $search)
                . '_c' . md5(implode(',', $categories));

            $stocks = Cache::remember($cacheKey, 60, function () use (
                $locationContext, $search, $categories, $page, $perPage, $selectedWarehouse
            ) {
                return $this->fetchLiveStocks(
                    $locationContext, $search, $categories, $page, $perPage,
                    $selectedWarehouse ? $selectedWarehouse->name : 'All Warehouses'
                );
            });

        } catch (\Exception $e) {
            Log::error('Odoo Live Stock Fetch Failed: ' . $e->getMessage());
            $dataSource = 'cached';

            $stocks = $this->fetchCachedStocks(
                $warehouseId, $search, $categories, $page, $perPage, $activeWarehouses
            );
        }

        return Inertia::render('Stocks/Index', [
            'stocks'              => $stocks,
            'warehouses'          => $activeWarehouses->values(),
            'availableCategories' => $availableCategories,
            'filters'             => [
                'search'       => $search,
                'warehouse_id' => $warehouseId,
                'categories'   => array_values($categories),
            ],
            'dataSource'          => $dataSource,
        ]);
    }

    /**
     * Fetch live stocks from Odoo using a single web_search_read call.
     * Location context drives which warehouse(s) the quantities are scoped to.
     */
    protected function fetchLiveStocks(
        array $locationContext,
        ?string $search,
        array $categories,
        int $page,
        int $perPage,
        string $warehouseName
    ): LengthAwarePaginator {
        $offset = ($page - 1) * $perPage;

        $specification = [
            'id'               => (object)[],
            'display_name'     => (object)[],
            'categ_id'         => ['fields' => ['display_name' => (object)[]]],
            'qty_available'    => (object)[],
            'free_qty'         => (object)[],
            'incoming_qty'     => (object)[],
            'outgoing_qty'     => (object)[],
            'virtual_available'=> (object)[],
        ];

        $domain = [['is_storable', '=', true]];
        if ($search) {
            $domain[] = ['name', 'ilike', $search];
        }
        // Filter by category ID (robust — not name-string matching)
        if (!empty($categories)) {
            $domain[] = ['categ_id', 'in', array_values($categories)];
        }

        $context = [
            'lang'     => 'en_US',
            'tz'       => 'Australia/Sydney',
            'bin_size' => true,
            'default_is_storable' => true,
            'location' => $locationContext,
        ];

        // Single RPC call — web_search_read returns both count and records
        $result = $this->callOdooRpc('product.product', 'web_search_read', [], [
            'specification' => $specification,
            'offset'        => $offset,
            'limit'         => $perPage,
            'order'         => 'name asc',
            'context'       => $context,
            'count_limit'   => 10001,
            'domain'        => $domain,
        ]);

        $total   = $result['length'] ?? 0;
        $records = $result['records'] ?? [];

        $formatted = collect($records)->map(function ($product) use ($warehouseName) {
            $categ = $product['categ_id'] ?? null;
            return [
                'id'               => $product['id'],
                'odoo_id'          => $product['id'],
                'display_name'     => $product['display_name'] ?? '',
                'categ_name'       => is_array($categ) ? ($categ['display_name'] ?? 'Uncategorized') : 'Uncategorized',
                'warehouse'        => ['name' => $warehouseName],
                'qty_available'    => $product['qty_available'] ?? 0,
                'free_qty'         => $product['free_qty'] ?? 0,
                'incoming_qty'     => $product['incoming_qty'] ?? 0,
                'outgoing_qty'     => $product['outgoing_qty'] ?? 0,
                'virtual_available'=> $product['virtual_available'] ?? 0,
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
     * Fallback: aggregate stock quantities from the local DB,
     * excluding the MPG and Proto warehouses (Option B).
     */
    protected function fetchCachedStocks(
        string $warehouseId,
        ?string $search,
        array $categories,
        int $page,
        int $perPage,
        $activeWarehouses
    ): LengthAwarePaginator {
        if ($warehouseId !== '0') {
            // Single warehouse — simple query
            $query = ProductStock::with('warehouse')
                ->where('warehouse_id', $warehouseId);

            if ($search) {
                $query->where(fn($q) => $q
                    ->where('display_name', 'like', "%{$search}%")
                    ->orWhere('categ_name', 'like', "%{$search}%")
                );
            }
            if (!empty($categories)) {
                $query->whereIn('categ_name', $categories);
            }

            return $query->orderBy('display_name')->paginate($perPage);
        }

        // "All Warehouses" — aggregate SUM across active (non-excluded) warehouses only
        $activeWarehouseIds = $activeWarehouses->pluck('id')->toArray();

        $query = DB::table('product_stocks')
            ->whereIn('warehouse_id', $activeWarehouseIds)
            ->select([
                DB::raw('MIN(id) as id'),
                DB::raw('MIN(odoo_id) as odoo_id'),
                'display_name',
                'categ_name',
                DB::raw('SUM(qty_available)     as qty_available'),
                DB::raw('SUM(free_qty)          as free_qty'),
                DB::raw('SUM(incoming_qty)      as incoming_qty'),
                DB::raw('SUM(outgoing_qty)      as outgoing_qty'),
                DB::raw('SUM(virtual_available) as virtual_available'),
                DB::raw('AVG(avg_cost)          as avg_cost'),
                DB::raw('SUM(total_value)       as total_value'),
            ])
            ->groupBy('display_name', 'categ_name');

        if ($search) {
            $query->where(fn($q) => $q
                ->where('display_name', 'like', "%{$search}%")
                ->orWhere('categ_name', 'like', "%{$search}%")
            );
        }
        if (!empty($categories)) {
            $query->whereIn('categ_name', $categories);
        }

        $query->orderBy('display_name');

        $total = $query->clone()->getCountForPagination();
        $records = $query->offset(($page - 1) * $perPage)->limit($perPage)->get();

        $items = $records->map(fn($row) => (array) $row + ['warehouse' => ['name' => 'All Warehouses']]);

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => route('stocks.index')]
        );
    }

    /**
     * Call Odoo JSON-RPC (execute_kw) with cached UID authentication.
     */
    protected function callOdooRpc(string $model, string $method, array $args = [], array $kwargs = [])
    {
        $host     = rtrim(config('odoo.host'), '/');
        $db       = config('odoo.database');
        $username = config('odoo.username');
        $password = config('odoo.password');

        // Cache authenticated UID for 24 hours
        $uid = Cache::remember('odoo_uid_' . md5($username), 86400, function () use ($host, $db, $username, $password) {
            $authResponse = Http::withHeaders(['Content-Type' => 'application/json'])
                ->withoutVerifying()
                ->post("{$host}/jsonrpc", [
                    'jsonrpc' => '2.0',
                    'method'  => 'call',
                    'params'  => [
                        'service' => 'common',
                        'method'  => 'authenticate',
                        'args'    => [$db, $username, $password, []],
                    ],
                    'id' => mt_rand(1, 1000),
                ]);

            if ($authResponse->failed()) {
                throw new \Exception('Authentication request to Odoo failed.');
            }

            $uid = $authResponse->json()['result'] ?? null;
            if (!$uid) {
                throw new \Exception("Odoo Authentication Failed: Access Denied for {$username}");
            }

            return (int) $uid;
        });

        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->withoutVerifying()
            ->post("{$host}/jsonrpc", [
                'jsonrpc' => '2.0',
                'method'  => 'call',
                'params'  => [
                    'service' => 'object',
                    'method'  => 'execute_kw',
                    'args'    => [$db, $uid, $password, $model, $method, $args, $kwargs],
                ],
                'id' => mt_rand(1, 1000000),
            ]);

        if ($response->failed()) {
            throw new \Exception('HTTPS request to Odoo failed.');
        }

        $result = $response->json();

        if (isset($result['error'])) {
            if (str_contains(json_encode($result['error']), 'Access Denied')) {
                Cache::forget('odoo_uid_' . md5($username));
            }
            throw new \Exception('Odoo RPC Error: ' . json_encode($result['error']));
        }

        return $result['result'];
    }

    public function show($id)
    {
        $productStock = ProductStock::where('id', $id)->orWhere('odoo_id', $id)->first();
        $dataSource   = 'live';

        try {
            $odooId = $productStock ? $productStock->odoo_id : (int) $id;

            $result = $this->callOdooRpc('product.product', 'web_search_read', [], [
                'specification' => [
                    'id'               => (object)[],
                    'display_name'     => (object)[],
                    'categ_id'         => ['fields' => ['display_name' => (object)[]]],
                    'qty_available'    => (object)[],
                    'free_qty'         => (object)[],
                    'incoming_qty'     => (object)[],
                    'outgoing_qty'     => (object)[],
                    'virtual_available'=> (object)[],
                    'avg_cost'         => (object)[],
                    'total_value'      => (object)[],
                ],
                'domain'  => [['id', '=', $odooId]],
                'limit'   => 1,
                'context' => [
                    'bin_size'            => true,
                    'default_is_storable' => true,
                ],
            ]);

            $odooProduct = !empty($result['records']) ? (object) $result['records'][0] : null;

            if ($odooProduct) {
                if (!$productStock) {
                    $productStock = new ProductStock([
                        'odoo_id'      => $odooProduct->id,
                        'display_name' => $odooProduct->display_name,
                    ]);
                }

                $productStock->qty_available     = $odooProduct->qty_available;
                $productStock->free_qty          = $odooProduct->free_qty;
                $productStock->incoming_qty      = $odooProduct->incoming_qty;
                $productStock->outgoing_qty      = $odooProduct->outgoing_qty;
                $productStock->virtual_available = $odooProduct->virtual_available;
                $productStock->avg_cost          = $odooProduct->avg_cost;
                $productStock->total_value       = $odooProduct->total_value;
            } elseif (!$productStock) {
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
            'stock'      => $productStock,
            'dataSource' => $dataSource,
        ]);
    }
}
