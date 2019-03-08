<?php

use Faker\Generator as Faker;

$factory->define(App\Models\User\Account::class, function (Faker $faker) {
    $user = factory(\App\Models\User::class)->create();
    $bank = factory(\App\Models\Bank::class)->create();

    $account = $bank->country.$faker->randomNumber(2).$bank->bankcode.$faker->randomNumber(9);

    return [
        'user_id' => $user->id,
        'bank_id' => $bank->id,
        'account' => $account,
        'account_hash' => hash('sha256', $account),
        'name' => $faker->name,
        'description' => $faker->sentence(4),
    ];
});