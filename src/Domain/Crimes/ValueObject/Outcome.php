<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Domain\Crimes\ValueObject;

use Carbon\Carbon;

final class Outcome
{
    private OutcomeCategory $category;

    private Carbon $date;

    private ?string $personId;

    public function __construct(OutcomeCategory $category, Carbon $date, ?string $personId)
    {
        $this->category = $category;
        $this->date = $date;
        $this->personId = $personId;
    }

    public function category(): OutcomeCategory
    {
        return $this->category;
    }

    public function date(): Carbon
    {
        return $this->date;
    }

    public function personId(): ?string
    {
        return $this->personId;
    }
}
