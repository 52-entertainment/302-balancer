<?php

declare(strict_types=1);

namespace App\Model;

final class Server
{
    public string $scheme;

    public function __construct(
        public string $hostname,
        ?string $scheme = null,
        public ?string $userInfo = null,
        public ?int $port = null,
    ) {
        $this->scheme = $scheme ?? 'https';
    }
}
