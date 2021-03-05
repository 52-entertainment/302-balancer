<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Model\Server;
use App\PickAlgorithm\RandomPicker;
use App\Repository\InMemoryRepository;
use App\RequestHandler;
use RingCentral\Psr7\ServerRequest;

it('yells a 503 response when there is no back-end server', function () {
    $repository = new InMemoryRepository();
    $pick = new RandomPicker();
    $handle = new RequestHandler($repository, $pick);
    $response = $handle(new ServerRequest('GET', '/'));
    expect($response->getStatusCode())->toBe(503);
    expect($response->getHeaderLine('Location'))->toBeEmpty();
});

it('returns a 302 response with one of the back-end servers otherwise', function () {
    $repository = new InMemoryRepository([
        new Server('example.org'),
        new Server('example.com'),
    ]);
    $pick = new RandomPicker();
    $handle = new RequestHandler($repository, $pick);
    $response = $handle(new ServerRequest('GET', '/'));
    expect($response->getStatusCode())->toBe(302);
    expect($response->getHeaderLine('Location'))->not()->toBeNull();

    $locations = [];
    for ($i = 0; $i <= 10; $i++) {
        $response = $handle(new ServerRequest('GET', '/'));
        $locations[] = $response->getHeaderLine('Location');
    }

    expect($locations)->toContain('https://example.org/');
    expect($locations)->toContain('https://example.com/');
});

it('keeps path and query string untouched', function () {
    $repository = new InMemoryRepository([
        new Server('example.org'),
    ]);
    $pick = new RandomPicker();
    $handle = new RequestHandler($repository, $pick);
    $response = $handle(new ServerRequest('GET', '/foo?bar=baz'));
    expect($response->getHeaderLine('Location'))->toBe('https://example.org/foo?bar=baz');
});
