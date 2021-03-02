<?php

declare(strict_types=1);

namespace App;

use Clue\React\Redis\Factory as AsyncRedisFactory;
use Clue\React\Redis\Client as AsyncRedis;
use Clue\React\Redis\Client;
use Nyholm\Dsn\DsnParser;
use React\EventLoop\LoopInterface;
use Redis;

use function assert;

final class RedisFactory
{
    private AsyncRedisFactory $factory;

    public function __construct(
        private LoopInterface $loop,
        private ?string $dsn = null
    ) {
        $this->factory = new AsyncRedisFactory($this->loop);
    }

    public function __invoke(bool $async = false): Redis | AsyncRedis
    {
        assert(null !== $this->dsn);

        return $async ? $this->createAsyncClient() : $this->createClient();
    }

    private function createClient(): Redis
    {
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

    private function createAsyncClient(): AsyncRedis
    {
        return $this->factory->createLazyClient($this->dsn);
    }
}
