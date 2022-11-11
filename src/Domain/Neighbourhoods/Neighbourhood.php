<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods;

use RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods\ValueObjects\Centre;
use RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods\ValueObjects\ContactDetails;
use RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods\ValueObjects\Link;
use RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods\ValueObjects\Location;

final class Neighbourhood
{
    private string $name;

    private string $id;

    private string $urlForce;

    private string $description;

    private int $population;

    private ContactDetails $contactDetails;

    private Centre $centre;

    /**
     * @var array<int, Link>
     */
    private array $links;

    /** @var array<int, Location> */
    private array $locations;

    public function __construct(
        Summary $summary,
        string $urlForce,
        string $description,
        int $population,
        ContactDetails $contactDetails,
        Centre $centre,
        array $links = [],
        array $locations = []
    ) {
        $this->name = $summary->name();
        $this->id = $summary->id();
        $this->urlForce = $urlForce;
        $this->description = $description;
        $this->population = $population;
        $this->contactDetails = $contactDetails;
        $this->centre = $centre;
        $this->links = $links;
        $this->locations = $locations;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function urlForce(): string
    {
        return $this->urlForce;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function population(): int
    {
        return $this->population;
    }

    public function contactDetails(): ContactDetails
    {
        return $this->contactDetails;
    }

    public function centre(): Centre
    {
        return $this->centre;
    }

    public function links(): array
    {
        return $this->links;
    }

    public function locations(): array
    {
        return $this->locations;
    }
}
