<?php

declare(strict_types=1);

namespace App\Repository;

use App\RedisFactory;

final class ServerRepositoryFactory
{
    public function __construct(
        private RedisFactory $redisFactory,
        private FileRepository $fileRepository,
        private RedisRepository $redisRepository,
    ) {
    }

    public function __invoke(bool $useRedis): ServerRepositoryInterface
    {
        if ($useRedis) {
            $client = ($this->redisFactory)(false);
            $asyncClient = ($this->redisFactory)(true);

            return $this->redisRepository->withRedis($client, $asyncClient);
        }

        return $this->fileRepository;
    }
}
