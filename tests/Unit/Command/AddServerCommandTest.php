<?php

declare(strict_types=1);

namespace App\Tests\Unit\Command;

use App\Command\AddServerCommand;
use App\Model\Server;
use App\Repository\InMemoryRepository;
use Symfony\Component\Console\Tester\CommandTester;

it('adds a server to the repository', function () {
    $repository = new InMemoryRepository();
    $command = new AddServerCommand($repository);
    $tester = new CommandTester($command);
    $status = $tester->execute(['hostname' => 'example.com']);
    expect($status)->toBe($command::SUCCESS);
    expect($repository->getServers())->toHaveCount(1);
    expect($repository->getServers()[0])->toEqual(
        new Server('example.com')
    );
});
it('handles different values for port, scheme, etc', function () {
    $repository = new InMemoryRepository();
    $command = new AddServerCommand($repository);
    $tester = new CommandTester($command);
    $status = $tester->execute([
        'hostname' => 'example.com',
        '--scheme' => 'http',
        '--port' => '8000',
        '--user-info' => 'foo:bar',
    ]);
    expect($status)->toBe($command::SUCCESS);
    expect($repository->getServers())->toHaveCount(1);
    expect($repository->getServers()[0])->toEqual(
        new Server(
            scheme: 'http',
            hostname: 'example.com',
            port: 8000,
            userInfo: 'foo:bar',
        )
    );
});
