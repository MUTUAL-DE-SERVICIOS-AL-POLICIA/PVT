<?php

namespace App;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Util;

class Record extends Model
{
    use Traits\EloquentGetTableNameTrait;

    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = ['user_id', 'role_id', 'record_type_id', 'recordable_id', 'recordable_type', 'action'];

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        if (Auth::user()) $this->user_id = Auth::user()->id;
    }

    public function getActionAttribute()
    {
        $action = "[{$this->record_type->display_name}] El usuario {$this->user->username} {$this->attributes['action']}. ";
        if ($this->recordable) {
            $action .= Util::translate($this->attributes['recordable_type']);
            $action .= ': ';
            switch (get_class($this->recordable)) {
                case 'App\Affiliate':
                    $action .= $this->recordable->full_name;
                    break;
                case 'App\Spouse':
                    $action .= $this->recordable->full_name;
                    break;
                case 'App\User':
                    $action .= $this->recordable->username;
                    break;
                case 'App\Role':
                    $action .= $this->recordable->display_name;
                    break;
                case 'App\Loan':
                    $action .= $this->recordable->code;
                    break;
                case 'App\LoanPayment':
                    $action .= $this->recordable->id;
                    break;
                case 'App\ProcedureType':
                    $action .= $this->recordable->name;
                    break;
                case 'App\Voucher':
                    $action .= $this->recordable->id;
                    break;
                case 'App\AidContribution':
                    $action .= $this->recordable->id;
                    break;
                case 'App\LoanContributionAdjust':
                    $action .= $this->recordable->id;
                    break;
                case 'App\Sismu':
                    $action .= $this->recordable->id;
                    break;
                case 'App\MovementFundRotatory':
                    $action .= $this->recordable->id;
                    break;
                case 'App\MovementConcept':
                    $action .= $this->recordable->id;
                break;

            }
        }
        unset($this['record_type'], $this['user'], $this['recordable']);
        return $action;
    }

    public function recordable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function record_type()
    {
        return $this->belongsTo(RecordType::class);
    }

    public function role(){
        return $this->belongsTo(Role::class);
    }
}
