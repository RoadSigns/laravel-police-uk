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
use RoadSigns\LaravelPoliceUK\Domain\Crimes\StreetCrime;
use RoadSigns\LaravelPoliceUK\Domain\Crimes\ValueObject\Location;
use RoadSigns\LaravelPoliceUK\Domain\Crimes\ValueObject\Outcome;
use RoadSigns\LaravelPoliceUK\Domain\Crimes\ValueObject\Street;
use RoadSigns\LaravelPoliceUK\Domain\Crimes\ValueObject\StreetLocation;
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

    /** @test */
    public function throwsExceptionWhenUnableToGetCrimeOutcome(): void
    {
        $client = $this->createStub(Client::class);
        $client
            ->method('get')
            ->willThrowException(
                $this->createMock(GuzzleException::class)
            );

        $this->expectException(CrimeServiceException::class);
        $this->expectExceptionMessage('unable to get outcome for crime id 56862854');

        $crimeService = new CrimeService($client);
        $crimeService->outcome("56862854");
    }

    /** @test */
    public function throwsExceptionWhenUnableToParseCrimeOutcome(): void
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
        $crimeService->outcome("56862854");
    }

    /** @test */
    public function throwsExceptionWhenUnableToParseCrimeOutcomeResponse(): void
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
        $this->expectExceptionMessage('unable to parse outcome for crime id 56862854');

        $crimeService = new CrimeService($client);
        $crimeService->outcome("56862854");
    }

    /** @test */
    public function canGetTheCrimeOutcome(): void
    {
        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn(
            '
            {
                "crime": {
                    "category": "violent-crime", 
                    "persistent_id": "590d68b69228a9ff95b675bb4af591b38de561aa03129dc09a03ef34f537588c", 
                    "location_subtype": "", 
                    "location_type": "Force", 
                    "location": {
                        "latitude": "52.639814", 
                        "street": {
                            "id": 883235, 
                            "name": "On or near Sanvey Gate"
                        }, 
                        "longitude": "-1.139118"
                    }, 
                    "context": "", 
                    "month": "2017-05", 
                    "id": 56880258
                }, 
                "outcomes": [
                    {
                        "category": {
                            "code": "under-investigation", 
                            "name": "Under investigation"
                        }, 
                        "date": "2017-05", 
                        "person_id": null
                    }, 
                    {
                        "category": {
                            "code": "formal-action-not-in-public-interest", 
                            "name": "Formal action is not in the public interest"
                        }, 
                        "date": "2017-06", 
                        "person_id": null
                    }
                ]
            }
        '
        );

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createStub(Client::class);
        $client
            ->method('get')
            ->willReturn($response);

        $crimeService = new CrimeService($client);
        $outcome = $crimeService->outcome("56880258");

        $this->assertSame(56880258, $outcome->crime()->id());
        $this->assertSame("violent-crime", $outcome->crime()->category());
        $this->assertSame("", $outcome->crime()->context());
        $this->assertSame("2017-05", $outcome->crime()->month()->format('Y-m'));
        $this->assertInstanceOf(Location::class, $outcome->crime()->location());
        $this->assertSame(52.639814, $outcome->crime()->location()->latitude());
        $this->assertSame(-1.139118, $outcome->crime()->location()->longitude());
        $this->assertInstanceOf(Street::class, $outcome->crime()->location()->street());
        $this->assertSame(883235, $outcome->crime()->location()->street()->id());
        $this->assertSame("On or near Sanvey Gate", $outcome->crime()->location()->street()->name());

        $this->assertSame(2, $outcome->outcomes()->count());

        /** @var Outcome $firstOutcome */
        $firstOutcome = $outcome->outcomes()->first();
        $this->assertSame("under-investigation", $firstOutcome->category()->code());
        $this->assertSame("Under investigation", $firstOutcome->category()->name());
        $this->assertSame("2017-05", $firstOutcome->date()->format('Y-m'));
        $this->assertNull($firstOutcome->personId());
    }

    /** @test */
    public function throwsAnExceptionWhenUnableToGetStreetAtLocation(): void
    {
        $client = $this->createStub(Client::class);
        $client
            ->method('get')
            ->willThrowException(
                $this->createMock(GuzzleException::class)
            );

        $this->expectException(CrimeServiceException::class);
        $this->expectExceptionMessage(
            'unable to get street level crime for longitude 10.1 and latitude 11.2 for date 2022-01'
        );

        $crimeService = new CrimeService($client);
        $crimeService->streetLevelCrimeInLocation(
            10.1,
            11.2,
            Carbon::createFromFormat('Y-m', '2022-01')
        );
    }

    /** @test */
    public function throwsAnExceptionWhenUnableToParseStreetAtLocation(): void
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
        $crimeService->streetLevelCrimeInLocation(
            10.1,
            11.2,
            Carbon::createFromFormat('Y-m', '2022-01')
        );
    }

    /** @test */
    public function throwsExceptionWhenUnableToParseStreetCrimeAtLocationResponse(): void
    {
        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn(
            '
            [
                {
                    "category": "anti-social-behaviour", 
                    "location_type": "Force", 
                    "location": {
                        "latitude": "52.639814", 
                        "street": {
                            "id": 883235, 
                            "name": "On or near Sanvey Gate"
                        }, 
                        "longitude": "-1.139118"
                    }, 
                    "context": "", 
                    "month": "2017-05", 
                    "id": 56880258
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

        $this->expectException(CrimeServiceException::class);
        $this->expectExceptionMessage(
            'unable to parse street level crime for longitude 10.1 and latitude 11.2 for date 2022-01'
        );

        $crimeService = new CrimeService($client);
        $crimeService->streetLevelCrimeInLocation(
            10.1,
            11.2,
            Carbon::createFromFormat('Y-m', '2022-01')
        );
    }

    /** @test */
    public function canGetStreetCrimeAtLocation()
    {
        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn(
            '
            [
                {
                    "category": "anti-social-behaviour",
                    "location_type": "Force",
                    "location": {
                        "latitude": "52.640961",
                        "street": {
                            "id": 884343,
                            "name": "On or near Wharf Street North"
                        },
                        "longitude": "-1.126371"
                    },
                    "context": "",
                    "outcome_status": null,
                    "persistent_id": "",
                    "id": 54164419,
                    "location_subtype": "",
                    "month": "2017-01"
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
        $streetCrime = $crimeService->streetLevelCrimeInLocation(
            10.1,
            11.2,
            Carbon::createFromFormat('Y-m', '2022-01')
        );

        $this->assertSame(1, $streetCrime->count());

        /** @var StreetCrime $firstStreetCrime */
        $firstStreetCrime = $streetCrime->first();
        $this->assertSame("anti-social-behaviour", $firstStreetCrime->category());
        $this->assertSame("Force", $firstStreetCrime->location()->type());
        $this->assertSame("", $firstStreetCrime->context());
        $this->assertSame("2017-01", $firstStreetCrime->month()->format('Y-m'));
        $this->assertInstanceOf(StreetLocation::class, $firstStreetCrime->location());
        $this->assertSame(52.640961, $firstStreetCrime->location()->latitude());
        $this->assertSame(-1.126371, $firstStreetCrime->location()->longitude());
        $this->assertInstanceOf(Street::class, $firstStreetCrime->location()->street());
        $this->assertSame(884343, $firstStreetCrime->location()->street()->id());
        $this->assertSame("On or near Wharf Street North", $firstStreetCrime->location()->street()->name());
        $this->assertNull($firstStreetCrime->outcomeStatus());
    }

    /** @test */
    public function canGetStreetCrimeAtLocationWhenOutcomeStatusIsPopulated()
    {
        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn(
            '
            [
                {
                    "category": "anti-social-behaviour",
                    "location_type": "Force",
                    "location": {
                        "latitude": "52.640961",
                        "street": {
                            "id": 884343,
                            "name": "On or near Wharf Street North"
                        },
                        "longitude": "-1.126371"
                    },
                    "context": "",
                    "outcome_status": {
                        "category": "awaiting-court-result",
                        "date": "2017-01"
                    },
                    "persistent_id": "123456789",
                    "id": 54164419,
                    "location_subtype": "",
                    "month": "2017-01"
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
        $streetCrime = $crimeService->streetLevelCrimeInLocation(
            10.1,
            11.2,
            Carbon::createFromFormat('Y-m', '2022-01')
        );

        $this->assertSame(1, $streetCrime->count());

        /** @var StreetCrime $firstStreetCrime */
        $firstStreetCrime = $streetCrime->first();
        $this->assertSame(54164419, $firstStreetCrime->id());
        $this->assertSame('123456789', $firstStreetCrime->persistentId());
        $this->assertSame("anti-social-behaviour", $firstStreetCrime->category());
        $this->assertSame("Force", $firstStreetCrime->location()->type());
        $this->assertSame("", $firstStreetCrime->context());
        $this->assertSame("2017-01", $firstStreetCrime->month()->format('Y-m'));
        $this->assertInstanceOf(StreetLocation::class, $firstStreetCrime->location());
        $this->assertSame(52.640961, $firstStreetCrime->location()->latitude());
        $this->assertSame(-1.126371, $firstStreetCrime->location()->longitude());
        $this->assertInstanceOf(Street::class, $firstStreetCrime->location()->street());
        $this->assertSame(884343, $firstStreetCrime->location()->street()->id());
        $this->assertSame("On or near Wharf Street North", $firstStreetCrime->location()->street()->name());
        $this->assertSame("awaiting-court-result", $firstStreetCrime->outcomeStatus()->category());
        $this->assertSame("2017-01", $firstStreetCrime->outcomeStatus()->date()->format('Y-m'));
    }


    /** @test */
    public function canGetStreetCrimeAtLocationWhenNoDateProvided()
    {
        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn(
            '
            [
                {
                    "category": "anti-social-behaviour",
                    "location_type": "Force",
                    "location": {
                        "latitude": "52.640961",
                        "street": {
                            "id": 884343,
                            "name": "On or near Wharf Street North"
                        },
                        "longitude": "-1.126371"
                    },
                    "context": "",
                    "outcome_status": {
                        "category": "awaiting-court-result",
                        "date": "2017-01"
                    },
                    "persistent_id": "123456789",
                    "id": 54164419,
                    "location_subtype": "",
                    "month": "2017-01"
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
        $streetCrime = $crimeService->streetLevelCrimeInLocation(10.1, 11.2);

        $this->assertSame(1, $streetCrime->count());

        /** @var StreetCrime $firstStreetCrime */
        $firstStreetCrime = $streetCrime->first();
        $this->assertSame(54164419, $firstStreetCrime->id());
        $this->assertSame('123456789', $firstStreetCrime->persistentId());
        $this->assertSame("anti-social-behaviour", $firstStreetCrime->category());
        $this->assertSame("Force", $firstStreetCrime->location()->type());
        $this->assertSame("", $firstStreetCrime->context());
        $this->assertSame("2017-01", $firstStreetCrime->month()->format('Y-m'));
        $this->assertInstanceOf(StreetLocation::class, $firstStreetCrime->location());
        $this->assertSame(52.640961, $firstStreetCrime->location()->latitude());
        $this->assertSame(-1.126371, $firstStreetCrime->location()->longitude());
        $this->assertInstanceOf(Street::class, $firstStreetCrime->location()->street());
        $this->assertSame(884343, $firstStreetCrime->location()->street()->id());
        $this->assertSame("On or near Wharf Street North", $firstStreetCrime->location()->street()->name());
        $this->assertSame("awaiting-court-result", $firstStreetCrime->outcomeStatus()->category());
        $this->assertSame("2017-01", $firstStreetCrime->outcomeStatus()->date()->format('Y-m'));
    }
}
