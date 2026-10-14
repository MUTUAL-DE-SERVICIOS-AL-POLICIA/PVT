<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddRegisterToLoanTrackingTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function () {
            $exists = DB::table('loan_tracking_types')
                ->where('name', 'Visita Domiciliaria')
                ->exists();
            
            if (!$exists) {
                DB::table('loan_tracking_types')
                    ->where('sequence_number', '>=', 2)
                    ->increment('sequence_number');

                DB::table('loan_tracking_types')->insert([
                    'sequence_number' => 2,
                    'name' => 'Visita Domiciliaria',
                    'is_valid' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::transaction(function () {
            $LoanTrackingType = DB::table('loan_tracking_types')
                ->where('name', 'Visita Domiciliaria')
                ->where('sequence_number', 2)
                ->first();

            if (!$LoanTrackingType) return;
            
            if (DB::table('loan_trackings')
                ->where('loan_tracking_type_id', $LoanTrackingType->id)
                ->exists()) {
                return;
            }

            DB::table('loan_tracking_types')
                ->where('id', $LoanTrackingType->id)
                ->delete();

            DB::table('loan_tracking_types')
                ->where('sequence_number', '>', 2)
                ->decrement('sequence_number');
        });
    }
}
