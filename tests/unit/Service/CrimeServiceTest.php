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
use RoadSigns\LaravelPoliceUK\Domain\Crimes\Exceptions\CrimeServiceException;
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
}
