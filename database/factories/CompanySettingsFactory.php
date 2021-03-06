<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\CompanySetting::class, function (Faker $faker) {
    return [
        'invoice_prefix'     => $faker->domainWord,
        'quote_prefix'       => $faker->domainWord,
        'invoice_conditions' => $faker->realText($maxNbChars = 200, $indexSize = 2),
        'quote_conditions'   => $faker->realText($maxNbChars = 200, $indexSize = 2),
        'tax'                => $faker->numberBetween($min = 1, $max = 100),
        'company_id'         => function () {
            return factory(\App\Models\Company::class)->create()->id;
        },
    ];
});
