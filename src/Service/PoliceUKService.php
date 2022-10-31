<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use \JsonException;
use RoadSigns\LaravelPoliceUK\Domain\Forces\Exceptions\ForceNotFoundException;
use RoadSigns\LaravelPoliceUK\Domain\Forces\Exceptions\InvalidForceDataException;
use RoadSigns\LaravelPoliceUK\Domain\Forces\Force;
use RoadSigns\LaravelPoliceUK\Domain\Forces\Summary;
use RoadSigns\LaravelPoliceUK\Domain\Forces\ValueObject\EngagementMethod;

final class PoliceUKService
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
    public function forces(): Collection
    {
        $collection = new Collection();
        $response = $this->client->get('https://data.police.uk/api/forces');

        try {
            $content = (array) json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $collection;
        }

        $collection->push(
            ...array_map(
                static fn (array $force) => new Summary($force['id'], $force['name']),
                $content
            )
        );

        return $collection;
    }

    /**
     * @param string $id
     * @return Force
     * @throws ForceNotFoundException
     * @throws InvalidForceDataException
     */
    public function specificForce(string $id): Force
    {
        try {
            $response = $this->client->get('https://data.police.uk/api/forces/' . $id);
        } catch (GuzzleException $guzzleException) {
            throw new ForceNotFoundException(
                message: sprintf('unable to find force with id of %s', $id),
                code: $guzzleException->getCode(),
                previous: $guzzleException
            );
        }

        try {
            $content = (array) json_decode(
                json: $response->getBody()->getContents(),
                associative: true,
                depth: 512,
                flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $jsonException) {
            throw new InvalidForceDataException(
                message: sprintf('invalid json response for force id %s', $id),
                code: $jsonException->getCode(),
                previous: $jsonException
            );
        }

        $engagementMethods = array_map(static function($engagementMethod) {
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
}