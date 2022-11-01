<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;
use RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods\Exceptions\NeighbourhoodsNotFoundException;
use RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods\Summary;


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
     * @throws NeighbourhoodsNotFoundException
     */
    public function byForceId(string $forceId): Collection
    {
        try {
            $response = $this->client->get(sprintf('https://data.police.uk/api/%s/neighbourhoods', $forceId));
        } catch (GuzzleException $guzzleException) {
            throw new NeighbourhoodsNotFoundException(
                message: sprintf('unable to find neighbourhoods with id of %s', $forceId),
                code: $guzzleException->getCode(),
                previous: $guzzleException
            );
        }

        $content = $this->getJsonDecode($response);

        return new Collection(...array_map(static function (array $summary) {
            return new Summary(
                id: $summary['id'],
                name: $summary['name']
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
            // Throw Exception
        }
        return $content;
    }
}
