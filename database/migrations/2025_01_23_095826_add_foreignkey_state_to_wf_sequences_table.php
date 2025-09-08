<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignkeyStateToWfSequencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wf_sequences', function (Blueprint $table) {
            $table->foreign('wf_state_current_id')->references('id')->on('wf_states');
            $table->foreign('wf_state_next_id')->references('id')->on('wf_states');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wf_sequences', function (Blueprint $table) {
            $table->dropForeign(['wf_state_current_id']);
            $table->dropForeign(['wf_state_next_id']);
        });
    }
}