<?php

declare(strict_types=1);

namespace App\PickAlgorithm\RoundRobin;

use App\Model\Server;
use App\Repository\ServerRepositoryInterface;
use Redis;

use function assert;

final class RedisStorage implements RoundRobinStorageInterface
{
    public function __construct(
        private ?Redis $redis = null,
        private string $key = '302-last-server',
    ) {
    }

    public function getLastServer(ServerRepositoryInterface $repository): ?Server
    {
        assert($this->redis instanceof Redis);
        $fingerprint = $this->redis->get($this->key);

        if (false === $fingerprint) {
            return null;
        }

        return Server::fromFingerprint($fingerprint, ...\array_values($repository->getServers()));
    }

    public function storeLastServer(Server $server): void
    {
        assert($this->redis instanceof Redis);
        $this->redis->set($this->key, $server->getFingerprint());
    }

    public function withRedis(Redis $redis): self
    {
        $clone = clone $this;
        $clone->redis = $redis;

        return $clone;
    }
}
