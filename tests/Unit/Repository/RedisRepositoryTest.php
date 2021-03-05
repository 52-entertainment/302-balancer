<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use App\Model\Server;
use App\RedisFactory;
use App\Repository\RedisRepository;
use React\EventLoop\Factory;

it('adds and removes servers', function () {
    $loop = Factory::create();
    $createRedisInstance = new RedisFactory(
        $loop,
        $_SERVER['REDIS_DSN'] ?? throw new \LogicException('REDIS_DSN was not supplied.')
    );
    $redis = $createRedisInstance();
    $asyncRedis = $createRedisInstance(true);
    $key = \uniqid('302', true);
    expect($redis->isConnected())->toBeTrue();

    $server = new Server('example.com');
    $repository = new RedisRepository($loop, $redis, $asyncRedis, $key);
    $repository->addServer($server);
    expect($repository->getServers())->toHaveCount(1);

    $repository->removeServer(new Server('example.org'));
    expect($repository->getServers())->toHaveCount(1);

    $repository->removeServer(new Server('example.com'));
    expect($repository->getServers())->toHaveCount(0);
});
