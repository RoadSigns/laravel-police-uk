<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Service;

use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use \JsonException;
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

}