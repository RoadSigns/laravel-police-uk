<?php

namespace unit\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods\Exceptions\NeighbourhoodServiceException;
use RoadSigns\LaravelPoliceUK\Service\NeighbourhoodService;

class NeighbourhoodServiceTest extends TestCase
{
    /** @test */
    public function throwsExceptionWhenNeighbourhoodsNotFound(): void
    {
        $this->expectException(NeighbourhoodServiceException::class);
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
    public function throwsExceptionWhenUnableToParseNeighbourhoodsInformation(): void
    {
        $this->expectException(NeighbourhoodServiceException::class);
        $this->expectExceptionMessage('unable to decode json');

        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn('{"hello":"world"');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);


        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/foo/neighbourhoods')
            ->willReturn($response);

        $service = new NeighbourhoodService($client);
        $service->byForceId('foo');
    }

    /** @test */
    public function throwsExceptionWhenUnableToParseNeighbourhoodSummaries(): void
    {
        $this->expectException(NeighbourhoodServiceException::class);
        $this->expectExceptionMessage('unable to parse neighbourhood summaries');

        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn('{"hello":"world"}');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/foo/neighbourhoods')
            ->willReturn($response);

        $service = new NeighbourhoodService($client);
        $service->byForceId('foo');
    }

    /** @test */
    public function returnsNeighbourhoodSummaries(): void
    {
        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn('[{"id":"foo","name":"bar"}]');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/foo/neighbourhoods')
            ->willReturn($response);

        $service = new NeighbourhoodService($client);
        $summaries = $service->byForceId('foo');

        $this->assertCount(1, $summaries);
        $this->assertSame('foo', $summaries->first()->id());
        $this->assertSame('bar', $summaries->first()->name());
    }
}