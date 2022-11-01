<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Service;

use GuzzleHttp\Client;

final class PoliceUKService
{
    private ForceService $forceService;

    private CrimeService $crimeService;

    private NeighbourhoodService $neighbourhoodService;

    public function __construct(Client $client)
    {
        $this->forceService = new ForceService($client);
        $this->crimeService = new CrimeService($client);
        $this->neighbourhoodService = new NeighbourhoodService($client);
    }

    public function forces(): ForceService
    {
        return $this->forceService;
    }

    public function crimes(): CrimeService
    {
        return $this->crimeService;
    }

    public function neighbourhoods(): NeighbourhoodService
    {
        return $this->neighbourhoodService;
    }

    public function stopAndSearches()
    {
    }
}
