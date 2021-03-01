<?php

declare(strict_types=1);

namespace App\PickAlgorithm;

use App\Model\Server;
use App\Repository\ServerRepositoryInterface;

interface PickAlgorithmInterface
{
    public function pick(ServerRepositoryInterface $repository): Server;

    public static function getName(): string;
}
