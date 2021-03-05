<?php

declare(strict_types=1);

namespace App\Tests\Unit\PickAlgorithm\RoundRobin;

use App\Model\Server;
use App\PickAlgorithm\RoundRobin\InMemoryStorage;
use App\Repository\InMemoryRepository;

use function expect;

it('correctly provides the last server used', function () {
    $servers = [
        new Server('example.com'),
        new Server('example.org'),
    ];
    $repository = new InMemoryRepository($servers);

    $storage = new InMemoryStorage();
    expect($storage->getLastServer($repository))->toBeNull();

    $storage->storeLastServer($servers[0]);
    expect($storage->getLastServer($repository))->toBe($servers[0]);

    $storage->storeLastServer($servers[1]);
    expect($storage->getLastServer($repository))->toBe($servers[1]);
});

it('returns null when last server is not in the repository', function () {
    $servers = [
        new Server('example.com'),
        new Server('example.org'),
    ];
    $repository = new InMemoryRepository($servers);

    $storage = new InMemoryStorage();

    $storage->storeLastServer($servers[1]);
    $repository->removeServer($servers[1]);
    expect($storage->getLastServer($repository))->toBeNull();
});
