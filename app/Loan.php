<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Fico7489\Laravel\Pivot\Traits\PivotEventTrait;
use Carbon\CarbonImmutable;
use App\LoanGlobalParameter;
use App\Rules\LoanIntervalTerm;
use Carbon;
use Util;

class Loan extends Model
{
    use Traits\EloquentGetTableNameTrait;
    //use Traits\RelationshipsTrait;
    use PivotEventTrait;
    use SoftDeletes;

    protected $dates = [
        //'disbursement_date',
        'request_date'
    ];
    // protected $appends = ['balance', 'estimated_quota', 'defaulted'];
    public $timestamps = true;
    // protected $hidden = ['pivot'];
    public $guarded = ['id'];
    public $fillable = [
        'code',
        'disbursable_id',
        'disbursable_type',
        'procedure_modality_id',
        'disbursement_date',
        'disbursement_time',
        //'num_budget_certification',
        'num_accounting_voucher',
        'parent_loan_id',
        'parent_reason',
        'request_date',
        'amount_requested',
        'city_id',
        'interest_id',
        'state_id',
        'amount_approved',
        'indebtedness_calculated',
        'liquid_qualification_calculated',
        'loan_term',
        'refinancing_balance',
        'guarantor_amortizing',
        'payment_type_id',
        'number_payment_type',
        'property_id',
        'destiny_id',
        'financial_entity_id',
        'role_id',
        'validated',
        'user_id',
        'delivery_contract_date',
        'return_contract_date'
    ];

    function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (!$this->request_date) {
            $this->request_date = Carbon::now();
        }
        if (!$this->state_id) {
            $state = LoanState::whereName('En Proceso')->first();
            if ($state) {
                $this->state_id = $state->id;
            }
        }
        if (!$this->code) {
            if($this->parent_reason == 'REPROGRAMACIÓN' && $this->parent_loan)
            {
                    if(substr($this->parent_loan->code, -3) != substr($this->parent_reason,0,3))
                        $this->code = Loan::find($this->parent_loan_id)->code." - ".substr($this->parent_reason,0,3);
                    else
                        $this->code = $this->parent_loan->code;
            }else{
                $latest_loan = DB::table('loans')->orderBy('created_at', 'desc')->limit(1)->first();
                if (!$latest_loan) $latest_loan = (object)['id' => 0];
                $this->code = implode(['PTMO', str_pad($latest_loan->id + 1, 6, '0', STR_PAD_LEFT), '-', Carbon::now()->year]);
            }
        }
    }

    public function setProcedureModalityIdAttribute($id)
    {
        $this->attributes['procedure_modality_id'] = $id;
        $this->attributes['interest_id'] = $this->modality->current_interest->id;
    }

    public function loan_property()
    {
        return $this->belongsTo(LoanProperty::class, 'property_id','id');
    }

    public function notes()
    {
        return $this->morphMany(Note::class, 'annotable');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable')->withPivot('user_id', 'date')->withTimestamps();
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function parent_loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function state()
    {
      return $this->belongsTo(LoanState::class, 'state_id','id'); 
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function payment_type()
    {
        return $this->belongsTo(PaymentType::class,'payment_type_id','id');
    }

    public function financial_entity()
    {
        return $this->belongsTo(FinancialEntity::class,'financial_entity_id','id');
    }

    public function submitted_documents()
    {
        return $this->belongsToMany(ProcedureDocument::class, 'loan_submitted_documents', 'loan_id')->withPivot('reception_date', 'comment', 'is_valid');
    }
    public function documents_modality()
    {     
        $ids= $this->submitted_documents->pluck('id');
        $documents=collect();
        foreach($ids as $id){
           $documents->push(ProcedureDocument::where('procedure_documents.id',$id)->leftJoin('procedure_requirements as req','req.procedure_document_id','=','procedure_documents.id')->select('procedure_documents.name','req.number')->first());
        }  
         return (object)$documents->sortBy('number');
    }

    public function getSubmittedDocumentsListAttribute()
    {
        return  [
            'required' => ($this->submitted_documents)->intersect($this->modality->required_documents),
            'optional' => ($this->submitted_documents)->intersect($this->modality->optional_documents)
        ];
    }

    public function guarantors()
    {
        return $this->loan_affiliates()->withPivot('payment_percentage','payable_liquid_calculated', 'bonus_calculated', 'quota_previous','quota_treat','indebtedness_calculated','liquid_qualification_calculated','contributionable_ids','contributionable_type')->whereGuarantor(true);
    }

    public function lenders()
    {
        return $this->loan_affiliates()->withPivot('payment_percentage','payable_liquid_calculated', 'bonus_calculated', 'quota_previous','quota_treat', 'indebtedness_calculated','liquid_qualification_calculated','contributionable_ids','contributionable_type')->whereGuarantor(false);
    }

    public function loan_affiliates()
    {
        return $this->belongsToMany(Affiliate::class, 'loan_affiliates')->withPivot('contributionable_ids','contributionable_type');
    }

    public function loan_affiliates_ballot()
    {
        return $this->belongsToMany(Affiliate::class, 'loan_affiliates')->withPivot('contributionable_ids','contributionable_type');
    }

    public function personal_references()
    {
        return $this->loan_persons()->withPivot('cosigner')->whereCosigner(false);
    }

    public function cosigners()
    {
        return $this->loan_persons()->withPivot('cosigner')->whereCosigner(true);
    }

    public function loan_persons()
    {
        return $this->belongsToMany(PersonalReference::class, 'loan_persons');
    }

    public function modality()
    {
        return $this->belongsTo(ProcedureModality::class,'procedure_modality_id', 'id');
    }

    public function getDefaultedAttribute()
    {
        return LoanPayment::days_interest2($this)->penal > 0 ? true : false;
    }
    public function getdelay()
    {
        return LoanPayment::days_interest($this);
    }

    public function payments()
    {
        return $this->hasMany(LoanPayment::class)->orderBy('quota_number', 'desc')->orderBy('created_at');
    }
    public function payments_pendings_confirmations()
    {
        $state_id = LoanState::whereName('Pendiente por confirmar')->first()->id;
        return $this->hasMany(LoanPayment::class)->where('state_id', $state_id)->orderBy('quota_number', 'desc')->orderBy('created_at');
    }
    public function payment_pending_confirmation()//pago de pendiente por confirmacion para refin
    {
        $state_id = LoanState::whereName('Pendiente por confirmar')->first()->id;
        return $this->hasMany(LoanPayment::class)->where('state_id', $state_id)->orderBy('quota_number', 'desc')->orderBy('created_at')->first();
    }
    public function paymentsKardex()
    {
        $id = LoanState::whereName('Pagado')->orWhere('name', 'Pendiente por confirmar')->get('id');
        $ids = LoanState::whereName('Pendiente de Pago')->first()->id;
        $modality = ProcedureModality::where('name', 'like', 'A.AUT.%')->get('id');
        return $this->hasMany(LoanPayment::class)->whereIn('state_id', $id)->orWhere('state_id', $ids)->whereIn('procedure_modality_id', $modality)->orderBy('quota_number', 'desc')->orderBy('created_at');
    }
    //relacion uno a muchos
    public function loan_contribution_adjusts()
    {
        return $this->hasMany(LoanContributionAdjust::class);
    }
    public function interest()
    {
        return $this->belongsTo(LoanInterest::class, 'interest_id', 'id');
    }
    public function data_loan()
    {
        return $this->hasOne(Sismu::class,'loan_id','id');
    }
    
    public function getRecordsUserAttribute()
    {
        return $this->records()->first()->user();
    }

    public function observations()
    {
        return $this->morphMany(Observation::class, 'observable')->latest('updated_at');
    }
    //desembolso --> afiliado, esposa
    public function disbursable()
    {
        return $this->morphTo();
    }

    public function destiny()
    {
        return $this->belongsTo(LoanDestiny::class, 'destiny_id', 'id');
    }
     // add records
    public function records()
    {
        return $this->morphMany(Record::class, 'recordable')->latest('updated_at');
    }
    // Saldo capital
    public function getBalanceAttribute()
    {
        $balance = $this->amount_approved;
        $loan_states = LoanState::where('name', 'Pagado')->orWhere('name', 'Pendiente por confirmar')->get();
        if ($this->payments()->count() > 0) {
            $balance -= $this->payments()->where('state_id', $loan_states->first()->id)->sum('capital_payment');
            $balance -= $this->payments()->where('state_id', $loan_states->last()->id)->sum('capital_payment');
        }
        return Util::round($balance);
    }

    public function getLastPaymentAttribute()
    {
        return $this->payments()->latest()->first();
    }

    public function getLastPaymentValidatedAttribute()
    {
        $loan_states = LoanState::where('name', 'Pagado')->orWhere('name', 'Pendiente por confirmar')->get();
        return $this->payments()->whereLoanId($this->id)->where('state_id', $loan_states->first()->id)->orWhere('state_id',$loan_states->last()->id)->whereLoanId($this->id)->latest()->first();
    }

    public function getObservedAttribute()
    {
        return ($this->observations()->count() > 0) ? true : false;
    }

    public static function get_percentage($dato)
    {
        if(count($dato)>0){
            return Util::round(1/count($dato)*100);
        }
    }

    public function last_quota()
    {
        $latest_quota = $this->last_payment;
        if ($latest_quota) {
            $payments = $this->payments()->whereQuotaNumber($latest_quota->quota_number)->get();
            $latest_quota = new LoanPayment();
            $latest_quota = $latest_quota->merge($payments);
        }
        return $latest_quota;
    }

    public function getEstimatedQuotaAttribute()
    {
        $monthly_interest = $this->interest->monthly_current_interest;
        unset($this->interest);
        return Util::round($monthly_interest * $this->amount_approved / (1 - 1 / pow((1 + $monthly_interest), $this->loan_term)));
    }

    public function next_payment2($affiliate_id, $estimated_date = null, $paid_by, $procedure_modality_id, $estimated_quota, $adjust = false)
    {
        $grace_period = LoanGlobalParameter::latest()->first()->grace_period;
            $total_interests = 0;
            $partial_amount = 0;
            $total_amount = Util::round($estimated_quota);$amount = 0;
            $liquidate = false;//return ProcedureModality::where('id', $procedure_modality_id)->where('name', 'like', '%pactada')->first();
            if(ProcedureModality::where('id', $procedure_modality_id)->where('name', 'like', '%pactada')->first())
                $amount = $this->estimated_quota;
            if(ProcedureModality::where('id', $procedure_modality_id)->where('name', 'like', '%Liquidar%')->first())
            {
                $amount = $this->balance;
                $liquidate = true;
            }
            if(ProcedureModality::where('id', $procedure_modality_id)->where('name', 'like', '%Introducir%')->first() || ProcedureModality::where('id', $procedure_modality_id)->where('name', 'like', '%Parcial')->first())
                $amount = $estimated_quota;
            $quota = new LoanPayment();
            $next_payment = LoanPayment::quota_date($this);
            if (!$estimated_date) {
                $quota->estimated_date = $next_payment->date;
            } else {
                $quota->estimated_date = Carbon::parse($estimated_date)->toDateString();
            }
            $quota->previous_balance = Util::round($this->balance);
            $quota->previous_payment_date = $next_payment->previous_payment_date;
            $quota->quota_number = $this->balance > 0 ? $next_payment->quota : null;
            $interest = $this->interest;
            $quota->estimated_days = LoanPayment::days_interest2($this, $quota->estimated_date);
            $quota->paid_days = clone($quota->estimated_days);
            $quota->balance = $this->balance;
            $quota->penal_remaining = $quota->paid_days->penal_accumulated;
            $quota->penal_payment  = 0;
            $quota->interest_remaining = $quota->paid_days->interest_accumulated;
            $quota->capital_payment = $total_interests = $quota->interest_payment = 0;
            $quota->penal_accumulated = $quota->interest_accumulated = 0;
            //$total_amount = Util::round($amount);

            //calculo en caso de primera cuota

            $date_ini = CarbonImmutable::parse($this->disbursement_date);
            if($date_ini->day <= LoanGlobalParameter::latest()->first()->offset_interest_day)
                $date_pay = $date_ini->endOfMonth()->format('Y-m-d');
            else
                $date_pay = $date_ini->addMonth()->endOfMonth()->format('Y-m-d');
            //$date_compare = CarbonImmutable::parse($date_ini->addMonth()->endOfMonth())->format('Y-m-d');
            if(!$this->last_payment_validated && $estimated_date <= $date_pay){
                $quota->paid_days->current +=1;
                $quota->estimated_days->current +=1;
                $quota->paid_days->current_generated = Util::round(LoanPayment::interest_by_days($quota->paid_days->current, $this->interest->annual_interest, $this->balance));
                $quota->estimated_days->current_generated = Util::round(LoanPayment::interest_by_days($quota->paid_days->current, $this->interest->annual_interest, $this->balance));
                if($date_ini->day >= LoanGlobalParameter::latest()->first()->offset_interest_day){
                    $date_fin = CarbonImmutable::parse($date_ini->endOfMonth());
                    $rest_days_of_month = $date_fin->diffInDays($date_ini);
                    $partial_amount = ($quota->balance * $interest->daily_current_interest * $rest_days_of_month);
                    $quota->paid_days->penal = 0;
                    $quota->estimated_days->penal = 0;
                    $amount = $amount + $partial_amount;
                }
            }

        // Calcular intereses

        // Interes acumulado penal

        if($quota->penal_remaining > 0){
            if($amount >= $quota->penal_remaining){
                $amount = $amount - $quota->penal_remaining;
                //$quota->penal_remaining = 0;
            }
            else{
                $quota->penal_remaining = $amount;
                $amount = 0;
            }
        }
        else{
            $quota->penal_remaining = 0;
        }
        $total_interests += $quota->penal_remaining;

        // Interes acumulado corriente
        
        if($quota->interest_remaining > 0){
            if($amount >= $quota->interest_remaining){
                $amount = $amount - $quota->interest_remaining;
                //$quota->interest_remaining = 0;
            }
            else{
                $quota->interest_remaining = $amount;
                $amount = 0;
            }
        }
        else{
            $quota->interest_remaining = 0;
        }
        $total_interests += $quota->interest_remaining;

        // Interés penal 

        if($quota->estimated_days->penal >= $grace_period){
            $quota->penal_payment = Util::round($quota->balance * $interest->daily_penal_interest * $quota->paid_days->penal);
            if($quota->penal_payment >= 0){
                if($amount >= $quota->penal_payment){
                    $amount = $amount - $quota->penal_payment;
                }
                else{
                    $quota->penal_accumulated = Util::round($quota->penal_remaining + ($quota->penal_payment - $amount));
                    //$quota->penal_remaining = $quota->penal_remaining + ($quota->penal_payment - $amount);
                    $quota->penal_payment = $amount;
                    $amount = 0;
                }
            }else{
                $quota->penal_payment = 0;
            }
            $total_interests += $quota->penal_payment;
        }


        // Interés corriente
            
        $quota->interest_payment = Util::round($quota->balance * $interest->daily_current_interest * $quota->paid_days->current);
        if($amount >= $quota->interest_payment){
                $amount = $amount - $quota->interest_payment;
        }
        else{
            $quota->interest_accumulated = Util::round($quota->interests_remaining + ($quota->interest_payment - $amount));
            $quota->interest_payment = Util::round($amount);
            $amount = 0;
        }

        $total_interests += $quota->interest_payment;

        // Calcular amortización de capital        
        if($liquidate)
        {
            $quota->capital_payment = $quota->balance;
        }
        else{
            if($this->regular_payment() && $this->payments->count()+1 == $this->loan_term){
                $quota->capital_payment = Util::round($this->balance);
            }
            else{
                if($amount >= $this->balance){
                    $quota->capital_payment = Util::round($this->balance);
                }
                else
                    $quota->capital_payment = Util::round($amount);
            }
        }
                //calculo de la ultima cuota, solo si fue regular en los pagos

        /*if($this->regular_payment() && $this->payments->count()+1 == $this->loan_term){
            $amount = $this->balance + $quota->estimated_days;
            $quota->est += $quota->next_balance;
            $quota->next_balance = 0;
        }*/
        // Calcular monto total de la cuota

        if ($quota->balance == $quota->capital_payment) {
            $quota->next_balance = 0;
        } else {
            $quota->next_balance = Util::round($this->balance - $quota->capital_payment);
        }
        $quota->estimated_quota = Util::round($quota->capital_payment + $total_interests);
        $quota->next_balance = Util::round($quota->balance - $quota->capital_payment);


        //calculo de los nuevos montos restantes

        $quota->penal_accumulated = Util::round($quota->penal_accumulated + ($quota->estimated_days->penal_accumulated - $quota->penal_remaining));
        $quota->interest_accumulated = Util::round($quota->interest_accumulated + ($quota->estimated_days->interest_accumulated - $quota->interest_remaining));

        //redondeos

        $quota->interest_remaining = Util::round($quota->interest_remaining);
        $quota->penal_remaining = Util::round($quota->penal_remaining);
        //$quota->excesive_payment = Util::round($total_amount - ($quota->estimated_quota));


        //validacion pago excesivo
        
        /*if($total_amount == 0)
            $quota->excesive_payment = 0;
        else
            $quota->excesive_payment = Util::round($total_amount - $quota->estimated_quota);*/

        //$total_amount = $quota->penal_accumulated + $quota->interest_accumulated + $loan->balance + $quota->interest_remaining + $quota->penal_remaining;

        //

        return $quota;
    }

    public function next_payment($estimated_date = null, $amount = null, $liquidate = false)
    {
        do {
            if ($liquidate) {
                $amount = $this->amount_requested * $this->amount_requested;
            } else {
                if (!$amount) $amount = $this->estimated_quota;
            }
            $quota = new LoanPayment();
            $next_payment = LoanPayment::quota_date($this);
            if (!$estimated_date) {
                $quota->estimated_date = $next_payment->date;
            } else {
                $quota->estimated_date = Carbon::parse($estimated_date)->toDateString();
            }
            $quota->quota_number = $this->balance > 0 ? $next_payment->quota : null;
            $interest = $this->interest;
            $quota->estimated_days = LoanPayment::days_interest($this, $quota->estimated_date);
            $quota->paid_days = clone($quota->estimated_days);
            $quota->balance = $this->balance;
            $quota->penal_payment = $quota->accumulated_payment = $quota->interest_payment = $quota->capital_payment = $total_interests = 0;
            // Calcular intereses
            // Interés penal
            do {
                $total_interests -= $quota->penal_payment;
                $quota->penal_payment = Util::round($quota->balance * $interest->daily_penal_interest * $quota->paid_days->penal);
                $total_interests += $quota->penal_payment;
                if ($total_interests > $amount) {
                    $quota->paid_days->penal = intval($amount * $quota->paid_days->penal / $quota->penal_payment);
                    $quota->paid_days->accumulated = $quota->paid_days->current = 0;
                }
            } while ($total_interests > $amount);
            // Interés acumulado
            do {
                $total_interests -= $quota->accumulated_payment;
                $quota->accumulated_payment = Util::round($quota->balance * $interest->daily_current_interest * $quota->paid_days->accumulated);
                $total_interests += $quota->accumulated_payment;
                if ($total_interests > $amount) {
                    $quota->paid_days->accumulated = intval(($amount - $quota->penal_payment) * $quota->paid_days->accumulated / $quota->accumulated_payment);
                    $quota->paid_days->current = 0;
                }
            } while ($total_interests > $amount);
            // Interés corriente
            do {
                $total_interests -= $quota->interest_payment;
                $quota->interest_payment = Util::round($quota->balance * $interest->daily_current_interest * $quota->paid_days->current);
                $total_interests += $quota->interest_payment;
                if ($total_interests > $amount) {
                    $quota->paid_days->current = intval(($amount - $quota->penal_payment - $quota->accumulated_payment) * $quota->paid_days->current / $quota->interest_payment);
                }
            } while ($total_interests > $amount);
            // Calcular amortización de capital
            //if ($total_interests > 0) {
                if (($quota->balance + $total_interests) > $amount) {
                    if($quota->quota_number == 1 && $quota->estimated_days->accumulated < 31){
                        $quota->capital_payment = Util::round($amount + $quota->accumulated_payment - $total_interests);
                    }else{
                        $quota->capital_payment = Util::round($amount - $total_interests);
                    }
                } else {
                    $quota->capital_payment = $quota->balance;
                }
            //}
            // Calcular monto total de la cuota
            $quota->estimated_quota = Util::round($quota->capital_payment + $total_interests);
            $quota->next_balance = Util::round($quota->balance - $quota->capital_payment);

            if ($liquidate) {
                if ($quota->next_balance > 0) {
                    $amount *= $this->amount_requested;
                } else {
                    $liquidate = false;
                }
            }
        } while ($liquidate);
        return $quota;
    }

    public function getPlanAttribute() {
        $plan = [];
        $daily_interest = $this->interest->daily_current_interest;
        $balance = $this->amount_approved;
        $estimated_quota = $this->estimated_quota;
        $loan_term = $this->loan_term;
        $i = 0;
        while ($balance > 0) {
            if (count($plan) == 0) {
                $next_payment = LoanPayment::quota_date($this, true);
            } else {
                $next_payment = (object)[
                    'quota' => $plan[$i-1]->quota + 1,
                    'date' => Carbon::parse($plan[$i-1]->date)->startOfMonth()->addMonth()->endOfMonth()->toDateString()
                ];
            }
            $interest = LoanPayment::days_interest($this, $next_payment->date);
            $next_interest = Util::round($balance * $interest->current * $daily_interest);
            if (($balance + $next_interest) > $estimated_quota) {
                $next_balance = $estimated_quota - $next_interest;
            } else {
                $next_balance = $balance;
            }
            $next_balance = Util::round($next_balance);
            $balance = Util::round($balance - $next_balance);
            $total = $next_balance + $next_interest;
            if($next_payment->quota == $loan_term){
                $next_balance = $next_balance + $balance;
                $estimated_quota = $estimated_quota + $balance;
                $total = $total + $balance;
                $balance = 0;
            }
            array_push($plan, (object)[
                'quota' => $next_payment->quota,
                'date' => $next_payment->date,
                'days' => $interest->current,
                'interest_accumulated' => $interest->accumulated_amount,
                'estimated_quota' => ($estimated_quota >= $total) ? $total : $estimated_quota,
                'capital' => $next_balance,
                'interest' => $next_interest,
                'next_balance' => $balance,
                'accumulated' => $interest->accumulated
            ]);
            $i++;
        }
        return $plan;
    }

    //obtener modalidad teniendo el tipo y el afiliado
    public static function get_modality($modality, $affiliate, $type_sismu, $cpop_sismu, $cpop_affiliate){
        $verify=false;
        $modality_name=$modality->name;
        /*if(strpos($modality->name, 'Refinanciamiento') === false){//para restringir para no tener prestamos paralelos de la misma sub mod
            foreach ($affiliate->active_loans() as $loan) {
                if($loan->modality->procedure_type->id == $modality->id)
                $verify=true;
            }
        }*/
    
        //if($verify && !$remake_loan) abort(403, 'El affiliado tiene préstamos activos en la modalidad: '.$modality_name);
    
        $modality = null;
        if ($affiliate->affiliate_state){
            $affiliate_state = $affiliate->affiliate_state->name;
            $affiliate_state_type = $affiliate->affiliate_state->affiliate_state_type->name;
        switch($modality_name){
            case 'Préstamo Anticipo':
                if($affiliate_state_type == "Activo"){
                    if($affiliate_state == "Servicio" || $affiliate_state == "Comisión" )
                    {
                        $modality=ProcedureModality::whereShortened("ANT-ACT")->first(); //Anticipo activo
                    }else{
                        $modality=ProcedureModality::whereShortened("ANT-DIS")->first(); // Anicipo dismponibilidad
                    }
                }
                if($affiliate_state_type == "Pasivo"){
                    if($affiliate->pension_entity->name != 'SENASIR')
                    {
                    $modality=ProcedureModality::whereShortened("ANT-AFP")->first(); //  Prestamo a anticipo afp
                    }else{
                        $modality=ProcedureModality::whereShortened("ANT-SEN")->first(); // Prestamo a anticipo senasir
                    }
                }
            break;
            case 'Préstamo a Corto Plazo':
                if($affiliate_state_type == "Activo"){
                    if($affiliate_state == "Servicio" || $affiliate_state == "Comisión" )
                    {
                        $modality=ProcedureModality::whereShortened("COR-ACT")->first(); //corto plazo activo
                    }else{
                        $modality=ProcedureModality::whereShortened("COR-DIS")->first(); // corto plazo activo letra A, no le corresponde refinanciamiento segun Art 76 del reglamento
                    }
                }
                if($affiliate_state_type == "Pasivo"){
                    if($affiliate->pension_entity->name != 'SENASIR')
                    {
                    $modality=ProcedureModality::whereShortened("COR-AFP")->first(); //  Prestamo a corto plazo sector pasivo afp caso SISMU
                    }else{
                        $modality=ProcedureModality::whereShortened("COR-SEN")->first(); // Prestamo a corto plazo senarir caso SISMU
                    }
                }
            break;
            case 'Refinanciamiento Préstamo a Corto Plazo':
                if($affiliate_state_type == "Activo" && $affiliate_state !== "Disponibilidad") //affiliados con estado en disponibilidad no realizaran refinanciamientos 
                {
                    $modality = ProcedureModality::whereShortened("REF-COR-ACT")->first();//Refinanciamiento corto plazo activo                           
                }else{
                    if($affiliate_state_type == "Pasivo"){
                    
                        if($affiliate->pension_entity->name != 'SENASIR')
                        {
                        $modality=ProcedureModality::whereShortened("REF-COR-AFP")->first();// refi afp pasivo sismu
                        }else{
                            $modality=ProcedureModality::whereShortened("REF-COR-SEN")->first();// refi senasir pasivo sismu
                        }
                    }
                }
            break;
            case 'Préstamo a Largo Plazo':
                if($affiliate_state_type == "Activo")
                {
                    if($affiliate_state !== "Disponibilidad" ) // disponibilidad letra A o C no puede acceder a prestamos a largo plazo
                    {
                        if($cpop_affiliate || $cpop_sismu){
                            $modality=ProcedureModality::whereShortened("LAR-CPOP")->first();
                        }else{
                            $modality=ProcedureModality::whereShortened("LAR-ACT")->first();
                        }
                    }
                }
                if($affiliate_state_type == "Pasivo")
                {
                    if((!$cpop_affiliate && !$cpop_sismu)){
                        if($affiliate->pension_entity->name != 'SENASIR')
                        {
                            $modality=ProcedureModality::whereShortened("LAR-AFP")->first();// Largo plazo Sector PAsivo
                        }else{
                            $modality=ProcedureModality::whereShortened("LAR-SEN")->first();// Largo plazo Sector PAsivo
                        }
                    }
                }
            break;  
            case 'Refinanciamiento Préstamo a Largo Plazo':
                if($affiliate_state_type == "Activo")
                {
                    if($affiliate_state !== "Disponibilidad" ) //disponibilidad letra A o C no tiene prestamos
                    {
                        if($cpop_affiliate || $cpop_sismu){
                            $modality=ProcedureModality::whereShortened("REF-ACT-CPOP")->first(); //refi largo plazo activo  cpop
                        }else{
                            $modality=ProcedureModality::whereShortened("REF-LAR-ACT")->first(); //refi largo plazo activo
                        }
                    }
                }
                else{
                    if($affiliate_state_type == "Pasivo"){
                        if((!$cpop_affiliate && !$cpop_sismu)){
                            if($affiliate->pension_entity->name != 'SENASIR')
                            {
                                $modality=ProcedureModality::whereShortened("REF-LAR-AFP")->first();// ref Largo plazo Sector Pasivo
                            }else{
                                $modality=ProcedureModality::whereShortened("REF-LAR-SEN")->first();// ref Largo plazo Sector Pasivo
                            }
                        }
                    }
                }
            break;
            case 'Préstamo Hipotecario':
                if($affiliate_state_type == "Activo")
                {
                    if($affiliate_state_type !== "Comisión"){
                        
                        if($cpop_affiliate || $cpop_sismu){
                            $modality=ProcedureModality::whereShortened("HIP-ACT-CPOP")->first(); //hipotecario CPOP
                        }else{
                            $modality=ProcedureModality::whereShortened("HIP-ACT")->first(); //hipotecario Sector Activo
                        }
                    }
                }
            break;
            case 'Refinanciamiento Préstamo Hipotecario':
                if($affiliate_state_type == "Activo")
                {
                    if($affiliate_state_type !== "Comisión" && $affiliate_state !== "Disponibilidad"){//affiliados con estado en disponibilidad no realizaran refinanciamientos 
                        if($cpop_affiliate || $cpop_sismu){
                            $modality=ProcedureModality::whereShortened("REF-HIP-ACT-CPOP")->first(); // Refinanciamiento hipotecario CPOP
                        }else{
                            $modality=ProcedureModality::whereShortened("REF-HIP-ACT")->first(); // Refinanciamiento hipotecario Sector Activo
                        }
                    }                   
                }
            break;
            }
        }
        if ($modality) {
            $modality->loan_modality_parameter;
            $modality->procedure_type;
            return response()->json($modality);
        }else{
            return response()->json();
        }
    }


    //verificar pagos manuales consecutivos
   public function verify_payment_consecutive()
   {
     $loan_global_parameter  = $loan_global_parameter = LoanGlobalParameter::latest()->first();
     $number_payment_consecutive = $loan_global_parameter->consecutive_manual_payment;//3
     $modality_id=ProcedureModality::whereShortened("AD-Cuota-pactada")->first()->id;

     $Pagado = LoanState::whereName('Pagado')->first()->id;
    
     $payments=$this->payments->where('procedure_modality_id','=',$modality_id)->where('state_id','=',$Pagado)->sortBy('estimated_date');
    
     $consecutive=1;
     $verify=false;

     if(count($payments)>=$number_payment_consecutive){
        foreach($payments as $i => $payment){
            // $j=$i+1;
             foreach($payments as $j => $paymentd){
              $stimated_date=CarbonImmutable::parse($payments[$i]->estimated_date);
              $stimated_date_compare=CarbonImmutable::parse($payments[$j++]->estimated_date);
              //return $payments[$j++]->estimated_date;
              if($stimated_date->startOfMonth()->diffInMonths($stimated_date_compare->startOfMonth()) == $consecutive){
               $consecutive++;
              }else{
               $consecutive=1;
              }
           }
           if($consecutive >= $number_payment_consecutive){
               $verify=true;
               break;
           }
           $consecutive=1;
         }
     }else{
        $verify=false;
     }
    return $verify;
   }
    public function get_sismu(){
        return Sismu::find($this->id);
    }
    public function user(){
        return $this->hasOne(User::class,'id','id');
    }

    //obtener mod 
    public static function get_modality_search($modality_name, $affiliate){
        $modality = null;
        if ($affiliate->affiliate_state){
            $affiliate_state = $affiliate->affiliate_state->name;
            $affiliate_state_type = $affiliate->affiliate_state->affiliate_state_type->name;
        switch($modality_name){
            case 'Préstamo Anticipo':
                if($affiliate_state_type == "Activo"){
                    if($affiliate_state == "Servicio" || $affiliate_state == "Comisión" )
                    {
                        $modality=ProcedureModality::whereShortened("ANT-ACT")->first(); //Anticipo activo
                    }else{
                        $modality=ProcedureModality::whereShortened("ANT-DIS")->first(); // Anicipo dismponibilidad
                    }
                }
            break;
            case 'Préstamo a Corto Plazo':
                if($affiliate_state_type == "Activo"){
                    if($affiliate_state == "Servicio" || $affiliate_state == "Comisión" )
                    {
                        $modality=ProcedureModality::whereShortened("COR-ACT")->first(); //corto plazo activo
                    }else{
                        $modality=ProcedureModality::whereShortened("COR-DIS")->first(); // corto plazo activo letra A, no le corresponde refinanciamiento segun Art 76 del reglamento
                    }
                }
            break;
            case 'Préstamo a Largo Plazo':
                if($affiliate_state_type == "Activo")
                {
                    if($affiliate_state !== "Disponibilidad" ) // disponibilidad letra A o C no puede acceder a prestamos a largo plazo
                    {
                        $modality=ProcedureModality::whereShortened("LAR-ACT")->first();
                    }
                }
            break; 
            case 'Préstamo Hipotecario':
                if($affiliate_state_type == "Activo")
                {
                    if($affiliate_state_type !== "Comisión"){
                        $modality=ProcedureModality::whereShortened("HIP-ACT")->first(); //hipotecario Sector Activo
                    }
                }
            break;
            }
        }
        if ($modality) {
            $modality->loan_modality_parameter;
            return $modality;
        }else{
            $modality=[];
            return $modality;
        }
    }
    //Saldo del padre a refinanciar 
    public function balance_parent_refi(){
        $balance_parent = 0;
       if($this->data_loan){
        $balance_parent=$this->data_loan->balance;
       }else{
           if($this->parent_loan && $this->parent_loan->payment_pending_confirmation() != null){
            $balance_parent = $this->parent_loan->payment_pending_confirmation()->estimated_quota;
           }
       }
       return  $balance_parent;

    }
    //fecha de corte para refi
     public function date_cut_refinancing(){
         $date_cut_refinancing= null;
       if($this->data_loan){
         $date_cut_refinancing=$this->data_loan->date_cut_refinancing;
       }else{
           if($this->parent_loan && $this->parent_loan->payment_pending_confirmation() != null){
            $date_cut_refinancing = $this->parent_loan->payment_pending_confirmation()->estimated_date;
           }
       }
       return  $date_cut_refinancing;
    }

    //Verifica si los pagos realizados fueron regulares y sin mora
    public function regular_payment()
    {
        $loan_payments = $this->payments;
        $quota_number = 1;
        $sw = false;
        foreach($loan_payments as $payments){
            if($quota_number == 1 && $payments->estimated_quota >= $this->estimated_quota)
                $sw = true;
            else{
                if($payments->estimated_quota == $this->estimated_quota)
                    $sw = true;
                else
                    break;
            }
            $quota_number++;
        }
        return $sw;
    }

    //verificacion de saldo con pagos
    public function verify_balance()
    {
        $payments = $this->payments;
        $loan_state = LoanState::where('name', 'Pagado')->first();
        $balance = $this->amount_approved;
        foreach($payments as $payment)
        {
            if($payment->state_id == $loan_state->id)
                $balance -= $payment->capital_payment;
        }
        return $balance;
    }
    //muestra boletas de afiliado
    public function ballot_affiliate($affiliate_id){
        foreach($this->loan_affiliates as $affiliate){  
            if( $affiliate->id == $affiliate_id){
            $contributions = $affiliate->pivot->contributionable_ids;
            $contributions_type = $affiliate->pivot->contributionable_type;
            $ballots=json_decode($contributions);
            $ballot = collect();
            $adjusts = collect();
                if($contributions_type == "contributions"){ 
                    foreach($ballots as $is_ballot_id){
                        if(Contribution::find($is_ballot_id))
                        $ballot->push(Contribution::find($is_ballot_id));
                        if(LoanContributionAdjust::where('adjustable_id', $is_ballot_id)->first()){
                        $adjusts->push(LoanContributionAdjust::where('adjustable_id', $is_ballot_id)->first());
                        }  
                    }                  
                }
                if($contributions_type == "aid_contributions"){
                    foreach($ballots as $is_ballot_id){
                        if(AidContribution::find($is_ballot_id))
                        $ballot->push(AidContribution::find($is_ballot_id));
                        if(LoanContributionAdjust::where('adjustable_id', $is_ballot_id)->first())
                        $adjusts->push(LoanContributionAdjust::where('adjustable_id', $is_ballot_id)->first());
                    }
                }
                if($contributions_type == "loan_contribution_adjusts"){
                    $liquid_ids= LoanContributionAdjust::where('loan_id',$this->id)->where('type_adjust',"liquid")->get()->pluck('id');
                    $adjust_ids= LoanContributionAdjust::where('loan_id',$this->id)->where('type_adjust',"adjust")->get()->pluck('id');
                    foreach($liquid_ids as $liquid_id){  
                        $ballot->push(LoanContributionAdjust::find($liquid_id));
                    }
                foreach($adjust_ids as $adjust_id){  
                        $adjusts->push( LoanContributionAdjust::find($adjust_id));
                    }      
                }

            }      
        }
        $data = [
            'ballot' => $ballot,   
            'adjusts' => $adjusts 
        ];
          return (object)$data; 
    }

}
