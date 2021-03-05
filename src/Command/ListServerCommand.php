<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\ServerRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function App\draw_servers_table;

final class ListServerCommand extends Command
{
    protected static $defaultName = 'server:list';

    public function __construct(
        private ServerRepositoryInterface $repository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        draw_servers_table($io, ...$this->repository->getServers());

        return self::SUCCESS;
    }
}
