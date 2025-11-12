<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeAdjustToContributionAdjustsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            ALTER TABLE loan_contribution_adjusts
            DROP CONSTRAINT IF EXISTS loan_contribution_adjusts_type_adjust_check;
        ");

        DB::statement("
            ALTER TABLE loan_contribution_adjusts
            ADD CONSTRAINT loan_contribution_adjusts_type_adjust_check
            CHECK (type_adjust IN ('adjust', 'liquid', 'last_eco_com', 'reprogramming'));
        ");

        DB::statement("
            ALTER TABLE role_user 
            RENAME COLUMN user_type TO role_active;
        ");

        DB::statement("
            ALTER TABLE role_user
            ALTER COLUMN role_active TYPE boolean
            USING (
                CASE
                    WHEN role_active IN ('1', 'true', 't', 'yes', 'y', 'activo', 'active') THEN true
                    ELSE false
                END
            );
        ");

        DB::statement("
            UPDATE role_user
            SET role_active = false
            WHERE role_active IS NULL;
        ");

        DB::statement("
            ALTER TABLE role_user
            ALTER COLUMN role_active SET DEFAULT false;
        ");

        DB::statement("
            ALTER TABLE role_user
            ALTER COLUMN role_active SET NOT NULL;
        ");

        DB::statement("
            CREATE UNIQUE INDEX role_user_one_active_per_user
            ON role_user(user_id)
            WHERE role_active = true;
        ");

        DB::statement("insert into permissions (created_at, updated_at, name, display_name)
            values (now(), now(), 'registration-date-contract', 'Registrar fecha de firma de contrato');
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("
            ALTER TABLE loan_contribution_adjusts
            DROP CONSTRAINT IF EXISTS loan_contribution_adjusts_type_adjust_check;
        ");

        DB::statement("
            ALTER TABLE loan_contribution_adjusts
            ADD CONSTRAINT loan_contribution_adjusts_type_adjust_check
            CHECK (type_adjust IN ('adjust', 'liquid', 'last_eco_com'));
        ");
    }
}
