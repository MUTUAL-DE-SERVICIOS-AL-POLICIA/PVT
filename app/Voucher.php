<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon;

class Voucher extends Model
{
    use SoftDeletes;
    use Traits\EloquentGetTableNameTrait;
    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = ['voucher_type_id','code','total','payment_date', 'payable_id', 'payable_type', 'bank_pay_number', 'description'];

    function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (!$this->code) {
            $latest_vouchers = DB::table('vouchers')->orderBy('created_at', 'desc')->limit(1)->first();
            if (!$latest_vouchers) $latest_vouchers = (object)['id' => 0];
            $this->code = implode(['TRANS', str_pad($latest_vouchers->id + 1, 6, '0', STR_PAD_LEFT), '-', Carbon::now()->year]);
        }
    }

    public function payable()
    {
        return $this->morphTo();
    }


    public function voucher_type()
    {
        return $this->belongsTo(VoucherType::class);
    }

    public function records()
    {
        return $this->morphMany(Record::class, 'recordable')->latest('updated_at');
    }
}
