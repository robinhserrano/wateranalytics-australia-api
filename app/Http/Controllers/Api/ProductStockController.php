<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductStock;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProductStockController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 15);
        
        // --- Active (non-excluded) warehouses ---
        $activeWarehouses = Warehouse::where('odoo_id', '>', 0)
            ->where('is_excluded', false)
            ->orderBy('name')
            ->get();

        $activeLocationPaths = $activeWarehouses->map(fn($w) => "{$w->code}/Stock")->values()->all();

        try {
            $offset = 0;
            
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

            $context = [
                'lang'     => 'en_US',
                'tz'       => 'Australia/Sydney',
                'bin_size' => true,
                'default_is_storable' => true,
                'location' => $activeLocationPaths,
            ];

            // Single RPC call
            $result = $this->callOdooRpc('product.product', 'web_search_read', [], [
                'specification' => $specification,
                'offset'        => $offset,
                'limit'         => $perPage,
                'order'         => 'name asc',
                'context'       => $context,
                'count_limit'   => 10001,
                'domain'        => $domain,
            ]);

            $records = $result['records'] ?? [];

            $formatted = collect($records)->map(function ($product) {
                $categ = $product['categ_id'] ?? null;
                return [
                    'id'               => $product['id'],
                    'display_name'     => $product['display_name'] ?? '',
                    'category'         => is_array($categ) ? ($categ['display_name'] ?? 'Uncategorized') : 'Uncategorized',
                    'qty_available'    => $product['qty_available'] ?? 0,
                    'free_qty'         => $product['free_qty'] ?? 0,
                    'incoming_qty'     => $product['incoming_qty'] ?? 0,
                    'outgoing_qty'     => $product['outgoing_qty'] ?? 0,
                ];
            });

            return response()->json([
                'success' => true,
                'source'  => 'live',
                'data'    => $formatted,
            ]);

        } catch (\Exception $e) {
            Log::error('API Odoo Live Stock Fetch Failed: ' . $e->getMessage());
            
            // Fallback to cached DB
            $activeWarehouseIds = $activeWarehouses->pluck('id')->toArray();

            $query = DB::table('product_stocks')
                ->whereIn('warehouse_id', $activeWarehouseIds)
                ->select([
                    'display_name',
                    'categ_name',
                    DB::raw('SUM(qty_available)     as qty_available'),
                    DB::raw('SUM(free_qty)          as free_qty'),
                    DB::raw('SUM(incoming_qty)      as incoming_qty'),
                    DB::raw('SUM(outgoing_qty)      as outgoing_qty'),
                ])
                ->groupBy('display_name', 'categ_name');

            if ($search) {
                $query->where(fn($q) => $q
                    ->where('display_name', 'like', "%{$search}%")
                    ->orWhere('categ_name', 'like', "%{$search}%")
                );
            }

            $records = $query->orderBy('display_name')->limit($perPage)->get();

            $formatted = $records->map(function ($row) {
                return [
                    'display_name'  => $row->display_name,
                    'category'      => $row->categ_name,
                    'qty_available' => $row->qty_available,
                    'free_qty'      => $row->free_qty,
                    'incoming_qty'  => $row->incoming_qty,
                    'outgoing_qty'  => $row->outgoing_qty,
                ];
            });

            return response()->json([
                'success' => true,
                'source'  => 'cached',
                'data'    => $formatted,
            ]);
        }
    }

    protected function callOdooRpc(string $model, string $method, array $args = [], array $kwargs = [])
    {
        $host     = rtrim(config('odoo.host'), '/');
        $db       = config('odoo.database');
        $username = config('odoo.username');
        $password = config('odoo.password');

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
                throw new \Exception("Odoo Authentication Failed");
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
}
