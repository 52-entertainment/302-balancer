<?php

declare(strict_types=1);

namespace App\PickAlgorithm;

use App\Model\Server;

interface PickAlgorithmInterface
{
    public function pick(Server ...$servers): Server;

    public static function getName(): string;
}
