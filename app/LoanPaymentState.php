<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoanPaymentState extends Model
{
    public $timestamps = false;
    public $guarded = ['id'];
    public $fillable = ['name', 'description'];

    public function loan_payments()
	{
		return $this->hasMany(LoanPayment::class);
    }
}
