<?php

namespace App;

use App\Helpers\Util;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use Traits\EloquentGetTableNameTrait;

    protected $table = "addresses";
    public $guarded =  [];
    protected $fillable = ['city_address_id', 'zone', 'street', 'housing_unit', 'number_address','description','latitude','longitude'];

    protected $attributes = array(
        'city_address_id' => null,
        'zone' => null,
        'street' => null,
        'housing_unit' => null,
        'number_address' => null,
        'description' => null,
        'latitude' => null,
        'longitude' => null,
    );

    public function getFullAddressAttribute($value)
    {
        // if (!$this->number_address || Util::trim_spaces($this->number_address) == '') {
        //     $number = 'S/N';
        // } else {
        //     $number = 'Nº ' . $this->number_address;
        // }
        //return Util::trim_spaces(implode(' ', [$this->description]));

        if($this->zone || $this->street || $this->housing_unit || $this->number_address){
            $zone = Util::trim_spaces($this->zone) ? :'';
            $street = Util::trim_spaces($this->street) ? : '';
            $number_address = Util::trim_spaces($this->number_address) ? : '';
            $housing_unit = Util::trim_spaces($this->housing_unit) ? : '';
            return "{$zone} {$street} {$number_address} {$housing_unit}";

        } else if($this->description){
            return Util::trim_spaces($this->description);
        } else {
            return 'SIN REGISTRO    ';
        }
    }

    public function affiliate()
    {
    	return $this->belongsToMany(Affiliate::class);
    }
    public function city()
    {
        return $this->belongsTo(City::class,'city_address_id','id');
    }
    public function cityName()
    {
        return $this->city->name;
    }
}