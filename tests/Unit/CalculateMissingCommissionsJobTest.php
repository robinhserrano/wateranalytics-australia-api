<?php

use App\Jobs\CalculateMissingCommissionsJob;

test('runs commissions:calculate-missing with a bounded limit and the status-based gate', function () {
    $job = new CalculateMissingCommissionsJob(1);

    $command = new ReflectionMethod($job, 'command');
    $command->setAccessible(true);
    $options = new ReflectionMethod($job, 'options');
    $options->setAccessible(true);

    expect($command->invoke($job))->toBe('commissions:calculate-missing')
        ->and($options->invoke($job))->toBe([
            '--limit' => 200,
            '--update-unconfirmed' => true,
        ]);
});
