<?php

declare(strict_types=1);

namespace App\PickAlgorithm\RoundRobin;

use App\Model\Server;
use App\Repository\ServerRepositoryInterface;

interface RoundRobinStorageInterface
{
    public function getLastServer(ServerRepositoryInterface $repository): ?Server;
    public function storeLastServer(Server $server): void;
}
