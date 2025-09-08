<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateViewLoanGuarantorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
        CREATE OR REPLACE VIEW public.view_loan_guarantors
        AS
        SELECT l.id AS id_loan,
           l.code AS code_loan,
           a.id AS id_affiliate,
           a.identity_card AS identity_card_affiliate,
           a.registration AS registration_affiliate,
           a.last_name AS last_name_affiliate,
           a.mothers_last_name AS mothers_last_name_affiliate,
           a.first_name AS first_name_affiliate,
           a.second_name AS second_name_affiliate,
           a.surname_husband AS surname_husband_affiliate,
           concat_full_name(a.first_name, a.second_name, a.last_name, a.mothers_last_name, a.surname_husband) AS full_name_affiliate,
           ca.first_shortened AS city_exp_first_shortened_affiliate,
           ast.name AS state_type_affiliate,
           afs.name AS state_affiliate,
           pe.name AS pension_entity_affiliate,
           lg.identity_card AS identity_card_guarantor,
           lg.registration AS registration_guarantor,
           lg.last_name AS last_name_guarantor,
           lg.mothers_last_name AS mothers_last_name_guarantor,
           lg.first_name AS first_name_guarantor,
           lg.second_name AS second_name_guarantor,
           lg.surname_husband AS surname_husband_guarantor,
           concat_full_name(lg.first_name, lg.second_name, lg.last_name, lg.mothers_last_name, lg.surname_husband) AS full_name_guarantor,
           cs.first_shortened AS city_exp_first_shortened_guarantor,
           pm.name AS sub_modality_loan,
           pm.shortened AS shortened_sub_modality_loan,
           pt.second_name AS modality_loan,
           pt.name AS name_modality_loan,
           l.amount_approved AS amount_approved_loan,
           lg.quota_treat AS quota_loan,
           ls.name AS state_loan,
           c.name AS city_loan,
           u.username AS user_loan,
           wf.name AS name_state_loan,
           l.loan_term,
           l.disbursement_date AS disbursement_date_loan,
           l.request_date AS request_date_loan,
           l.validated AS validated_loan,
           lg.type AS type_affiliate_spouse_loan,
           l.guarantor_amortizing AS guarantor_amortizing_loan
          FROM loans l
            JOIN loan_guarantors lg ON l.id = lg.loan_id
            JOIN affiliates a ON a.id = lg.affiliate_id
            JOIN cities c ON l.city_id = c.id
            JOIN procedure_modalities pm ON pm.id = l.procedure_modality_id
            JOIN procedure_types pt ON pm.procedure_type_id = pt.id
            JOIN loan_states ls ON ls.id = l.state_id
            LEFT JOIN cities cs ON lg.city_identity_card_id = cs.id
            LEFT JOIN cities ca ON a.city_identity_card_id = ca.id
            JOIN affiliate_states afs ON a.affiliate_state_id = afs.id
            JOIN affiliate_state_types ast ON afs.affiliate_state_type_id = ast.id
            LEFT JOIN users u ON l.user_id = u.id
            JOIN wf_states wf ON l.wf_states_id = wf.id
            LEFT JOIN pension_entities pe ON pe.id = a.pension_entity_id;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW view_loan_guarantors;");
    }
}
