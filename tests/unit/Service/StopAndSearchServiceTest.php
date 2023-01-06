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
use RoadSigns\LaravelPoliceUK\Domain\StopAndSearches\Stop;
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

    /** @test */
    public function throwsExceptionWhenUnableToParseStopAndSearchWithNoLocationResponse(): void
    {
        $this->expectException(StopAndSearchServiceException::class);
        $this->expectExceptionMessage('unable to parse stop and searches with no location with id of foo');

        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn('{"hello":"world"}');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createStub(Client::class);
        $client
            ->method('get')
            ->willReturn($response);

        $service = new StopAndSearchService($client);
        $service->withNoLocation('foo');
    }

    /** @test */
    public function returnsStopAndSearchesWithNoLocation(): void
    {
        $date = Carbon::now()->subMonth()->format('Y-m');

        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn('
        [
            {
                "age_range": "over 34",
                "self_defined_ethnicity": "White - White British (W1)",
                "outcome_linked_to_object_of_search": null,
                "datetime": "2017-01-24T01:50:00+00:00",
                "removal_of_more_than_outer_clothing": null,
                "operation": null,
                "officer_defined_ethnicity": "White",
                "object_of_search": "Controlled drugs",
                "involved_person": true,
                "gender": "Male",
                "legislation": "Misuse of Drugs Act 1971 (section 23)",
                "location": null,
                "outcome": false,
                "type": "Person search",
                "operation_name": null
            },
            {
                "age_range": "25-34",
                "self_defined_ethnicity": "White - White British (W1)",
                "outcome_linked_to_object_of_search": null,
                "datetime": "2017-01-22T19:40:00+00:00",
                "removal_of_more_than_outer_clothing": null,
                "operation": null,
                "officer_defined_ethnicity": "White",
                "object_of_search": "Controlled drugs",
                "involved_person": true,
                "gender": "Male",
                "legislation": "Misuse of Drugs Act 1971 (section 23)",
                "location": null,
                "outcome": false,
                "type": "Person and Vehicle search",
                "operation_name": null
            }
        ]');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createStub(Client::class);
        $client
            ->method('get')
            ->willReturn($response);

        $service = new StopAndSearchService($client);
        $stopAndSearches = $service->withNoLocation('foo');

        $this->assertCount(2, $stopAndSearches);
        /** @var Stop $firstStopAndSearch */
        $firstStopAndSearch = $stopAndSearches->first();
        $this->assertEquals('over 34', $firstStopAndSearch->ageRange());
        $this->assertEquals('White - White British (W1)', $firstStopAndSearch->selfDefinedEthnicity());
        $this->assertNull($firstStopAndSearch->outcomeLinkedToObjectOfSearch());
        $this->assertNull($firstStopAndSearch->removalOfMoreThanOuterClothing());
        $this->assertNull($firstStopAndSearch->operation());
        $this->assertEquals('White', $firstStopAndSearch->officerDefinedEthnicity());
        $this->assertEquals('Controlled drugs', $firstStopAndSearch->objectOfSearch());
        $this->assertTrue($firstStopAndSearch->involvedPerson());
    }
}
