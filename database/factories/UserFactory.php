<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
	    'username' => $faker->unique()->userName,
        'email' => $faker->unique()->safeEmail,
        'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
        'remember_token' => str_random(10),
	    'info' => $faker->word
    ];
});

$factory->define(App\Task::class, function (Faker $faker) {
    return [
    	'name' => $faker->word,
        'description' => $faker->word,
        'deadline' => '2018-05-15 00:00:00',
	    'status' => false,
	    'privacy' => 0,
	    'user_id' => function (){
    	return factory(\App\User::class)->create()->id;
	    }
    ];
});

$factory->state(\App\Task::class, 'private', function (Faker $faker){
	return [
		'privacy' => 1
	];
});


$factory->define(App\Invitation::class, function (Faker $faker) {
    return [
    	'status' => 'pending',

    ];
});
