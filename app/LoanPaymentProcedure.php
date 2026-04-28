<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanPaymentProcedure extends Model
{
    use SoftDeletes;
    public $timestamps = true;
    public $guarded = ['id'];
    public $fillable = ['penal_payment',
                        'description',
                        ];
}
