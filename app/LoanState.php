<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoanState extends Model
{
    public $timestamps = false;
    public $guarded = ['id'];
    public $fillable = ['name', 'description'];

    public function loans()
	{
		return $this->hasMany(Loan::class);
    }

    /*public function loan_payments()
	{
		return $this->hasMany(Loan::class);
    }*/
      
}