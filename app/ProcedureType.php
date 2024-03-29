<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcedureType extends Model
{
    use SoftDeletes;
    use Traits\EloquentGetTableNameTrait;

    protected $fillable = [
        'module_id',
        'name',
        'second_name'
      ];
    public function procedure_modalities()
    {
        return $this->hasMany(ProcedureModality::class);
    }


    public function loan_destinies()
    {
        return $this->belongsToMany(LoanDestiny::class);
    }

    public function getWorkflowAttribute()
    {
        return RoleSequence::workflow($this->id);
    }
}