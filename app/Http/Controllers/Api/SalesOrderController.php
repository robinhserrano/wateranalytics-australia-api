<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use Illuminate\Http\Request;

/**
 * @group Sales Orders
 *
 * APIs for managing sales orders
 */
class SalesOrderController extends Controller
{
    /**
     * List Sales Orders
     *
     * Get a paginated list of sales orders with their order lines.
     *
     * @queryParam page int The page number. Example: 1
     * @queryParam per_page int The number of items per page. Example: 15
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);

        $orders = SalesOrder::with('lines')
            ->orderBy('create_date', 'desc')
            ->paginate($perPage);

        return response()->json($orders);
    }

    /**
     * Get Sales Order
     *
     * Get a specific sales order by its ID (local ID) or Odoo ID.
     *
     * @urlParam id int required The ID of the sales order. Example: 1
     */
    public function show($id)
    {
        $order = SalesOrder::with('lines')->findOrFail($id);
        return response()->json($order);
    }
}
