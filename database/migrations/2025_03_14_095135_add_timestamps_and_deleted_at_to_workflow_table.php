<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimestampsAndDeletedAtToWorkflowTable extends Migration
{
    public function up()
    {
        Schema::table('workflows', function (Blueprint $table) {
            if (!Schema::hasColumn('workflows', 'created_at')) {
                $table->timestamps(); // created_at y updated_at
            }
            if (!Schema::hasColumn('workflows', 'deleted_at')) {
                $table->softDeletes(); // deleted_at
            }
        });
    }

    public function down()
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->dropTimestamps();
            $table->dropSoftDeletes();
        });
    }
}
