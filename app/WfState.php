<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WfState extends Model
{
    protected $fillable = ['id', 'module_id', 'role_id','name', 'first_shortened',];

    public function roles()
    {
        return $this->hasMany(Role::class, 'wf_states_id', 'id');
    }

    public function currentSequences()
    {
        return $this->hasMany(WfSequence::class, 'wf_state_current_id', 'id');
    }

    public function nextSequences()
    {
        return $this->hasMany(WfSequence::class, 'wf_state_next_id', 'id');
    }
}