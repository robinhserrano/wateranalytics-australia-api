<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Obuchmann\OdooJsonRpc\Odoo;

class OdooController extends Controller
{
    public function index(Odoo $odoo)
    {
        // DEBUG: Check if configuration is loaded correctly
        $host = config('odoo.host'); 
        $database = config('odoo.database');
        $username = config('odoo.username');
        
        if (empty($host)) {
            return response()->json([
                'error' => 'Configuration Missing',
                'message' => 'ODOO_HOST is not set or is empty in your config.',
                'debug_config_host' => $host,
                'debug_env_host' => env('ODOO_HOST'), // Check if env is readable
                'tip' => 'Ensure your .env file has ODOO_HOST=https://your-instance.odoo.com (no semicolons!) and run "php artisan config:clear"'
            ], 500);
        }

        try {
            // Example: Fetch 5 partners
            $partners = $odoo->model('res.partner')
                ->limit(5)
                ->get();

            return response()->json($partners);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Odoo Connection Failed',
                'message' => $e->getMessage(),
                'config_host' => $host,
                'config_database' => $database,
                'config_username' => $username,
            ], 500);
        }
    }

    public function salesOrders(Request $request, Odoo $odoo)
    {
        try {
            $page = (int) $request->input('page', 1);
            $perPage = (int) $request->input('per_page', 80);
            $offset = ($page - 1) * $perPage;

            // Fields requested
            $fields = [
                'name',
                'create_date',
                'partner_id',
                'x_studio_sales_rep_1',
                'x_studio_sales_source',
                'x_studio_commission_paid',
                'x_studio_referred_by',
                'x_studio_referrer_processed',
                'x_studio_payment_type',
                'amount_total',
                'delivery_status',
                'amount_to_invoice',
                'x_studio_invoice_payment_status',
                // 'internal_note_display',
                'state',
                'user_id',
                'team_id',
                'tag_ids',
                'order_line',
                'tax_totals',
            ];

            // 1. Get Total Count
            $total = $odoo->model('sale.order')
                ->where('tag_ids', 'in', [2])
                ->count();

            // 2. Get Paginated Data
            $orders = $odoo->model('sale.order')
                ->fields($fields)
                ->where('tag_ids', 'in', [2])
                ->limit($perPage)
                ->offset($offset)
                ->orderBy('id', 'desc')
                ->get();

            return response()->json([
                'data' => $orders,
                'meta' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => ceil($total / $perPage),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch Sales Orders',
                'message' => $e->getMessage(),
                'hint' => 'Ensure all requested fields exist.',
            ], 500);
        }
    }
}
