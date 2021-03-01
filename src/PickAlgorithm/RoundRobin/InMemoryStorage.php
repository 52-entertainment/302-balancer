<?php

declare(strict_types=1);

namespace App\PickAlgorithm\RoundRobin;

use App\Model\Server;

final class InMemoryStorage implements RoundRobinStorageInterface
{
    private ?Server $last = null;

    public function getLastServer(): ?Server
    {
        return $this->last;
    }

    public function storeLastServer(Server $server): void
    {
        $this->last = $server;
    }
}
