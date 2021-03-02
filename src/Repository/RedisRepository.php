<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Server;
use Redis;

use function assert;

final class RedisRepository implements ServerRepositoryInterface
{

    public function __construct(
        private ?Redis $redis = null,
        private string $key = '302-servers',
    ) {
    }

    public function getServers(): array
    {
        assert($this->redis instanceof Redis);

        return \array_map(
            fn(array $server) => Server::fromArray($server),
            \json_decode(
                json: $this->redis->get($this->key),
                associative: true,
                flags: \JSON_THROW_ON_ERROR,
            )
        );
    }

    public function addServer(Server $server): void
    {
        assert($this->redis instanceof Redis);

        $servers = $this->getServers();
        $servers[] = $server;

        $this->redis->set($this->key, $servers);
    }

    public function removeServer(Server $server): void
    {
        assert($this->redis instanceof Redis);

        $repo = new InMemoryRepository($this->getServers());
        $repo->removeServer($server);

        $this->redis->set($this->key, $repo->getServers());
    }

    public function withRedis(Redis $redis): self
    {
        $clone = clone $this;
        $clone->redis = $redis;

        return $clone;
    }
}
