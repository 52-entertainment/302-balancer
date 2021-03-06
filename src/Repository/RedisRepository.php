<?php

declare(strict_types=1);

namespace App\Repository;

use Clue\React\Redis\Client as AsyncRedis;
use App\Model\Server;
use React\EventLoop\LoopInterface;
use Redis;

final class RedisRepository implements ServerRepositoryInterface
{
    private const NOTIFY_CHANNEL = 'servers_changed';
    private ?InMemoryRepository $repository = null;

    public function __construct(
        private LoopInterface $loop,
        private Redis $redis,
        private AsyncRedis $asyncRedis,
        private string $key = '302-servers',
    ) {
        $this->watch();
    }

    public function getServers(): array
    {
        $this->repository ??= new InMemoryRepository($this->fetchServers());
        return $this->repository->getServers();
    }

    public function addServer(Server $server): void
    {
        $this->getServers();
        $this->repository->addServer($server);

        $payload = \json_encode($this->repository->getServers());
        $this->redis->set($this->key, $payload);
        $this->redis->publish(self::NOTIFY_CHANNEL, $payload);
    }

    public function removeServer(Server $server): void
    {
        $this->getServers();
        $this->repository->removeServer($server);

        $payload = \json_encode($this->repository->getServers());
        $this->redis->set($this->key, $payload);
        $this->redis->publish(self::NOTIFY_CHANNEL, $payload);
    }

    private function watch(): void
    {
        $this->asyncRedis->subscribe(self::NOTIFY_CHANNEL); // @phpstan-ignore-line
        $this->asyncRedis->on('message', function (string $channel, $payload) {
            if (self::NOTIFY_CHANNEL !== $channel) {
                return;
            }
            $this->repository = new InMemoryRepository($this->decodePayload($payload));
        });
    }

    /**
     * @return array<Server>
     */
    private function fetchServers(): array
    {
        $payload = $this->redis->get($this->key);

        return $this->decodePayload($payload);
    }

    /**
     * @return array<Server>
     */
    private function decodePayload(mixed $payload): array
    {
        if (empty($payload)) {
            return [];
        }

        return \array_map(
            fn(array $server) => Server::fromArray($server),
            \json_decode(
                json: $payload,
                associative: true,
                flags: \JSON_THROW_ON_ERROR,
            )
        );
    }
}
