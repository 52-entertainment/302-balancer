<?php

declare(strict_types=1);

namespace App\PickAlgorithm\RoundRobin;

use App\RedisFactory;

final class RoundRobinStorageFactory
{
    public function __construct(
        private RedisFactory $redisFactory,
        private RedisStorage $redisStorage,
        private InMemoryStorage $inMemoryStorage,
    ) {
    }

    public function __invoke(bool $useRedis): RoundRobinStorageInterface
    {
        return $useRedis ? $this->redisStorage->withRedis(($this->redisFactory)()) : $this->inMemoryStorage;
    }
}
