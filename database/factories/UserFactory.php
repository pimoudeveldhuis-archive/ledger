<?php

use Faker\Generator as Faker;

$factory->define(App\Models\User::class, function (Faker $faker) {
    // Generate the email address
    $email = $faker->unique()->safeEmail;

    // Create the factory object
    return [
        'name' => $faker->name,
        'email' => $email,
        'email_hash' => hash('sha256', $email),
        'language' => 'nl',

        'publickey' => '',
        'secretkey' => '',
        'recoverykey' => '',
    ];
});