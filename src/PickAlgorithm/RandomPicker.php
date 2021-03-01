<?php

declare(strict_types=1);

namespace App\PickAlgorithm;

use App\Model\Server;
use App\Repository\ServerRepositoryInterface;

final class RandomPicker implements PickAlgorithmInterface
{
    public function pick(ServerRepositoryInterface $repository): Server
    {
        $servers = $repository->getServers();
        if (0 === \count($servers)) {
            throw new \RuntimeException('Nore more servers in the pool!');
        }
        $random = \random_int(0, \count($servers) - 1);

        return $servers[$random];
    }

    public static function getName(): string
    {
        return 'random';
    }
}
