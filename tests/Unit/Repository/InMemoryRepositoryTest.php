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
