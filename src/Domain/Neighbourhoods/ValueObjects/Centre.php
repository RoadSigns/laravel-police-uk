<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods\ValueObjects;

final class Centre
{
    private ?float $longitude;

    private ?float $latitude;

    public function __construct(?float $longitude, ?float $latitude)
    {
        $this->longitude = $longitude;
        $this->latitude = $latitude;
    }

    public function longitude(): ?float
    {
        return $this->longitude;
    }


    public function latitude(): ?float
    {
        return $this->latitude;
    }
}
