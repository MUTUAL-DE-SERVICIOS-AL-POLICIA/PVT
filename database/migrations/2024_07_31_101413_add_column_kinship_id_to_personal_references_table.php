<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnKinshipIdToPersonalReferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('personal_references', function (Blueprint $table) {
            $table->unsignedBigInteger('kinship_id')->nullable();
            $table->foreign('kinship_id')->references('id')->on('kinships')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('personal_references', function (Blueprint $table) {
            $table->dropForeign(['kinship_id']);
            $table->dropColumn('kinship_id');
        });
    }
}
