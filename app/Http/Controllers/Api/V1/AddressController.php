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
use Illuminate\Support\Facades\Http;

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
        $image_map = $request->input('imagen');
        $spouse = $affiliate->getSpouseAttribute();
        $address->city_name = $address->cityName();
        if($spouse){
            $spouse->fullname = $spouse->fullname;
        }
        
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

    public function resolve_url(Request $request)
    {

        $url = $request->query('url');

        if (!$url) {
            return response()->json(['error' => 'Falta el parámetro url'], 400);
        }

        try {
            // 1️ Resolver short URL (goo.gl, maps.app.goo.gl)
            if (preg_match('/goo\.gl|maps\.app\.goo\.gl/', $url)) {
                $response = Http::withOptions(['allow_redirects' => true])
                                ->get($url);
                $url = (string) $response->effectiveUri();
            }

            // 2️ Patrones para extraer coordenadas
            $patterns = [
                '/@(-?\d+\.\d+),(-?\d+\.\d+)/',
                '/\/search\/(-?\d+\.\d+)[,|%2C]\+?(-?\d+\.\d+)/i',
                '/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/',
                '/\/dir\/(-?\d+\.\d+)[,|%2C]\+?(-?\d+\.\d+)/i',
                '/[?&]q=loc:(-?\d+\.\d+),(-?\d+\.\d+)/i'
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $url, $matches)) {
                    return response()->json([
                        'lat' => (float) $matches[1],
                        'lng' => (float) $matches[2],
                        'final_url' => $url
                    ]);
                }
            }

            return response()->json(['error' => 'No se encontraron coordenadas'], 404);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error procesando la URL',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}