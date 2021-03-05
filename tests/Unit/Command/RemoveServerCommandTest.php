<?php

declare(strict_types=1);

namespace App\Tests\Unit\Command;

use App\Command\RemoveServerCommand;
use App\Model\Server;
use App\Repository\InMemoryRepository;
use Symfony\Component\Console\Tester\CommandTester;

it('removes a server from the repository', function () {
    $repository = new InMemoryRepository([new Server('example.com')]);
    $command = new RemoveServerCommand($repository);
    $tester = new CommandTester($command);
    $status = $tester->execute(['hostname' => 'example.com']);
    expect($status)->toBe($command::SUCCESS);
    expect($repository->getServers())->toHaveCount(0);
});

it('handles different values for port, scheme, etc', function () {
    $repository = new InMemoryRepository([
        new Server(
            scheme: 'http',
            hostname: 'example.com',
            port: 8000,
            userInfo: 'foo:bar',
        ),
    ]);
    $command = new RemoveServerCommand($repository);
    $tester = new CommandTester($command);
    $status = $tester->execute([
        'hostname' => 'example.com',
        '--scheme' => 'http',
        '--port' => '8000',
        '--user-info' => 'foo:bar',
    ]);
    expect($status)->toBe($command::SUCCESS);
    expect($repository->getServers())->toHaveCount(0);
});
