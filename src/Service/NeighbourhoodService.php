<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Service;

use GuzzleHttp\Client;

final class NeighbourhoodService
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }
}
