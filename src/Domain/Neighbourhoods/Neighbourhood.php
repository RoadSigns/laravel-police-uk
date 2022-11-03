<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods;

use RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods\ValueObjects\Centre;
use RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods\ValueObjects\ContactDetails;
use RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods\ValueObjects\Link;

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

    /** @var array<int,  */
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


}