<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
    return [
        //'name' => $faker->name,
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => strtolower($faker->email),
        'password' => bcrypt(str_random(10)),
        //'remember_token' => str_random(10),
    ];
});

$factory->define(App\Models\Contract::class, function (Faker\Generator $faker) {
    return [
        'start_date' => $faker->date($format = 'd.m.Y', $max = 'now'),
        'end_date' => $faker->date($format = 'd.m.Y', $max = 'now'),
        'description' => $faker->sentence(2),
        'company_id' => $faker->buildingNumber,
        'resp_staff_id' => $faker->buildingNumber,
        'real_signed_contract' => $faker->text,
       // 'status' => $faker->boolean($chanceOfGettingTrue = 60),
    ];
});

$factory->define(App\Models\Lead::class, function (Faker\Generator $faker) {
    return [
        'opportunity' => $faker->text(12),
        'company_name' => $faker->company,
        'customer' => $faker->randomDigit,
        'address' => $faker->address,
        'country_id' => $faker->randomDigit,
        'state_id' => $faker->buildingNumber,
        'city_id' => $faker->buildingNumber,
        'salesperson_id' => $faker->buildingNumber,
        'sales_team_id' => $faker->buildingNumber,
        'contact_name' => $faker->userName,
        'title' => $faker->title,
        'email' => strtolower($faker->email),
        'function' => $faker->text(10),
        'phone' => $faker->phoneNumber,
        'mobile' => $faker->phoneNumber,
        'fax' => $faker->phoneNumber,
        'tags' => $faker->randomDigit,
        'priority' => $faker->text(8),
        'internal_notes' => $faker->text(11),
        'assigned_partner_id' => $faker->randomDigit,
       // 'register_time' => $faker->time($format = 'H:i:s', $max = 'now'),
      //  'ip_address' => $faker->ipv4,
    ];
});

$factory->define(App\Models\Opportunity::class, function (Faker\Generator $faker) {
    return [
        'opportunity' => $faker->text(12),
        'stages' => $faker->text(10),
        'customer_id' => $faker->randomDigit,
        'expected_revenue' => $faker->numerify('#####'),
        'probability' => $faker->numerify('##'),
        'email' => strtolower($faker->email),
        'phone' => $faker->phoneNumber,
        'sales_person_id' => $faker->buildingNumber,
        'sales_team_id' => $faker->buildingNumber,
        'next_action' => $faker->date($format = Settings::get('date_format'), $max = 'now'),
        'next_action_title' => $faker->text(8),
        'expected_closing' => $faker->date($format = Settings::get('date_format'), $max = 'now'),
        'priority' => $faker->text(10),
        'tags' => $faker->text(10),
        'lost_reason' => $faker->text(10),
        'internal_notes' => $faker->text(13),
        'assigned_partner_id' => $faker->buildingNumber,
    ];
});

$factory->define(App\Models\Customer::class, function (Faker\Generator $faker) {
    return [
        'address' => $faker->address,
        'website' => $faker->url,
        'job_position' => $faker->text,
        //'phone' => $faker->phoneNumber,
        'mobile' => $faker->phoneNumber,
        'fax' => $faker->phoneNumber,
        'title' => $faker->text(13),
        'company_avatar' => $faker->text(10),
       // 'company' => $faker->buildingNumber,
        'sales_team_id' => $faker->buildingNumber,
    ];
});

$factory->define(App\Models\Salesteam::class, function (Faker\Generator $faker) {
    return [
        'salesteam' => $faker->name,
        'quotations' => $faker->text(10),
        'leads' => $faker->text(10),
        'opportunities' => $faker->text(10),
        'team_leader' => $faker->buildingNumber,
        'invoice_target' => $faker->randomDigit,
        'invoice_forecast' => $faker->randomDigit,
//        'actual_invoice' => $faker->randomDigit,
       // 'status' => $faker->boolean($chanceOfGettingTrue = 70),
        //'team_members' => $faker->userName,
        'notes' => $faker->text(10),
        //'register_time' => $faker->time($format = 'H:i:s', $max = 'now'),
        //'ip_address' => $faker->ipv4,
    ];
});
