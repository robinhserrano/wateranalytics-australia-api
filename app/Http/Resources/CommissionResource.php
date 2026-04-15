<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sales_order_id' => $this->sales_order_id,
            'user_id' => $this->user_id,
            'sales_manager_id' => $this->sales_manager_id,
            
            // Sales information
            'sales_source' => $this->sales_source,
            'payment_type' => $this->payment_type,
            
            // Breakdown components
            'selling_price' => (float) $this->selling_price,
            'additional_cost' => (float) $this->additional_cost,
            'landing_price' => (float) $this->landing_price,
            'profit' => (float) $this->profit,
            
            // Commission amounts
            'base_commission' => (float) $this->base_commission,
            'extra_commission' => (float) $this->extra_commission,
            'manual_adjustment' => (float) $this->manual_adjustment,
            'final_commission' => (float) $this->final_commission,
            
            // Flags
            'is_special_product' => (bool) $this->is_special_product,
            'status' => $this->status,
            'confirmed_by_manager' => (bool) $this->confirmed_by_manager,
            'entered_to_odoo' => (bool) $this->entered_to_odoo,
            
            // Approval information
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at?->toISOString(),
            'rejected_by' => $this->rejected_by,
            'rejected_at' => $this->rejected_at?->toISOString(),
            'rejection_reason' => $this->rejection_reason,
            'paid_at' => $this->paid_at?->toISOString(),
            
            // Latest Audit Info for UI
            'last_adjustment_note' => $this->adjustments()->latest()->first()?->reason,
            'last_adjustment_by' => $this->adjustments()->latest()->with('adjuster')->first()?->adjuster?->name,
            'last_action_by' => $this->approvals()->latest()->with('approver')->first()?->approver?->name,
            'last_action_at' => $this->approvals()->latest()->first()?->created_at?->toISOString(),

            
            // Metadata
            'calculation_metadata' => $this->calculation_metadata,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            
            // Relationships
            'sales_order' => $this->whenLoaded('salesOrder', function () {
                return [
                    'id' => $this->salesOrder->id,
                    'name' => $this->salesOrder->name,
                    'partner_name' => $this->salesOrder->partner_name,
                    'amount_total' => (float) $this->salesOrder->amount_total,
                ];
            }),
            
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            
            'sales_manager' => $this->whenLoaded('salesManager', function () {
                return $this->salesManager ? [
                    'id' => $this->salesManager->id,
                    'name' => $this->salesManager->name,
                ] : null;
            }),
            
            'approver' => $this->whenLoaded('approver', function () {
                return $this->approver ? [
                    'id' => $this->approver->id,
                    'name' => $this->approver->name,
                ] : null;
            }),
            
            'rejecter' => $this->whenLoaded('rejecter', function () {
                return $this->rejecter ? [
                    'id' => $this->rejecter->id,
                    'name' => $this->rejecter->name,
                ] : null;
            }),
            
            'adjustments' => $this->whenLoaded('adjustments', function () {
                return $this->adjustments->map(function ($adjustment) {
                    return [
                        'id' => $adjustment->id,
                        'adjustment_amount' => (float) $adjustment->adjustment_amount,
                        'reason' => $adjustment->reason,
                        'created_at' => $adjustment->created_at->toISOString(),
                        'adjuster' => $adjustment->adjuster ? [
                            'id' => $adjustment->adjuster->id,
                            'name' => $adjustment->adjuster->name,
                        ] : null,
                    ];
                });
            }),
            
            'approvals' => $this->whenLoaded('approvals', function () {
                return $this->approvals->map(function ($approval) {
                    return [
                        'id' => $approval->id,
                        'action' => $approval->action,
                        'notes' => $approval->notes,
                        'created_at' => $approval->created_at->toISOString(),
                        'approver' => $approval->approver ? [
                            'id' => $approval->approver->id,
                            'name' => $approval->approver->name,
                        ] : null,
                    ];
                });
            }),
        ];
    }
}
