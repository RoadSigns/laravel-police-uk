<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Service;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;
use RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods\Boundary;
use RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods\Event;
use RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods\Exceptions\NeighbourhoodServiceException;
use RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods\LocateNeighbourhood;
use RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods\Neighbourhood;
use RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods\Person;
use RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods\Priority;
use RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods\Summary;
use RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods\ValueObjects\Centre;
use RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods\ValueObjects\ContactDetails;
use RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods\ValueObjects\Link;
use RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods\ValueObjects\Location;

final class NeighbourhoodService
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return Collection<int, Summary>
     * @throws NeighbourhoodServiceException
     */
    public function byForceId(string $forceId): Collection
    {
        try {
            $response = $this->client->get(sprintf('https://data.police.uk/api/%s/neighbourhoods', $forceId));
        } catch (GuzzleException $guzzleException) {
            throw new NeighbourhoodServiceException(
                message: sprintf('unable to find neighbourhoods with id of %s', $forceId),
                code: $guzzleException->getCode(),
                previous: $guzzleException
            );
        }

        $content = $this->getJsonDecode($response);

        try {
            $neighbourhoodSummaries = new Collection(
                array_map(static function (array $summary) {
                    return new Summary(
                        id: $summary['id'],
                        name: $summary['name']
                    );
                }, $content)
            );
        } catch (\Throwable $throwable) {
            throw new NeighbourhoodServiceException(
                message: 'unable to parse neighbourhood summaries',
                code: $throwable->getCode(),
                previous: $throwable
            );
        }

        return $neighbourhoodSummaries;
    }

    /**
     * @throws NeighbourhoodServiceException
     */
    public function neighbourhood(string $forceId, string $neighbourhoodId): Neighbourhood
    {
        try {
            $response = $this->client->get(
                sprintf('https://data.police.uk/api/%s/%s', $forceId, $neighbourhoodId)
            );
        } catch (GuzzleException $guzzleException) {
            throw new NeighbourhoodServiceException(
                message: sprintf(
                    'unable to find neighbourhood with force id of %s and id of %s',
                    $forceId,
                    $neighbourhoodId
                ),
                code: $guzzleException->getCode(),
                previous: $guzzleException
            );
        }

        $content = $this->getJsonDecode($response);

        try {
            $neighbourhood = new Neighbourhood(
                summary: new Summary(
                    id: $content['id'],
                    name: $content['name']
                ),
                urlForce: $content['url_force'] ?? '',
                description: $content['description'] ?? '',
                population: (int) $content['population'],
                contactDetails: new ContactDetails(
                    email: $content['contact_details']['email'] ?? '',
                    telephone: $content['contact_details']['telephone'] ?? '',
                    mobile: $content['contact_details']['mobile'] ?? '',
                    web: $content['contact_details']['web'] ?? '',
                    facebook: $content['contact_details']['facebook'] ?? '',
                    twitter: $content['contact_details']['twitter'] ?? '',
                    youtube: $content['contact_details']['youtube'] ?? '',
                ),
                centre: new Centre(
                    longitude: (float)$content['centre']['longitude'],
                    latitude: (float)$content['centre']['latitude']
                ),
                links: array_map(static function (array $link): Link {
                    return new Link(
                        title: $link['title'] ?? '',
                        url: $link['url'] ?? '',
                        description: $link['description'] ?? ''
                    );
                }, $content['links']),
                locations: array_map(static function (array $location): Location {
                    return new Location(
                        name: $location['name'] ?? '',
                        type: $location['type'] ?? '',
                        telephone: $location['telephone'] ?? '',
                        address: $location['address'] ?? '',
                        postCode: $location['postcode'] ?? '',
                        latitude: (float)$location['latitude'],
                        longitude: (float)$location['longitude'],
                        description: $location['description'] ?? ''
                    );
                }, $content['locations'])
            );
        } catch (\Throwable $throwable) {
            throw new NeighbourhoodServiceException(
                message: 'unable to parse neighbourhood',
                code: $throwable->getCode(),
                previous: $throwable
            );
        }

        return $neighbourhood;
    }

    /**
     * @return Collection<int, Priority>
     * @throws NeighbourhoodServiceException
     */
    public function priorities(string $forceId, string $neighbourhoodId): Collection
    {
        try {
            $response = $this->client->get(
                sprintf('https://data.police.uk/api/%s/%s/priorities', $forceId, $neighbourhoodId)
            );
        } catch (GuzzleException $guzzleException) {
            throw new NeighbourhoodServiceException(
                message: sprintf(
                    'unable to find neighbourhood priorities with force id of %s and id of %s',
                    $forceId,
                    $neighbourhoodId
                ),
                code: $guzzleException->getCode(),
                previous: $guzzleException
            );
        }

        $content = $this->getJsonDecode($response);

        try {
            $priorities = new Collection(
                array_map(static function (array $priority) {
                    $actionDate = is_null($priority['action-date'])
                        ? null
                        : Carbon::createFromFormat(
                            format: 'Y-m-d\TH:i:s',
                            time: $priority['action-date']
                        );

                    return new Priority(
                        issue: $priority['issue'] ?? '',
                        issueDate: Carbon::createFromFormat('Y-m-d\TH:i:s', $priority['issue-date']),
                        action: $priority['action'] ?? '',
                        actionDate: $actionDate,
                    );
                }, $content)
            );
        } catch (\Throwable $throwable) {
            throw new NeighbourhoodServiceException(
                message: 'unable to parse neighbourhood priorities',
                code: $throwable->getCode(),
                previous: $throwable
            );
        }

        return $priorities;
    }

    /**
     * @return Collection<int, Event>
     * @throws NeighbourhoodServiceException
     */
    public function events(string $forceId, string $neighbourhoodId): Collection
    {
        try {
            $response = $this->client->get(
                sprintf('https://data.police.uk/api/%s/%s/events', $forceId, $neighbourhoodId)
            );
        } catch (GuzzleException $guzzleException) {
            throw new NeighbourhoodServiceException(
                message: sprintf(
                    'unable to find neighbourhood events with force id of %s and id of %s',
                    $forceId,
                    $neighbourhoodId
                ),
                code: $guzzleException->getCode(),
                previous: $guzzleException
            );
        }

        $content = $this->getJsonDecode($response);

        try {
            $events = new Collection(
                array_map(static function (array $event) {
                    return new Event(
                        title: $event['title'] ?? '',
                        description: $event['description'] ?? '',
                        type: $event['type'] ?? '',
                        address: $event['address'] ?? '',
                        startDate: Carbon::createFromFormat('Y-m-d\TH:i:s', $event['start_date']),
                        endDate: Carbon::createFromFormat('Y-m-d\TH:i:s', $event['end_date']),
                    );
                }, $content)
            );
        } catch (\Throwable $throwable) {
            throw new NeighbourhoodServiceException(
                message: 'unable to parse neighbourhood events',
                code: $throwable->getCode(),
                previous: $throwable
            );
        }

        return $events;
    }

    /**
     * @throws NeighbourhoodServiceException
     */
    public function locate(float $latitude, float $longitude): LocateNeighbourhood
    {
        try {
            $response = $this->client->get(
                sprintf('https://data.police.uk/api/locate-neighbourhood?q=%s,%s', $latitude, $longitude)
            );
        } catch (GuzzleException $guzzleException) {
            throw new NeighbourhoodServiceException(
                message: sprintf(
                    'unable to find neighbourhood with latitude of %s and longitude of %s',
                    $latitude,
                    $longitude
                ),
                code: $guzzleException->getCode(),
                previous: $guzzleException
            );
        }

        $content = $this->getJsonDecode($response);

        try {
            $locateNeighbourhood = new LocateNeighbourhood(
                forceId: $content['force'],
                neighbourhoodId: $content['neighbourhood']
            );
        } catch (\Throwable $throwable) {
            throw new NeighbourhoodServiceException(
                message: 'unable to parse neighbourhood',
                code: $throwable->getCode(),
                previous: $throwable
            );
        }

        return $locateNeighbourhood;
    }

    /**
     * @return Collection<int, Person>
     * @throws NeighbourhoodServiceException
     */
    public function people(string $forceId, string $neighbourhoodId): Collection
    {
        try {
            $response = $this->client->get(
                sprintf('https://data.police.uk/api/%s/%s/people', $forceId, $neighbourhoodId)
            );
        } catch (GuzzleException $guzzleException) {
            throw new NeighbourhoodServiceException(
                message: sprintf(
                    'unable to find people for force %s and neighbourhood %s',
                    $forceId,
                    $neighbourhoodId
                ),
                code: $guzzleException->getCode(),
                previous: $guzzleException
            );
        }

        $content = $this->getJsonDecode($response);

        try {
            $people = new Collection(
                array_map(static function (array $person) {
                    return new Person(
                        name: $person['name'],
                        rank: $person['rank'],
                        bio: $person['bio'] ?? ''
                    );
                }, $content)
            );
        } catch (\Throwable $throwable) {
            throw new NeighbourhoodServiceException(
                message: 'unable to parse neighbourhood people',
                code: $throwable->getCode(),
                previous: $throwable
            );
        }

        return $people;
    }

    /**
     * @return Collection<int, Boundary>
     * @throws NeighbourhoodServiceException
     */
    public function boundary(string $forceId, string $neighbourhoodId): Collection
    {
        try {
            $response = $this->client->get(
                sprintf('https://data.police.uk/api/%s/%s/boundary', $forceId, $neighbourhoodId)
            );
        } catch (GuzzleException $guzzleException) {
            throw new NeighbourhoodServiceException(
                message: sprintf(
                    'unable to find boundary for force %s and neighbourhood %s',
                    $forceId,
                    $neighbourhoodId
                ),
                code: $guzzleException->getCode(),
                previous: $guzzleException
            );
        }

        $content = $this->getJsonDecode($response);

        try {
            $boundary = new Collection(
                array_map(static function (array $boundary) {
                    return new Boundary(
                        latitude: (float) $boundary['latitude'],
                        longitude: (float) $boundary['longitude']
                    );
                }, $content)
            );
        } catch (\Throwable $throwable) {
            throw new NeighbourhoodServiceException(
                message: 'unable to parse neighbourhood boundary',
                code: $throwable->getCode(),
                previous: $throwable
            );
        }

        return $boundary;
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
            throw new NeighbourhoodServiceException(
                message: 'unable to decode json',
                code: $jsonException->getCode(),
                previous: $jsonException
            );
        }

        return $content;
    }
}
