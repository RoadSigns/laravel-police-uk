<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods;

use Carbon\Carbon;

final class Event
{
    private string $title;
    private string $description;
    private string $address;
    private string $type;
    private Carbon $startDate;
    private Carbon $endDate;

    public function __construct(
        string $title,
        string $description,
        string $type,
        string $address,
        Carbon $startDate,
        Carbon $endDate
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->type = $type;
        $this->address = $address;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function address(): string
    {
        return $this->address;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function startDate(): Carbon
    {
        return $this->startDate;
    }

    public function endDate(): Carbon
    {
        return $this->endDate;
    }
}
