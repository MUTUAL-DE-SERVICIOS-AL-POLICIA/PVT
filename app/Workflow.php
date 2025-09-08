<?php

namespace App;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    use SoftDeletes;
    protected $fillable = ['id', 'module_id', 'name', 'shortened'];

    // Relación: Un Workflow pertenece a un módulo
    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    // Relación: Un Workflow tiene muchas secuencias de flujo
    public function wf_sequences()
    {
        return $this->hasMany(WfSequence::class);
    }

    // Método actualizado para manejar el flujo
    public function flow($current_state_id)
    {
        if (is_null($current_state_id)) {
            throw new \InvalidArgumentException("El parámetro 'current_state_id' es requerido.");
        }
        return [
            'current' => $current_state_id,
            'previous' => $this->wf_sequences()
                ->where('wf_state_next_id', $current_state_id)
                ->pluck('wf_state_current_id')
                ->toArray(), // Devuelve un array de IDs de los estados anteriores
            'next' => $this->wf_sequences()
                ->where('wf_state_current_id', $current_state_id)
                ->pluck('wf_state_next_id')
                ->toArray(), // Devuelve un array de IDs de los estados siguientes
        ];
    }

    // add records
    public function records()
    {
        return $this->morphMany(Record::class, 'recordable')->latest('updated_at');
    }

    public function procedure_modality()
    {
        return $this->hasMany(ProcedureModality::class, 'workflow_id');
    }
}