<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Domain\Crimes\ValueObject;

final class Location
{
    private string $title;

    private string $type;

    private string $subtype;

    public function __construct(string $title, string $type, string $subtype)
    {
        $this->title = $title;
        $this->type = $type;
        $this->subtype = $subtype;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function subtype(): string
    {
        return $this->subtype;
    }
}
