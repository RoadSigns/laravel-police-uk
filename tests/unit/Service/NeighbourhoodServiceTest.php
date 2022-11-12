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
}
