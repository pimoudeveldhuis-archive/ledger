<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Bank::class, function (Faker $faker) {
    return [
        'name' => $faker->company,
        'bic' => strtoupper($faker->lexify('??????')),
        'country' => strtoupper($faker->lexify('??')),
        'bankcode' => strtoupper($faker->lexify('????')),
    ];
});