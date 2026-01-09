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

        DB::transaction(function () use ($salesOrder, $calculation) {
            // Handle Manager Confirmation
            if ($salesOrder->legacy_confirmed_by_manager && !$calculation->confirmed_by_manager) {
                $calculation->update(['confirmed_by_manager' => true]);

                if ($salesOrder->legacy_last_confirmed_by) {
                    $approver = User::where('legacy_id', $salesOrder->legacy_last_confirmed_by)->first();
                    if ($approver) {
                        CommissionApproval::firstOrCreate([
                            'commission_calculation_id' => $calculation->id,
                            'approver_id' => $approver->id,
                            'action' => 'confirmed',
                        ], [
                            'notes' => 'Legacy V1 Migration Mapping',
                        ]);
                    }
                }
            }

            // Handle Entered to Odoo
            if ($salesOrder->legacy_is_entered_odoo && !$calculation->entered_to_odoo) {
                $calculation->update(['entered_to_odoo' => true]);

                if ($salesOrder->legacy_last_entered_odoo_by) {
                    $approver = User::where('legacy_id', $salesOrder->legacy_last_entered_odoo_by)->first();
                    if ($approver) {
                        CommissionApproval::firstOrCreate([
                            'commission_calculation_id' => $calculation->id,
                            'approver_id' => $approver->id,
                            'action' => 'entered_to_odoo',
                        ], [
                            'notes' => 'Legacy V1 Migration Mapping',
                        ]);
                    }
                }
            }
        });
    }
}
