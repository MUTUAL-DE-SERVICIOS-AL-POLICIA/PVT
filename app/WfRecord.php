<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WfRecord extends Model
{
    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = ['user_id', 'record_type_id', 'wf_states_id', 'recordable_id', 'recordable_type', 'message', 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
