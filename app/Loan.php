<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Carbon;
use Util;

class Loan extends Model
{
    use Traits\EloquentGetTableNameTrait;

    // protected $appends = ['balance', 'estimated_quota', 'defaulted'];
    public $timestamps = true;
    // protected $hidden = ['pivot'];
    public $guarded = ['id'];
    public $fillable = [
        'code',
        'disbursable_id',
        'disbursable_type',
		'procedure_modality_id',
		'parent_loan_id',
        'parent_reason',
        'request_date',
        'amount_requested',
        'city_id',
        'loan_interest_id',
        'loan_state_id',
        'amount_approved',
        'loan_term',
        'disbursement_date',
        'disbursement_type_id',
        'modification_date',
        
    ];

    function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->request_date = Carbon::now();
        $state = LoanState::whereName('En Proceso')->first();
        if ($state) {
            $this->loan_state_id = $state->id;
        }
        $latest_loan = DB::table('loans')->orderBy('created_at', 'desc')->limit(1)->first();
        if (!$latest_loan) $latest_loan = (object)['id' => 0];
        $this->code = implode(['PTMO', str_pad($latest_loan->id + 1, 6, '0', STR_PAD_LEFT), '-', Carbon::now()->year]);
    }

    public function setProcedureModalityIdAttribute($id)
    {
        $this->attributes['procedure_modality_id'] = $id;
        $this->attributes['loan_interest_id'] = $this->modality->current_interest->id;
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable')->withPivot('user_id', 'date')->withTimestamps();
    }

    public function state()
    {
      return $this->belongsTo(LoanState::class, 'loan_state_id','id');
    }
    public function city()
    {
      return $this->belongsTo(City::class);
    }
    public function payment_type()
    {
      return $this->belongsTo(PaymentType::class,'disbursement_type_id','id');
    }

    public function submitted_documents()
    {
        return $this->belongsToMany(ProcedureDocument::class, 'loan_submitted_documents', 'loan_id')->withPivot('reception_date', 'comment', 'is_valid');
    }

    public function guarantors()
    {
        return $this->loan_affiliates()->withPivot('payment_percentage')->whereGuarantor(true);
    }

    public function lenders()
    {
        return $this->loan_affiliates()->withPivot('payment_percentage')->whereGuarantor(false);
    }

    public function loan_affiliates()
    {
        return $this->belongsToMany(Affiliate::class, 'loan_affiliates');
    }

    public function modality()
    {
      return $this->belongsTo(ProcedureModality::class,'procedure_modality_id', 'id');
    }

    public function getDefaultedAttribute()
    {
        return LoanPayment::days_interest($this->id, Carbon::now()->toDateString())['dias_penal'] > 0 ? true : false;
    }

    public function payments()
    {
        return $this->hasMany(LoanPayment::class)->orderBy('quota_number')->orderBy('created_at');
    }
    public function interest()
    {
        return $this->belongsTo(LoanInterest::class, 'loan_interest_id', 'id');
    }

    public function observations()
    {
        return $this->morphMany(Observable::class, 'observable');
    }
    //desembolso --> afiliado, esposa
    public function disbursable()
    {
        return $this->morphTo();
    }

    // Saldo capital
    public function getBalanceAttribute()
    {
        $balance = $this->amount_approved;
        if ($this->payments()->count() > 0) {
            $balance -= $this->payments()->sum('capital_payment');
        }
        return Util::round($balance);
    }

    public function last_payment()
    {
        return $this->payments()->latest()->first();
    }

    public static function get_percentage($dato)
    {
        if(count($dato)>0){
            return Util::round(1/count($dato)*100);
        }  
    }

    public function last_quota()
    {
        $latest_quota = $this->last_payment();
        if ($latest_quota) {
            $payments = LoanPayment::whereLoanId($this->id)->whereQuotaNumber($latest_quota->quota_number)->get();
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
 

    public function getNextPaymentAttribute()
    {
        $quota = $this->last_quota();
        if (!$quota) {
            $quota = new LoanPayment();
            $quota->estimated_date = LoanPayment::quota_date($this->id)[1];
            $current_date = Carbon::now();
            $last_date = Carbon::parse($quota->estimated_date);
            if ($last_date->month <= $current_date->month) {
                $quota->estimated_date = $current_date;
            } else {
                $quota->estimated_date = $current_date->addMonth();
            }
            $quota->quota_number = 1;
        } else {
            $quota->estimated_date = Carbon::now();
            $quota->quota_number = $quota->quota_number + 1;
        }
        $quota->estimated_date = $quota->estimated_date->endOfMonth()->toDateString();
        unset($quota->pay_date);
        $interest = $this->interest;
        $interest_days = LoanPayment::days_interest($this->id, $quota->estimated_date);

        // Calcular intereses
        $quota->balance = $this->balance;
        $quota->interest_payment = Util::round($quota->balance * $interest->daily_current_interest * $interest_days['dias_corriente']);
        $quota->penal_payment = Util::round($quota->balance * $interest->daily_penal_interest * $interest_days['dias_penal']);
        $quota->accumulation_interest = Util::round($quota->balance * $interest->daily_current_interest * $interest_days['dias_acumulado']);
        // Calcular amortización de capital
        $total_interests = $quota->interest_payment + $quota->penal_payment + $quota->accumulation_interest;
        if (($quota->balance + $total_interests) > $this->estimated_quota) {
            $quota->capital_payment = $this->estimated_quota - $total_interests;
        } else {
            $quota->capital_payment = $quota->balance;
        }
        // Calcular monto total de la cuota
        $quota->quota_estimated = $quota->capital_payment + $total_interests;
        $quota->next_balance = Util::round($quota->balance - $quota->capital_payment);
        return $quota;
    }
    public function  plan(){
        $saldo_capital=$this->amount_approved;
        $interes_diario=$this->interest->daily_current_interest;
        $cuota_estimada=$this->estimated_quota;
        $plan=[];
        $fechas = [];
        $fechas[] = $this->disbursement_date;
        $n=1;
        while($saldo_capital>0) {
            $fechas[] = Carbon::parse($fechas[$n-1])->startOfMonth()->addMonth()->endOfMonth()->toDateString();
            $dias_interes=LoanPayment::current_interest($this->id,$fechas[$n-1],$fechas[$n],$n);
            $amortizacion_interes=$dias_interes*$saldo_capital*$interes_diario;
            if($cuota_estimada>=$saldo_capital){
                $amortizacion_capital=$saldo_capital;
            } else {
                $amortizacion_capital=$cuota_estimada-$amortizacion_interes;
            }
            $total_pagar=$amortizacion_interes+$amortizacion_capital;
            $saldo_capital=$saldo_capital-$amortizacion_capital;

            $plan[] = [
                'cuota'=>$n,
                'fecha'=>$fechas[$n],
                'Dias'=>$dias_interes,
                'Capital'=>Util::round($amortizacion_capital),
                'Interes'=>Util::round($amortizacion_interes),
                'Saldo Capital'=>Util::round($saldo_capital)
            ];
            $n+=1;
        }
        return $plan;

    } 
    //obtener modalidad teniendo el tipo y el afiliado
    public static function get_modality($modality_name, $affiliate){
        $modality=null;
        if ($affiliate->affiliate_state){
            $affiliate_state = $affiliate->affiliate_state->name;
            $affiliate_state_type = $affiliate->affiliate_state->affiliate_state_type->name; 
        switch($modality_name){
            case 'Préstamo Anticipo':
                if($affiliate_state_type == "Activo")
                { 
                    $modality=ProcedureModality::whereShortened("ANT-SA")->first();
                }
                else{
                    if($affiliate_state_type == "Pasivo"){
                        $modality=ProcedureModality::whereShortened("ANT-SP")->first();  
                    }
                }
            break;
            case 'Préstamo a corto plazo':
                if($affiliate_state_type == "Activo"){
                    if($affiliate->active_loans()){
                        $modality = ProcedureModality::whereShortened("PCP-R-SA")->first();//Refinanciamiento corto plazo activo
                    }else{
                        if($affiliate_state == "Servicio" || $affiliate_state == "Comisión" )
                        { 
                            $modality=ProcedureModality::whereShortened("PCP-SA")->first(); //corto plazo activo 
                        }else{
                            $modality=ProcedureModality::whereShortened("PCP-DLA")->first(); // corto plazo activo letra A 
                        }
                    }     
                }else{
                    if($affiliate_state_type == "Pasivo"){
                        if($affiliate->afp){
                            if($affiliate->active_loans()){
                                $modality=ProcedureModality::whereShortened("PCP-R-SP-AFP")->first();
                                // refi afp pasivo
                            }else{
                                $modality=ProcedureModality::whereShortened("PCP-SP-AFP")->first();
                                //$sub_modality="Prestamo a corto plazo sector pasivo afp";
                            }
                              
                        }else{
                            if($affiliate->active_loans()){
                                $modality=ProcedureModality::whereShortened("PCP-R-SP-SEN")->first();
                                // refi senasir pasivo
                            }else{
                                $modality=ProcedureModality::whereShortened("PCP-SP-SEN")->first();
                            }
                        }                       
                    }
                }
                break;
            case 'Préstamo a largo plazo':
                if($affiliate_state_type == "Activo")
                {
                    if($affiliate->cpop){
                        if($affiliate->active_loans()){
                            $modality=ProcedureModality::whereShortened("PLP-R-SA-CPOP")->first();
                            // Refi largo plazo activo 1 solo garante
                        }else{
                            $modality=ProcedureModality::whereShortened("PLP-CPOP")->first();
                        }
                    }else{
                        $modality=ProcedureModality::whereShortened("PLP-GP-SAYADM")->first();
                    }
                }
                else{
                        if($affiliate_state_type == "Pasivo"){
                            if($affiliate->active_loans()){
                                if($affiliate->cpop){
                                    $modality=ProcedureModality::whereShortened("PLP-R-SP-CPOP")->first();
                                    // Refi largo plazo pasivo 1 solo garante
                                }
                            }else{
                                $modality=ProcedureModality::whereShortened("PLP-GP-SP")->first();  
                            }
                        }
                }
                break;
            case 'Préstamo hipotecario':
                if($affiliate_state_type == "Activo")
                {
                    if($affiliate->cpop){
                        $modality=ProcedureModality::whereShortened("PLP-GH-CPOP")->first();
                        //hipotecario CPOP
                    }else{
                        $modality=ProcedureModality::whereShortened("PLP-GH-SA")->first();
                    } 
                }
                break;
            } 
        }
        if ($modality) $modality->loan_modality_parameter;
        return response()->json($modality);
             
    }
}
