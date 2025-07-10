<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LoanInterestRefSeasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $modality_id = DB::table('procedure_modalities')
            ->where('shortened', 'REF-EST-PAS-CON')
            ->value('id');

        DB::table('loan_interests')->updateOrInsert(
            ['procedure_modality_id' => $modality_id],
            [
                'annual_interest' => 15,
                'penal_interest' => 6,
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ]
        );
    }
}