<?php

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

