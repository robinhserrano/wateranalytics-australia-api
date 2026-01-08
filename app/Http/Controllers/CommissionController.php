<?php

namespace App\Http\Controllers;

use App\Models\CommissionCalculation;
use App\Models\CommissionApproval;
use App\Models\SalesOrder;
use App\Services\CommissionCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CommissionController extends Controller
{
    protected CommissionCalculator $calculator;

    public function __construct(CommissionCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Display a listing of commission calculations
     */
    public function index(Request $request)
    {
        $query = CommissionCalculation::with(['salesOrder', 'user', 'salesManager'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by sales source
        if ($request->has('sales_source') && $request->sales_source !== 'all') {
            $query->where('sales_source', $request->sales_source);
        }

        // Filter by user (salesperson)
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by sales manager
        if ($request->has('sales_manager_id')) {
            $query->where('sales_manager_id', $request->sales_manager_id);
        }

        // Search by sales order name or salesperson
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('salesOrder', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('partner_name', 'like', "%{$search}%");
            })->orWhereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $commissions = $query->paginate(50)->withQueryString();

        // Calculate summary statistics
        $summary = [
            'total_pending' => CommissionCalculation::where('status', 'pending')->sum('final_commission'),
            'total_approved' => CommissionCalculation::where('status', 'approved')->sum('final_commission'),
            'total_paid' => CommissionCalculation::where('status', 'paid')->sum('final_commission'),
            'count_pending' => CommissionCalculation::where('status', 'pending')->count(),
            'count_approved' => CommissionCalculation::where('status', 'approved')->count(),
            'count_paid' => CommissionCalculation::where('status', 'paid')->count(),
        ];

        return Inertia::render('Commissions/Index', [
            'commissions' => $commissions,
            'filters' => $request->only(['status', 'sales_source', 'user_id', 'sales_manager_id', 'search']),
            'summary' => $summary,
        ]);
    }

    /**
     * Display the specified commission calculation with breakdown
     */
    public function show(CommissionCalculation $commission)
    {
        $commission->load([
            'salesOrder.lines.product',
            'salesOrder.lines.landingPrice',
            'user',
            'salesManager',
            'approver',
            'rejecter',
            'adjustments.adjuster',
            'approvals.approver',
        ]);

        return Inertia::render('Commissions/Show', [
            'commission' => $commission,
        ]);
    }

    /**
     * Calculate commission for a sales order
     */
    public function calculate(Request $request)
    {
        $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
        ]);

        $salesOrder = SalesOrder::findOrFail($request->sales_order_id);

        try {
            $commission = $this->calculator->calculateCommission($salesOrder);

            return response()->json([
                'success' => true,
                'message' => 'Commission calculated successfully',
                'commission' => $commission->load(['salesOrder', 'user']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate commission: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Recalculate commission for an existing calculation
     */
    public function recalculate(CommissionCalculation $commission)
    {
        try {
            $newCommission = $this->calculator->recalculate($commission);



            return back()->with('success', 'Commission recalculated successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to recalculate commission: ' . $e->getMessage());
        }
    }

    /**
     * Apply manual adjustment to commission
     */
    public function adjust(Request $request, CommissionCalculation $commission)
    {
        $request->validate([
            'adjustment_amount' => 'required|numeric',
            'reason' => 'nullable|string',
        ]);

        try {
            $updatedCommission = $this->calculator->applyManualAdjustment(
                $commission,
                $request->adjustment_amount,
                Auth::id(),
                $request->reason ?? 'Manual Adjustment from UI'
            );

            return back()->with('success', 'Adjustment applied successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to apply adjustment: ' . $e->getMessage());
        }
    }

    /**
     * Approve commission calculation
     */
    public function approve(Request $request, CommissionCalculation $commission)
    {
        $request->validate([
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $commission->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            // Create approval record
            CommissionApproval::create([
                'commission_calculation_id' => $commission->id,
                'approver_id' => Auth::id(),
                'action' => 'approved',
                'notes' => $request->notes,
            ]);

            DB::commit();

            return back()->with('success', 'Commission approved successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve commission: ' . $e->getMessage());
        }
    }

    /**
     * Reject commission calculation
     */
    public function reject(Request $request, CommissionCalculation $commission)
    {
        $request->validate([
            'reason' => 'required|string|min:10',
        ]);

        DB::beginTransaction();
        try {
            $commission->update([
                'status' => 'rejected',
                'rejected_by' => Auth::id(),
                'rejected_at' => now(),
                'rejection_reason' => $request->reason,
            ]);

            // Create approval record
            CommissionApproval::create([
                'commission_calculation_id' => $commission->id,
                'approver_id' => Auth::id(),
                'action' => 'rejected',
                'notes' => $request->reason,
            ]);

            DB::commit();

            return back()->with('success', 'Commission rejected');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to reject commission: ' . $e->getMessage());
        }
    }

    /**
     * Mark commission as confirmed by manager
     */
    public function confirm(Request $request, CommissionCalculation $commission)
    {
        $request->validate([
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $commission->update([
                'confirmed_by_manager' => true,
            ]);

            // Create approval record
            CommissionApproval::create([
                'commission_calculation_id' => $commission->id,
                'approver_id' => Auth::id(),
                'action' => 'confirmed',
                'notes' => $request->notes,
            ]);

            DB::commit();

            return back()->with('success', 'Commission confirmed by manager');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to confirm commission: ' . $e->getMessage());
        }
    }

    /**
     * Mark commission as paid
     */
    public function markAsPaid(Request $request, CommissionCalculation $commission)
    {
        if ($commission->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Only approved commissions can be marked as paid',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $commission->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Commission marked as paid');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to mark commission as paid: ' . $e->getMessage());
        }
    }

    /**
     * Mark commission as entered to Odoo
     */
    public function markAsEnteredToOdoo(Request $request, CommissionCalculation $commission)
    {
        DB::beginTransaction();
        try {
            $commission->update([
                'entered_to_odoo' => true,
            ]);

            // Create approval record
            CommissionApproval::create([
                'commission_calculation_id' => $commission->id,
                'approver_id' => Auth::id(),
                'action' => 'entered_to_odoo',
                'notes' => $request->notes ?? 'Entered to Odoo',
            ]);

            DB::commit();

            return back()->with('success', 'Commission marked as entered to Odoo');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to mark as entered to Odoo: ' . $e->getMessage());
        }
    }

    /**
     * Reset manager confirmation (Admin only)
     */
    public function resetConfirm(Request $request, CommissionCalculation $commission)
    {
        // Check if user is admin
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();
        try {
            $commission->update([
                'confirmed_by_manager' => false,
                'status' => 'pending', // Revert to pending
            ]);

            CommissionApproval::create([
                'commission_calculation_id' => $commission->id,
                'approver_id' => Auth::id(),
                'action' => 'reset_confirm',
                'notes' => 'Reset manager confirmation',
            ]);

            DB::commit();

            return back()->with('success', 'Manager confirmation reset successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to reset confirmation: ' . $e->getMessage());
        }
    }

    /**
     * Reset Odoo sync status (Admin only)
     */
    public function resetOdooSync(Request $request, CommissionCalculation $commission)
    {
        // Check if user is admin
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();
        try {
            $commission->update([
                'entered_to_odoo' => false,
            ]);

            CommissionApproval::create([
                'commission_calculation_id' => $commission->id,
                'approver_id' => Auth::id(),
                'action' => 'reset_odoo',
                'notes' => 'Reset Odoo sync status',
            ]);

            DB::commit();

            return back()->with('success', 'Odoo sync status reset successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to reset Odoo sync: ' . $e->getMessage());
        }
    }

    /**
     * Bulk approve commissions
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'commission_ids' => 'required|array',
            'commission_ids.*' => 'exists:commission_calculations,id',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $count = 0;
            foreach ($request->commission_ids as $commissionId) {
                $commission = CommissionCalculation::find($commissionId);
                
                if ($commission && $commission->status === 'pending') {
                    $commission->update([
                        'status' => 'approved',
                        'approved_by' => Auth::id(),
                        'approved_at' => now(),
                    ]);

                    CommissionApproval::create([
                        'commission_calculation_id' => $commission->id,
                        'approver_id' => Auth::id(),
                        'action' => 'approved',
                        'notes' => $request->notes ?? 'Bulk approval',
                    ]);

                    $count++;
                }
            }

            DB::commit();

            return back()->with('success', "{$count} commissions approved successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to bulk approve commissions: ' . $e->getMessage());
        }
    }

    /**
     * Get commission statistics for a user
     */
    public function userStats(Request $request, $userId)
    {
        $stats = [
            'total_commissions' => CommissionCalculation::where('user_id', $userId)->count(),
            'pending_amount' => CommissionCalculation::where('user_id', $userId)
                ->where('status', 'pending')
                ->sum('final_commission'),
            'approved_amount' => CommissionCalculation::where('user_id', $userId)
                ->where('status', 'approved')
                ->sum('final_commission'),
            'paid_amount' => CommissionCalculation::where('user_id', $userId)
                ->where('status', 'paid')
                ->sum('final_commission'),
            'total_earned' => CommissionCalculation::where('user_id', $userId)
                ->whereIn('status', ['approved', 'paid'])
                ->sum('final_commission'),
            'average_commission' => CommissionCalculation::where('user_id', $userId)
                ->avg('final_commission'),
            'self_gen_count' => CommissionCalculation::where('user_id', $userId)
                ->where('sales_source', 'self_gen')
                ->count(),
            'company_lead_count' => CommissionCalculation::where('user_id', $userId)
                ->where('sales_source', 'company_lead')
                ->count(),
        ];

        return response()->json($stats);
    }
}
