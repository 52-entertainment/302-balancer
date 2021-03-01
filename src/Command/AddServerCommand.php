<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Server;
use App\Repository\ServerRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function App\cast_if_not_null;
use function App\nullify;

final class AddServerCommand extends Command
{
    protected static $defaultName = 'server:add';

    public function __construct(
        private ServerRepositoryInterface $repository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('hostname', InputArgument::REQUIRED);
        $this->addOption('scheme', null, InputOption::VALUE_OPTIONAL);
        $this->addOption('user-info', null, InputOption::VALUE_OPTIONAL);
        $this->addOption('port', null, InputOption::VALUE_OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $server = new Server(
            scheme: nullify($input->getOption('scheme')),
            userInfo: nullify($input->getOption('user-info')),
            hostname: nullify($input->getArgument('hostname')),
            port: cast_if_not_null($input->getOption('port'), 'int'),
        );

        $this->repository->addServer($server);
        $io->success('Done.');

        return self::SUCCESS;
    }
}
