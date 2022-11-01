<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use RoadSigns\LaravelPoliceUK\Service\PoliceUKService;

$policeUk = new PoliceUKService(new Client());

// Get All Forces Summary
// https://data.police.uk/docs/method/forces/
$policeUk->forces()->all();

// Get By Force id
// https://data.police.uk/docs/method/force/
$policeUk->forces()->byForceId('leicestershire');

// Get Senior Officers by Force id
// https://data.police.uk/docs/method/senior-officers/
$policeUk->forces()->seniorOfficersByForceId('leicestershire');


// Get the last time the Crimes were updated
// https://data.police.uk/docs/method/crime-last-updated/
$policeUk->crimes()->lastUpdated();

// Get the Crime Categories
// https://data.police.uk/api/crime-categories
$policeUk->crimes()->categories();

// Get Crimes with no location
// https://data.police.uk/api/crimes-no-location?category=all-crime&force=leicestershire&date=2022-09
$policeUk->crimes()->crimeWithNoLocation('leicestershire');

// Get List of neighbourhoods for a Force id
// https://data.police.uk/api/leicestershire/neighbourhoods
$policeUk->neighbourhoods()->byForceId('leicestershire');