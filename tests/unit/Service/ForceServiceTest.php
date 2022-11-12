<?php

declare(strict_types=1);

namespace unit\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Stream;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use RoadSigns\LaravelPoliceUK\Domain\Forces\Exceptions\ForceServiceException;
use RoadSigns\LaravelPoliceUK\Domain\Forces\SeniorOfficer;
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

    /** @test */
    public function throwsExceptionWhenForceNotFound(): void
    {
        $this->expectException(ForceServiceException::class);
        $this->expectExceptionMessage('unable to find force');

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/forces/avon-and-somerset')
            ->willThrowException($this->createMock(GuzzleException::class));

        $service = new ForceService($client);
        $service->byForceId('avon-and-somerset');
    }

    /** @test */
    public function throwsExceptionWhenUnableToParseForceInformation(): void
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
            ->with('https://data.police.uk/api/forces/avon-and-somerset')
            ->willReturn($response);

        $service = new ForceService($client);
        $service->byForceId('avon-and-somerset');
    }

    /** @test */
    public function throwsExceptionWhenUnableToParseForce(): void
    {
        $this->expectException(ForceServiceException::class);
        $this->expectExceptionMessage('unable to parse force');

        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn('{"hello":"world"}');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/forces/avon-and-somerset')
            ->willReturn($response);

        $service = new ForceService($client);
        $service->byForceId('avon-and-somerset');
    }

    /** @test */
    public function returnsForce(): void
    {
        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn('
            {
                "description": null,
                "url": "http://www.avonandsomerset.police.uk",
                "engagement_methods": [
                    {
                        "url": "https://www.facebook.com/avonandsomersetpolice/",
                        "type": "facebook",
                        "description": null,
                        "title": "facebook"
                    },
                    {
                        "url": "http://twitter.com/aspolice",
                        "type": "twitter",
                        "description": null,
                        "title": "twitter"
                    },
                    {
                        "url": "http://www.flickr.com/photos/aspolice",
                        "type": "flickr",
                        "description": null,
                        "title": "flickr"
                    },
                    {
                        "url": "http://www.youtube.com/user/ASPolice",
                        "type": "youtube",
                        "description": null,
                        "title": "youtube"
                    }
                ],
                "telephone": "101",
                "id": "avon-and-somerset",
                "name": "Avon and Somerset Constabulary"
            }
        ');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/forces/avon-and-somerset')
            ->willReturn($response);

        $service = new ForceService($client);
        $this->assertEquals(
            'avon-and-somerset',
            $service->byForceId('avon-and-somerset')->id()
        );
    }

    /** @test */
    public function throwsExceptionWhenSeniorOfficersNotFound(): void
    {
        $this->expectException(ForceServiceException::class);
        $this->expectExceptionMessage('unable to find senior officers for force with id of avon-and-somerset');

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/forces/avon-and-somerset/people')
            ->willThrowException($this->createMock(GuzzleException::class));

        $service = new ForceService($client);
        $service->seniorOfficersByForceId('avon-and-somerset');
    }

    /** @test */
    public function throwsExceptionWhenUnableToParseSeniorOfficers(): void
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
            ->with('https://data.police.uk/api/forces/avon-and-somerset/people')
            ->willReturn($response);

        $service = new ForceService($client);
        $service->seniorOfficersByForceId('avon-and-somerset');
    }

    /** @test */
    public function throwsExceptionWhenUnableToParseSeniorOfficer(): void
    {
        $this->expectException(ForceServiceException::class);
        $this->expectExceptionMessage('unable to parse senior officer');

        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn('{"hello":"world"}');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/forces/avon-and-somerset/people')
            ->willReturn($response);

        $service = new ForceService($client);
        $service->seniorOfficersByForceId('avon-and-somerset');
    }

    /** @test */
    public function willReturnSeniorOfficers(): void
    {
        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn('
            [
                {
                    "bio": "Test",
                    "contact_details": {
                        "twitter": "http://www.twitter.com/PoliceUK"
                    },
                    "name": "David Jones",
                    "rank": "Deputy Chief Officer (Crime)"
                },
                {
                    "bio": "Test",
                    "contact_details": {},
                    "name": "Nigel Williams",
                    "rank": "Assistant Chief Officer (Crime)"
                }
            ]
        ');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/forces/avon-and-somerset/people')
            ->willReturn($response);

        $service = new ForceService($client);
        /** @var Collection<int, SeniorOfficer> $seniorOfficers */
        $seniorOfficers = $service->seniorOfficersByForceId('avon-and-somerset');
        $this->assertCount(2, $seniorOfficers);
        $this->assertSame('David Jones', $seniorOfficers[0]->name());
        $this->assertSame('Nigel Williams', $seniorOfficers[1]->name());
    }
}
