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
    private ForceService $forceService;

    public function __construct(Client $client)
    {
        $this->forceService = new ForceService($client);
    }

    public function forces(): ForceService
    {
        return $this->forceService;
    }

    public function crimes()
    {
    }

    public function neighbourhoods()
    {
    }

    public function stopAndSearches()
    {
    }
}