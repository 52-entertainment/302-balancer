<?php

declare(strict_types=1);

namespace App\Model;

final class Server implements \JsonSerializable
{
    public string $scheme;
    private ?string $fingerprint = null;

    public function __construct(
        public string $hostname,
        ?string $scheme = null,
        public ?string $userInfo = null,
        public ?int $port = null,
    ) {
        $this->scheme = $scheme ?? 'https';
    }

    public function getFingerprint(): ?string
    {
        return $this->fingerprint ??= \md5(\json_encode($this));
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'scheme' => $this->scheme,
            'userInfo' => $this->userInfo,
            'hostname' => $this->hostname,
            'port' => $this->port,
        ];
    }

    /**
     * @param array<mixed> $server
     * @return static
     */
    public static function fromArray(array $server): self
    {
        return new self(
            scheme: $server['scheme'] ?? null,
            userInfo: $server['userInfo'] ?? null,
            hostname: $server['hostname'] ?? throw new \InvalidArgumentException('Missing hostname.'),
            port: $server['port'] ?? null,
        );
    }

    public static function fromFingerprint(string $fingerprint, Server ...$servers): ?Server
    {
        foreach ($servers as $server) {
            if ($server->getFingerprint() === $fingerprint) {
                return $server;
            }
        }

        return null;
    }
}
