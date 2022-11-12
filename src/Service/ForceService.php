<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use RoadSigns\LaravelPoliceUK\Domain\Forces\Exceptions\ForceServiceException;
use RoadSigns\LaravelPoliceUK\Domain\Forces\Force;
use RoadSigns\LaravelPoliceUK\Domain\Forces\SeniorOfficer;
use RoadSigns\LaravelPoliceUK\Domain\Forces\Summary;
use RoadSigns\LaravelPoliceUK\Domain\Forces\ValueObject\EngagementMethod;

final class ForceService
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return Collection<int, Summary>
     * @throws GuzzleException
     */
    public function all(): Collection
    {
        $collection = new Collection();
        try {
            $response = $this->client->get('https://data.police.uk/api/forces');
        } catch (GuzzleException $exception) {
            throw new ForceServiceException(
                message: 'unable to find forces',
                code: 0,
                previous: $exception
            );
        }

        $content = $this->getJsonDecode($response);

        try {
            $collection->push(
                ...array_map(
                    static fn(array $force) => new Summary($force['id'], $force['name']),
                    $content
                )
            );
        } catch (\Throwable $throwable) {
            throw new ForceServiceException(
                message: 'unable to parse forces',
                code: $throwable->getCode(),
                previous: $throwable
            );
        }

        return $collection;
    }

    /**
     * @param string $id
     * @return Force
     * @throws ForceServiceException
     */
    public function byForceId(string $id): Force
    {
        try {
            $response = $this->client->get('https://data.police.uk/api/forces/' . $id);
        } catch (GuzzleException $guzzleException) {
            throw new ForceServiceException(
                message: sprintf('unable to find force with id of %s', $id),
                code: $guzzleException->getCode(),
                previous: $guzzleException
            );
        }

        $content = $this->getJsonDecode($response, $id);

        $engagementMethods = array_map(static function ($engagementMethod) {
            return new EngagementMethod(
                title: $engagementMethod['title'],
                description: $engagementMethod['description'],
                url: $engagementMethod['url']
            );
        }, $content['engagement_methods'] ?? []);

        return new Force(
            id: $content['id'],
            name: $content['name'],
            url: $content['url'],
            description: $content['description'],
            telephone: $content['telephone'],
            engagementMethods: $engagementMethods
        );
    }

    /**
     * @param string $id
     * @return Collection<int, SeniorOfficer>
     * @throws ForceServiceException
     */
    public function seniorOfficersByForceId(string $id): Collection
    {
        try {
            $response = $this->client->get('https://data.police.uk/api/forces/' . $id . '/people');
        } catch (GuzzleException $guzzleException) {
            throw new ForceServiceException(
                message: sprintf('unable to find force with id of %s', $id),
                code: $guzzleException->getCode(),
                previous: $guzzleException
            );
        }

        $content = $this->getJsonDecode($response, $id);

        return new Collection(...array_map(static function (array $officer) {
            return new SeniorOfficer(
                name: $officer['name'],
                rank: $officer['officer'],
                bio: $officer['bio']
            );
        }, $content));
    }

    /**
     * @param ResponseInterface $response
     * @return array
     * @throws ForceServiceException
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
        } catch (JsonException $jsonException) {
            throw new ForceServiceException(
                message: sprintf('unable to decode json'),
                code: $jsonException->getCode(),
                previous: $jsonException
            );
        }

        return $content;
    }
}
