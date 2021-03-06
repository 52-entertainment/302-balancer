<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Server;
use App\PickAlgorithm\RoundRobin;
use App\Repository\InMemoryRepository;
use App\Repository\ServerRepositoryInterface;
use App\RequestHandler;
use Psr\Http\Message\UriInterface;
use React\EventLoop\LoopInterface;
use React\Http\Server as HttpServer;
use React\Socket\Server as SocketServer;
use Redis;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ServiceLocator;

use function App\draw_servers_table;
use function App\nullify;
use function BenTools\UriFactory\Helper\uri;

final class ServeCommand extends Command
{
    protected static $defaultName = 'serve';

    public function __construct(
        private LoopInterface $loop,
        private RequestHandler $requestHandler,
        private ServiceLocator $pickingMethods,
        private Redis $redis,
        private string $host,
        private int $port,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('hosts', InputArgument::IS_ARRAY, 'List of hosts to redirect to.');
        $this->addOption('host', null, InputOption::VALUE_OPTIONAL, 'Host to serve this app from.', $this->host);
        $this->addOption('port', null, InputOption::VALUE_OPTIONAL, 'Port to serve this app from.', $this->port); // @phpstan-ignore-line
        $this->addOption('pick', null, InputOption::VALUE_OPTIONAL, 'Picking method.', RoundRobin::getName());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dsn = \sprintf('%s:%s', $input->getOption('host'), $input->getOption('port'));
        $this->requestHandler->algorithm = $this->pickingMethods->get($input->getOption('pick'));
        $this->requestHandler->serverRepository = $this->getServerRepository($input);
        $server = new HttpServer($this->loop, $this->requestHandler);
        $server->listen(new SocketServer($dsn, $this->loop));
        $this->loop->futureTick(
            function () use ($io, $dsn) {
                $io->success(\sprintf('Server running at http://%s', $dsn));
                draw_servers_table($io, ...$this->requestHandler->serverRepository->getServers());
            }
        );

        $this->loop->addSignal(
            \SIGINT,
            function () use ($io) {
                $io->comment('Graceful shutdown requested. ????');
                $io->info(
                    \sprintf(
                        'Memory usage: %dMB / Peak: %dMB',
                        \round(\memory_get_usage(true) / 1024 / 1024),
                        \round(\memory_get_peak_usage(true) / 1024 / 1024),
                    )
                );
                $this->loop->stop();
            }
        );

        $this->loop->run();

        return self::SUCCESS;
    }

    private function getServerRepository(InputInterface $input): ServerRepositoryInterface
    {
        if ([] === ($hosts = $input->getArgument('hosts'))) {
            return $this->requestHandler->serverRepository;
        }

        $uris = \array_map(
            static function (string $host): UriInterface {
                if (false === \str_starts_with($host, 'http://') && false === \str_starts_with($host, 'https://')) {
                    return uri('https://' . $host);
                }

                return uri($host);
            },
            $hosts
        );
        $servers = \array_map(
            static fn(UriInterface $uri) => new Server(
                scheme: nullify($uri->getScheme()),
                userInfo: nullify($uri->getUserInfo()),
                hostname: $uri->getHost(),
                port: nullify($uri->getPort())
            ),
            $uris
        );

        return new InMemoryRepository($servers);
    }
}
