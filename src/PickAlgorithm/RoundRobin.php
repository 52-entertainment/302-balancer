<?php

declare(strict_types=1);

namespace App\PickAlgorithm;

use App\Model\Server;
use App\Repository\ServerRepositoryInterface;

final class RoundRobin implements PickAlgorithmInterface
{
    private ?Server $last = null;

    public function pick(ServerRepositoryInterface $repository): Server
    {
        $servers = $repository->getServers();

        if (0 === \count($servers)) {
            throw new \RuntimeException('Nore more servers in the pool!');
        }

        if (null === $this->last) {
            return $this->last = ($servers[0] ?? throw new \RuntimeException('No more servers in the pool!'));
        }

        $current = \array_search($this->last, $servers, true);
        if (false === $current) {
            return $this->last = ($servers[0] ?? throw new \RuntimeException('No more servers in the pool!'));
        }

        return $this->last = $servers[$current + 1]
            ?? $servers[0]
            ?? throw new \RuntimeException('No more servers in the pool!');
    }

    public static function getName(): string
    {
        return 'round-robin';
    }
}
