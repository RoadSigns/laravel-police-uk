<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Domain\StopAndSearches;

use Carbon\Carbon;

final class Stop
{
    private string $type;

    private Carbon $dateTime;

    private string $ageRange;

    private string $gender;

    private bool $involvedPerson;

    private string $selfDefinedEthnicity;

    /**
     * Whether the person searched had more than their outer clothing removed,
     * as a boolean value (or null if not provided)
     * @var bool|null
     */
    private ?bool $removalOfMoreThanOuterClothing;

    private string $officerDefinedEthnicity;

    private string $objectOfSearch;

    private string $legislation;

    private ?string $location;

    private ?string $operation;

    private ?string $operationName;

    private bool $outcome;

    private ?string $outcomeLinkedToObjectOfSearch;

    public function __construct(
        string $type,
        Carbon $dateTime,
        string $ageRange,
        string $gender,
        bool $involvedPerson,
        string $selfDefinedEthnicity,
        ?bool $removalOfMoreThanOuterClothing,
        string $officerDefinedEthnicity,
        string $objectOfSearch,
        string $legislation,
        ?string $location,
        ?string $operation,
        ?string $operationName,
        bool $outcome,
        ?string $outcomeLinkedToObjectOfSearch
    ) {
        $this->type = $type;
        $this->dateTime = $dateTime;
        $this->ageRange = $ageRange;
        $this->gender = $gender;
        $this->involvedPerson = $involvedPerson;
        $this->selfDefinedEthnicity = $selfDefinedEthnicity;
        $this->removalOfMoreThanOuterClothing = $removalOfMoreThanOuterClothing;
        $this->officerDefinedEthnicity = $officerDefinedEthnicity;
        $this->objectOfSearch = $objectOfSearch;
        $this->legislation = $legislation;
        $this->location = $location;
        $this->operation = $operation;
        $this->operationName = $operationName;
        $this->outcome = $outcome;
        $this->outcomeLinkedToObjectOfSearch = $outcomeLinkedToObjectOfSearch;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function datetime(): Carbon
    {
        return $this->dateTime;
    }

    public function ageRange(): string
    {
        return $this->ageRange;
    }

    public function gender(): string
    {
        return $this->gender;
    }

    public function involvedPerson(): bool
    {
        return $this->involvedPerson;
    }

    public function selfDefinedEthnicity(): string
    {
        return $this->selfDefinedEthnicity;
    }

    public function removalOfMoreThanOuterClothing(): ?bool
    {
        return $this->removalOfMoreThanOuterClothing;
    }

    public function officerDefinedEthnicity(): string
    {
        return $this->officerDefinedEthnicity;
    }

    public function objectOfSearch(): string
    {
        return $this->objectOfSearch;
    }

    public function legislation(): string
    {
        return $this->legislation;
    }

    public function location(): ?string
    {
        return $this->location;
    }

    public function operation(): ?string
    {
        return $this->operation;
    }

    public function operationName(): ?string
    {
        return $this->operationName;
    }

    public function outcome(): bool
    {
        return $this->outcome;
    }

    public function outcomeLinkedToObjectOfSearch(): ?string
    {
        return $this->outcomeLinkedToObjectOfSearch;
    }
}
