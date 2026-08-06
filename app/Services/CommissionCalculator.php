<?php

namespace App\Services;

use App\Models\CommissionCalculation;
use App\Models\Contact;
use App\Models\LandingPrice;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use App\Models\User;
use Illuminate\Support\Collection;
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
     * Memoized salesperson-resolution lookup maps, built once per instance
     * (e.g. once per whole `commissions:calculate-missing` run) instead of
     * re-querying Contact/User for every sales order.
     */
    private ?Collection $contactUserIdByOdooId = null;

    private ?Collection $contactUserIdByDisplayName = null;

    private ?Collection $usersByOdooOrSalespersonId = null;

    private ?Collection $usersByName = null;

    private ?Collection $usersById = null;

    /**
     * Main method to calculate commission for a sales order
     */
    public function calculateCommission(SalesOrder $salesOrder): ?CommissionCalculation
    {
        // Load necessary relationships
        $salesOrder->load([
            'lines.product.landingPrices' => function ($query) {
                $query->orderBy('effective_from', 'desc');
            },
            'lines.landingPrice',
        ]);

        $user = $this->resolveSalesperson($salesOrder);

        if (! $user) {
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
        // Preserve existing manual adjustments and status if recalculating
        // (uses the commissionCalculation relation so callers can eager-load it across a batch)
        $existingCalculation = $salesOrder->commissionCalculation;
        $manualAdjustment = $existingCalculation ? $existingCalculation->manual_adjustment : 0;
        $status = $existingCalculation ? $existingCalculation->status : 'pending';

        $finalCommission = $baseCommission + $extraCommission + $manualAdjustment;

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
                'manual_adjustment' => $manualAdjustment,
                'final_commission' => $finalCommission,
                'is_special_product' => $isSpecialProduct,
                'status' => $status,
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
     */
    public function calculateAdditionalCost(SalesOrder $salesOrder): float
    {
        $additionalCost = 0;

        foreach ($salesOrder->lines as $line) {
            // Logic: If it has a landing price, it is NOT an additional cost (it's handled in calculateLandingPrice)
            $hasLandingPrice = $this->resolveLandingPriceForLine($line, $salesOrder) !== null;

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
     */
    public function calculateLandingPrice(SalesOrder $salesOrder): float
    {
        $landingPrice = 0;

        // Check if order has any supply-only items (flag or name)
        $hasSupplyOnly = $salesOrder->lines->contains(function ($line) {
            return $line->is_supply_only || str_contains(strtolower($line->product_name ?? ''), 'supply only');
        });

        foreach ($salesOrder->lines as $line) {
            $lineLandingPrice = $this->resolveLandingPriceForLine($line, $salesOrder);

            if (! $lineLandingPrice) {
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
     * @param  float  $adjustmentAmount
     */
    public function applyManualAdjustment(
        CommissionCalculation $calculation,
        float $targetTotal,
        int $adjustedBy,
        string $reason
    ): CommissionCalculation {
        DB::transaction(function () use ($calculation, $targetTotal, $adjustedBy, $reason) {
            $currentTotal = (float) $calculation->manual_adjustment;
            $difference = $targetTotal - $currentTotal;

            // Only create an entry if there's actually a change
            if ($difference !== 0.0) {
                // Create adjustment record for the difference
                $calculation->adjustments()->create([
                    'adjusted_by' => $adjustedBy,
                    'adjustment_amount' => $difference,
                    'reason' => $reason." (Adjusted total to $targetTotal)",
                ]);

                // Update commission calculation
                $calculation->manual_adjustment = $targetTotal;
                $calculation->final_commission =
                    $calculation->base_commission +
                    $calculation->extra_commission +
                    $calculation->manual_adjustment;
                $calculation->save();
            }
        });

        return $calculation->fresh();
    }

    /**
     * Recalculate commission for an existing calculation
     * Useful when order details change
     */
    public function recalculate(CommissionCalculation $calculation): CommissionCalculation
    {
        $salesOrder = $calculation->salesOrder;

        // Recalculate (this will now preserve manual_adjustment via updateOrCreate)
        return $this->calculateCommission($salesOrder);
    }

    /**
     * Resolve the salesperson (User) that owns a sales order, via a
     * priority-ordered fallback chain. Backed by lookup maps that are
     * built once per instance (see loadSalespersonLookupMaps()) so
     * processing many orders in one run doesn't re-query per order.
     */
    private function resolveSalesperson(SalesOrder $salesOrder): ?User
    {
        $this->loadSalespersonLookupMaps();

        // Priority 1: Check if the customer (Contact) has an explicitly assigned user (Commission Owner)
        // This satisfies the requirement: "user values... must be used based from which user owns... the specific contact id"
        $userId = $salesOrder->partner_id
            ? $this->contactUserIdByOdooId->get($salesOrder->partner_id)
            : null;

        // Priority 2: Match salesperson through the resolved salesperson_partner_id
        if (! $userId && $salesOrder->salesperson_partner_id) {
            $userId = $this->contactUserIdByOdooId->get($salesOrder->salesperson_partner_id);
        }

        // Priority 3: Fallback to older methods
        if (! $userId) {
            // 3a. Try matching by Odoo User ID
            if ($salesOrder->user_id) {
                $userId = $this->usersByOdooOrSalespersonId->get($salesOrder->user_id)?->id;
            }

            // 3b. Try matching by Name (if ID match failed or ID missing)
            if (! $userId && $salesOrder->user_name) {
                $userId = $this->usersByName->get($salesOrder->user_name)?->id;
            }

            // 3c. Try lookup by Salesperson Name match to Contact Display Name -> Resolve to Contact Owner
            if (! $userId && $salesOrder->user_name) {
                $userId = $this->contactUserIdByDisplayName->get($salesOrder->user_name);
            }

            // 3d. Try lookup by Salesperson ID match to Contact Odoo ID -> Resolve to Contact Owner
            if (! $userId && $salesOrder->user_id) {
                $userId = $this->contactUserIdByOdooId->get($salesOrder->user_id);
            }
        }

        return $userId ? $this->usersById->get($userId) : null;
    }

    /**
     * Build the memoized Contact/User lookup maps used by resolveSalesperson(),
     * once per CommissionCalculator instance.
     */
    private function loadSalespersonLookupMaps(): void
    {
        if ($this->usersById !== null) {
            return;
        }

        $this->usersById = User::all()->keyBy('id');

        $this->usersByOdooOrSalespersonId = collect();
        $this->usersByName = collect();
        foreach ($this->usersById as $user) {
            if ($user->odoo_user_id) {
                $this->usersByOdooOrSalespersonId->put($user->odoo_user_id, $user);
            }
            if ($user->odoo_salesperson_id) {
                $this->usersByOdooOrSalespersonId->put($user->odoo_salesperson_id, $user);
            }
            if ($user->name) {
                $this->usersByName->put($user->name, $user);
            }
        }

        $this->contactUserIdByOdooId = collect();
        $this->contactUserIdByDisplayName = collect();
        Contact::query()
            ->whereNotNull('user_id')
            ->get(['odoo_id', 'display_name', 'user_id'])
            ->each(function (Contact $contact) {
                $this->contactUserIdByOdooId->put($contact->odoo_id, $contact->user_id);
                $this->contactUserIdByDisplayName->put($contact->display_name, $contact->user_id);
            });
    }

    /**
     * Determine sales source from order
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
     */
    private function isSpecialProduct(SalesOrder $salesOrder): bool
    {
        return $salesOrder->lines->contains(function (SalesOrderLine $line) {
            $productName = strtolower($line->product_name ?? '');

            return str_contains($productName, self::SPECIAL_PRODUCT_CODE);
        });
    }

    /**
     * Resolve best matching landing price without triggering extra DB queries.
     */
    private function resolveLandingPriceForLine(SalesOrderLine $line, SalesOrder $salesOrder): ?LandingPrice
    {
        if (! $line->product || ! $line->product->relationLoaded('landingPrices')) {
            return null;
        }

        $landingPrices = $line->product->landingPrices;
        if ($landingPrices->isEmpty()) {
            return null;
        }

        $createDate = $salesOrder->create_date ?? now();

        $matched = $landingPrices->first(function ($price) use ($createDate) {
            return ! $price->effective_from || $price->effective_from <= $createDate;
        });

        return $matched ?: $landingPrices->first();
    }
}
