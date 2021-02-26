<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Server;

final class InMemoryRepository implements ServerRepositoryInterface
{
    /**
     * @var array<Server>
     */
    private array $servers;

    /**
     * @param array<Server> $servers
     */
    public function __construct(array $servers = [])
    {
        $this->servers = (static fn(Server ...$servers) => $servers)(...$servers);
    }

    public function addServer(Server $server): void
    {
        $this->servers[] = $server;
    }

    public function removeServer(Server $serverToRemove): void
    {
        $this->servers = \array_values(
            \array_filter(
                $this->servers,
                static fn (Server $server) => $server !== $serverToRemove
            )
        );
    }

    public function getServers(): array
    {
        return $this->servers;
    }
}
