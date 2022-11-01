<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Domain\Crimes\ValueObject;

use Carbon\Carbon;

final class OutcomeStatus
{
    private string $category;

    private Carbon $date;

    public function __construct(string $category, Carbon $date)
    {
        $this->category = $category;
        $this->date = $date;
    }

    public function category(): string
    {
        return $this->category;
    }

    public function date(): Carbon
    {
        return $this->date;
    }
}
