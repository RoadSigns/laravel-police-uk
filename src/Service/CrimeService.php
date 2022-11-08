<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Service;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;
use RoadSigns\LaravelPoliceUK\Domain\Crimes\Category;
use RoadSigns\LaravelPoliceUK\Domain\Crimes\Crime;
use RoadSigns\LaravelPoliceUK\Domain\Crimes\Exceptions\CrimeServiceException;
use RoadSigns\LaravelPoliceUK\Domain\Crimes\ValueObject\Location;
use RoadSigns\LaravelPoliceUK\Domain\Crimes\ValueObject\OutcomeStatus;

final class CrimeService
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Month of the latest crime data in ISO format.
     * The day is irrelevant and is only there to keep a standard formatted date.
     *
     * @return Carbon
     * @throws CrimeServiceException
     */
    public function lastUpdated(): Carbon
    {
        try {
            $response = $this->client->get('https://data.police.uk/api/crime-last-updated');
        } catch (GuzzleException $guzzleException) {
            throw new CrimeServiceException(
                message: 'unable to get last updated information',
                code: $guzzleException->getCode(),
                previous: $guzzleException
            );
        }

        $content = $this->getJsonDecode($response);

        try {
            $date = Carbon::createFromFormat('Y-m-d', $content['date']);
            assert($date instanceof Carbon);
        } catch (\Throwable $throwable) {
            throw new CrimeServiceException(
                message: 'unable to parse last updated date',
                code: $throwable->getCode(),
                previous: $throwable
            );
        }

        return $date;
    }

    /**
     * @param Carbon|null $date
     * @return Collection
     */
    public function categories(Carbon $date = null): Collection
    {
        $url = $date !== null
            ? 'https://data.police.uk/api/crime-categories?date=' . $date->format('Y-m')
            : 'https://data.police.uk/api/crime-categories';

        try {
            $response = $this->client->get($url);
        } catch (GuzzleException $guzzleException) {
            throw new CrimeServiceException(
                message: 'unable to get categories',
                code: $guzzleException->getCode(),
                previous: $guzzleException
            );
        }

        $content = $this->getJsonDecode($response);

        return new Collection(...array_map(static function (array $category) {
            return new Category(
                url: $category['url'],
                name: $category['name']
            );
        }, $content));
    }

    /**
     * @param string $forceId
     * @param string $crimeType
     * @param Carbon|null $date
     * @return Collection<int, Crime>
     */
    public function crimeWithNoLocation(
        string $forceId,
        string $crimeType = 'all-crime',
        Carbon $date = null
    ): Collection {
        $dateFormat = $date?->format('Y-m') ?? Carbon::now()->subMonth()->format('Y-m');

        $url = sprintf(
            'https://data.police.uk/api/crimes-no-location?category=%s&force=%s&date=%s',
            $crimeType,
            $forceId,
            $dateFormat
        );

        try {
            $response = $this->client->get($url);
        } catch (GuzzleException $guzzleException) {
            throw new CrimeServiceException(
                message: 'unable to get crimes with no location',
                code: $guzzleException->getCode(),
                previous: $guzzleException
            );
        }

        $content = $this->getJsonDecode($response);

        return new Collection(...array_map(static function (array $crime) {
            return new Crime(
                id: $crime['id'],
                persistentId: $crime['persistent_id'],
                category: $crime['category'],
                context: $crime['context'],
                month: Carbon::createFromFormat('Y-m', $crime['month']),
                location: new Location(
                    title: $crime['location'] ?? '',
                    type: $crime['location_type'] ?? '',
                    subtype: $crime['location_subtype'] ?? ''
                ),
                outcomeStatus: new OutcomeStatus(
                    category: $crime['outcome_status']['category'],
                    date: Carbon::createFromFormat('Y-m', $crime['outcome_status']['date'])
                )
            );
        }, $content));
    }

    /**
     * @param ResponseInterface $response
     * @return array
     */
    private function getJsonDecode(ResponseInterface $response): array
    {
        try {
            $content = (array)json_decode(
                json: $response->getBody()->getContents(),
                associative: true,
                depth: 512,
                flags: JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $jsonException) {
            throw new CrimeServiceException(
                message: 'unable to parse json response',
                code: $jsonException->getCode(),
                previous: $jsonException
            );
        }
        return $content;
    }
}
