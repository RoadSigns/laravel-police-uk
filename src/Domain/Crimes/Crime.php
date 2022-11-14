<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Domain\Crimes;

use Carbon\Carbon;
use RoadSigns\LaravelPoliceUK\Domain\Crimes\ValueObject\Location;
use RoadSigns\LaravelPoliceUK\Domain\Crimes\ValueObject\OutcomeStatus;
use RoadSigns\LaravelPoliceUK\Domain\Crimes\ValueObject\UnknownLocation;

final class Crime
{
    private int $id;

    private string $persistentId;

    private string $category;

    private string $context;

    private Carbon $month;

    private UnknownLocation|Location $location;

    private OutcomeStatus $outcomeStatus;

    public function __construct(
        int $id,
        string $persistentId,
        string $category,
        string $context,
        Carbon $month,
        UnknownLocation|Location $location,
        OutcomeStatus $outcomeStatus
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

    public function location(): UnknownLocation|Location
    {
        return $this->location;
    }

    public function outcomeStatus(): OutcomeStatus
    {
        return $this->outcomeStatus;
    }
}
