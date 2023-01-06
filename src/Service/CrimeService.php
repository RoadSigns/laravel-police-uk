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
use RoadSigns\LaravelPoliceUK\Domain\Crimes\Outcome;
use RoadSigns\LaravelPoliceUK\Domain\Crimes\StreetCrime;
use RoadSigns\LaravelPoliceUK\Domain\Crimes\ValueObject\Location;
use RoadSigns\LaravelPoliceUK\Domain\Crimes\ValueObject\Outcome as OutcomeItem;
use RoadSigns\LaravelPoliceUK\Domain\Crimes\ValueObject\OutcomeCategory;
use RoadSigns\LaravelPoliceUK\Domain\Crimes\ValueObject\OutcomeStatus;
use RoadSigns\LaravelPoliceUK\Domain\Crimes\ValueObject\Street;
use RoadSigns\LaravelPoliceUK\Domain\Crimes\ValueObject\StreetLocation;
use RoadSigns\LaravelPoliceUK\Domain\Crimes\ValueObject\UnknownLocation;

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
     * @throws CrimeServiceException
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

        try {
            $categories = new Collection(
                array_map(static function (array $category) {
                    return new Category(
                        url: $category['url'],
                        name: $category['name']
                    );
                }, $content)
            );
        } catch (\Throwable $throwable) {
            throw new CrimeServiceException(
                message: 'unable to parse categories',
                code: $throwable->getCode(),
                previous: $throwable
            );
        }

        return $categories;
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

        try {
            $crimes = new Collection(
                array_map(static function (array $crime) {
                    return new Crime(
                        id: $crime['id'],
                        persistentId: $crime['persistent_id'],
                        category: $crime['category'],
                        context: $crime['context'],
                        month: Carbon::createFromFormat('Y-m', $crime['month']),
                        location: new UnknownLocation(
                            title: $crime['location'] ?? '',
                            type: $crime['location_type'] ?? '',
                            subtype: $crime['location_subtype'] ?? ''
                        ),
                        outcomeStatus: new OutcomeStatus(
                            category: $crime['outcome_status']['category'],
                            date: Carbon::createFromFormat('Y-m', $crime['outcome_status']['date'])
                        )
                    );
                }, $content)
            );
        } catch (\Throwable $throwable) {
            throw new CrimeServiceException(
                message: 'unable to parse crimes with no location',
                code: $throwable->getCode(),
                previous: $throwable
            );
        }

        return $crimes;
    }

    /**
     * @return Collection<int, Crime>
     * @throws CrimeServiceException
     */
    public function atLocationId(int $locationId, Carbon $date): Collection
    {
        try {
            $response = $this->client->get(
                sprintf(
                    'https://data.police.uk/api/crimes-at-location?date=%s&location_id=%s',
                    $date->format('Y-m'),
                    $locationId
                )
            );
        } catch (GuzzleException $guzzleException) {
            throw new CrimeServiceException(
                message: sprintf(
                    'unable to get crimes at location id %s for date %s',
                    $locationId,
                    $date->format('Y-m')
                ),
                code: $guzzleException->getCode(),
                previous: $guzzleException
            );
        }

        $content = $this->getJsonDecode($response);

        try {
            $crimes = new Collection(
                array_map(static function (array $crime) {
                    return new Crime(
                        id: $crime['id'],
                        persistentId: $crime['persistent_id'],
                        category: $crime['category'],
                        context: $crime['context'],
                        month: Carbon::createFromFormat('Y-m', $crime['month']),
                        location: new Location(
                            latitude: (float) $crime['location']['latitude'],
                            longitude: (float) $crime['location']['longitude'],
                            street: new Street(
                                id: (int) $crime['location']['street']['id'],
                                name: $crime['location']['street']['name']
                            )
                        ),
                        outcomeStatus: new OutcomeStatus(
                            category: $crime['outcome_status']['category'],
                            date: Carbon::createFromFormat('Y-m', $crime['outcome_status']['date'])
                        )
                    );
                }, $content)
            );
        } catch (\Throwable $throwable) {
            throw new CrimeServiceException(
                message: sprintf(
                    'unable to parse crimes at location id %s for date %s',
                    $locationId,
                    $date->format('Y-m')
                ),
                code: $throwable->getCode(),
                previous: $throwable
            );
        }

        return $crimes;
    }

    public function outcome(string $crimeId): Outcome
    {
        try {
            $response = $this->client->get(
                sprintf(
                    'https://data.police.uk/api/outcomes-for-crime/%s',
                    $crimeId
                )
            );
        } catch (GuzzleException $guzzleException) {
            throw new CrimeServiceException(
                message: sprintf(
                    'unable to get outcome for crime id %s',
                    $crimeId
                ),
                code: $guzzleException->getCode(),
                previous: $guzzleException
            );
        }

        $content = $this->getJsonDecode($response);

        try {
            $outcome = new Outcome(
                crime: new Crime(
                    id: $content['crime']['id'],
                    persistentId: $content['crime']['persistent_id'],
                    category: $content['crime']['category'],
                    context: $content['crime']['context'],
                    month: Carbon::createFromFormat('Y-m', $content['crime']['month']),
                    location: new Location(
                        latitude: (float) $content['crime']['location']['latitude'],
                        longitude: (float) $content['crime']['location']['longitude'],
                        street: new Street(
                            id: (int) $content['crime']['location']['street']['id'],
                            name: $content['crime']['location']['street']['name']
                        )
                    ),
                    outcomeStatus: null
                ),
                outcomes: new Collection(
                    array_map(static function (array $outcome) {
                        return new OutcomeItem(
                            category: new OutcomeCategory(
                                code: $outcome['category']['code'],
                                name: $outcome['category']['name']
                            ),
                            date: Carbon::createFromFormat('Y-m', $outcome['date']),
                            personId: $outcome['person_id'] ?? null,
                        );
                    }, $content['outcomes'])
                )
            );
        } catch (\Throwable $throwable) {
            throw new CrimeServiceException(
                message: sprintf(
                    'unable to parse outcome for crime id %s',
                    $crimeId
                ),
                code: $throwable->getCode(),
                previous: $throwable
            );
        }

        return $outcome;
    }

    /**
     * @param float $longitude
     * @param float $latitude
     * @param Carbon $date
     * @return Collection<int, StreetCrime>
     * @throws CrimeServiceException
     */
    public function streetLevelCrimeInLocation(float $longitude, float $latitude, Carbon $date = null): Collection
    {
        if ($date === null) {
            $date = Carbon::now();
        }

        try {
            $response = $this->client->get(
                sprintf(
                    'https://data.police.uk/api/crimes-street/all-crime?lat=%s&lng=%s&date=%s',
                    $latitude,
                    $longitude,
                    $date->format('Y-m')
                )
            );
        } catch (GuzzleException $guzzleException) {
            throw new CrimeServiceException(
                message: sprintf(
                    'unable to get street level crime for longitude %s and latitude %s for date %s',
                    $longitude,
                    $latitude,
                    $date->format('Y-m')
                ),
                code: $guzzleException->getCode(),
                previous: $guzzleException
            );
        }

        $content = $this->getJsonDecode($response);

        try {
            $crimes = new Collection(
                array_map(static function (array $crime) {
                    $outcomeStatus = null;
                    if ($crime['outcome_status'] !== null) {
                        $outcomeStatus = new OutcomeStatus(
                            category: $crime['outcome_status']['category'],
                            date: Carbon::createFromFormat('Y-m', $crime['outcome_status']['date'])
                        );
                    }

                    return new StreetCrime(
                        id: $crime['id'],
                        persistentId: $crime['persistent_id'],
                        category: $crime['category'],
                        context: $crime['context'],
                        month: Carbon::createFromFormat('Y-m', $crime['month']),
                        location: new StreetLocation(
                            type: $crime['location_type'],
                            subtype: $crime['location_subtype'],
                            latitude: (float) $crime['location']['latitude'],
                            longitude: (float) $crime['location']['longitude'],
                            street: new Street(
                                id: (int) $crime['location']['street']['id'],
                                name: $crime['location']['street']['name']
                            )
                        ),
                        outcomeStatus: $outcomeStatus
                    );
                }, $content)
            );
        } catch (\Throwable $throwable) {
            throw new CrimeServiceException(
                message: sprintf(
                    'unable to parse street level crime for longitude %s and latitude %s for date %s',
                    $longitude,
                    $latitude,
                    $date->format('Y-m')
                ),
                code: $throwable->getCode(),
                previous: $throwable
            );
        }

        return $crimes;
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
