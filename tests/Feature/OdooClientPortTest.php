<?php

use App\Console\Commands\SyncStocks;
use App\Models\ProductStock;
use App\Models\SyncLog;
use App\Models\Warehouse;
use App\Services\OdooClient;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

// The sync commands moved from obuchmann/odoo-jsonrpc to App\Services\OdooClient (the same client as
// waa_filter_subscriptions). These pin the two things that could silently change: the Odoo calls the
// rewritten query-builder sites send, and the object-shaped results the commands were written against.

beforeEach(function () {
    config(['odoo.url' => 'https://odoo.test', 'odoo.db' => 'waa', 'odoo.login' => 'bot', 'odoo.api_key' => 'pw']);
    Sleep::fake();
});

function syncStocksCommand(): SyncStocks
{
    $command = new SyncStocks;
    $command->setOutput(new OutputStyle(new ArrayInput([]), new NullOutput));

    return $command;
}

function callProtected(object $obj, string $method, ...$args): mixed
{
    $m = new ReflectionMethod($obj, $method);
    $m->setAccessible(true);

    return $m->invoke($obj, ...$args);
}

test('config maps the existing ODOO_* settings onto the shared client and asks for objects', function () {
    $config = require base_path('config/odoo.php');

    expect($config['as_objects'])->toBeTrue()
        ->and(file_get_contents(base_path('config/odoo.php')))
        ->toContain("'url' => env('ODOO_HOST'")
        ->toContain("'db' => env('ODOO_DATABASE'")
        ->toContain("'login' => env('ODOO_USERNAME'")
        ->toContain("'api_key' => env('ODOO_PASSWORD'");
});

test('the stock activity check sends the same search_count the query builder did', function () {
    Http::fake(['odoo.test/*' => Http::sequence()->push(['result' => 7])->push(['result' => 2])]);

    $active = callProtected(syncStocksCommand(), 'hasQuantActivitySince', app(OdooClient::class),
        new Warehouse(['odoo_id' => 3, 'name' => 'Sydney']), Carbon::parse('2026-10-16 08:00:00'));

    expect($active)->toBeTrue();
    Http::assertSent(fn ($r) => ($r['params']['args'][3] ?? null) === 'stock.quant'
        && $r['params']['args'][4] === 'search_count'
        && $r['params']['args'][5] === [[['warehouse_id', '=', 3], ['write_date', '>', '2026-10-16 08:00:00']]]);
});

test('warehouses sync from object-shaped results, as they did with obuchmann', function () {
    config(['odoo.as_objects' => true]);
    Http::fake(['odoo.test/*' => Http::sequence()->push(['result' => 7])->push(['result' => [
        ['id' => 3, 'name' => 'Sydney', 'code' => 'SYD'],
        ['id' => 4, 'name' => 'Perth', 'code' => false],
    ]])]);

    $warehouses = callProtected(syncStocksCommand(), 'syncWarehouses', app(OdooClient::class));

    expect($warehouses)->toHaveCount(2)
        ->and(Warehouse::where('odoo_id', 3)->value('name'))->toBe('Sydney');
    Http::assertSent(fn ($r) => ($r['params']['args'][4] ?? null) === 'search_read'
        && $r['params']['args'][3] === 'stock.warehouse'
        && $r['params']['args'][6]['fields'] === ['id', 'name', 'code']);
});

test('odoo:sync-stocks runs end to end and asks Odoo for per-warehouse quantities via the call context', function () {
    // obuchmann held the warehouse context as client state (getContext/setContext); the shared client has no
    // such methods, which crashed every stocks run after #46. Run the real command against a fake Odoo.
    Http::fake(['odoo.test/*' => function ($request) {
        $args = $request['params']['args'];
        $result = match (true) {
            $request['params']['service'] === 'common' => 7,
            ($args[4] ?? null) === 'search_read' && $args[3] === 'stock.warehouse' => [['id' => 3, 'name' => 'Sydney', 'code' => 'SYD']],
            ($args[4] ?? null) === 'search_count' => 1,
            ($args[4] ?? null) === 'web_search_read' => ['length' => 1, 'records' => $args[5][2] === 0 ? [[
                'id' => 501, 'display_name' => 'Filter Cartridge', 'categ_id' => ['id' => 4, 'display_name' => 'Filters'],
                'cost_method' => 'standard', 'standard_price' => 10, 'qty_available' => 7, 'free_qty' => 7,
                'incoming_qty' => 0, 'outgoing_qty' => 0, 'virtual_available' => 7,
            ]] : []],
            default => [],
        };

        return Http::response(['result' => $result]);
    }]);

    $this->artisan('odoo:sync-stocks')->assertExitCode(0);

    $warehouse = Warehouse::where('odoo_id', 3)->firstOrFail();
    expect(ProductStock::where('warehouse_id', $warehouse->id)->where('odoo_id', 501)->exists())->toBeTrue()
        ->and(SyncLog::latest('id')->first()->status)->toBe('completed');
    Http::assertSent(fn ($r) => ($r['params']['args'][4] ?? null) === 'web_search_read'
        && ($r['params']['args'][6]['context']['warehouse'] ?? null) === 3);
});

test('the removed debug and Odoo test routes are gone', function (string $url) {
    $this->get($url)->assertNotFound();
})->with(['/odoo-test', '/odoo-sales', '/debug-odoo', '/debug-commission-user/1', '/debug-order-lines/1']);
