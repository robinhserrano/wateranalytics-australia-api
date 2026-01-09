<?php

namespace App\Services;

use App\Models\SalesOrder;
use App\Models\User;
use App\Models\CommissionCalculation;
use App\Models\SalesOrderLine;
use Illuminate\Support\Facades\DB;

class CommissionCalculator
{
    /**
     * Special product that gets fixed $200 commission
     */
    private const SPECIAL_PRODUCT_CODE = 'usro-6s1-2w';
    private const SPECIAL_PRODUCT_COMMISSION = 200.00;

    /**
     * Payment type considered as cash (no discount)
     */
    private const CASH_PAYMENT_TYPE = 'cash';

    /**
     * Non-cash payment discount factor (10% discount = 0.9)
     */
    private const NON_CASH_DISCOUNT_FACTOR = 0.9;

    /**
     * Additional cost markup factor (10% markup = 1.1)
     */
    private const ADDITIONAL_COST_MARKUP = 1.1;

    /**
     * Main method to calculate commission for a sales order
     * 
     * @param SalesOrder $salesOrder
     * @return CommissionCalculation
     */
    public function calculateCommission(SalesOrder $salesOrder): ?CommissionCalculation
    {
        // Load necessary relationships
        $salesOrder->load(['lines.product', 'lines.landingPrice']);

        // Determine the salesperson
        // Priority 1: Check if the customer (Contact) has an explicitly assigned user (Commission Owner)
        // This satisfies the requirement: "user values... must be used based from which user owns... the specific contact id"
        $user = null;
        if ($salesOrder->partner_id) {
            $contact = \App\Models\Contact::where('odoo_id', $salesOrder->partner_id)->first();
            if ($contact && $contact->user_id) {
                $user = User::find($contact->user_id);
            }
        }

        // Priority 2: Fallback to SalesOrder's assigned salesperson
        if (!$user) {
            // 2a. Try matching by Odoo User ID
            if ($salesOrder->user_id) {
                $user = User::where('odoo_user_id', $salesOrder->user_id)
                            ->orWhere('odoo_salesperson_id', $salesOrder->user_id)
                            ->first();
            }

            // 2b. Try matching by Name (if ID match failed or ID missing)
            if (!$user && $salesOrder->user_name) {
                $user = User::where('name', $salesOrder->user_name)->first();
            }

            // 2c. [NEW] Try lookup by Salesperson Name match to Contact Display Name -> Resolve to Contact Owner
            if (!$user && $salesOrder->user_name) {
                $contact = \App\Models\Contact::where('display_name', $salesOrder->user_name)->first();
                if ($contact && $contact->user_id) {
                    $user = User::find($contact->user_id);
                }
            }

            // 2d. [NEW] Try lookup by Salesperson ID match to Contact Odoo ID -> Resolve to Contact Owner
            if (!$user && $salesOrder->user_id) {
                $contact = \App\Models\Contact::where('odoo_id', $salesOrder->user_id)->first();
                if ($contact && $contact->user_id) {
                    $user = User::find($contact->user_id);
                }
            }
        }
        
        if (!$user) {
            // Skip this order if no user can be found
            \Log::info("Skipping commission calculation for order #{$salesOrder->id}: No matching user found");
            return null;
        }

        // Determine sales source from order data
        $salesSource = $this->determineSalesSource($salesOrder);

        // Check if this is a special product
        $isSpecialProduct = $this->isSpecialProduct($salesOrder);

        // Calculate components
        $sellingPrice = $this->calculateSellingPrice($salesOrder);
        $additionalCost = $this->calculateAdditionalCost($salesOrder);
        $landingPrice = $this->calculateLandingPrice($salesOrder);
        $profit = $this->calculateProfit($sellingPrice, $additionalCost, $landingPrice);

        // Calculate commission amounts
        $baseCommission = $this->calculateBaseCommission($user, $salesSource, $isSpecialProduct);
        $extraCommission = $this->calculateExtraCommission($profit, $user->commission_split);
        $finalCommission = $baseCommission + $extraCommission;

        // Create or update commission calculation
        $commissionCalculation = CommissionCalculation::updateOrCreate(
            ['sales_order_id' => $salesOrder->id],
            [
                'user_id' => $user->id,
                'sales_manager_id' => $user->sales_manager_id,
                'sales_source' => $salesSource,
                'payment_type' => $salesOrder->x_studio_payment_type ?? 'unknown',
                'selling_price' => $sellingPrice,
                'additional_cost' => $additionalCost,
                'landing_price' => $landingPrice,
                'profit' => $profit,
                'base_commission' => $baseCommission,
                'extra_commission' => $extraCommission,
                'manual_adjustment' => 0,
                'final_commission' => $finalCommission,
                'is_special_product' => $isSpecialProduct,
                'status' => 'pending',
                'calculation_metadata' => [
                    'calculated_at' => now(),
                    'order_amount' => $salesOrder->amount_total,
                    'payment_type' => $salesOrder->x_studio_payment_type,
                    'commission_split' => $user->commission_split,
                    'self_gen_base' => $user->self_gen_base,
                    'company_lead_base' => $user->company_lead_base,
                ],
            ]
        );

        // Update sales order flag
        $salesOrder->update([
            'has_commission_calculation' => true,
            'commission_calculation_id' => $commissionCalculation->id,
        ]);

        return $commissionCalculation;
    }

    /**
     * Calculate selling price based on payment type
     * 
     * Formula:
     * - Cash Payment: amountTotal
     * - Non-Cash Payment: amountTotal × 0.9 (10% discount)
     * 
     * @param SalesOrder $salesOrder
     * @return float
     */
    public function calculateSellingPrice(SalesOrder $salesOrder): float
    {
        $amountTotal = (float) $salesOrder->amount_total;
        $paymentType = strtolower($salesOrder->x_studio_payment_type ?? '');

        if ($paymentType === self::CASH_PAYMENT_TYPE || $paymentType === 'cash or online payment') {
            return $amountTotal;
        }

        // Apply 10% discount for non-cash payments
        return $amountTotal * self::NON_CASH_DISCOUNT_FACTOR;
    }

    /**
     * Calculate additional cost from order lines
     * 
     * Formula:
     * Sum of (Tax Exclusive line item amounts × 1.1) for lines that:
     * - Do not have a matching Landing Price
     * - Are not 'installation service'
     * - Are not 'supply only'
     * 
     * @param SalesOrder $salesOrder
     * @return float
     */
    public function calculateAdditionalCost(SalesOrder $salesOrder): float
    {
        $additionalCost = 0;

        foreach ($salesOrder->lines as $line) {
            // Logic: If it has a landing price, it is NOT an additional cost (it's handled in calculateLandingPrice)
            $hasLandingPrice = false;
            if ($line->product) {
                 $hasLandingPrice = $line->product->landingPrices()
                    ->where('effective_from', '<=', $salesOrder->create_date ?? now())
                    ->orderBy('effective_from', 'desc')
                    ->exists();
            }

            if ($hasLandingPrice) {
                continue;
            }

            // Skip if it's installation service or supply only (flags or name)
            if ($line->is_installation_service || $line->is_supply_only || 
                str_contains(strtolower($line->product_name ?? ''), 'installation service') ||
                str_contains(strtolower($line->product_name ?? ''), 'supply only')) {
                continue;
            }

            // Use tax_exclusive_amount if available, otherwise calculate from price_subtotal
            $taxExclusiveAmount = $line->tax_exclusive_amount ?? $line->price_subtotal ?? 0;
            
            // Apply 1.1 markup
            $additionalCost += $taxExclusiveAmount * self::ADDITIONAL_COST_MARKUP;
        }

        return $additionalCost;
    }

    /**
     * Calculate landing price from order lines
     * 
     * Formula:
     * Sum of associated Installation Service or Supply Only costs from LandingPrice
     * - Uses supplyOnly cost if order contains a 'supply only' product line
     * - Uses installationService cost otherwise
     * 
     * @param SalesOrder $salesOrder
     * @return float
     */
    public function calculateLandingPrice(SalesOrder $salesOrder): float
    {
        $landingPrice = 0;
        
        // Check if order has any supply-only items (flag or name)
        $hasSupplyOnly = $salesOrder->lines->contains(function ($line) {
            return $line->is_supply_only || str_contains(strtolower($line->product_name ?? ''), 'supply only');
        });

        foreach ($salesOrder->lines as $line) {
            $lineLandingPrice = null;

            // Find best matching LandingPrice record based on effective_from date
            if ($line->product) {
                 $lineLandingPrice = $line->product->landingPrices()
                    ->where('effective_from', '<=', $salesOrder->create_date ?? now())
                    ->orderBy('effective_from', 'desc')
                    ->first();
                 
                 // Fallback if no effective record found
                 if (!$lineLandingPrice) {
                    $lineLandingPrice = $line->product->landingPrices()->first();
                 }
            }

            if (!$lineLandingPrice) {
                continue;
            }

            // Use supply_only or installation_service based on order type
            if ($hasSupplyOnly) {
                $landingPrice += (float) ($lineLandingPrice->supply_only ?? 0);
            } else {
                $landingPrice += (float) ($lineLandingPrice->installation_service ?? 0);
            }
        }

        return $landingPrice;
    }

    /**
     * Calculate profit
     * 
     * Formula:
     * Profit = Selling Price - Additional Cost - Landing Price
     * 
     * @param float $sellingPrice
     * @param float $additionalCost
     * @param float $landingPrice
     * @return float
     */
    public function calculateProfit(float $sellingPrice, float $additionalCost, float $landingPrice): float
    {
        return $sellingPrice - $additionalCost - $landingPrice;
    }

    /**
     * Calculate base commission
     * 
     * Rules:
     * 1. Special Product (usro-6s1-2w): $200 fixed
     * 2. Self-Generated: User's selfGen amount (default $1000)
     * 3. Company Lead: User's companyLead amount (default $500)
     * 
     * @param User $user
     * @param string $salesSource
     * @param bool $isSpecialProduct
     * @return float
     */
    private function calculateBaseCommission(User $user, string $salesSource, bool $isSpecialProduct): float
    {
        // Special product override
        if ($isSpecialProduct) {
            return self::SPECIAL_PRODUCT_COMMISSION;
        }

        // Self-generated vs Company lead
        if ($salesSource === 'self_gen') {
            return (float) $user->self_gen_base;
        }

        return (float) $user->company_lead_base;
    }

    /**
     * Calculate extra commission based on profit
     * 
     * Formula:
     * - If Profit > 0: Profit × (Commission Split / 100)
     * - If Profit ≤ 0: Profit (full negative profit)
     * 
     * @param float $profit
     * @param float $commissionSplit
     * @return float
     */
    private function calculateExtraCommission(float $profit, float $commissionSplit): float
    {
        if ($profit > 0) {
            return $profit * ($commissionSplit / 100);
        }

        // Return full profit if negative (penalty)
        return $profit;
    }

    /**
     * Apply manual adjustment to a commission calculation
     * 
     * @param CommissionCalculation $calculation
     * @param float $adjustmentAmount
     * @param int $adjustedBy
     * @param string $reason
     * @return CommissionCalculation
     */
    public function applyManualAdjustment(
        CommissionCalculation $calculation,
        float $adjustmentAmount,
        int $adjustedBy,
        string $reason
    ): CommissionCalculation {
        DB::transaction(function () use ($calculation, $adjustmentAmount, $adjustedBy, $reason) {
            // Create adjustment record
            $calculation->adjustments()->create([
                'adjusted_by' => $adjustedBy,
                'adjustment_amount' => $adjustmentAmount,
                'reason' => $reason,
            ]);

            // Update commission calculation
            $calculation->manual_adjustment = $calculation->adjustments()->sum('adjustment_amount');
            $calculation->final_commission = 
                $calculation->base_commission + 
                $calculation->extra_commission + 
                $calculation->manual_adjustment;
            $calculation->save();
        });

        return $calculation->fresh();
    }

    /**
     * Recalculate commission for an existing calculation
     * Useful when order details change
     * 
     * @param CommissionCalculation $calculation
     * @return CommissionCalculation
     */
    public function recalculate(CommissionCalculation $calculation): CommissionCalculation
    {
        $salesOrder = $calculation->salesOrder;
        
        // Delete the old calculation
        $calculation->delete();

        // Recalculate from scratch
        return $this->calculateCommission($salesOrder);
    }

    /**
     * Determine sales source from order
     * 
     * @param SalesOrder $salesOrder
     * @return string
     */
    private function determineSalesSource(SalesOrder $salesOrder): string
    {
        // Check if the order has x_studio_sales_source field
        $source = strtolower($salesOrder->x_studio_sales_source ?? '');
        
        if (str_contains($source, 'self')) {
            return 'self_gen';
        }

        return 'company_lead';
    }

    /**
     * Check if order contains the special product
     * 
     * @param SalesOrder $salesOrder
     * @return bool
     */
    private function isSpecialProduct(SalesOrder $salesOrder): bool
    {
        return $salesOrder->lines->contains(function (SalesOrderLine $line) {
            $productName = strtolower($line->product_name ?? '');
            return str_contains($productName, self::SPECIAL_PRODUCT_CODE);
        });
    }
}
