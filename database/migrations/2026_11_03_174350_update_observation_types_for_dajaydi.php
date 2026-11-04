
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateObservationTypesForDajaydi extends Migration
{
    private $name = 'Excluido - Tiene sentencias condenatorias ejecutoriadas por delitos cometidos contra la MUSERPOL o MUSEPOL.';
    private $newName = 'Presenta sentencia ejecutoriada.';
    private $moduleName = 'asesoramiento-jurídico';
    private $shortened = 'Sentencias condenatorias ejecutoriadas';
    private $NewShortened = 'Sentencias ejecutoriadas';

    public function up()
    {
        DB::transaction(function () {

            $module = DB::table('modules')
                ->where('name', $this->moduleName)
                ->first();

            $query = DB::table('observation_types')
                ->where('module_id', $module->id)
                ->where('name', $this->name)
                ->where('shortened', $this->shortened);

            $query->update([
                'name' => $this->newName,
                'shortened' => $this->NewShortened,
            ]);
        });
    }

    public function down()
    {
        DB::transaction(function () {

            $module = DB::table('modules')
                ->where('name', $this->moduleName)
                ->first();

            $query = DB::table('observation_types')
                ->where('module_id', $module->id)
                ->where('name', $this->newName)
                ->where('shortened', $this->NewShortened);

            $query->update([
                'name' => $this->name,
                'shortened' => $this->shortened,
            ]);
        });
    }
}
