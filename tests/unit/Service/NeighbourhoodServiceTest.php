<?php

namespace unit\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods\Exceptions\NeighbourhoodsNotFoundException;
use RoadSigns\LaravelPoliceUK\Service\NeighbourhoodService;

class NeighbourhoodServiceTest extends TestCase
{
    /** @test */
    public function throwsExceptionWhenNeighbourhoodsNotFound(): void
    {
        $this->expectException(NeighbourhoodsNotFoundException::class);
        $this->expectExceptionMessage('unable to find neighbourhoods with id of foo');

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/foo/neighbourhoods')
            ->willThrowException($this->createMock(GuzzleException::class));

        $service = new NeighbourhoodService($client);
        $service->byForceId('foo');
    }

    /** @test */
    public function throwsExceptionWhenUnableToParseNeighbourInformation(): void
    {
        $this->expectException();
    }
}