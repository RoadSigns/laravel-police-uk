<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Domain\Crimes;

final class Category
{
    private string $url;

    private string $name;

    public function __construct(string $url, string $name)
    {
        $this->url = $url;
        $this->name = $name;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
