<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods\ValueObjects;

final class ContactDetails
{
    private string $email;
    private string $telephone;
    private string $mobile;
    private string $web;
    private string $facebook;
    private string $twitter;
    private string $youtube;

    public function __construct(
        string $email,
        string $telephone,
        string $mobile,
        string $web,
        string $facebook,
        string $twitter,
        string $youtube
    ) {
        $this->email = $email;
        $this->telephone = $telephone;
        $this->mobile = $mobile;
        $this->web = $web;
        $this->facebook = $facebook;
        $this->twitter = $twitter;
        $this->youtube = $youtube;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function telephone(): string
    {
        return $this->telephone;
    }

    public function mobile(): string
    {
        return $this->mobile;
    }

    public function web(): string
    {
        return $this->web;
    }

    public function facebook(): string
    {
        return $this->facebook;
    }

    public function twitter(): string
    {
        return $this->twitter;
    }

    public function youtube(): string
    {
        return $this->youtube;
    }
}
