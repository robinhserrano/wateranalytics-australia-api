<?php

use App\Console\Commands\SyncOdooSalesOrders;
use App\Models\SalesOrder;
use App\Models\SyncLog;
use App\Services\OdooClient;

// odoo:reconcile-sales fixes orders the incremental sync can no longer see: it only fetches tag 2 +
// state sale, so an order cancelled / untagged / deleted in Odoo stayed frozen locally as `sale`.

function reconcileOrder(int $odooId, array $attrs = []): SalesOrder
{
    return SalesOrder::create(array_merge(['odoo_id' => $odooId, 'name' => "S{$odooId}", 'state' => 'sale', 'tag_ids' => [2]], $attrs));
}

// $inScope: ids Odoo returns for the sync domain; $current: what Odoo says about the rest (missing = deleted)
function fakeOdooForReconcile(array $inScope, array $current): void
{
    test()->mock(OdooClient::class, function ($mock) use ($inScope, $current) {
        $mock->shouldReceive('search')->andReturn($inScope);
        $mock->shouldReceive('searchRead')->andReturnUsing(fn ($model, $domain) => collect($current)
            ->filter(fn ($o) => in_array($o->id, $domain[0][2]))->values()->all());
    });
}

test('an order cancelled in Odoo gets its real state and drops out of state=sale views', function () {
    reconcileOrder(1);
    reconcileOrder(2);
    fakeOdooForReconcile([1], [(object) ['id' => 2, 'state' => 'cancel', 'tag_ids' => [2]]]);

    $this->artisan('odoo:reconcile-sales')->assertExitCode(0);

    expect(SalesOrder::where('odoo_id', 2)->value('state'))->toBe('cancel')
        ->and(SalesOrder::where('state', 'sale')->pluck('odoo_id')->all())->toBe([1]);
});

test('an order deleted in Odoo is flagged odoo_missing_at, not deleted locally', function () {
    foreach (range(1, 10) as $id) {
        reconcileOrder($id);
    }
    fakeOdooForReconcile(range(1, 9), []);

    $this->artisan('odoo:reconcile-sales')->assertExitCode(0);

    expect(SalesOrder::where('odoo_id', 10)->first())->not->toBeNull()
        ->odoo_missing_at->not->toBeNull()
        ->and(SalesOrder::whereNotNull('odoo_missing_at')->count())->toBe(1);
});

test('an order whose tag was removed keeps state sale but gets its real tags', function () {
    reconcileOrder(1);
    fakeOdooForReconcile([], [(object) ['id' => 1, 'state' => 'sale', 'tag_ids' => [5]]]);

    $this->artisan('odoo:reconcile-sales')->assertExitCode(0);

    expect(SalesOrder::where('odoo_id', 1)->first()->tag_ids)->toBe([5])
        ->and(SyncLog::latest('id')->first()->message)->toContain('sale (untagged): 1');
});

test('orders still in scope are left untouched', function () {
    reconcileOrder(1, ['tag_ids' => [2, 7]]);
    fakeOdooForReconcile([1], []);

    $this->artisan('odoo:reconcile-sales')->assertExitCode(0);

    expect(SalesOrder::where('odoo_id', 1)->first())
        ->state->toBe('sale')
        ->tag_ids->toBe([2, 7])
        ->odoo_missing_at->toBeNull();
});

test('refuses and changes nothing when too many orders would be flagged deleted', function () {
    foreach (range(1, 10) as $id) {
        reconcileOrder($id);
    }
    fakeOdooForReconcile([], []); // Odoo suddenly "has" none of them: access problem, not deletions

    $this->artisan('odoo:reconcile-sales')->assertExitCode(1);

    expect(SalesOrder::whereNotNull('odoo_missing_at')->count())->toBe(0)
        ->and(SyncLog::latest('id')->first()->status)->toBe('failed');
});

test('the sales sync clears the missing flag when an order is back in scope', function () {
    expect(SyncOdooSalesOrders::SCOPE_DOMAIN)->toBe([['tag_ids', 'in', [2]], ['state', '=', 'sale']])
        ->and(file_get_contents(app_path('Console/Commands/SyncOdooSalesOrders.php')))
        ->toContain("'odoo_missing_at' => null")
        ->toContain("'odoo_missing_at',");
});
