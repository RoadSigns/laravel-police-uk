<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods\ValueObjects;

final class Location
{
    private string $name;

    private string $type;

    private string $telephone;

    private string $address;

    private string $postCode;

    private ?float $latitude;

    private ?float $longitude;

    private string $description;

    public function __construct(
        string $name,
        string $type,
        string $telephone,
        string $address,
        string $postCode,
        ?float $latitude,
        ?float $longitude,
        string $description
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->telephone = $telephone;
        $this->address = $address;
        $this->postCode = $postCode;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->description = $description;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function telephone(): string
    {
        return $this->telephone;
    }

    public function address(): string
    {
        return $this->address;
    }


    public function postCode(): string
    {
        return $this->postCode;
    }

    public function latitude(): ?float
    {
        return $this->latitude;
    }

    public function longitude(): ?float
    {
        return $this->longitude;
    }

    public function description(): string
    {
        return $this->description;
    }
}
