<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Domain\Crimes\ValueObject;

final class Location
{
    private float $latitude;

    private float $longitude;

    private Street $street;

    public function __construct(float $latitude, float $longitude, Street $street)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->street = $street;
    }

    public function latitude(): float
    {
        return $this->latitude;
    }

    public function longitude(): float
    {
        return $this->longitude;
    }

    public function street(): Street
    {
        return $this->street;
    }
}
