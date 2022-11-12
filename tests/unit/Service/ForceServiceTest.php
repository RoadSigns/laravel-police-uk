<?php

declare(strict_types=1);

namespace unit\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use RoadSigns\LaravelPoliceUK\Domain\Forces\Exceptions\ForceServiceException;
use RoadSigns\LaravelPoliceUK\Service\ForceService;

final class ForceServiceTest extends TestCase
{
    /** @test */
    public function throwsExceptionWhenForcesNotFound(): void
    {
        $this->expectException(ForceServiceException::class);
        $this->expectExceptionMessage('unable to find forces');

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/forces')
            ->willThrowException($this->createMock(GuzzleException::class));

        $service = new ForceService($client);
        $service->all();
    }

    /** @test */
    public function throwsExceptionWhenUnableToParseForcesInformation(): void
    {
        $this->expectException(ForceServiceException::class);
        $this->expectExceptionMessage('unable to decode json');

        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn('{"hello":"world"');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/forces')
            ->willReturn($response);

        $service = new ForceService($client);
        $service->all();
    }

    /** @test */
    public function throwsExceptionWhenUnableToParseForces(): void
    {
        $this->expectException(ForceServiceException::class);
        $this->expectExceptionMessage('unable to parse forces');

        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn('{"hello":"world"}');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/forces')
            ->willReturn($response);

        $service = new ForceService($client);
        $service->all();
    }

    /** @test */
    public function returnsForces(): void
    {
        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn('[{"id":"avon-and-somerset","name":"Avon and Somerset Constabulary"}]');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/forces')
            ->willReturn($response);

        $service = new ForceService($client);
        $this->assertCount(1, $service->all());
    }
}