<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use App\Model\Server;
use App\Repository\InMemoryRepository;

it('inits a repository', function (array $servers) {
    $repository = new InMemoryRepository($servers);
    expect($repository->getServers())->toBe($servers ?? []);
})->with(function () {
    yield [[]];
    yield [[
        new Server('http', 'example.org'),
        new Server('http', 'example.com'),
    ]];
});

it('adds a server', function () {
    $server = new Server('example.com');
    $repository = new InMemoryRepository();
    $repository->addServer($server);
    expect($repository->getServers())->toEqual([$server]);
});

it('removes a server', function () {
    $repository = new InMemoryRepository([new Server('example.com')]);
    $repository->removeServer(new Server('example.org'));
    expect($repository->getServers())->toHaveCount(1);
    $repository->removeServer(new Server('example.com'));
    expect($repository->getServers())->toHaveCount(0);
});
