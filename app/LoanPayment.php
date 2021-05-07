<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\CarbonImmutable;
use Carbon;
use Util;
use Illuminate\Support\Facades\DB;

class LoanPayment extends Model
{
    use Traits\EloquentGetTableNameTrait;
    //protected $primaryKey = null;
    //public $incrementing = false;
    public $timestamps = true;
    //public $guarded = ['id'];
    use SoftDeletes;
    public $fillable = [
        'loan_id',
        'code',
        'procedure_modality_id',
        'estimated_date',
        'quota_number',
        'estimated_quota',
        'penal_remaining',
        'penal_payment',
        'interest_remaining',
        'interest_payment',
        'capital_payment',
        'interest_accumulated',
        'penal_accumulated',
        'previous_balance',
        'previous_payment_date',
        'state_id',
        'voucher',
        'paid_by',
        'role_id',
        'affiliate_id',
        'amortization_type_id',
        'validated',
        'description',
        'user_id'

    ];

    function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (!$this->code) {
            //$latest_payments = DB::table('loan_payments')->orderBy('created_at', 'desc')->limit(1)->first();
            $latest_payments = DB::table('loan_payments')->orderBy('id', 'desc')->latest()->first();
            if (!$latest_payments) $latest_payments = (object)['id' => 0];
            $this->code = implode(['PAY', str_pad($latest_payments->id + 1, 6, '0', STR_PAD_LEFT), '-', Carbon::now()->year]);
        }
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function payment_type()
    {
        return $this->belongsTo(PaymentType::class);
    }

    public function amortization_type()
    {
        return $this->belongsTo(AmortizationType::class);
    }

    
    public function voucher_treasury()
    {
        return $this->morphOne(Voucher::class, 'payable')->latest('updated_at');
    }

    public function modality()
    {
        return $this->belongsTo(ProcedureModality::class,'procedure_modality_id', 'id');
    }

    public static function days_interest(Loan $loan, $estimated_date = null)
    {
        $interest = [
            'penal' => 0,
            'accumulated' => 0,
            'current' => 0,
            'accumulated_amount' => 0
        ];
        if ($loan->balance == 0) return (object)$interest;
        $estimated_date = CarbonImmutable::parse($estimated_date ?? CarbonImmutable::now()->toDateString());
        //$latest_quota = $loan->payments()->first();

        //comentado para volver plan de pagos estatico
        //if (!$latest_quota) {
            $payment_date = $loan->disbursement_date;
            $latest_quota = (object)[
                'penal_remaining' => 0,
                'accumulated_remaining' => 0
            ];
            if (!$payment_date) return (object)$interest;
        /*} else {
            $payment_date = Carbon::parse($latest_quota->estimated_date)->toDateString();
        }*/
        $payment_date = CarbonImmutable::parse($payment_date);
        if ($estimated_date->lessThan($payment_date)) 
            return (object)$interest;
        $diff_days = $estimated_date->diffInDays($payment_date)+1;
        if ($estimated_date->diffInMonths($payment_date) == 0) {
            $interest['current'] = $diff_days;
            $interest['accumulated'] = 0;
        } else {
            $interest['current'] = $estimated_date->day;
        }
        if ($diff_days > $interest['current']){ 
            $interest['accumulated'] = $diff_days - $interest['current'];
            if($interest['accumulated'] > 0)
                $interest['accumulated_amount'] = self::interest_by_days($interest['accumulated'], $loan->interest->annual_interest, $loan->amount_approved); //$loan->balance caso dinamico
        }
        $interest['accumulated'] += $latest_quota->accumulated_remaining;
        if ($interest['accumulated'] >= 90) {
            $interest['penal'] = $interest['accumulated'];
        }
        $interest['penal'] += $latest_quota->penal_remaining;
        // Máximo 360 días para el cálculo de interés
        foreach ($interest as $key => $value) {
            if ($value > 360) $interest[$key] = 360;
        }
        return (object)$interest;
    }

    // Unión de pagos con el mismo número de cuota
    public function merge($payments)
    {
        $merged = new LoanPayment();
        foreach ($payments as $key => $payment) {
            if ($key == 0) {
                $merged = $payment;
            } else {
                $merged->penal_payment += $payment->penal_payment;
                $merged->accumulated_payment += $payment->accumulated_payment;
                $merged->capital_payment += $payment->capital_payment;
                $merged->interest_payment += $payment->interest_payment;
            }
            if (!next($payments)) {
                $merged->pay_date = $payment->pay_date;
                $merged->estimated_date = $payment->estimated_date;
                $merged->penal_remaining = $payment->penal_remaining;
                $merged->interest_remaining = $payment->interest_remaining;
            }
        }
        unset($merged->affiliate_id, $merged->payment_type, $merged->voucher_number, $merged->receipt_number, $merged->description, $merged->created_at, $merged->updated_at);
        return $merged;
    }

    public static function quota_date(Loan $loan, $first = false)
    {
        $quota = 1;
        $latest_quota = $loan->last_payment_validated;
        $estimated_date = Carbon::now()->endOfMonth();
        if (!$latest_quota || $first) {
            $payment_date = $loan->disbursement_date ? $loan->disbursement_date : $loan->request_date;
            //$payment_date = $loan->disbursement_date;
            $payment_date = CarbonImmutable::parse($payment_date);
            if ($estimated_date->lessThan($payment_date) || $first) $estimated_date = $payment_date->endOfMonth();
            if ($payment_date->day >= LoanGlobalParameter::latest()->first()->offset_interest_day && $estimated_date->diffInMonths($payment_date) == 0) {
                $estimated_date = $payment_date->startOfMonth()->addMonth()->endOfMonth();
            }
        } else {
            $estimated_date = Carbon::parse($latest_quota->estimated_date)->startOfMonth()->addMonth()->endOfMonth();
            $quota = $latest_quota->quota_number + 1;
        }
        return (object)[
            'previous_payment_date' => $latest_quota ? $latest_quota->estimated_date : $loan->disbursement_date,
            'date' => $estimated_date->toDateString(),
            'quota' => $quota
        ];
    }

    public function state()
    {
      return $this->belongsTo(LoanState::class, 'state_id','id');
    }

    public function records()
    {
        return $this->morphMany(Record::class, 'recordable')->latest('updated_at');
    }
    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public static function registry_payment(Loan $loan, $estimated_date, $description, $procedure_modality, $voucher, $paid_by, $payment_type, $percentage_quota, $affiliate_id, $state_id = null, $validated_payment = false)
    {
        $payment = $loan->next_payment2($affiliate_id, $estimated_date, $paid_by, $procedure_modality, $percentage_quota); //$percentage_quota
        $payment->description = $description;
        if($state_id == null) $payment->state_id = LoanState::whereName('Pendiente de Pago')->first()->id;
        if($state_id !== null)$payment->state_id = $state_id;
        $payment->role_id = Role::whereName('PRE-cobranzas')->first()->id;
        $payment->procedure_modality_id = $procedure_modality;
        $payment->validated = $validated_payment;
        //$payment->affiliate_id = $loan->disbursable->id;
        $payment->affiliate_id = $affiliate_id;
        $payment->voucher = $voucher;
        $payment->paid_by = $paid_by;
        $payment->amortization_type_id = $payment_type->id;
        $loan_payment = $loan->payments()->create($payment->toArray());
        return $loan_payment;
    }

    public static function interest_by_days($days, $annual_interest, $balance){
            return (($annual_interest/100)/360)*$days*$balance;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function users(){
        return $this->hasOne(User::class,'id','id');
    }

    public static function days_interest2(Loan $loan, $estimated_date = null){
        $interest = [
            'penal' => 0,
            'current' => 0,
            'penal_generated' => 0,
            'current_generated' => 0,
            'penal_accumulated' => 0,
            'interest_accumulated' => 0,
        ];
        if($loan->balance = 0) return $interest;
            $estimated_date = CarbonImmutable::parse($estimated_date ?? CarbonImmutable::now()->toDateString());
        //$latest_quota = $loan->payments()->first();
        $latest_quota = $loan->last_payment_validated;
        if (!$latest_quota) {
            $payment_date = $loan->disbursement_date;
            if (!$payment_date) return (object)$interest;
        } else {
            $payment_date = Carbon::parse($latest_quota->estimated_date)->toDateString();
            $interest['penal_accumulated'] = $latest_quota->penal_accumulated;
            $interest['interest_accumulated'] = $latest_quota->interest_accumulated;
        }
        //return $latest_quota;
        $payment_date = CarbonImmutable::parse($payment_date);
        if ($estimated_date->lessThan($payment_date)) {
            return (object)$interest;}
        $diff_days = $estimated_date->diffInDays($payment_date);//correcto
        $interest['current'] = $diff_days;
        $interest['current_generated'] = Util::round2(self::interest_by_days($diff_days,$loan->interest->annual_interest, $loan->balance));
        if ($interest['current'] > 31) {
            $interest['penal'] = $interest['current']-31;
            $interest['penal_generated'] = Util::round2(self::interest_by_days($diff_days-31,$loan->interest->penal_interest, $loan->balance));
        }
        return (object)$interest;
    }
}
