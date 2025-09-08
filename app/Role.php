<?php

namespace App;

use Laratrust\Models\LaratrustRole;
use Fico7489\Laravel\Pivot\Traits\PivotEventTrait;

class Role extends LaratrustRole
{
    use PivotEventTrait;

    public $timestamps = true;
    protected $hidden = ['pivot'];
    public $guarded = ['id'];
    protected $fillable = ['module_id', 'name', 'display_name', 'sequence_number', 'wf_states_id'];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions')->withTimestamps();
    }

    public function records()
    {
        return $this->morphMany(Record::class, 'recordable');
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function wf_states()
    {
        return $this->belongsTo(WfState::class, 'wf_states_id', 'id');
    }
}