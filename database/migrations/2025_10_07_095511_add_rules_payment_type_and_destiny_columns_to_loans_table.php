<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRulesPaymentTypeAndDestinyColumnsToLoansTable extends Migration
{
public function up()
    {
        // Quitar NOT NULL de las columnas
        DB::statement("ALTER TABLE loans ALTER COLUMN payment_type_id DROP NOT NULL;");
        DB::statement("ALTER TABLE loans ALTER COLUMN destiny_id DROP NOT NULL;");

        // Asegurar nombres únicos de constraints: primero eliminar si existieran
        DB::statement("ALTER TABLE loans DROP CONSTRAINT IF EXISTS loans_payment_type_id_null_repro;");
        DB::statement("ALTER TABLE loans DROP CONSTRAINT IF EXISTS loans_destiny_id_null_repro;");
        DB::statement("ALTER TABLE loans DROP CONSTRAINT IF EXISTS uq_loan;");

        // Agregar CHECKs
        // Permitir NULL solo si parent_reason indica reprogramación (con y sin tilde)
        DB::statement(<<<'SQL'
            ALTER TABLE loans
            ADD CONSTRAINT loans_payment_type_id_null_repro
            CHECK (
                parent_reason IN ('REPROGRAMACION','REPROGRAMACIÓN') OR payment_type_id IS NOT NULL
            )
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE loans
            ADD CONSTRAINT loans_destiny_id_null_repro
            CHECK (
                parent_reason IN ('REPROGRAMACION','REPROGRAMACIÓN') OR destiny_id IS NOT NULL
            )
        SQL);

        DB::statement("
            CREATE UNIQUE INDEX ux_loans_code_nonrepro
            ON loans (code)
            WHERE COALESCE(parent_reason,'') NOT IN ('REPROGRAMACIÓN','REPROGRAMACION')");

        DB::statement("
            CREATE UNIQUE INDEX ux_loans_code_repro_active
            ON loans (code)
            WHERE COALESCE(parent_reason,'') IN ('REPROGRAMACIÓN','REPROGRAMACION')
            AND (deleted_at IS NULL OR state_id <> 2)"
        );

        DB::statement("
            ALTER TABLE loans
            ADD CONSTRAINT ck_loans_code_rprefix_implies_repro
            CHECK (
                (COALESCE(code,'') NOT LIKE 'R-%')
                OR (COALESCE(parent_reason,'') IN ('REPROGRAMACIÓN','REPROGRAMACION'))
            )");

        // Eliminar columna property_id si existe
        DB::statement("ALTER TABLE loans DROP COLUMN IF EXISTS property_id;");

        // Eliminar tabla loan_properties si existe
        DB::statement("DROP TABLE IF EXISTS loan_properties;");
    }

    public function down()
    {
        // Quitar los CHECKs añadidos
        DB::statement("ALTER TABLE loans DROP CONSTRAINT IF EXISTS loans_payment_type_id_null_repro;");
        DB::statement("ALTER TABLE loans DROP CONSTRAINT IF EXISTS loans_destiny_id_null_repro;");
        DB::statement("ALTER TABLE loans DROP CONSTRAINT IF EXISTS ck_loans_code_rprefix_implies_repro;");
        DB::statement('DROP INDEX IF EXISTS ux_loans_code_nonrepro;');
        DB::statement('DROP INDEX IF EXISTS ux_loans_code_repro_active;');

        // Volver a imponer NOT NULL (ajusta si ya hay datos nulos)
        DB::statement("ALTER TABLE loans ALTER COLUMN payment_type_id SET NOT NULL;");
        DB::statement("ALTER TABLE loans ALTER COLUMN destiny_id SET NOT NULL;");
        DB::statement('ALTER TABLE loans ADD CONSTRAINT loans_code_unique UNIQUE (code);');
    }
}
