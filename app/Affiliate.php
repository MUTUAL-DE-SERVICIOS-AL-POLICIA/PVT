<?php

namespace App;

use App\Http\Controllers\Api\V1\AffiliateController;
use Carbon;

use Illuminate\Database\Eloquent\Model;
use Laratrust\Traits\LaratrustUserTrait;
use Illuminate\Support\Facades\Storage;
use Carbon\CarbonImmutable;
use Util;
use App\LoanState;
use Illuminate\Support\Facades\DB;
use App\LoanProcedure;
use App\RetirementFundAverage;

class Affiliate extends Model
{
    use Traits\EloquentGetTableNameTrait;
    use Traits\RelationshipsTrait;

    public $relationships = ['City', 'AffiliateState'];
    // protected $appends = ['picture_saved', 'fingerprint_saved', 'full_name'];
    // protected $hidden = ['pivot'];
    protected $fillable = [
        'affiliate_state_id',
        'city_identity_card_id',
        'city_birth_id',
        'degree_id',
        'unit_id',
        'category_id',
        'pension_entity_id',
        'identity_card',
        'registration',
        'type',
        'last_name',
        'mothers_last_name',
        'first_name',
        'second_name',
        'surname_husband',
        'gender',
        'civil_status',
        'birth_date',
        'date_entry',
        'date_death',
        'reason_death',
        'date_derelict',
        'reason_derelict',
        'change_date',
        'phone_number',
        'cell_phone_number',
        'afp',
        'nua',
        'item',
        'service_years',
        'service_months',
        'death_certificate_number',
        'due_date',
        'is_duedate_undefined',
        'affiliate_registration_number',
        'file_code',
        'account_number',
        'financial_entity_id',
        'sigep_status',
        'unit_police_description',
        'user_id',
        'availability_info'
      ];

    public function getTitleAttribute()
    {
      $data = "";
      if ($this->degree) $data = $this->degree->shortened;;
      return $data;
    }

    public function getPictureSavedAttribute()
    {
        try {
            $base_path = 'picture/';
            return Storage::disk('ftp')->exists($base_path . $this->id . '_perfil.jpg');
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getIdentityCardExtAttribute()
    {
        $data = $this->identity_card;
        if ($this->city_identity_card && $this->city_identity_card != 'NINGUNO'){
          $data .= ' ' . $this->city_identity_card->first_shortened;
        } 
        return rtrim($data);
    }

    public function getExpeditionCardAttribute()
    {
        $data = ' ';
        if ($this->city_identity_card && $this->city_identity_card != 'NINGUNO'){
          $data .= ' ' . $this->city_identity_card->first_shortened;
        } 
        return $data;
    }

    public function getFullUnitAttribute()
    {
        $data = "";
        // if ($this->unit) $data .= ' ' . $this->unit->district.' - '.$this->unit->name.' ('.$this->unit->shortened.')'.' - '.$this->unit_police_description;
        if ($this->unit) $data .= ' ' . $this->unit->district.' - '.$this->unit->name.' ('.$this->unit->shortened.')';
        return $data;
    }

    public function getCivilStatusGenderAttribute()
    {
        return Util::get_civil_status($this->civil_status, $this->gender);
    }

    public function getFingerprintSavedAttribute()
    {
        try {
            $base_path = 'picture/';
            $fingerprint_pictures = ['_left_four.png', '_right_four.png', '_thumbs.png'];
            $fingerprint_exists = false;
            foreach ($fingerprint_pictures as $picture) {
                $fingerprint_exists |= Storage::disk('ftp')->exists($base_path . $this->id . $picture);
            }
            return boolval($fingerprint_exists);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getFullNameAttribute()
    {
      return rtrim(preg_replace('/[[:blank:]]+/', ' ', join(' ', [$this->first_name, $this->second_name, $this->last_name, $this->mothers_last_name,$this->surname_husband])));
    }

    public function getDeadAttribute()
    {
      return ($this->date_death != null || $this->reason_death != null || $this->death_certificate_number != null || $this->affiliate_state->name == "Fallecido");
    }

    public function getDefaultedLenderAttribute()
    {
        $loans = $this->loans()->whereHas('state', function($q) {
            $q->where('name', 'Vigente');
        })->get();
        foreach ($loans as $loan) {
            if ($loan->defaulted) return true;
        }
        return false;
    }

    public function getDefaultedGuarantorAttribute()
    {
        $loans = $this->guarantees()->whereHas('state', function($q) {
            $q->where('name', 'Vigente');
        })->get();
        foreach ($loans as $loan) {
            if ($loan->defaulted) return true;
        }
        return false;
    }

    public function getDisbursementLoansAttribute()
    {
        $loans = $this->loans()->whereHas('state', function($q) {
            $q->where('name', 'Vigente');
        })->get();
        return $loans;
    }

    public function getProcessLoansAttribute()
    {
        $loans = $this->loans()->whereHas('state', function($q) {
            $q->where('name', 'En Proceso');
        })->get();
        return $loans;
    }

    public function getAdministrativeAttribute()
    {
      $data = $this->degree;
      if($this->degree)
        if(strstr($this->degree->name,'administrativo'))
        $data = true;
      return $data;
    }

    public function getCategoryAttribute()
    {
      $category = null;
      if($this->category_id){
        $category =  Category::whereId($this->category_id)->first();
      }
      return $category;
    }

    public function degree()
    {
      return $this->belongsTo(Degree::class);
    }
    public function unit()
    {
      return $this->belongsTo(Unit::class);
    }
    public function city_identity_card()
    {
      return $this->belongsTo(City::class,'city_identity_card_id', 'id');
    }
    public function affiliate_state()
    {
      return $this->belongsTo(AffiliateState::class);
    }
    public function city_birth()
    {
      return $this->belongsTo(City::class, 'city_birth_id', 'id');
    }
    public function pension_entity()
    {
      return $this->belongsTo(PensionEntity::class);
    }
    public function financial_entity()
    {
      return $this->belongsTo(FinancialEntity::class);
    }
      // add records
    public function records()
    {
      return $this->morphMany(Record::class, 'recordable');
    }
      //address
    public function addresses()
    {
      return $this->morphToMany(Address::class, 'addressable')->withPivot('validated')->withTimestamps()->latest('updated_at');
    }

    public function getAddressAttribute()
    {
        return $this->addresses()->withPivot('validated')->whereValidated(true)->first();
    }

    //spouses
    public function spouses()
    {
      return $this->hasMany(Spouse::class);
    }
    public function getSpouseAttribute()
    {
      return $this->spouses()->first();
    }
    //contributions
    public function contributions()
    {
      return $this->hasMany(Contribution::class);
    }
    public function aid_contributions()
    {
      return $this->hasMany(AidContribution::class);
    }

    public function observations()
    {
        return $this->morphMany(Observation::class, 'observable')->latest('updated_at');
    }

    public function guarantees()
    {
      return $this->hasMany(LoanGuarantor::class, 'affiliate_id', 'id');
    }

    public function loans()
    {
        return $this->hasMany(Loan::class, 'affiliate_id', 'id');
    }

    public function active_loans()
    {
      return $this->verify_balance($this->loans);
    }

    public function getActiveLoansEstacionalAttribute()
    {
      return $this->loans->whereIn('procedure_modality_id', [95,96])->where('state_id', 3);
    }

    public function current_loans()
    {
      $loan_state = LoanState::whereName('Vigente')->first();
      return $this->hasMany(Loan::class, 'affiliate_id', 'id')->where('state_id', $loan_state->id);
    }
    public function active_guarantees()
    {
      $guarantees = LoanGuarantor::whereAffiliateId($this->id)->get();
      $data_loan = [];
      foreach($guarantees as $guarantee){
        $loan = Loan::find($guarantee->loan_id);
        if($loan){
          array_push($data_loan, $loan);
        }
      }
      return $this->verify_balance($data_loan);
    }
    //
    public function affiliate_fullName()
    {
        return rtrim($this->first_name.' '.$this->second_name.' '.$this->last_name.' '.$this->mothers_last_name.' '.$this->surname_husband);
    }

    private function verify_balance($loans)
    {
      $active_loans = [];
      foreach ($loans as $loan) {
        $loan->balance = $loan->balance;
        if ($loan->balance > 0) {
          $loan->estimated_quota = $loan->estimated_quota;
          array_push($active_loans, $loan);
        }
      }
        return $active_loans;
    }
    public function inactive_loans()
    {
        return $this->verify_balance_liquidated($this->loans);
    }

    private function verify_balance_liquidated($loans)
    {
        $inactive_loans = [];
        foreach ($loans as $loan) {
            $loan->balance = $loan->balance;
            if ($loan->balance == 0) {
                array_push($inactive_loans, $loan);
            }
        }
        return $inactive_loans;
    }

    //document
    public function submitted_documents()
    {
        return $this->hasMany(AffiliateSubmittedDocument::class);
    }

    public function getCpopAttribute(){
      $cpop = false;
      if($this->defaulted_lender) return $cpop ;
      $date_now = Carbon::now()->format('Y-m-d');
      $date_year_ago = Carbon::parse($date_now)->addYear(-1)->toDateString(); // para verificar registro de pagos un hasta 1 año a tras
      $active_loans = $this->active_loans();
      $inactive_loans = $this->inactive_loans();
      $loans = array_merge($active_loans, $inactive_loans ); // se debe verificar en pagados y en vigencia
      if(count($loans)>=1){
        foreach($loans as $loan){
          $c = 0;
          if(count($loan->payments)>=12){
            foreach ($loan->payments as $payment) {
              if($c < 12){
                if(($payment->estimated_date >= $date_year_ago) && ($payment->estimated_date <= $date_now)) $c++; $cpop = true;
                if(!$payment->penal_payment == 0 ) { // verificar que en ese periodo no haya tenido penalidad
                  $cpop = false;
                  return $cpop;
                }
              }
            }
          }
        }
      }
      return $cpop;
    }

    public function test_guarantor($modality, $sw, $remake_evaluation = false, $remake_loan_id = null){
      $guarantor= self::verify_guarantor($this);
      $remake_loan = 0;
    if($guarantor===true){
      if(!$this->dead){
        if($modality){
            $modality = ProcedureModality::findOrFail($modality); //evaluando categoria acorde a la modalidad
            if($modality->loan_modality_parameter->min_guarantor_category <= $this->category->percentage && $this->category->percentage <= $modality->loan_modality_parameter->max_guarantor_category) $guarantor = true;
            else $guarantor = false;
        }else{
            $loan_modality_parameter = LoanModalityParameter::get();
            if( $loan_modality_parameter->min('min_guarantor_category')<= $this->category->percentage && $this->category->percentage <= $loan_modality_parameter->max('max_guarantor_category')) $guarantor = true; //evaluando categoria sin tomar en cuenta la modalidad
            else $guarantor = false;
        }
      }
      if($guarantor){
          $loan_global_parameter = LoanProcedure::where('is_enable', true)->first()->loan_global_parameter;
          if($this->affiliate_state->affiliate_state_type->name == 'Activo'){
            if($remake_evaluation)
              $remake_loan = 1;
            //if($loan_global_parameter->max_guarantor_active <= count($this->active_guarantees()) + count($this->active_guarantees_sismu()) - $remake_loan) $guarantor = false;
          }
          if($this->affiliate_state->affiliate_state_type->name == 'Pasivo'){
            if($remake_evaluation)
              $remake_loan = count($this->guarantees->where('id', $remake_loan_id));
            //if($loan_global_parameter->max_guarantor_passive <= count($this->active_guarantees()) + count($this->active_guarantees_sismu())) $guarantor = false;
          }
          if($this->affiliate_state->affiliate_state_type->name != 'Activo' && $this->affiliate_state->affiliate_state_type->name != 'Pasivo') $guarantor = false; // en otro caso no corresponde ya que seria Disponibilidad A o C
          //if($this->defaulted_lender || $this->defaulted_guarantor) $guarantor = false;
          if($this->affiliate_state->name == 'Comisión') $guarantor = false;
      }
    }
    $affiliate= AffiliateController::append_data($this, true);
    $affiliate->loan=$affiliate->loans;
      return response()->json([
          'affiliate' => $affiliate,
          'guarantor' => $guarantor,
          'active_guarantees_quantity' => count($this->active_guarantees()) - $remake_loan,
          'guarantor_information' => self::verify_information($this),
          'double_perception'=> false,
          'loans_sismu' => $this->active_loans_sismu(),
          'guarantees_sismu' => $this->active_guarantees_sismu()
      ]);
  }

    public static function verify_information(Affiliate $affiliate)
    {
      $needed_keys = ['affiliate_state', 'city_birth', 'address'];
      $information = true;
      foreach ($needed_keys as $key) {
          if (!$affiliate[$key]) $information = false;//abort(409, 'Debe actualizar los datos personales de los garantes');
      }
      return $information;
    }
    public static function verify_double_perception($ci){
      if(Spouse::where('identity_card', $ci)->first() && Spouse::where('identity_card', '=', $ci)->first()->affiliate->dead){
        $double_perception= true;
      } else{
        $double_perception = false;}
      return $double_perception;
    }
    //veriifca si garante no es fallecido, ademas verificas que garante= SENASIR en caso pasivo
    public static function verify_guarantor(Affiliate $affiliate)
    {    $guarantor = false;
          if($affiliate->affiliate_state->name == 'Fallecido'){ 
            if($affiliate->pension_entity){
            if($affiliate->pension_entity->name == 'SENASIR'){
              $spouse = $affiliate->spouse;
              if(isset($spouse)){
                    $guarantor = true;
                  } else{
                    $guarantor = false;
                  }
              } else{
                $guarantor = false;
              }
          }else{ 
            $guarantor = false;
          }
         }elseif($affiliate->affiliate_state->affiliate_state_type->name == 'Pasivo'){
          if($affiliate->pension_entity){
                 if($affiliate->pension_entity->name == 'SENASIR'){
                   $guarantor=true;
                }else{
                  $guarantor=false;
                 }              
         }else{ 
        $guarantor = false;
        }
      }else{ 
        $guarantor = true;
      }
      return $guarantor;                             
    }  
    
   //verificar si se tiene boletas del affiliado
    public function contributions_exist()
    {
      if($this->affiliate_state == null) abort(403, 'Debe actualizar el estado del afiliado');
      $state_affiliate = $this->affiliate_state->affiliate_state_type->name;
      $contributions = null;
      $before_month=1;
      $response=false;
      $now = CarbonImmutable::now();
      if($state_affiliate == 'Activo') $table_contribution ='contributions';
      if($state_affiliate == 'Pasivo') $table_contribution ='aid_contributions';

      if(count($this->$table_contribution)>0){
        $contributions=$this->$table_contribution->sortByDesc('month_year',SORT_NATURAL);
        $contributions=$contributions->values()->first();
        $current_ticket = CarbonImmutable::parse($contributions->month_year);
        $current_ticket_true = $now->startOfMonth()->subMonths($before_month);
        if($now->startOfMonth()->diffInMonths($current_ticket->startOfMonth()) == $before_month){
          $response = true;
        }
      }
     return  $response;
   }

   public function active_guarantees_sismu(){
    $query = "SELECT Prestamos.IdPrestamo as IdPrestamo, trim(p2.PadNombres) as PadNombres, trim(p2.PadPaterno) as PadPaterno, trim(p2.PadMaterno) as PadMaterno, trim(p2.PadApellidoCasada) as PadApellidoCasada, Prestamos.IdPrestamo, Prestamos.PresNumero, Prestamos.IdPadron, Prestamos.PresCuotaMensual, Prestamos.PresEstPtmo, Prestamos.PresMeses, Prestamos.PresFechaDesembolso, Prestamos.PresFechaPrestamo, Prestamos.PresSaldoAct, Prestamos.PresMntDesembolso, p3.PrdDsc
    FROM Padron
    join PrestamosLevel1 on PrestamosLevel1.IdPadronGar = Padron.IdPadron
    join Prestamos on PrestamosLevel1.IdPrestamo = prestamos.IdPrestamo
    join Padron p2 on p2.IdPadron = Prestamos.IdPadron
    join Producto p3 on p3.PrdCod = Prestamos.PrdCod
    where Padron.PadCedulaIdentidad = '$this->identity_card'
    and Prestamos.PresEstPtmo = 'V'";
    $loans = DB::connection('sqlsrv')->select($query);
    foreach($loans as $loan){
      $query = "SELECT count(*) as quantity
        from PrestamosLevel1
        where PrestamosLevel1.IdPrestamo = '$loan->IdPrestamo'";
        $quantity = DB::connection('sqlsrv')->select($query);
        $loan->quantity_guarantors = $quantity[0]->quantity;
    }
    return $loans;
   }

   public function active_loans_sismu(){
    $query = "SELECT Prestamos.IdPrestamo, Prestamos.PresNumero, Prestamos.IdPadron, Prestamos.PresCuotaMensual, Prestamos.PresEstPtmo, Prestamos.PresMeses, Prestamos.PresFechaDesembolso, Prestamos.PresFechaPrestamo, Prestamos.PresSaldoAct, Prestamos.PresMntDesembolso
    FROM Prestamos
    join Padron on Prestamos.IdPadron = Padron.IdPadron
    where Padron.PadCedulaIdentidad = '$this->identity_card'
    and Prestamos.PresEstPtmo = 'V'";
    $loans = DB::connection('sqlsrv')->select($query);
    return $loans;
   }

   public function process_loans_sismu(){
    $query = "SELECT Prestamos.IdPrestamo, Prestamos.PresNumero, Prestamos.IdPadron, Prestamos.PresCuotaMensual, Prestamos.PresEstPtmo, Prestamos.PresMeses
    FROM Prestamos
    join Padron on Prestamos.IdPadron = Padron.IdPadron
    where Padron.PadCedulaIdentidad = '$this->identity_card'
    and Prestamos.PresEstPtmo = 'A'";
    $loans = DB::connection('sqlsrv')->select($query);
    return $loans;
   }

   public function process_guarantees_sismu(){
    $query = "SELECT Prestamos.IdPrestamo, Prestamos.PresNumero, Prestamos.IdPadron, Prestamos.PresCuotaMensual, Prestamos.PresEstPtmo, Prestamos.PresMeses
    FROM Padron
    join PrestamosLevel1 on PrestamosLevel1.IdPadronGar = Padron.IdPadron
    join Prestamos on PrestamosLevel1.IdPrestamo = prestamos.IdPrestamo
    where Padron.PadCedulaIdentidad = '$this->identity_card'
    and Prestamos.PresEstPtmo = 'A'";
    $loans = DB::connection('sqlsrv')->select($query);
    foreach($loans as $loan){
      $query = "SELECT count(*) as quantity
        from PrestamosLevel1
        where PrestamosLevel1.IdPrestamo = '$loan->IdPrestamo'";
        $quantity = DB::connection('sqlsrv')->select($query);
        $loan->quantity_guarantors = $quantity[0]->quantity;
    }
    return $loans;
   }

   public function getInitialsAttribute(){
     //return (substr($this->first_name, 0, 1).substr($this->second_name, 0, 1).substr($this->last_name, 0, 1).substr($this->mothers_last_name, 0, 1).substr($this->surname_husband, 0, 1));
       return (mb_substr($this->first_name, 0, 1).mb_substr($this->second_name, 0, 1).mb_substr($this->last_name, 0, 1).mb_substr($this->mothers_last_name, 0, 1).mb_substr($this->surname_husband, 0, 1));
   }
   public function default_alert_date_import(){
    $loan_procedure = LoanProcedure::where('is_enable', true)->first()->id;
    $current_date = Carbon::now();
    $days_for_​import = LoanGlobalParameter::where('loan_procedure_id', $loan_procedure)->first()->days_for_​import;
    $year = $current_date->year;
    $month = $current_date->month;
    $current_date = $current_date->endOfDay()->format('Y-m-d'); //Fecha actual
    $days_for_​import_date = Carbon::create($year,$month,$days_for_​import)->endOfDay()->format('Y-m-d');
    if($current_date <= $days_for_​import_date){ //Fecha menor al 20
      $new_current_date = Carbon::parse($current_date)->subMonthNoOverflow()->day($days_for_​import)->endOfDay();
    }else{
      $new_current_date = Carbon::parse($current_date)->copy()->day($days_for_​import)->endOfDay();
    }
    return $new_current_date;
   }

   public function retirement_fund_average()
   {
    return RetirementFundAverage::where('degree_id', $this->degree_id)->where('category_id', $this->category_id)->where('is_active', true)->first();
   }

   public function active_loans_query()
   {
       $loan_state_ids = LoanPaymentState::whereIn('name', ['Pagado'])->pluck('id')->toArray();
   
       return $this->loans()
           ->whereRaw("
               amount_approved - (
                   SELECT COALESCE(SUM(capital_payment), 0)
                   FROM loan_payments
                   WHERE loan_payments.loan_id = loans.id
                     AND loan_payments.state_id IN (" . implode(',', $loan_state_ids) . ")
               ) > 0.001
           ");
   }
   
   public function get_loan_sismu($loan_id){
      $query = "SELECT p.IdPrestamo, p.PresNumero, concat(trim(pad.PadPaterno), ' ', trim(pad.PadMaterno), ' ', trim(pad.PadNombres)) as full_name,
                p.IdPadron, p.PresCuotaMensual, p.PresEstPtmo, p.PresMeses, p.PresFechaDesembolso, p.PresFechaPrestamo, p.PresSaldoAct, p.PresMntDesembolso, 
                p2.PrdDsc, pad.PadCedulaIdentidad, pad.PadMatriculaTit
        FROM Prestamos p
        join Producto p2 on p2.PrdCod = p.PrdCod
        join Padron pad on pad.IdPadron = p.IdPadron
        where p.IdPrestamo = '$loan_id'";
      $loan = DB::connection('sqlsrv')->select($query);
    return $loan;
   }
}