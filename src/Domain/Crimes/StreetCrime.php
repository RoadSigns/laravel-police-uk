<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Domain\Crimes;

use Carbon\Carbon;
use RoadSigns\LaravelPoliceUK\Domain\Crimes\ValueObject\OutcomeStatus;
use RoadSigns\LaravelPoliceUK\Domain\Crimes\ValueObject\StreetLocation;

final class StreetCrime
{
    private int $id;

    private string $persistentId;

    private string $category;

    private string $context;

    private Carbon $month;

    private StreetLocation $location;

    private ?OutcomeStatus $outcomeStatus;

    public function __construct(
        int $id,
        string $persistentId,
        string $category,
        string $context,
        Carbon $month,
        StreetLocation $location,
        ?OutcomeStatus $outcomeStatus
    ) {
        $this->id = $id;
        $this->persistentId = $persistentId;
        $this->category = $category;
        $this->context = $context;
        $this->month = $month;
        $this->location = $location;
        $this->outcomeStatus = $outcomeStatus;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function persistentId(): string
    {
        return $this->persistentId;
    }

    public function category(): string
    {
        return $this->category;
    }

    public function context(): string
    {
        return $this->context;
    }

    public function month(): Carbon
    {
        return $this->month;
    }

    public function location(): StreetLocation
    {
        return $this->location;
    }

    public function outcomeStatus(): ?OutcomeStatus
    {
        return $this->outcomeStatus;
    }
}