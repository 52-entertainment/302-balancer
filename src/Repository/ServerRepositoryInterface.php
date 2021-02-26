<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Server;

interface ServerRepositoryInterface
{
    /**
     * @return array<Server>
     */
    public function getServers(): array;
    public function addServer(Server $server): void;
    public function removeServer(Server $server): void;
}
