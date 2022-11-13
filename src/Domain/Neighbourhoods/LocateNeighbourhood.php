<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods;

final class LocateNeighbourhood
{
    private string $forceId;

    private string $neighbourhoodId;

    public function __construct(string $forceId, string $neighbourhoodId)
    {
        $this->forceId = $forceId;
        $this->neighbourhoodId = $neighbourhoodId;
    }

    public function forceId(): string
    {
        return $this->forceId;
    }

    public function neighbourhoodId(): string
    {
        return $this->neighbourhoodId;
    }
}
