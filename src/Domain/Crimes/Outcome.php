<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Domain\Crimes;

use Illuminate\Support\Collection;

final class Outcome
{
    private Crime $crime;

    private Collection $outcomes;

    public function __construct(Crime $crime, Collection $outcomes)
    {
        $this->crime = $crime;
        $this->outcomes = $outcomes;
    }

    public function crime(): Crime
    {
        return $this->crime;
    }

    public function outcomes(): Collection
    {
        return $this->outcomes;
    }
}
