<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods;

final class Boundary
{
    private float $latitude;

    private float $longitude;

    public function __construct(float $latitude, float $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }


    public function latitude(): float
    {
        return $this->latitude;
    }

    public function longitude(): float
    {
        return $this->longitude;
    }
}
