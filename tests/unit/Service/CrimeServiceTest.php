<?php

declare(strict_types=1);

namespace unit\Service;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use RoadSigns\LaravelPoliceUK\Domain\Crimes\Category;
use RoadSigns\LaravelPoliceUK\Domain\Crimes\Crime;
use RoadSigns\LaravelPoliceUK\Domain\Crimes\Exceptions\CrimeServiceException;
use RoadSigns\LaravelPoliceUK\Domain\Crimes\ValueObject\Location;
use RoadSigns\LaravelPoliceUK\Service\CrimeService;

final class CrimeServiceTest extends TestCase
{
    /** @test */
    public function throwsAnExceptionWhenUnableToGetLastUpdatedInformation(): void
    {
        $client = $this->createStub(Client::class);
        $client
            ->method('get')
            ->willThrowException(
                $this->createMock(GuzzleException::class)
            );

        $this->expectException(CrimeServiceException::class);
        $this->expectExceptionMessage('unable to get last updated information');

        $crimeService = new CrimeService($client);
        $crimeService->lastUpdated();
    }

    /** @test */
    public function throwsAnExceptionWhenUnableToParseLastUpdatedInformation(): void
    {
        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn('{"hello":"world"');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);


        $client = $this->createStub(Client::class);
        $client
            ->method('get')
            ->willReturn($response);

        $this->expectException(CrimeServiceException::class);
        $this->expectExceptionMessage('unable to parse json response');

        $crimeService = new CrimeService($client);
        $crimeService->lastUpdated();
    }

    /** @test */
    public function throwsAnExceptionWhenUnableToParseLastUpdatedDate(): void
    {
        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn('{"date":"2022-12"}');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);


        $client = $this->createStub(Client::class);
        $client
            ->method('get')
            ->willReturn($response);

        $this->expectException(CrimeServiceException::class);
        $this->expectExceptionMessage('unable to parse last updated date');

        $crimeService = new CrimeService($client);
        $crimeService->lastUpdated();
    }

    /** @test */
    public function throwsAnExceptionWhenLastUpdatedDateDoesNotExistInResponse(): void
    {
        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn('{"hello":"world"}');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);


        $client = $this->createStub(Client::class);
        $client
            ->method('get')
            ->willReturn($response);

        $this->expectException(CrimeServiceException::class);
        $this->expectExceptionMessage('unable to parse last updated date');

        $crimeService = new CrimeService($client);
        $crimeService->lastUpdated();
    }

    /** @test */
    public function canGetTheLastUpdatedResponse(): void
    {
        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn('{"date":"2022-12-01"}');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);


        $client = $this->createStub(Client::class);
        $client
            ->method('get')
            ->willReturn($response);

        $crimeService = new CrimeService($client);
        $date = $crimeService->lastUpdated();

        $this->assertSame("2022-12", $date->format('Y-m'));
    }

    /** @test */
    public function throwsAnExceptionWhenUnableToGetTheCategories(): void
    {
        $client = $this->createStub(Client::class);
        $client
            ->method('get')
            ->willThrowException(
                $this->createMock(GuzzleException::class)
            );

        $this->expectException(CrimeServiceException::class);
        $this->expectExceptionMessage('unable to get categories');

        $crimeService = new CrimeService($client);
        $crimeService->categories();
    }

    /** @test */
    public function throwsAnExceptionWhenUnableToParseTheCategories(): void
    {
        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn('{"hello":"world"');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createStub(Client::class);
        $client
            ->method('get')
            ->willReturn($response);

        $this->expectException(CrimeServiceException::class);
        $this->expectExceptionMessage('unable to parse json response');

        $crimeService = new CrimeService($client);
        $crimeService->categories();
    }

    /** @test */
    public function throwsAnExceptionWhenUnableToParseTheCategoriesResponse(): void
    {
        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn('{"hello":"world"}');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createStub(Client::class);
        $client
            ->method('get')
            ->willReturn($response);

        $this->expectException(CrimeServiceException::class);
        $this->expectExceptionMessage('unable to parse categories');

        $crimeService = new CrimeService($client);
        $crimeService->categories();
    }

    /** @test */
    public function canGetTheCategories(): void
    {
        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn('
            [
                {
                    "url": "all-crime",
                    "name": "All crime and ASB"
                },
                {
                    "url": "burglary",
                    "name": "Burglary"
                },
                {
                    "url": "anti-social-behaviour",
                    "name": "Anti-social behaviour"
                }
            ]
        ');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createStub(Client::class);
        $client
            ->method('get')
            ->willReturn($response);

        $crimeService = new CrimeService($client);
        $categories = $crimeService->categories();

        $this->assertSame(3, $categories->count());

        /** @var Category $category */
        $category = $categories->first();
        $this->assertSame("All crime and ASB", $category->name());
        $this->assertSame("all-crime", $category->url());
    }

    /** @test */
    public function canGetTheCategoriesWithDate(): void
    {
        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn('
            [
                {
                    "url": "all-crime",
                    "name": "All crime and ASB"
                },
                {
                    "url": "burglary",
                    "name": "Burglary"
                },
                {
                    "url": "anti-social-behaviour",
                    "name": "Anti-social behaviour"
                }
            ]
        ');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createStub(Client::class);
        $client
            ->method('get')
            ->willReturn($response);

        $crimeService = new CrimeService($client);
        $categories = $crimeService->categories(Carbon::now());

        $this->assertSame(3, $categories->count());

        /** @var Category $category */
        $category = $categories->first();
        $this->assertSame("All crime and ASB", $category->name());
        $this->assertSame("all-crime", $category->url());
    }

    /** @test */
    public function throwsAnExceptionWhenUnableToGetTheCrimes(): void
    {
        $client = $this->createStub(Client::class);
        $client
            ->method('get')
            ->willThrowException(
                $this->createMock(GuzzleException::class)
            );

        $this->expectException(CrimeServiceException::class);
        $this->expectExceptionMessage('unable to get crimes with no location');

        $crimeService = new CrimeService($client);
        $crimeService->crimeWithNoLocation('leicestershire');
    }

    /** @test */
    public function throwsAnExceptionWhenUnableToParseTheCrimes(): void
    {
        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn('{"hello":"world"');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createStub(Client::class);
        $client
            ->method('get')
            ->willReturn($response);

        $this->expectException(CrimeServiceException::class);
        $this->expectExceptionMessage('unable to parse json response');

        $crimeService = new CrimeService($client);
        $crimeService->crimeWithNoLocation('leicestershire');
    }

    /** @test */
    public function throwsAnExceptionWhenUnableToParseTheCrimesResponse(): void
    {
        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn('{"hello":"world"}');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createStub(Client::class);
        $client
            ->method('get')
            ->willReturn($response);

        $this->expectException(CrimeServiceException::class);
        $this->expectExceptionMessage('unable to parse crimes with no location');

        $crimeService = new CrimeService($client);
        $crimeService->crimeWithNoLocation('leicestershire');
    }

    /** @test */
    public function canGetTheCrimes(): void
    {
        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn(
            '
            [
                {
                    "category": "burglary", 
                    "persistent_id": "4ea1d4da29bd8b9e362af35cbabb6157149f62b65d37486dffd185a18e1aaadd", 
                    "location_subtype": "", 
                    "id": 56862854, 
                    "location": null, 
                    "context": "", 
                    "month": "2017-03", 
                    "location_type": null, 
                    "outcome_status": {
                        "category": "Investigation complete; no suspect identified", 
                        "date": "2017-03"
                    }
                }, 
                {
                    "category": "criminal-damage-arson", 
                    "persistent_id": "979f2338f25f62196268b52c8405ca8ff431fd2fb02ab11b2192c479816547e5", 
                    "location_subtype": "", 
                    "id": 56866806, 
                    "location": null, 
                    "context": "", 
                    "month": "2017-03", 
                    "location_type": null, 
                    "outcome_status": {
                        "category": "Under investigation", 
                        "date": "2017-03"
                    }
                }
            ]
        '
        );

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createStub(Client::class);
        $client
            ->method('get')
            ->willReturn($response);

        $crimeService = new CrimeService($client);
        $crimes = $crimeService->crimeWithNoLocation('leicestershire');

        $this->assertSame(2, $crimes->count());

        /** @var Crime $crime */
        $crime = $crimes->first();
        $this->assertSame("burglary", $crime->category());
        $this->assertSame("4ea1d4da29bd8b9e362af35cbabb6157149f62b65d37486dffd185a18e1aaadd", $crime->persistentId());
        $this->assertSame(56862854, $crime->id());
        $this->assertSame("", $crime->context());
        $this->assertSame("2017-03", $crime->month()->format('Y-m'));
        $this->assertSame("Investigation complete; no suspect identified", $crime->outcomeStatus()->category());
        $this->assertSame("2017-03", $crime->outcomeStatus()->date()->format('Y-m'));
        $this->assertSame("", $crime->location()->title());
        $this->assertSame("", $crime->location()->type());
        $this->assertSame("", $crime->location()->subtype());
    }

    /** @test */
    public function throwsExceptionWhenUnableToGetTheCrimesWithLocation(): void
    {
        $client = $this->createStub(Client::class);
        $client
            ->method('get')
            ->willThrowException(
                $this->createMock(GuzzleException::class)
            );

        $this->expectException(CrimeServiceException::class);
        $this->expectExceptionMessage('unable to get crimes at location id 884227 for date 2022-04');

        $crimeService = new CrimeService($client);
        $crimeService->atLocationId(884227, Carbon::createFromFormat('Y-m', '2022-04'));
    }

    /** @test */
    public function throwsExceptionWhenUnableToParseTheCrimesWithLocation(): void
    {
        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn('{"hello":"world"');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createStub(Client::class);
        $client
            ->method('get')
            ->willReturn($response);

        $this->expectException(CrimeServiceException::class);
        $this->expectExceptionMessage('unable to parse json response');

        $crimeService = new CrimeService($client);
        $crimeService->atLocationId(884227, Carbon::createFromFormat('Y-m', '2022-04'));
    }

    /** @test */
    public function throwsExceptionWhenUnableToParseTheCrimesWithLocationResponse(): void
    {
        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn('{"hello":"world"}');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createStub(Client::class);
        $client
            ->method('get')
            ->willReturn($response);

        $this->expectException(CrimeServiceException::class);
        $this->expectExceptionMessage('unable to parse crimes at location id 884227 for date 2022-04');

        $crimeService = new CrimeService($client);
        $crimeService->atLocationId(884227, Carbon::createFromFormat('Y-m', '2022-04'));
    }

    /** @test */
    public function canGetTheCrimesWithLocation(): void
    {
        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn(
            '
            [
                {
                    "category": "burglary", 
                    "persistent_id": "4ea1d4da29bd8b9e362af35cbabb6157149f62b65d37486dffd185a18e1aaadd", 
                    "location_subtype": "", 
                    "id": 56862854, 
                    "location": {
                        "latitude": "52.6333", 
                        "street": {
                            "id": 884327, 
                            "name": "On or near The Green"
                        }, 
                        "longitude": "-1.13333"
                    }, 
                    "context": "", 
                    "month": "2017-03", 
                    "location_type": "Force", 
                    "outcome_status": {
                        "category": "Investigation complete; no suspect identified", 
                        "date": "2017-03"
                    }
                }, 
                {
                    "category": "criminal-damage-arson", 
                    "persistent_id": "979f2338f25f62196268b52c8405ca8ff431fd2fb02ab11b2192c479816547e5", 
                    "location_subtype": "", 
                    "id": 56866806, 
                    "location": {
                        "latitude": "52.6333", 
                        "street": {
                            "id": 884327, 
                            "name": "On or near The Green"
                        }, 
                        "longitude": "-1.13333"
                    }, 
                    "context": "", 
                    "month": "2017-03", 
                    "location_type": "Force", 
                    "outcome_status": {
                        "category": "Under investigation", 
                        "date": "2017-03"
                    }
                }
            ]
        '
        );

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createStub(Client::class);
        $client
            ->method('get')
            ->willReturn($response);

        $crimeService = new CrimeService($client);
        $crimes = $crimeService->atLocationId(884227, Carbon::now());


        $this->assertSame(2, $crimes->count());

        /** @var Crime $crime */
        $crime = $crimes->first();
        $this->assertSame("burglary", $crime->category());
        $this->assertSame(56862854, $crime->id());
        $this->assertSame("", $crime->context());
        $this->assertSame("2017-03", $crime->month()->format('Y-m'));
        $this->assertSame("Investigation complete; no suspect identified", $crime->outcomeStatus()->category());
        $this->assertSame("2017-03", $crime->outcomeStatus()->date()->format('Y-m'));
        $this->assertInstanceOf(Location::class, $crime->location());
    }
}
