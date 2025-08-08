<?php

namespace App\Http\Controllers\Api\V1;
use App\Address;
use App\Affiliate;
use App\Helpers\Util;
use App\Http\Requests\AddressForm;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** @group Direcciones
* Datos de las direcciones de los afiliados y de aquellas relacionadas con los trámites
*/
class AddressController extends Controller
{
    /**
    * Nueva dirección
    * Inserta nueva dirección
    * @bodyParam city_address_id integer ID de ciudad del CI. Example: 4
    * @bodyParam zone string Zona. Example: Chuquiaguillo
    * @bodyParam street string Calle. Example: Av. Panamericana
    * @bodyParam number_address integer Número de casa. Example: 45
    * @bodyParam description string Los prestamos se añade en este campo toda la direccion. Example: Avenida heroes del Mar nro 100
    * @authenticated
    * @responseFile responses/address/store.200.json
    */
    public function store(AddressForm $request)
    {
        return Address::create($request->all());
    }

    /**
    * Actualizar dirección
    * Actualizar los datos de una dirección existente
    * @urlParam address required ID de dirección. Example: 11805
    * @bodyParam city_address_id integer ID de ciudad del CI. Example: 4
    * @bodyParam zone string Zona. Example: Chuquiaguillo
    * @bodyParam street string Calle. Example: Av. Panamericana
    * @bodyParam number_address integer Número de casa. Example: 45
    * @bodyParam description string Los prestamos se añade en este campo toda la direccion. Example: Avenida heroes del Mar nro 100
    * @authenticated
    * @responseFile responses/address/update.200.json
    */
    public function update(AddressForm $request, Address $address)
    {
        $address->fill($request->all());
        $address->save();
        return $address;
    }

    /**
    * Eliminar dirección
    * Eliminar una dirección solo en caso de que no este relacionada ningún trámite
    * @urlParam address required ID de dirección. Example: 1077
    * @authenticated
    * @responseFile responses/address/destroy.200.json
    */
    public function destroy(Address $address)
    {
        $address->delete();
        return $address;
    }

    public function print_address(Request $request, Affiliate $affiliate, Address $address, $standalone =true)
    {
        // $affiliate = Affiliate::find($affiliate);
        // $address = $affiliate->addresses()->where('addresses.id', $address)->first();
        $image_map = $request->input('imagen');
        $spouse = $affiliate->getSpouseAttribute();
        if($spouse){
            $spouse->fullname = $spouse->fullname;
        }
        
        // Verificar que la dirección pertenece al afiliado
        // if (!$affiliate->addresses->contains($address)) {
        //     abort(403, 'La dirección no pertenece al afiliado.');
        // }
        $data = [
            'header' => [
                'direction' => 'DIRECCIÓN DE ESTRATEGIAS SOCIALES E INVERSIONES',
                'unity' => 'UNIDAD DE INVERSIÓN EN PRÉSTAMOS',
                'table' => [
                    ['Usuario', Auth::user()->username],
                    ['Fecha', Carbon::now()->format('d/m/Y')],
                    ['Hora', Carbon::now()->format('h:m:s a')]
                ]
            ],
            'title' => 'CROQUIS DE UBICACIÓN DE DOMICILIO',

            'affiliate' => $affiliate,
            'address' => $address,
            'spouse' => $spouse,
            'image_map' => $image_map
        ];
        $file_name='';
        $file_name = implode('_', ['CROQUIS', $affiliate->id]) . '.pdf';
        $view = view()->make('address.print_address')->with($data)->render();
        sleep(1);
        if ($standalone) return Util::pdf_to_base64([$view], $file_name, $file_name, 'legal', $request->copies ?? 1);
        return $view;
    }
}