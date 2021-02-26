<?php

declare(strict_types=1);

namespace App\PickAlgorithm;

use App\Model\Server;

final class RandomPicker implements PickAlgorithmInterface
{
    public function pick(Server ...$servers): Server
    {
        $random = \random_int(0, \count($servers) - 1);

        return $servers[$random];
    }

    public static function getName(): string
    {
        return 'random';
    }
}
