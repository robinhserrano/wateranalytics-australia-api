<?php

use App\Console\Commands\SyncOdooSalesOrders;
use App\Models\Contact;
use Illuminate\Support\Facades\DB;

// The user->partner map feeds sales_orders.salesperson_partner_id. Since 2026-08-07 it read the
// array-cast Contact.odoo_user_ids as a string, threw "Array to string conversion", got swallowed,
// and every sync wrote salesperson_partner_id = NULL.

function buildUserToPartnerMap(): array
{
    $method = new ReflectionMethod(SyncOdooSalesOrders::class, 'buildUserToPartnerMap');
    $method->setAccessible(true);

    return $method->invoke(new SyncOdooSalesOrders());
}

test('maps each Odoo user id to the partner whose contact lists it', function () {
    Contact::create(['odoo_id' => 500, 'display_name' => 'Jane Rep', 'odoo_user_ids' => [42, 43]]);
    Contact::create(['odoo_id' => 501, 'display_name' => 'Customer, no login']);

    // root cause of the old bug: pluck() applies the array cast, so this was never a JSON string
    expect(Contact::query()->whereNotNull('odoo_user_ids')->pluck('odoo_user_ids')->first())->toBeArray()
        ->and(fn () => (string) [42, 43])->toThrow(ErrorException::class, 'Array to string conversion')
        ->and(buildUserToPartnerMap())->toBe([42 => 500, 43 => 500]);
});

test('reads ids written straight to the column and strips the -G suffix', function () {
    Contact::create(['odoo_id' => 600, 'display_name' => 'Legacy Rep']);
    DB::table('contacts')->where('odoo_id', 600)->update(['odoo_user_ids' => json_encode(['77-G'])]);

    expect(buildUserToPartnerMap())->toBe([77 => 600]);
});
