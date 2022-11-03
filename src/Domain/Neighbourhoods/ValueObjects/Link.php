<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods\ValueObjects;

final class Link
{
    private string $title;

    private string $url;

    private string $description;

    public function __construct(string $title, string $url, string $description)
    {
        $this->title = $title;
        $this->url = $url;
        $this->description = $description;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function description(): string
    {
        return $this->description;
    }
}