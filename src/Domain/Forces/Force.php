<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Domain\Forces;

use Illuminate\Support\Collection;

final class Force
{
    private string $id;

    private string $name;

    private string $url;

    private string $description;

    private string $telephone;

    public function __construct(
        string $id,
        string $name,
        string $url,
        string $description,
        string $telephone,
        array $engagementMethods
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->url = $url;
        $this->description = strip_tags($description);
        $this->telephone = $telephone;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function telephone(): string
    {
        return $this->telephone;
    }
}
