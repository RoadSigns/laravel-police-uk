<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Service;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

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
     */
    public function lastUpdated(): Carbon
    {
        try {
            $response = $this->client->get('https://data.police.uk/api/crime-last-updated');
        } catch (GuzzleException $guzzleException) {
            // Throw Exception
        }

        try {
            $content = (array) json_decode(
                json: $response->getBody()->getContents(),
                associative: true,
                depth: 512,
                flags: JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $jsonException) {
            // Throw Exception
        }

        return Carbon::createFromFormat('Y-m-d', $content['date']);
    }
}
