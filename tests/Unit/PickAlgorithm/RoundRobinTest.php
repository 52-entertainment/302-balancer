<?php

declare(strict_types=1);

namespace App\Tests\Unit\PickAlgorithm;

use App\Model\Server;
use App\PickAlgorithm\RoundRobin;

it('picks a random server', function () {
    $picker = new RoundRobin();
    $servers = [
        'example.com' => new Server(hostname: 'example.com'),
        'example.net' => new Server(hostname: 'example.net'),
        'example.org' => new Server(hostname: 'example.org'),
    ];
    $picked = [];

    for ($i = 0; $i <= 1000; $i++) {
        $picked[] = $picker->pick(...\array_values($servers));
    }

    $counts = [
        'example.com' => 0,
        'example.net' => 0,
        'example.org' => 0,
    ];

    /** @var Server $server */
    foreach ($picked as $server) {
        $counts[$server->hostname]++;
    }

    // Should be ~30% each
    foreach ($servers as $hostname => $server) {
        expect($counts[$hostname])->toBeGreaterThanOrEqual(200)
            ->and($counts[$hostname])->toBeLessThanOrEqual(400);
    }
});