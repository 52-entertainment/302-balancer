<?php

declare(strict_types=1);

namespace App;

use App\PickAlgorithm\PickAlgorithmInterface;
use App\Repository\ServerRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;

final class RequestHandler
{
    public ServerRepositoryInterface $serverRepository;
    public PickAlgorithmInterface $algorithm;

    public function __construct(ServerRepositoryInterface $serverRepository, PickAlgorithmInterface $algorithm)
    {
        $this->serverRepository = $serverRepository;
        $this->algorithm = $algorithm;
    }

    public function __invoke(ServerRequestInterface $serverRequest): ResponseInterface
    {
        $servers = $this->serverRepository->getServers();
        if (empty($servers)) {
            return new Response(503, [], 'No back-end server was able to handle the request.');
        }

        $server = $this->algorithm->pick(...$servers);

        $location = (string) $serverRequest->getUri()
            ->withScheme($server->scheme)
            ->withUserInfo($server->userInfo)
            ->withHost($server->hostname)
            ->withPort($server->port)
        ;

        return new Response(302, ['Location' => $location]);
    }
}
