<?php

declare(strict_types=1);

namespace unit\Service;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use RoadSigns\LaravelPoliceUK\Domain\StopAndSearches\Exceptions\StopAndSearchServiceException;
use RoadSigns\LaravelPoliceUK\Service\StopAndSearchService;

final class StopAndSearchServiceTest extends TestCase
{
    /** @test */
    public function throwsExceptionWhenUnableToGetStopAndSearchesWithNoLocation(): void
    {
        $date = Carbon::now()->subMonth()->format('Y-m');

        $this->expectException(StopAndSearchServiceException::class);
        $this->expectExceptionMessage('unable to find stop and searches with no location with id of foo');

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/stops-no-location?force=foo&date=' . $date)
            ->willThrowException($this->createMock(GuzzleException::class));

        $service = new StopAndSearchService($client);
        $service->withNoLocation('foo');
    }

    /** @test */
    public function throwsExceptionWhenUnableToParseStopAndSearchWithNoLocation(): void
    {
        $this->expectException(StopAndSearchServiceException::class);
        $this->expectExceptionMessage('unable to parse json response');

        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn('{"hello":"world"');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createStub(Client::class);
        $client
            ->method('get')
            ->willReturn($response);

        $service = new StopAndSearchService($client);
        $service->withNoLocation('foo');
    }
}