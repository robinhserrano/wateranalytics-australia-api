<?php

namespace App\Services;

use App\Models\CommissionApproval;
use App\Models\CommissionCalculation;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LegacySyncService
{
    /**
     * Sync legacy flags from SalesOrder to CommissionCalculation and Approvals.
     */
    public function syncLegacyStatus(SalesOrder $salesOrder): void
    {
        $calculation = $salesOrder->commissionCalculation;

        if (!$calculation) {
            return;
        }

        $calculator = app(CommissionCalculator::class);

        DB::transaction(function () use ($salesOrder, $calculation, $calculator) {
            // Handle Manager Confirmation
            if ($salesOrder->legacy_confirmed_by_manager) {
                $updates = ['confirmed_by_manager' => true];
                $approver = null;

                if ($salesOrder->legacy_last_confirmed_by) {
                    $approver = User::where('legacy_id', $salesOrder->legacy_last_confirmed_by)->first();
                }

                // Auto-approve if pending OR correct the approver if it's currently System/Null
                if ($calculation->status === 'pending') {
                    $updates['status'] = 'approved';
                    $updates['approved_at'] = now();
                    $updates['approved_by'] = $approver ? $approver->id : null;
                } elseif ($calculation->status === 'approved' && ($calculation->approved_by === null || $calculation->approved_by === 1)) {
                    if ($approver) {
                        $updates['approved_by'] = $approver->id;
                    }
                }

                $calculation->update($updates);

                if ($approver) {
                    // Record confirmation history if not already present
                    CommissionApproval::firstOrCreate([
                        'commission_calculation_id' => $calculation->id,
                        'approver_id' => $approver->id,
                        'action' => 'confirmed',
                    ], [
                        'notes' => 'Legacy V1 Migration Mapping',
                        'created_at' => $salesOrder->create_date ?? now(),
                    ]);

                     // Record approval history if status is approved but history is missing
                    if ($calculation->fresh()->status === 'approved') {
                        CommissionApproval::firstOrCreate([
                            'commission_calculation_id' => $calculation->id,
                            'approver_id' => $approver->id,
                            'action' => 'approved',
                        ], [
                            'notes' => 'Legacy V1 Auto-Approval',
                            'created_at' => $calculation->approved_at ?? now(),
                        ]);
                    }
                }
            }

            // Handle Entered to Odoo
            if ($salesOrder->legacy_is_entered_odoo) {
                $updates = ['entered_to_odoo' => true];
                $approver = null;

                if ($salesOrder->legacy_last_entered_odoo_by) {
                    $approver = User::where('legacy_id', $salesOrder->legacy_last_entered_odoo_by)->first();
                }

                // Logic here is similar to confirmation - ensure we have the record
                $calculation->update($updates);

                if ($approver) {
                    CommissionApproval::firstOrCreate([
                        'commission_calculation_id' => $calculation->id,
                        'approver_id' => $approver->id,
                        'action' => 'entered_to_odoo',
                    ], [
                        'notes' => 'Legacy V1 Migration Mapping',
                        'created_at' => now(),
                    ]);
                }
            }

            // Handle Manual Adjustments from Legacy
            if ($salesOrder->legacy_additional_deduction !== null) {
                // Find the user who made the adjustment
                $adjuster = null;
                if ($salesOrder->legacy_last_manual_add_by) {
                    $adjuster = User::where('legacy_id', $salesOrder->legacy_last_manual_add_by)->first();
                }

                $reason = $salesOrder->legacy_manual_notes ?? 'Legacy Manual Adjustment';
                
                // Use the Calculator to apply the adjustment as an absolute target
                $calculator->applyManualAdjustment(
                    $calculation,
                    (float) $salesOrder->legacy_additional_deduction,
                    $adjuster ? $adjuster->id : 1, // Fallback to System User
                    $reason
                );
            }
        });
    }
}
