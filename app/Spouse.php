<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laratrust\Traits\LaratrustUserTrait;
use Util;
use Illuminate\Support\Facades\DB;

class Spouse extends Model
{
    use Traits\EloquentGetTableNameTrait;
    protected $fillable = [
        'affiliate_id',
        'city_identity_card_id',
        'identity_card',
        'registration',
        'last_name',
        'mothers_last_name',
        'first_name',
        'second_name',
        'surname_husband',
        'civil_status',
        'birth_date',
        'date_death',
        'reason_death',
        'city_birth_id',
        'death_certificate_number',
        'due_date',
        'is_duedate_undefined',
        'official',
        'book',
        'departure',
        'marriage_date',
        'marriage_issue_date',
        'cell_phone_number'
    ];

    public function getCivilStatusGenderAttribute()
    {
        $civil_status = Util::get_civil_status($this->civil_status, $this->gender);
        unset($this->affiliate);
        return $civil_status;
    }

    public function getTitleAttribute()
    {   return '';
        //return 'Vd' . ($this->affiliate->gender == 'M' ? 'a' : 'o') . '.';
    }

    public function getGenderAttribute()
    {
        return $this->affiliate->gender == 'M' ? 'F' : 'M';
    }

    public function getIdentityCardExtAttribute()
    {
        $data = $this->identity_card;
        if ($this->city_identity_card && $this->city_identity_card != 'NINGUNO'){
          $data .= ' ' . $this->city_identity_card->first_shortened;
        }
        return rtrim($data);
    }

    public function getFullNameAttribute()
    {
        return preg_replace('/[[:blank:]]+/', ' ', join(' ', [$this->first_name, $this->second_name, $this->last_name, $this->mothers_last_name,$this->surname_husband]));
    }
    
    public function getDeadAttribute()
    {
      return ($this->date_death != null || $this->reason_death != null || $this->death_certificate_number != null);
    }
    
    public function getAddressAttribute()
    {
        return $this->affiliate->address;
    }

    // public function getCellPhoneNumberAttribute()
    // {
    //     return $this->affiliate->cell_phone_number;
    // }

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }
    public function city_identity_card()
    {
        return $this->belongsTo(City::class, 'city_identity_card_id', 'id');
    }
    public function city_birth()
    {
        return $this->belongsTo(City::class, 'city_birth_id', 'id');
    }

    public function records()
    {
      return $this->morphMany(Record::class, 'recordable');
    }
    
    public function loans(){
        return Loan::whereIn('id', function($query){
            $query->select('loan_id')->from('loan_borrowers')->where('affiliate_id',$this->affiliate_id)->where('type','spouses');
        })->whereIn('state_id',[1,3])->orderBy('loans.created_at', 'desc')->get();
    }

    public function active_loans()
    {
        return Loan::whereIn('id', function($query){
            $query->select('loan_id')->from('loan_borrowers')->where('affiliate_id',$this->affiliate_id)->where('type','spouses');
        })->count();
    }

    public function spouse_loans()
    {
        return Loan::whereIn('id', function($query){
            $query->select('loan_id')->from('loan_borrowers')->where('affiliate_id',$this->affiliate_id)->where('type','spouses');
        })->orderBy('loans.created_at', 'desc')->get();
    }

    public function spouse_guarantees()
    {
        return Loan::whereIn('id', function($query){
            $query->select('loan_id')->from('loan_guarantors')->where('affiliate_id',$this->affiliate_id)->where('type','spouses');
        })->orderBy('loans.created_at', 'desc')->get();
    }

    public function spouse_active_guarantees()
    {
        return Loan::whereIn('id', function($query){
            $query->select('loan_id')->from('loan_guarantors')->where('affiliate_id',$this->affiliate_id)->where('type','spouses');
        })->whereIn('state_id',[1,3])->get();
    }

    public function current_loans()
    {
        $loan_state = LoanState::whereName('Vigente')->first();
        return Loan::whereIn('id', function($query){
            $query->select('loan_id')->from('loan_guarantors')->where('affiliate_id',$this->affiliate_id)->where('type','spouses');
        })->where('state_id',$loan_state->id)->orderBy('loans.created_at', 'desc')->get();;
    }

    public function active_guarantees_sismu(){
        $query = "SELECT trim(p2.PadNombres) as PadNombres,trim(p2.PadPaterno) as PadPaterno, trim(p2.PadMaterno) as PadMaterno, trim(p2.PadApellidoCasada) as PadApellidoCasada, Prestamos.IdPrestamo, Prestamos.PresNumero, Prestamos.IdPadron, Prestamos.PresCuotaMensual, Prestamos.PresEstPtmo, Prestamos.PresMeses
        FROM Padron
        join PrestamosLevel1 on PrestamosLevel1.IdPadronGar = Padron.IdPadron
        join Prestamos on PrestamosLevel1.IdPrestamo = prestamos.IdPrestamo
        join Padron p2 on p2.IdPadron = Prestamos.IdPadron
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
        $query = "SELECT Prestamos.IdPrestamo, Prestamos.PresNumero, Prestamos.IdPadron, Prestamos.PresCuotaMensual, Prestamos.PresEstPtmo, Prestamos.PresMeses
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
            $loan->quantity = $quantity->quantity;
        }
        return $loans;
       }

       public function getInitialsAttribute(){
        //return (substr($this->first_name, 0, 1).substr($this->second_name, 0, 1).substr($this->last_name, 0, 1).substr($this->mothers_last_name, 0, 1).substr($this->surname_husband, 0, 1));
        return (mb_substr($this->first_name, 0, 1).mb_substr($this->second_name, 0, 1).mb_substr($this->last_name, 0, 1).mb_substr($this->mothers_last_name, 0, 1).mb_substr($this->surname_husband, 0, 1));
      }
}
