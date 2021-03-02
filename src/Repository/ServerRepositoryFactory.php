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
        return $useRedis ? $this->redisRepository->withRedis(($this->redisFactory)()) : $this->fileRepository;
    }
}
