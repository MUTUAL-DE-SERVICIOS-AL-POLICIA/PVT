<?php

namespace App;

use Fico7489\Laravel\Pivot\Traits\PivotEventTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laratrust\Traits\LaratrustUserTrait;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use PivotEventTrait, LaratrustUserTrait, Notifiable;
    use Traits\EloquentGetTableNameTrait;
    use Traits\RelationshipsTrait;

    public $timestamps = true;
    public $guarded = ['id'];

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $fillable = ['first_name', 'last_name', 'username', 'password', 'active', 'position', 'city_id', 'phone'];

    /**
    * The attributes that should be hidden for arrays.
    *
    * @var array
    */
    protected $hidden = ['password', 'status', 'remember_token'];

    /**
    * Get the identifier that will be stored in the subject claim of the JWT.
    *
    * @return mixed
    */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
    * Return a key value array, containing any custom claims to be added to the JWT.
    *
    * @return array
    */
    public function getJWTCustomClaims()
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'user' => $this->full_name,
            'roles' => array_values(array_filter($this->roles()->where('module_id',6)->pluck('name')->toArray())),
            'permissions' => array_values(array_filter($this->allPermissions()->pluck('name')->toArray())),
            'city_id' => $this->city ? $this->city->id : null,
        ];
    }

    public function getFullNameAttribute()
    {
        return mb_strtoupper(preg_replace('/[[:blank:]]+/', ' ', join(' ', [$this->first_name, $this->last_name])));
    }

    public function getFirstNameAttribute($value)
    {
        if (!$value) return '';
        return $value;
    }

    public function getLastNameAttribute($value)
    {
        if (!$value) return '';
        return $value;
    }

    public function getPositionAttribute($value)
    {
        if (!$value) return '';
        return $value;
    }

    public function getModulesAttribute()
    {
        return $this->roles()->pluck('module_id')->unique()->toArray();
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function wf_states()
    {
        return $this->belongsToMany(WfState::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions');
    }

    public function affiliate_records()
    {
        return $this->hasMany(AffiliateRecord::class);
    }

    public function affiliate_records_pvt()
    {
        return $this->hasMany(AffiliateRecordPVT::class);
    }

    public function eco_com_records()
    {
        return $this->hasMany(EcoComRecord::class);
    }

    public function procedure_records()
    {
        return $this->hasMany(ProcedureRecord::class);
    }

    public function quota_aid_records()
    {
        return $this->hasMany(QuotaAidRecord::class);
    }

    public function ret_fun_records()
    {
        return $this->hasMany(RetFunRecord::class);
    }

    public function sequences_records()
    {
        return $this->hasMany(SequencesRecord::class);
    }

    public function wf_records()
    {
        return $this->hasMany(WfRecord::class);
    }

    public function wf_records_bck()
    {
        return $this->hasMany(WfRecordBck::class);
    }

    public function getHasActionsAttribute()
    {
        if ($this->affiliate_records()->first() || $this->affiliate_records_pvt()->first() || $this->eco_com_records()->first() || $this->procedure_records()->first() || $this->quota_aid_records()->first() || $this->ret_fun_records()->first() || $this->sequences_records()->first() || $this->wf_records()->first() || $this->wf_records_bck()->first() || $this->actions()->first() || $this->observations()->first()) {
            return true;
        } else {
            return false;
        }
    }
    // add records 
    public function actions()
    {
        return $this->hasMany(Record::class);
    }

    public function records()
    {
        return $this->morphMany(Record::class, 'recordable');
    }

    public function observations()
    {
        return $this->hasMany(Observation::class);
    }

    public function loan_tracking() {
        return $this->hasMany(LoanTracking::class);
    }
}