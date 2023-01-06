<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Domain\Crimes\ValueObject;

final class StreetLocation
{
    private float $latitude;

    private float $longitude;

    private Street $street;

    private string $type;

    private string $subtype;

    public function __construct(string $type, string $subtype, float $latitude, float $longitude, Street $street)
    {
        $this->type = $type;
        $this->subtype = $subtype;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->street = $street;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function subtype(): string
    {
        return $this->subtype;
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
