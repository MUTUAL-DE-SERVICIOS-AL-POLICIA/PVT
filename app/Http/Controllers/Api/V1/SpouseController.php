<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Spouse;
use App\Http\Requests\SpouseForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Util;
use App\Module;
use App\LoanBorrower;
use App\LoanState;
use Illuminate\Support\Facades\Auth;

/** @group Cónyugues
* Datos de los cónyugues y métodos para obtener y establecer sus relaciones
*/
class SpouseController extends Controller
{
    public static function append_data(Spouse $spouse)
    {
        $spouse->civil_status_gender = $spouse->civil_status_gender;
        return $spouse;
    }

    /**
    * Lista de cónyugues
    * Devuelve el listado con los datos paginados
    * @queryParam search Parámetro de búsqueda. Example: TEMO
    * @queryParam sortBy Vector de ordenamiento. Example: [last_name]
    * @queryParam sortDesc Vector de orden descendente(true) o ascendente(false). Example: [0]
    * @queryParam per_page Número de datos por página. Example: 8
    * @queryParam page Número de página. Example: 1
    * @authenticated
    * @responseFile responses/spouse/index.200.json
    */
    public function index(Request $request)
    {
        $data = Util::search_sort(new Spouse(), $request);
        $data->getCollection()->transform(function ($spouse) {
            return self::append_data($spouse);
        });
        return $data;
    }

    /**
    * Nuevo cónyugue
    * Inserta nuevo cónyugue
    * @bodyParam first_name string required Primer nombre. Example: MAXIMA
    * @bodyParam last_name string required Apellido paterno. Example: ARUQUIPA
    * @bodyParam city_identity_card_id integer required ID de ciudad del CI. Example: 4
    * @bodyParam birth_date date required Fecha de nacimiento (AÑO-MES-DÍA). Example: 1980-05-02
    * @bodyParam city_birth_id integer required ID de ciudad de nacimiento. Example: 10
    * @bodyParam affiliate_id integer required ID del afiliado. Example: 536
    * @bodyParam identity_card string required Carnet de identidad. Example: 165134-1L
    * @bodyParam civil_status string required Estado civil (S,C,D,V). Example: C
    * @bodyParam second_name string Segundo nombre. Example: ELOISA
    * @bodyParam mothers_last_name string Apellido materno. Example: ROJAS
    * @bodyParam due_date date Fecha de vencimiento del CI. Example: 2018-01-05
    * @bodyParam marriage_date date Fecha de matrimonio (AÑO-MES-DÍA). Example: 2001-04-30
    * @bodyParam registration string Matrícula. Example: 870914VBW
    * @bodyParam surname_husband string Apellido de casada. Example: PAREDES
    * @bodyParam date_death date Fecha de fallecimiento. Example: 2018-09-21
    * @bodyParam reason_death string Causa de fallecimiento. Example: Embolia
    * @bodyParam death_certificate_number string Número de certificado de defunción. Example: 180923-ATR
    * @authenticated
    * @responseFile responses/spouse/store.200.json
    */
    public function store(SpouseForm $request)
    {
        return Spouse::create($request->all());
    }

    /**
    * Detalle de cónyugue
    * Devuelve el detalle de un cónyugue mediante su ID
    * @urlParam id required ID de cónyugue. Example: 42
    * @authenticated
    * @responseFile responses/spouse/show.200.json
    */
    public function show(Spouse $spouse)
    {
        return self::append_data($spouse);
    }

    /**
    * Actualizar cónyugue
    * Actualizar datos personales de cónyugue
    * @urlParam spouse required ID de cónyugue. Example: 42
    * @bodyParam first_name string required Primer nombre. Example: MAXIMA
    * @bodyParam last_name string required Apellido paterno. Example: ARUQUIPA
    * @bodyParam city_identity_card_id integer required ID de ciudad del CI. Example: 4
    * @bodyParam birth_date date required Fecha de nacimiento (AÑO-MES-DÍA). Example: 1980-05-02
    * @bodyParam city_birth_id integer required ID de ciudad de nacimiento. Example: 10
    * @bodyParam affiliate_id integer required ID del afiliado. Example: 536
    * @bodyParam identity_card string required Carnet de identidad. Example: 165134-1L
    * @bodyParam civil_status string required Estado civil (S,C,D,V). Example: C
    * @bodyParam second_name string Segundo nombre. Example: ELOISA
    * @bodyParam mothers_last_name string Apellido materno. Example: ROJAS
    * @bodyParam due_date date Fecha de vencimiento del CI. Example: 2018-01-05
    * @bodyParam marriage_date date Fecha de matrimonio (AÑO-MES-DÍA). Example: 2001-04-30
    * @bodyParam registration string Matrícula. Example: 870914VBW
    * @bodyParam surname_husband string Apellido de casada. Example: PAREDES
    * @bodyParam date_death date Fecha de fallecimiento. Example: 2018-09-21
    * @bodyParam reason_death string Causa de fallecimiento. Example: Embolia
    * @bodyParam death_certificate_number string Número de certificado de defunción. Example: 180923-ATR
    * @authenticated
    * @responseFile responses/spouse/update.200.json
    */
    public function update(SpouseForm $request, Spouse $spouse)
    {
        try{
            DB::beginTransaction();
            $spouse->fill($request->all());
            $spouse->save();

            //actualizar datos de prestatario y garantes
            $id_prestamos = Module::where('name', 'prestamos')->first()->id;
            if(in_array($id_prestamos, Auth::user()->modules, true))
            {
                //$loans = $this->affiliate->processloans;
                foreach ($spouse->affiliate->processloans as $loan)
                {
                    $borrower = LoanBorrower::find($loan->borrower[0]->id);
                    if ($borrower->type == 'spouses')
                    {
                        $borrower->identity_card = $request['identity_card'];
                        $borrower->city_identity_card_id = $request['city_identity_card_id'];
                        $borrower->city_birth_id = $request['city_birth_id'];
                        $borrower->registration = $request['registration'];
                        $borrower->last_name = $request['last_name'];
                        $borrower->first_name = $request['first_name'];
                        $borrower->second_name = $request['second_name'];
                        $borrower->mothers_last_name = $request['mothers_last_name'];
                        $borrower->surname_husband = $request['surname_husband'];
                        $borrower->civil_status = $request['civil_status'];
                        $borrower->address_id = $spouse->affiliate->address->id;
                        $borrower->update();
                    }
                }
                $guarantees = $spouse->affiliate->guarantees;
                foreach($guarantees as $guarantee)
                {
                    if($guarantee->loan->state_id = LoanState::where('name','En Proceso')->first()->id && $guarantee->type == 'spouses')
                    {
                        $guarantee->update([
                            'identity_card' => $request['identity_card'],
                            'city_identity_card_id' => $request['city_identity_card_id'],
                            'city_birth_id' => $request['city_birth_id'],
                            'registration' => $request['registration'],
                            'last_name' => $request['last_name'],
                            'first_name' => $request['first_name'],
                            'second_name' => $request['second_name'],
                            'mothers_last_name' => $request['mothers_last_name'],
                            'surname_husband' => $request['surname_husband'],
                            'civil_status' => $request['civil_status'],
                            'address_id' => $spouse->affiliate->address->id,
                        ]);
                    }
                }
            }
            DB::commit();
            return $spouse;
        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
    * Eliminar cónyugue
    * Eliminar un cónyugue solo en caso de no estar relacionado a ningún afiliado
    * @urlParam id required ID de cónyugue. Example: 42
    * @authenticated
    * @responseFile responses/spouse/destroy.200.json
    */
    public function destroy(Spouse $spouse)
    {
        $spouse->delete();
        return $spouse;
    }
}
