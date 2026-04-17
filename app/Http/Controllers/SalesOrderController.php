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
        $user = auth()->user();
        $query = SalesOrder::query()->where('state', 'sale');

        // Apply role-based filtering
        if ($user->hasPermissionTo('view-all-sales-orders')) {
            // Admin and Account Officer - see all orders
            // No filter needed
        } elseif ($user->hasPermissionTo('view-team-sales-orders')) {
            // Sales Manager or Sales Team Manager - see team orders
            $teamUserIds = $user->getTeamUserIds();
            
            // Filter by user_id (Odoo user ID) or user_name
            $query->where(function ($q) use ($teamUserIds, $user) {
                // Match by Odoo user IDs
                $odooUserIds = \App\Models\User::whereIn('id', $teamUserIds)
                    ->whereNotNull('odoo_user_id')
                    ->pluck('odoo_user_id')
                    ->toArray();
                
                if (!empty($odooUserIds)) {
                    $q->whereIn('user_id', $odooUserIds);
                }
                
                // Also match by user names
                $userNames = \App\Models\User::whereIn('id', $teamUserIds)
                    ->pluck('name')
                    ->toArray();
                
                if (!empty($userNames)) {
                    $q->orWhereIn('user_name', $userNames);
                }
            });
        } else {
            // Salesperson - see only own orders
            $query->where(function ($q) use ($user) {
                if ($user->odoo_user_id) {
                    $q->where('user_id', $user->odoo_user_id);
                }
                if ($user->name) {
                    $q->orWhere('user_name', $user->name);
                }
            });
        }

        // Apply search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('partner_name', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%");
            });
        }

        // Apply Commission Status filter
        if ($request->filled('commission_status')) {
            $statuses = is_array($request->commission_status) ? $request->commission_status : [$request->commission_status];
            $query->where(function ($q) use ($statuses) {
                if (in_array('Paid', $statuses)) {
                    $q->orWhere('x_studio_commission_paid', 1);
                }
                if (in_array('Not Paid', $statuses)) {
                    $q->orWhere('x_studio_commission_paid', 0);
                }
            });
        }

        // Apply Invoice Payment Status filter
        if ($request->has('invoice_status')) {
            $statuses = is_array($request->invoice_status) ? $request->invoice_status : [$request->invoice_status];
            $query->where(function($q) use ($statuses) {
                foreach ($statuses as $status) {
                    if ($status === 'Paid') {
                        $q->orWhereIn('x_studio_invoice_payment_status', ['paid', 'in_payment']);
                    } elseif ($status === 'Partial') {
                        $q->orWhere('x_studio_invoice_payment_status', 'partial');
                    } elseif ($status === 'Not Paid') {
                        $q->orWhere('x_studio_invoice_payment_status', 'not_paid');
                    } elseif ($status === 'Not Set') {
                        $q->orWhereNull('x_studio_invoice_payment_status')
                          ->orWhere('x_studio_invoice_payment_status', '0');
                    }
                }
            });
        }

        // Apply Delivery Status filter
        if ($request->has('delivery_status')) {
            $statuses = is_array($request->delivery_status) ? $request->delivery_status : [$request->delivery_status];
            $query->where(function($q) use ($statuses) {
                foreach ($statuses as $status) {
                    if ($status === 'Fully Delivered') {
                        $q->orWhere('delivery_status', 'full');
                    } elseif ($status === 'Partially Delivered') {
                        $q->orWhereIn('delivery_status', ['partial', 'started', 'pending']);
                    } elseif ($status === 'Not Delivered') {
                        $q->orWhereNull('delivery_status')
                          ->orWhere('delivery_status', '0');
                    }
                }
            });
        }

        // Apply Commission Owner (User) filter
        if ($request->filled('user_ids')) {
            $userIds = is_array($request->user_ids) ? $request->user_ids : [$request->user_ids];
            $query->where(function ($q) use ($userIds) {
                $hasPending = in_array('pending', $userIds);
                $realUserIds = array_filter($userIds, fn($id) => $id !== 'pending');

                if (count($realUserIds) > 0) {
                    $q->whereHas('commissionCalculation', function($sub) use ($realUserIds) {
                        $sub->whereIn('user_id', $realUserIds);
                    });
                }

                if ($hasPending) {
                    $q->orWhereDoesntHave('commissionCalculation')
                      ->orWhereHas('commissionCalculation', function($sub) {
                          $sub->whereNull('user_id');
                      });
                }
            });
        }

        // Apply Date Range filter
        if ($request->filled('date_from')) {
            $query->whereDate('create_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('create_date', '<=', $request->date_to);
        }

        $salesOrders = $query->with(['commissionCalculation.user'])
            ->orderBy('create_date', 'desc')
            ->paginate(80)
            ->withQueryString();

        // Determine view scope for UI
        $viewScope = 'My Orders';
        if ($user->hasPermissionTo('view-all-sales-orders')) {
            $viewScope = 'All Orders';
        } elseif ($user->hasPermissionTo('view-team-sales-orders')) {
            $viewScope = 'Team Orders';
        }

        // Get list of users for the filter
        $users = \App\Models\User::select('id', 'name')
            ->whereHas('commissionCalculations')
            ->orderBy('name')
            ->get();

        return Inertia::render('SalesOrders/Index', [
            'salesOrders' => $salesOrders,
            'filters' => $request->only(['search', 'commission_status', 'invoice_status', 'delivery_status', 'user_ids', 'date_from', 'date_to']),
            'users' => $users,
            'viewScope' => $viewScope,
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
            'partner',
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
