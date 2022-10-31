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

}