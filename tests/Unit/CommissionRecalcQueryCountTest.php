<?php

use App\Console\Commands\SyncOdooSalesOrders;
use App\Models\Contact;
use App\Models\LandingPrice;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use App\Models\User;
use App\Services\CommissionCalculator;
use Illuminate\Console\OutputStyle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

// The synced-orders commission recalc used to lazy-load lines/products/landing prices and the
// existing calculation per order (~5 reads each, N+1). Batched, each extra order should only
// cost its own writes: updateOrCreate (select + write) and the sales_orders flag update = 3.

uses(RefreshDatabase::class);

function seedOrdersWithLines(int $count): array
{
    $user = User::factory()->create(['self_gen_base' => 1000, 'company_lead_base' => 500, 'commission_split' => 50]);
    $ids = [];

    for ($i = 1; $i <= $count; $i++) {
        $contact = Contact::create(['odoo_id' => 70000 + $i, 'display_name' => "Contact $i", 'user_id' => $user->id]);
        $order = SalesOrder::create([
            'odoo_id' => 60000 + $i, 'name' => "SO-Q$i", 'amount_total' => 1000,
            'x_studio_payment_type' => 'cash', 'x_studio_sales_source' => 'company_lead', 'partner_id' => $contact->odoo_id,
        ]);
        foreach ([1, 2] as $n) {
            $product = Product::create(['odoo_id' => 80000 + $i * 10 + $n, 'name' => "Product $i-$n"]);
            LandingPrice::create(['name' => "LP $i-$n", 'product_id' => $product->id]);
            SalesOrderLine::create([
                'odoo_id' => 90000 + $i * 10 + $n, 'sales_order_id' => $order->id, 'odoo_order_id' => $order->odoo_id,
                'product_id' => $product->id, 'product_name' => "Product $i-$n", 'price_subtotal' => 100,
            ]);
        }
        $ids[] = $order->odoo_id;
    }

    return $ids;
}

function countRecalcQueries(array $odooIds): int
{
    $command = new SyncOdooSalesOrders;
    $command->setOutput(new OutputStyle(new ArrayInput([]), new NullOutput));
    $method = new ReflectionMethod($command, 'recalculateCommissionsForSyncedOrders');
    $method->setAccessible(true);

    DB::flushQueryLog();
    DB::enableQueryLog();
    $method->invoke($command, new CommissionCalculator, $odooIds);
    DB::disableQueryLog();

    return count(DB::getQueryLog());
}

test('recalculating commissions for synced orders does not run per-order read queries', function () {
    $ids = seedOrdersWithLines(10);

    $one = countRecalcQueries([$ids[0]]);
    DB::table('commission_calculations')->delete(); // both runs start from "no calculation yet"
    $ten = countRecalcQueries($ids);

    // 9 extra orders may only add their 3 writes each; the N+1 version added ~8 each
    expect($ten - $one)->toBeLessThanOrEqual(9 * 3);
});

test('batched recalculation produces the same commissions as one-by-one', function () {
    $ids = seedOrdersWithLines(3);

    countRecalcQueries($ids);
    $batched = SalesOrder::whereIn('odoo_id', $ids)->with('commissionCalculation')->get()
        ->mapWithKeys(fn ($o) => [$o->odoo_id => (float) $o->commissionCalculation->final_commission]);

    DB::table('commission_calculations')->delete();
    foreach ($ids as $id) {
        (new CommissionCalculator)->calculateCommission(SalesOrder::where('odoo_id', $id)->first());
    }
    $single = SalesOrder::whereIn('odoo_id', $ids)->with('commissionCalculation')->get()
        ->mapWithKeys(fn ($o) => [$o->odoo_id => (float) $o->commissionCalculation->final_commission]);

    expect($batched->all())->toBe($single->all());
});
