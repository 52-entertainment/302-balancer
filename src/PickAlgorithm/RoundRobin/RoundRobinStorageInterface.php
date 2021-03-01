<?php

declare(strict_types=1);

namespace App\PickAlgorithm\RoundRobin;

use App\Model\Server;

interface RoundRobinStorageInterface
{
    public function getLastServer(): ?Server;
    public function storeLastServer(Server $server): void;
}
