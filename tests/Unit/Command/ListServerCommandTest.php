<?php

declare(strict_types=1);

namespace App\Tests\Unit\Command;

use App\Command\ListServerCommand;
use App\Model\Server;
use App\Repository\InMemoryRepository;
use Symfony\Component\Console\Tester\CommandTester;

it('lists servers', function () {
    $repository = new InMemoryRepository(
        [
            new Server('example.com'),
            new Server(
                scheme: 'http',
                userInfo: 'foo:bar',
                hostname: 'example.org',
                port: 8000,
            ),
        ]
    );
    $command = new ListServerCommand($repository);
    $tester = new CommandTester($command);
    $status = $tester->execute([]);
    expect($status)->toBe($command::SUCCESS);
    expect(trim($tester->getDisplay(true)))->toBe(
        trim(
            <<<OUTPUT
 -------- ------------- ------ 
  Scheme   Hostname      Port  
 -------- ------------- ------ 
  https    example.com         
  http     example.org   8000  
 -------- ------------- ------
OUTPUT
        )
    );
});
