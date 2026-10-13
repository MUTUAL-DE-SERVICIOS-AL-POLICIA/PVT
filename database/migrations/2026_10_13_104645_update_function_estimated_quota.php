<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateFunctionEstimatedQuota extends Migration
{
    /**
     * Actualiza el cálculo de cuota fija
     * según el reglamento asociado al préstamo.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
            CREATE OR REPLACE FUNCTION public.estimated_quota(id_loan bigint)
            RETURNS numeric 
            LANGUAGE plpgsql
            AS \$function\$
            DECLARE
                v_amount_approved numeric;
                v_loan_term integer;
                v_annual_interest numeric;
                v_numerator numeric;
                v_denominator numeric;
                v_loan_month_term numeric;
                v_rate numeric;
                v_estimated_quota numeric;
            BEGIN
                SELECT l.amount_approved, l.loan_term, li.annual_interest, lgp.numerator, lgp.denominator, lmp.loan_month_term
                INTO v_amount_approved, v_loan_term, v_annual_interest, v_numerator, v_denominator, v_loan_month_term
                FROM loans l
                JOIN loan_interests li ON li.id = l.interest_id
                JOIN loan_global_parameters lgp ON lgp.loan_procedure_id = l.loan_procedure_id
                JOIN loan_modality_parameters lmp ON lmp.procedure_modality_id = l.procedure_modality_id
                AND lmp.loan_procedure_id = l.loan_procedure_id
                WHERE l.id = id_loan;

                -- Tasa según el reglamento de préstamo
                v_rate := ((v_annual_interest * (v_numerator / v_denominator))/ 100) / (12 / v_loan_month_term);

                -- Cálculo de la cuota fija
                IF v_rate = 0 THEN
                    v_estimated_quota := v_amount_approved / v_loan_term;
                ELSE
                    v_estimated_quota := v_rate * v_amount_approved / (1 - 1 / pow(1 + v_rate, v_loan_term));
                END IF;

                RETURN ROUND(v_estimated_quota, 4);
            END;
                \$function\$;
        ");
    }

    /**
     * Restaura la función original.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("
            CREATE OR REPLACE FUNCTION public.estimated_quota(id_loan bigint)
            RETURNS numeric
            LANGUAGE plpgsql
            AS \$function\$
                DECLARE
                    estimated_quota numeric;
                BEGIN
                    SELECT (monthly_current_interest(id_loan) * l.amount_approved / (1 - 1 / pow((1 + monthly_current_interest(id_loan)), l.loan_term)))
                    INTO estimated_quota
                    FROM loans l
                    WHERE l.id = id_loan;

                    RETURN round(estimated_quota, 4);
                END;
            \$function\$;
        ");
    }
}
