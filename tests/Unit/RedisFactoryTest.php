<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\RedisFactory;
use Clue\React\Redis\Client;
use React\EventLoop\Factory;
use Redis;

use function Clue\React\Block\await;

it('creates a synchronous client', function () {
    $loop = Factory::create();
    $createRedisInstance = new RedisFactory(
        $loop,
        $_SERVER['REDIS_DSN'] ?? throw new \LogicException('REDIS_DSN was not supplied.')
    );
    $client = $createRedisInstance();
    expect($client)->toBeInstanceOf(Redis::class);
    expect($client->isConnected())->toBeTrue();
});


it('creates an asynchronous client', function () {
    $loop = Factory::create();
    $createRedisInstance = new RedisFactory(
        $loop,
        $_SERVER['REDIS_DSN'] ?? throw new \LogicException('REDIS_DSN was not supplied.')
    );
    $client = $createRedisInstance(true);
    expect($client)->toBeInstanceOf(Client::class);
    expect(await($client->ping(), $loop))->toEqual('PONG');
});
