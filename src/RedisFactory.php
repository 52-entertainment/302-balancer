<?php

declare(strict_types=1);

namespace App;

use Nyholm\Dsn\DsnParser;
use Redis;

use function assert;

final class RedisFactory
{
    public function __construct(
        private ?string $dsn = null
    ) {
    }

    public function __invoke(): Redis
    {
        assert(null !== $this->dsn);
        $dsn = DsnParser::parse($this->dsn);
        $redis = new Redis();
        $redis->connect(
            $dsn->getHost(),
            $dsn->getPort() ?? 6379,
        );
        if (null !== $dsn->getPassword()) {
            $redis->auth($dsn->getPassword());
        }

        return $redis;
    }
}
