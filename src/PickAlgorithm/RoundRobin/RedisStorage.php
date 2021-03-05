<?php

declare(strict_types=1);

namespace App\PickAlgorithm\RoundRobin;

use App\Model\Server;
use App\Repository\ServerRepositoryInterface;
use Redis;

final class RedisStorage implements RoundRobinStorageInterface
{
    public function __construct(
        private Redis $redis,
        private string $key = '302-last-server',
    ) {
    }

    public function getLastServer(ServerRepositoryInterface $repository): ?Server
    {
        $fingerprint = $this->redis->get($this->key);

        if (false === $fingerprint) {
            return null;
        }

        return Server::fromFingerprint($fingerprint, ...\array_values($repository->getServers()));
    }

    public function storeLastServer(Server $server): void
    {
        $this->redis->set($this->key, $server->getFingerprint());
    }
}
