<?php

declare(strict_types=1);

namespace App\PickAlgorithm\RoundRobin;

use App\Model\Server;
use App\Repository\ServerRepositoryInterface;

final class InMemoryStorage implements RoundRobinStorageInterface
{
    private ?Server $last = null;

    public function getLastServer(ServerRepositoryInterface $repository): ?Server
    {
        if (null === $this->last) {
            return null;
        }

        return Server::fromFingerprint($this->last->getFingerprint(), ...$repository->getServers());
    }

    public function storeLastServer(Server $server): void
    {
        $this->last = $server;
    }
}
