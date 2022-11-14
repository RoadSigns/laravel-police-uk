<?php

declare(strict_types=1);

namespace unit\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods\Exceptions\NeighbourhoodServiceException;
use RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods\Neighbourhood;
use RoadSigns\LaravelPoliceUK\Service\NeighbourhoodService;

final class NeighbourhoodServiceTest extends TestCase
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

    /** @test */
    public function throwsExceptionWhenNeighbourhoodNotFound(): void
    {
        $this->expectException(NeighbourhoodServiceException::class);
        $this->expectExceptionMessage('unable to find neighbourhood with force id of foo and id of bar');

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/foo/bar')
            ->willThrowException($this->createMock(GuzzleException::class));

        $service = new NeighbourhoodService($client);
        $service->neighbourhood('foo', 'bar');
    }

    /** @test */
    public function throwsExceptionWhenUnableToParseNeighbourhoodInformation(): void
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
            ->with('https://data.police.uk/api/foo/bar')
            ->willReturn($response);

        $service = new NeighbourhoodService($client);
        $service->neighbourhood('foo', 'bar');
    }

    /** @test */
    public function throwsExceptionWhenUnableToParseNeighbourhood(): void
    {
        $this->expectException(NeighbourhoodServiceException::class);
        $this->expectExceptionMessage('unable to parse neighbourhood');

        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn('{"hello":"world"}');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/foo/bar')
            ->willReturn($response);

        $service = new NeighbourhoodService($client);
        $service->neighbourhood('foo', 'bar');
    }

    /** @test */
    public function returnsNeighbourhood(): void
    {
        $json = '{"url_force":"http://www.leics.police.uk/local-policing/city-centre","contact_details":{"twitter":"http://www.twitter.com/centralleicsNPA","facebook":"http://www.facebook.com/leicspolice","telephone":"101","email":"centralleicester.npa@leicestershire.pnn.police.uk"},"name":"City Centre","links":[{"url":"http://www.leicester.gov.uk/","description":null,"title":"Leicester City Council"}],"centre":{"latitude":"52.6389","longitude":"-1.13619"},"locations":[{"name":"Mansfield House","longitude":null,"postcode":"LE1 3GG","address":"74 Belgrave Gate\n, Leicester","latitude":null,"type":"station","description":null}],"description":"<p>The Castle neighbourhood is a diverse covering all of the City Centre. In addition it covers De Montfort University, the University of Leicester, Leicester Royal Infirmary, the Leicester Tigers rugby ground and the Clarendon Park and Riverside communities.</p>\n<p>The Highcross and Haymarket shopping centres and Leicester\'s famous Market are all covered by this neighbourhood.</p>","id":"NC04","population":"0"}';
        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn($json);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/leicestershire/NC04')
            ->willReturn($response);

        $service = new NeighbourhoodService($client);
        /** @var Neighbourhood $neighbourhood */
        $neighbourhood = $service->neighbourhood('leicestershire', 'NC04');

        $this->assertSame('City Centre', $neighbourhood->name());
        $this->assertSame('NC04', $neighbourhood->id());
    }

    /** @test */
    public function throwsExceptionWhenUnableToGetNeighbourhoodPriorities(): void
    {
        $this->expectException(NeighbourhoodServiceException::class);
        $this->expectExceptionMessage(
            'unable to find neighbourhood priorities with force id of leicestershire and id of NC04'
        );

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/leicestershire/NC04/priorities')
            ->willThrowException($this->createMock(GuzzleException::class));

        $service = new NeighbourhoodService($client);
        $service->priorities('leicestershire', 'NC04');
    }

    /** @test */
    public function throwsExceptionWhenUnableToParseNeighbourhoodPriorities(): void
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
            ->with('https://data.police.uk/api/leicestershire/NC04/priorities')
            ->willReturn($response);

        $service = new NeighbourhoodService($client);
        $service->priorities('leicestershire', 'NC04');
    }

    /** @test */
    public function throwsExceptionWhenUnableToParseNeighbourhoodPrioritiesInformation(): void
    {
        $this->expectException(NeighbourhoodServiceException::class);
        $this->expectExceptionMessage('unable to parse neighbourhood priorities');

        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn('{"hello":"world"}');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/leicestershire/NC04/priorities')
            ->willReturn($response);

        $service = new NeighbourhoodService($client);
        $service->priorities('leicestershire', 'NC04');
    }

    /** @test */
    public function returnsNeighbourhoodPrioritiesWhenActionIsNull(): void
    {
        $json = '[
            {
                "action": null,
                "issue-date": "2016-04-14T00:00:00",
                "action-date": null,
                "issue": "<p>To reduce the amount of Anti-Social Behaviour Humberstone Gate, Leicester.</p>"
            },
            {
                "action": null,
                "issue-date": "2016-04-14T00:00:00",
                "action-date": null,
                "issue": "<p>To reduce the amount of Anti-Social Behaviour Humberstone Gate, Leicester.</p>"
            }
        ]';
        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn($json);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/leicestershire/NC04/priorities')
            ->willReturn($response);

        $service = new NeighbourhoodService($client);
        $priorities = $service->priorities('leicestershire', 'NC04');
        $this->assertCount(2, $priorities);
    }

    /** @test */
    public function returnsNeighbourhoodPrioritiesWhenActionIsPopulated(): void
    {
        $json = '[
            {
                "action": "test",
                "issue-date": "2016-04-14T00:00:00",
                "action-date": "2016-04-14T00:00:00",
                "issue": "<p>To reduce the amount of Anti-Social Behaviour Humberstone Gate, Leicester.</p>"
            },
            {
                "action": null,
                "issue-date": "2016-04-14T00:00:00",
                "action-date": "2016-04-14T00:00:00",
                "issue": "<p>To reduce the amount of Anti-Social Behaviour Humberstone Gate, Leicester.</p>"
            }
        ]';
        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn($json);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/leicestershire/NC04/priorities')
            ->willReturn($response);

        $service = new NeighbourhoodService($client);
        $priorities = $service->priorities('leicestershire', 'NC04');
        $this->assertCount(2, $priorities);
    }

    /** @test */
    public function throwsExceptionWhenUnableToGetNeighbourhoodEvents(): void
    {
        $this->expectException(NeighbourhoodServiceException::class);
        $this->expectExceptionMessage(
            'unable to find neighbourhood events with force id of leicestershire and id of NC04'
        );

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/leicestershire/NC04/events')
            ->willThrowException($this->createMock(GuzzleException::class));

        $service = new NeighbourhoodService($client);
        $service->events('leicestershire', 'NC04');
    }

    /** @test */
    public function throwsExceptionWhenUnableToParseNeighbourhoodEvents(): void
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
            ->with('https://data.police.uk/api/leicestershire/NC04/events')
            ->willReturn($response);

        $service = new NeighbourhoodService($client);
        $service->events('leicestershire', 'NC04');
    }

    /** @test */
    public function throwsExceptionWhenUnableToParseNeighbourhoodEventsInformation(): void
    {
        $this->expectException(NeighbourhoodServiceException::class);
        $this->expectExceptionMessage('unable to parse neighbourhood events');

        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn('{"hello":"world"}');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/leicestershire/NC04/events')
            ->willReturn($response);

        $service = new NeighbourhoodService($client);
        $service->events('leicestershire', 'NC04');
    }

    /** @test */
    public function returnsNeighbourhoodEvents(): void
    {
        $json = '[
             {
                "contact_details": {},
                "description": null,
                "title": "Drop In Beat Surgery",
                "address": "Nagarjuna Buddhist Centre, 17 Guildhall Lane",
                "type": "meeting",
                "start_date": "2016-09-17T12:00:00",
                "end_date": "2016-09-17T14:00:00"
            }
        ]';
        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn($json);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/leicestershire/NC04/events')
            ->willReturn($response);

        $service = new NeighbourhoodService($client);
        $events = $service->events('leicestershire', 'NC04');
        $this->assertCount(1, $events);
    }

    /** @test */
    public function throwsExceptionWhenUnableToGetLocateNeighbourhood(): void
    {
        $this->expectException(NeighbourhoodServiceException::class);
        $this->expectExceptionMessage(
            'unable to find neighbourhood with latitude of 0 and longitude of 0'
        );

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/locate-neighbourhood?q=0,0')
            ->willThrowException($this->createMock(GuzzleException::class));

        $service = new NeighbourhoodService($client);
        $service->locate(0.0, 0.0);
    }

    /** @test */
    public function throwsExceptionWhenUnableToParseLocateNeighbourhood(): void
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
            ->with('https://data.police.uk/api/locate-neighbourhood?q=0,0')
            ->willReturn($response);

        $service = new NeighbourhoodService($client);
        $service->locate(0.0, 0.0);
    }

    /** @test */
    public function throwsExceptionWhenUnableToParseLocateNeighbourhoodInformation(): void
    {
        $this->expectException(NeighbourhoodServiceException::class);
        $this->expectExceptionMessage('unable to parse neighbourhood');

        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn('{"hello":"world"}');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/locate-neighbourhood?q=0,0')
            ->willReturn($response);

        $service = new NeighbourhoodService($client);
        $service->locate(0.0, 0.0);
    }

    /** @test */
    public function returnsLocateNeighbourhood(): void
    {
        $json = '{
            "force": "leicestershire",
            "neighbourhood": "NC04"
        }';
        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn($json);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/locate-neighbourhood?q=0,0')
            ->willReturn($response);

        $service = new NeighbourhoodService($client);
        $neighbourhood = $service->locate(0.0, 0.0);
        $this->assertSame('leicestershire', $neighbourhood->forceId());
        $this->assertSame('NC04', $neighbourhood->neighbourhoodId());
    }

    /** @test */
    public function throwsExceptionWhenUnableToGetNeighbourhoodPeople(): void
    {
        $this->expectException(NeighbourhoodServiceException::class);
        $this->expectExceptionMessage(
            'unable to find people for force leicestershire and neighbourhood NC04'
        );

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/leicestershire/NC04/people')
            ->willThrowException($this->createMock(GuzzleException::class));

        $service = new NeighbourhoodService($client);
        $service->people('leicestershire', 'NC04');
    }

    /** @test */
    public function throwsExceptionWhenUnableToParseNeighbourhoodPeople(): void
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
            ->with('https://data.police.uk/api/leicestershire/NC04/people')
            ->willReturn($response);

        $service = new NeighbourhoodService($client);
        $service->people('leicestershire', 'NC04');
    }

    /** @test */
    public function throwsExceptionWhenUnableToParseNeighbourhoodPeopleInformation(): void
    {
        $this->expectException(NeighbourhoodServiceException::class);
        $this->expectExceptionMessage('unable to parse neighbourhood people');

        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn('{"hello":"world"}');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/leicestershire/NC04/people')
            ->willReturn($response);

        $service = new NeighbourhoodService($client);
        $service->people('leicestershire', 'NC04');
    }

    /** @test */
    public function returnsNeighbourhoodPeople(): void
    {
        $json = '[
             {
                "bio": "Test",
                "contact_details": {},
                "name": "Andy Cooper",
                "rank": "Sgt"
            },
            {
                "bio": "Test",
                "contact_details": {},
                "name": "Andy Price",
                "rank": "Sgt"
            }
        ]';

        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn($json);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/leicestershire/NC04/people')
            ->willReturn($response);

        $service = new NeighbourhoodService($client);
        $people = $service->people('leicestershire', 'NC04');

        $this->assertCount(2, $people);
        $this->assertSame('Andy Cooper', $people[0]->name());
        $this->assertSame('Andy Price', $people[1]->name());
    }

    /** @test */
    public function throwsExceptionWhenUnableToGetNeighbourhoodBoundary(): void
    {
        $this->expectException(NeighbourhoodServiceException::class);
        $this->expectExceptionMessage(
            'unable to find boundary for force leicestershire and neighbourhood NC04'
        );

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/leicestershire/NC04/boundary')
            ->willThrowException($this->createMock(GuzzleException::class));

        $service = new NeighbourhoodService($client);
        $service->boundary('leicestershire', 'NC04');
    }

    /** @test */
    public function throwsExceptionWhenUnableToParseNeighbourhoodBoundary(): void
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
            ->with('https://data.police.uk/api/leicestershire/NC04/boundary')
            ->willReturn($response);

        $service = new NeighbourhoodService($client);
        $service->boundary('leicestershire', 'NC04');
    }

    /** @test */
    public function throwsExceptionWhenUnableToParseNeighbourhoodBoundaryInformation(): void
    {
        $this->expectException(NeighbourhoodServiceException::class);
        $this->expectExceptionMessage('unable to parse neighbourhood boundary');

        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn('{"hello":"world"}');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/leicestershire/NC04/boundary')
            ->willReturn($response);

        $service = new NeighbourhoodService($client);
        $service->boundary('leicestershire', 'NC04');
    }

    /** @test */
    public function returnsNeighbourhoodBoundary(): void
    {
        $json = '[
            {
                "latitude": "52.6394052587",
                "longitude": "-1.1458618876"
            },
            {
                "latitude": "52.6389452755",
                "longitude": "-1.1457057759"
            },
            {
                "latitude": "52.6383706746",
                "longitude": "-1.1455755443"
            }
        ]';

        $stream = $this->createMock(Stream::class);
        $stream->method('getContents')->willReturn($json);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('get')
            ->with('https://data.police.uk/api/leicestershire/NC04/boundary')
            ->willReturn($response);

        $service = new NeighbourhoodService($client);
        $boundary = $service->boundary('leicestershire', 'NC04');

        $this->assertCount(3, $boundary);
        $this->assertSame(52.6394052587, $boundary[0]->latitude());
        $this->assertSame(-1.1458618876, $boundary[0]->longitude());
    }
}
