<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\ProcedureDocument;
use App\LoanModalityParameter;
use App\LoanInterest;
use App\LoanProcedure;

class ProcedureModality extends Model
{
    use Traits\EloquentGetTableNameTrait;

    public $timestamps = false;
    // protected $hidden = ['pivot'];
    public $guarded = ['id'];
    protected $fillable = [
        'procedure_type_id',
        'name',
        'shortened',
        'is_valid',
        'workflow_id'
    ];

    public function getLoanModalityParameterAttribute()
    {
        $loan_procedure = LoanProcedure::where('is_enable', true)->first()->id;
        return LoanModalityParameter::where('procedure_modality_id', $this->id)->where('loan_procedure_id', $loan_procedure)->first();
    }

    public function documents()
    {
        return $this->belongsToMany(ProcedureDocument::class, 'procedure_requirements')->withPivot(['number']);
    }

    public function required_documents()
    {
        return $this->documents()->wherePivot('number', '!=', 0)->whereNull('deleted_at');
    }

    public function optional_documents()
    {
        return $this->documents()->wherePivot('number', 0)->whereNull('deleted_at');
    }

    public function getRequirementsListAttribute()
    {
        return [
            'required' => $this->required_documents->sortBy('pivot.number')->groupBy(['pivot.number'])->each(function($list) {
                $list->each(function($element) {
                    unset($element['pivot']);
                });
            })->values(),
            'optional' => $this->optional_documents->each(function($element) {
                unset($element['pivot']);
            })
        ];
    }

    public function procedure_type()
    {
        return $this->belongsTo(ProcedureType::class);
    }

    public function loan_interests()
    {
        return $this->hasMany(LoanInterest::class)->latest();
    }

    public function getCurrentInterestAttribute()
    {
        return $this->loan_interests()->first();
    }

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }
}