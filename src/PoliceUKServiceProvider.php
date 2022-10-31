<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use RoadSigns\LaravelPoliceUK\Service\PoliceUKService;

final class PoliceUKServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(
            PoliceUKService::class,
            fn () => new PoliceUKService(new Client())
        );
    }
}
