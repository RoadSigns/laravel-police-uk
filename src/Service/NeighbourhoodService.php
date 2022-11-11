<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;
use RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods\Exceptions\NeighbourhoodServiceException;
use RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods\Neighbourhood;
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
     * @param string $forceId
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
