<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Carbon\Carbon;
use App\Module;
use App\Affiliate;
use App\City;
use App\LoanState;
use App\PaymentType;
use App\Role;
use Faker\Generator as Faker;

$factory->define(App\Loan::class, function (Faker $faker) {
    $faker->addProvider(new \Faker\Provider\DateTime($faker));

    $module = Module::whereName('prestamos')->first();
    $affiliate = Affiliate::whereNull('date_death')->whereNull('reason_death')->whereNull('death_certificate_number')->limit(100)->get()->random();
    $procedure_type = $module->procedure_types->random();
    $procedure_modality = $procedure_type->procedure_modalities->random();
    if ($procedure_type->loan_destinies->count() == 0) {
        do {
            $destiny = factory(App\LoanDestiny::class)->create();
        } while ($destiny->procedure_type_id != $procedure_type->id);
    }
    $amount = intval($faker->numberBetween($procedure_type->interval->minimum_amount,$procedure_type->interval->maximum_amount) / 100) * 100;
    return [
        'disbursable_id' => $affiliate->id,
        'disbursable_type' => 'affiliates',
        'procedure_modality_id' => $module->procedure_types->random()->procedure_modalities->random()->id,
        'request_date' => $faker->dateTimeBetween($startDate = '-4 months', $endDate = '-2 months', $timezone = null),
        'parent_loan_id' => null,
        'parent_reason' => null,
        'disbursement_date' => $faker->dateTimeBetween($startDate = '-2 months', $endDate = 'now', $timezone = null),
        'amount_requested' => $amount,
        'city_id' => City::all()->random(),
        'interest_id' => $procedure_modality->current_interest,
        'state_id' => LoanState::whereName('Vigente')->first()->id,
        'amount_approved' => $amount,
        'loan_term' => $faker->numberBetween($procedure_type->interval->minimum_term,$procedure_type->interval->maximum_term),
        'payment_type_id' => PaymentType::whereName('Cheque')->first()->id,
        'destiny_id' => $procedure_type->loan_destinies->random()->id,
        'role_id' => Role::whereName('PRE-recepcion')->first()->id,
        'payable_liquid_calculated'=> $amount,
        'bonus_calculated' => $faker->numberBetween($min = 24, $max = 72),
        'liquid_qualification_calculated' => $amount,
        'indebtedness_calculated' => $faker->randomFloat($nbMaxDecimals = 2, $min = 50, $max = 90),
        'created_at' => $faker->dateTime($max = 'now'),
        'updated_at' => $faker->dateTime($max = 'now')
    ];
});
