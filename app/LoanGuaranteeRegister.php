<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanGuaranteeRegister extends Model
{
    public $timestamps = true;
    use SoftDeletes;
    public $guarded = ['id'];
    protected $fillable = [
        'user_id',
        'role_id',
        'loan_id',
        'affiliate_id',
        'guarantable_type',
        'guarantable_id',  
        'amount',
        'period_date',
        'database_name',
        'loan_code_guarantee',
    ]; 
    public function Loan()
    {
        return $this->belongsTo(Loan::class);
    }
    // add records
    public function records()
    {
        return $this->morphMany(Record::class, 'recordable');
    }
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }
}
