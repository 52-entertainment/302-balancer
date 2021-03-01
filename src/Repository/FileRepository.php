<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Server;
use Flowcontrol\React\Inotify\InotifyStream;
use React\EventLoop\LoopInterface;
use Symfony\Component\Filesystem\Filesystem;

final class FileRepository implements ServerRepositoryInterface
{
    private InMemoryRepository $repository;

    /**
     * @var resource|null
     */
    private $fileDescriptor;
    private int $watchDescriptor;

    public function __construct(
        private Filesystem $filesystem,
        private LoopInterface $loop,
        private string $file,
        private bool $reloadOnChange = true,
    ) {
        $this->repository = new InMemoryRepository();
        $this->read();
        $this->watch();
    }

    public function getServers(): array
    {
        return $this->repository->getServers();
    }

    public function addServer(Server $server): void
    {
        $this->repository->addServer($server);
        $this->write(...$this->repository->getServers());
    }

    public function removeServer(Server $server): void
    {
        $this->repository->removeServer($server);
        $this->write(...$this->repository->getServers());
    }

    public function __destruct()
    {
        if (\is_resource($this->fileDescriptor)) {
            \inotify_rm_watch($this->fileDescriptor, $this->watchDescriptor);
            \fclose($this->fileDescriptor);
        }
    }

    private function watch(): void
    {
        if (false === $this->reloadOnChange) {
            return;
        }

        if (false === \extension_loaded('inotify')) {
            return;
        }

        $this->fileDescriptor = \inotify_init();
        $this->watchDescriptor = \inotify_add_watch($this->fileDescriptor, $this->file, \IN_MODIFY);

        $watcher = new InotifyStream($this->fileDescriptor, $this->loop);
        $watcher->on('event', fn() => $this->read());
    }

    private function read(): void
    {
        \touch($this->file);
        if (false === \is_readable($this->file)) {
            throw new \RuntimeException('Unable to read server file.');
        }

        $content = \file_get_contents($this->file);
        if (empty($content)) {
            return;
        }

        $servers = \array_map(
            fn(array $server) => Server::fromArray($server),
            \json_decode(
                json: $content,
                associative: true,
                flags: \JSON_THROW_ON_ERROR
            )
        );

        $this->repository = new InMemoryRepository($servers);
    }

    private function write(Server ...$servers): void
    {
        \touch($this->file);
        if (false === \is_writable($this->file)) {
            throw new \RuntimeException('Unable to write server file.');
        }

        $this->filesystem->dumpFile(
            $this->file,
            \json_encode($servers, \JSON_PRETTY_PRINT | \JSON_THROW_ON_ERROR)
        );
    }
}
