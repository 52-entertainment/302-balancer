<?php

declare(strict_types=1);

namespace App\PickAlgorithm;

use App\Model\Server;
use App\PickAlgorithm\RoundRobin\InMemoryStorage;
use App\PickAlgorithm\RoundRobin\RoundRobinStorageInterface;
use App\Repository\ServerRepositoryInterface;

final class RoundRobin implements PickAlgorithmInterface
{
    private RoundRobinStorageInterface $storage;

    public function __construct(
        ?RoundRobinStorageInterface $storage = null,
    ) {
        $this->storage = $storage ?? new InMemoryStorage();
    }


    public function pick(ServerRepositoryInterface $repository): Server
    {
        $servers = \array_values($repository->getServers());

        if (0 === \count($servers)) {
            throw new \RuntimeException('Nore more servers in the pool!');
        }

        $lastServer = $this->storage->getLastServer();
        if (null === $lastServer) {
            $this->storage->storeLastServer($servers[0]);

            return $servers[0];
        }

        if (false === ($i = \array_search($lastServer, $servers, true))) {
            $this->storage->storeLastServer($servers[0]);

            return $servers[0];
        }

        $currentServer = $servers[$i + 1] ?? $servers[0];
        $this->storage->storeLastServer($currentServer);

        return $currentServer;
    }

    public static function getName(): string
    {
        return 'round-robin';
    }
}
