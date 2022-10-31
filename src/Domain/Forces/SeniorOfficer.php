<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Domain\Forces;

final class SeniorOfficer
{
    private string $name;

    private string $rank;

    private string $bio;

    public function __construct(string $name, string $rank, string $bio)
    {
        $this->name = $name;
        $this->rank = $rank;
        $this->bio = $bio;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function rank(): string
    {
        return $this->rank;
    }

    public function bio(): string
    {
        return $this->bio;
    }
}