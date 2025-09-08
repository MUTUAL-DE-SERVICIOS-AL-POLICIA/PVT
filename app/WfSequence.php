<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WfSequence extends Model
{
    protected $fillable = ['id', 'workflow_id', 'wf_state_current_id','wf_state_next_id', 'action',];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    public function current_state()
    {
        return $this->belongsTo(WfState::class, 'wf_state_current_id');
    }

    public function next_state()
    {
        return $this->belongsTo(WfState::class, 'wf_state_next_id');
    }
}