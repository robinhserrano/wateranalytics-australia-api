<?php

use App\Console\Commands\SyncOdooSalesOrders;

function resolvePaymentStatus(object $order): ?string
{
    $command = new SyncOdooSalesOrders();
    $method = new ReflectionMethod($command, 'resolvePaymentStatus');
    $method->setAccessible(true);

    return $method->invoke($command, $order);
}

test('resolvePaymentStatus returns the custom studio field when meaningfully set', function () {
    expect(resolvePaymentStatus((object) ['x_studio_invoice_payment_status' => 'paid']))->toBe('paid');
});

test('resolvePaymentStatus treats boolean false as not set', function () {
    expect(resolvePaymentStatus((object) ['x_studio_invoice_payment_status' => false]))->toBeNull();
});

test('resolvePaymentStatus treats empty string as not set', function () {
    expect(resolvePaymentStatus((object) ['x_studio_invoice_payment_status' => '']))->toBeNull();
});

test('resolvePaymentStatus treats the literal string "false" as not set', function () {
    expect(resolvePaymentStatus((object) ['x_studio_invoice_payment_status' => 'false']))->toBeNull();
});

test('resolvePaymentStatus returns null when the field is missing entirely', function () {
    expect(resolvePaymentStatus((object) []))->toBeNull();
});
