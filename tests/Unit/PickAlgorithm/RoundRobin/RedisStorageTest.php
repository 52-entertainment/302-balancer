<?php

declare(strict_types=1);

namespace App\Tests\Unit\PickAlgorithm\RoundRobin;

use App\Model\Server;
use App\PickAlgorithm\RoundRobin\RedisStorage;
use App\RedisFactory;
use App\Repository\InMemoryRepository;
use React\EventLoop\Factory;

use function expect;

it('correctly provides the last server used', function () {
    $loop = Factory::create();
    $createRedisInstance = new RedisFactory(
        $loop,
        $_SERVER['REDIS_DSN'] ?? throw new \LogicException('REDIS_DSN was not supplied.')
    );
    $redis = $createRedisInstance();
    $key = \uniqid('302', true);
    expect($redis->isConnected())->toBeTrue();

    $servers = [
        new Server('example.com'),
        new Server('example.org'),
    ];
    $repository = new InMemoryRepository($servers);
    $storage = new RedisStorage($redis, $key);

    expect($storage->getLastServer($repository))->toBeNull();

    $storage->storeLastServer($servers[0]);
    expect($storage->getLastServer($repository))->toBe($servers[0]);

    $storage->storeLastServer($servers[1]);
    expect($storage->getLastServer($repository))->toBe($servers[1]);
});

it('returns null when last server is not in the repository', function () {
    $loop = Factory::create();
    $createRedisInstance = new RedisFactory(
        $loop,
        $_SERVER['REDIS_DSN'] ?? throw new \LogicException('REDIS_DSN was not supplied.')
    );
    $redis = $createRedisInstance();
    $key = \uniqid('302', true);
    expect($redis->isConnected())->toBeTrue();

    $servers = [
        new Server('example.com'),
        new Server('example.org'),
    ];
    $repository = new InMemoryRepository($servers);
    $storage = new RedisStorage($redis, $key);

    $storage->storeLastServer($servers[1]);
    $repository->removeServer($servers[1]);
    expect($storage->getLastServer($repository))->toBeNull();
});
