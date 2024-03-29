<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Service;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;
use RoadSigns\LaravelPoliceUK\Domain\StopAndSearches\Exceptions\StopAndSearchServiceException;
use RoadSigns\LaravelPoliceUK\Domain\StopAndSearches\Stop;

final class StopAndSearchService
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function withNoLocation(string $forceId, Carbon $date = null): Collection
    {
        $dateString = $date !== null
            ? $date->format('Y-m')
            : Carbon::now()->subMonth()->format('Y-m');

        try {
            $response = $this->client->get(sprintf(
                'https://data.police.uk/api/stops-no-location?force=%s&date=%s',
                $forceId,
                $dateString
            ));
        } catch (GuzzleException $guzzleException) {
            throw new StopAndSearchServiceException(
                message: sprintf('unable to find stop and searches with no location with id of %s', $forceId),
                code: $guzzleException->getCode(),
                previous: $guzzleException
            );
        }

        $content = $this->getJsonDecode($response);

        try {
            $stopAndSearches = new Collection(array_map(static function (array $stops) {
                return new Stop(
                    type: $stops['type'],
                    dateTime: Carbon::parse($stops['datetime']),
                    ageRange: $stops['age_range'],
                    gender: $stops['gender'],
                    involvedPerson: $stops['involved_person'],
                    selfDefinedEthnicity: $stops['self_defined_ethnicity'],
                    removalOfMoreThanOuterClothing: $stops['removal_of_more_than_outer_clothing'],
                    officerDefinedEthnicity: $stops['officer_defined_ethnicity'],
                    objectOfSearch: $stops['object_of_search'],
                    legislation: $stops['legislation'],
                    location: $stops['location'],
                    operation: $stops['operation'],
                    operationName: $stops['operation_name'],
                    outcome: $stops['outcome'],
                    outcomeLinkedToObjectOfSearch: $stops['outcome_linked_to_object_of_search']
                );
            }, $content));
        } catch (\Throwable $throwable) {
            throw new StopAndSearchServiceException(
                message: sprintf('unable to parse stop and searches with no location with id of %s', $forceId),
                code: $throwable->getCode(),
                previous: $throwable
            );
        }

        return $stopAndSearches;
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
            throw new StopAndSearchServiceException(
                message: 'unable to parse json response',
                code: $jsonException->getCode(),
                previous: $jsonException
            );
        }
        return $content;
    }
}
