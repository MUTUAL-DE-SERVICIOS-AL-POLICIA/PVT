<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ObservationForModule extends Model
{
    //
    public function observation_type()
    {
        return $this->belongsTo(ObservationType::class, 'observation_type_id');
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }
}