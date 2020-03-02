<?php

namespace App\Http\Controllers\Api\V1;

use \Waavi\Sanitizer\Sanitizer;
use Util;
use App\Affiliate;
use App\RecordType;
use App\User;
use App\Category;
use App\Degree;
use App\City;
use App\Hierarchy;
use App\AffiliateState;
use App\AffiliateStateType;
use App\Spouse;
use App\Contribution;
use App\Unit;
use App\Loan;
use App\LoanGlobalParameter;
use App\ProcedureType;
use App\Http\Requests\AffiliateForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Events\FingerprintSavedEvent;
use Illuminate\Support\Facades\Storage;
use Carbon\CarbonImmutable;

/** @group Datos Afiliado
* Datos de los afiliados y métodos para obtener y establecer sus relaciones
*/
class AffiliateController extends Controller
{
    /**
    * Lista de afiliados
    * Devuelve el listado con los datos paginados
    * @queryParam search Parámetro de búsqueda. Example: TORRE
    * @queryParam sortBy Vector de ordenamiento. Example: [last_name]
    * @queryParam sortDesc Vector de orden descendente(true) o ascendente(false). Example: [false]
    * @queryParam per_page Número de datos por página. Example: 8
    * @queryParam page Número de página. Example: 1
    * @authenticated
    * @response
    * {
    *     "current_page": 1,
    *     "data": [
    *         {
    *             "id": 3561,
    *             "user_id": 1,
    *             "affiliate_state_id": 5,
    *             "city_identity_card_id": 4,
    *             "city_birth_id": 4,
    *             "degree_id": 32,
    *             "unit_id": 19,
    *             "category_id": 8,
    *             "pension_entity_id": 2,
    *             "identity_card": "2234288",
    *             "registration": "540328TMS",
    *             "type": "Comando",
    *             "last_name": "TORRE",
    *             "mothers_last_name": "MULLISACA",
    *             "first_name": "SIXTO",
    *             "second_name": null,
    *             "surname_husband": null,
    *             "gender": "M",
    *             "civil_status": "C",
    *             "birth_date": "1954-03-28",
    *             "date_entry": "1983-01-01",
    *             "date_death": null,
    *             "reason_death": "",
    *             "date_derelict": null,
    *             "reason_derelict": null,
    *             "change_date": "2016-07-01",
    *             "phone_number": "",
    *             "cell_phone_number": "(720)-15937",
    *             "afp": true,
    *             "nua": 26271503,
    *             "item": 32706,
    *             "created_at": "2017-06-01 10:43:12",
    *             "updated_at": "2018-10-26 10:04:49",
    *             "deleted_at": null,
    *             "service_years": null,
    *             "service_months": null,
    *             "death_certificate_number": "",
    *             "due_date": null,
    *             "is_duedate_undefined": true,
    *             "affiliate_registration_number": null,
    *             "file_code": null,
    *             "picture_saved": true,
    *             "fingerprint_saved": true,
    *             "full_name": "SIXTO TORRE MULLISACA"
    *         }, {}
    *     ],
    *     "first_page_url": "http://127.0.0.1/api/v1/affiliate?page=1",
    *     "from": 1,
    *     "last_page": 5760,
    *     "last_page_url": "http://127.0.0.1/api/v1/affiliate?page=5760",
    *     "next_page_url": "http://127.0.0.1/api/v1/affiliate?page=2",
    *     "path": "http://127.0.0.1/api/v1/affiliate",
    *     "per_page": 10,
    *     "prev_page_url": null,
    *     "to": 10,
    *     "total": 57592
    * }
    */
    public function index(Request $request)
    {
        $data = Util::search_sort(new Affiliate(), $request);
        foreach ($data as $item) {
            $this->append_data($item);
        }
        return $data;
    }

    /**
    * Nuevo afiliado
    * Inserta nuevo afiliado
    * @bodyParam first_name string required Primer nombre. Example: JUAN
    * @bodyParam last_name string required Apellido paterno. Example: PINTO
    * @bodyParam gender string required Género (M,F). Example: M
    * @bodyParam birth_date date required Fecha de nacimiento (AÑO-MES-DÍA). Example: 1980-05-02
    * @bodyParam city_birth_id integer required ID de ciudad de nacimiento. Example: 10
    * @bodyParam city_identity_card_id integer required ID de ciudad del CI. Example: 4
    * @bodyParam civil_status string required Estado civil (S,C,D,V). Example: C
    * @bodyParam identity_card string required Carnet de identidad. Example: 165134-1L
    * @bodyParam affiliate_state_id integer ID de estado de afiliado. Example: 2
    * @bodyParam degree_id integer ID del grado policial. Example: 4
    * @bodyParam unit_id integer ID de unidad de destino. Example: 7
    * @bodyParam category_id integer ID de categoría. Example: 9
    * @bodyParam pension_entity_id integer ID de entidad de pensiones. Example: 1
    * @bodyParam registration string Matrícula. Example: 870914VBW
    * @bodyParam type string Tipo de destino (Comando, Batallón). Example: Comando
    * @bodyParam second_name string Segundo nombre. Example: ROBERTO
    * @bodyParam mothers_last_name string Apellido materno. Example: ROJAS
    * @bodyParam surname_husband string Apellido de casada. Example: PAREDES
    * @bodyParam date_entry date Fecha de ingreso a la policía. Example: 1980-05-20
    * @bodyParam date_death date Fecha de fallecimiento. Example: 2018-09-21
    * @bodyParam death_certificate_number string Número de certificado de defunción. Example: 180923-ATR
    * @bodyParam reason_death string Causa de fallecimiento. Example: Embolia
    * @bodyParam date_derelict date Fecha de baja de la policía. Example: 2017-12-30
    * @bodyParam reason_derelict string Causa de baja de la policía. Example: Proceso administrativo
    * @bodyParam due_date date Fecha de vencimiento del CI. Example: 2018-01-05
    * @bodyParam is_duedate_undefined boolean Si la fecha de vencimiento de CI es indefinido . Example: 0
    * @bodyParam change_date date Fecha de cambio. Example: 2015-02-03
    * @bodyParam phone_number integer Número de teléfono fijo. Example: 2254101
    * @bodyParam cell_phone_number array Números de celular. Example: [76543210,65432101]
    * @bodyParam afp boolean Si el afiliado aporta a AFP(1) o SENASIR(0). Example: 1
    * @bodyParam nua integer Número NUA. Example: 26271503
    * @bodyParam item integer Número de ítem policial. Example: 32706
    * @bodyParam service_years integer Años de servicio. Example: 6
    * @bodyParam service_months integer Meses de servicio. Example: 4
    * @bodyParam affiliate_registration_number integer Número único de registro de afiliado. Example: 10512
    * @bodyParam file_code string Código de folder de afiliado. Example: AFW-12
    * @authenticated
    * @response
    * {
    *     "first_name": "JUAN",
    *     "last_name": "PINTO",
    *     "gender": "M",
    *     "birth_date": "1980-05-02",
    *     "city_birth_id": 10,
    *     "city_identity_card_id": 4,
    *     "civil_status": "C",
    *     "identity_card": "165134-1L",
    *     "affiliate_state_id": 2,
    *     "degree_id": 4,
    *     "unit_id": 7,
    *     "category_id": 9,
    *     "pension_entity_id": 1,
    *     "registration": "870925VBW",
    *     "type": "Comando",
    *     "second_name": "ROBERTO",
    *     "mothers_last_name": "ROJAS",
    *     "surname_husband": "PAREDES",
    *     "date_entry": "1980-05-20",
    *     "date_death": "2018-09-21",
    *     "reason_death": "EMBOLIA",
    *     "date_derelict": "2017-12-30",
    *     "reason_derelict": "Proceso administrativo",
    *     "due_date": "2018-01-05",
    *     "is_duedate_undefined": false,
    *     "change_date": "2015-02-03",
    *     "phone_number": 2254101,
    *     "cell_phone_number": "[76543210,65432101]",
    *     "afp": true,
    *     "nua": 26271503,
    *     "item": 32706,
    *     "updated_at": "2020-02-07 15:31:31",
    *     "created_at": "2020-02-07 15:31:31",
    *     "id": 58662
    * }
    */
    public function store(AffiliateForm $request)
    {
        return Affiliate::create($request->all());
    }

    /**
    * Detalle de afiliado
    * Devuelve el detalle de un afiliado mediante su ID
    * @urlParam affiliate required ID de afiliado. Example: 54
    * @response
    * {
    *     "id": 54,
    *     "user_id": 47,
    *     "affiliate_state_id": 5,
    *     "city_identity_card_id": 5,
    *     "city_birth_id": 7,
    *     "degree_id": 12,
    *     "unit_id": 1,
    *     "category_id": 8,
    *     "pension_entity_id": 2,
    *     "identity_card": "678681",
    *     "registration": "570702HGT",
    *     "type": "Comando",
    *     "last_name": "HIDALGO",
    *     "mothers_last_name": "GOMEZ",
    *     "first_name": "TRIFON",
    *     "second_name": null,
    *     "surname_husband": null,
    *     "gender": "M",
    *     "civil_status": "C",
    *     "birth_date": "1957-07-02",
    *     "date_entry": "1981-01-01",
    *     "date_death": null,
    *     "reason_death": null,
    *     "date_derelict": "2017-12-01",
    *     "reason_derelict": null,
    *     "change_date": "2017-12-01",
    *     "phone_number": "(6) 435-896",
    *     "cell_phone_number": "(754)-41119",
    *     "afp": true,
    *     "nua": 9439900,
    *     "item": 28709,
    *     "created_at": "2017-06-01 10:42:18",
    *     "updated_at": "2019-10-18 15:52:59",
    *     "deleted_at": null,
    *     "service_years": 37,
    *     "service_months": 0,
    *     "death_certificate_number": "",
    *     "due_date": null,
    *     "is_duedate_undefined": true,
    *     "affiliate_registration_number": null,
    *     "file_code": "",
    *     "picture_saved": false,
    *     "fingerprint_saved": false,
    *     "full_name": "TRIFON HIDALGO GOMEZ",
    *     "dead": false,
    *     "defaulted": false
    * }
    */
    public function show($id)
    {
        $affiliate = Affiliate::findOrFail($id);
        $this->append_data($affiliate);
        $affiliate->dead = $affiliate->dead;
        $affiliate->defaulted = $affiliate->defaulted;
        $affiliate->cpop = $affiliate->cpop;
        return $affiliate;
    }

    /**
    * Actualizar afiliado
    * Actualizar datos personales de afiliado
    * @urlParam id required ID de afiliado. Example: 54
    * @bodyParam first_name string Primer nombre. Example: JUAN
    * @bodyParam last_name string Apellido paterno. Example: PINTO
    * @bodyParam gender string Género (M,F). Example: M
    * @bodyParam birth_date date Fecha de nacimiento (AÑO-MES-DÍA). Example: 1980-05-02
    * @bodyParam city_birth_id integer ID de ciudad de nacimiento. Example: 10
    * @bodyParam city_identity_card_id integer ID de ciudad del CI. Example: 4
    * @bodyParam civil_status string Estado civil (S,C,D,V). Example: C
    * @bodyParam identity_card string Carnet de identidad. Example: 165134-1L
    * @bodyParam affiliate_state_id integer ID de estado de afiliado. Example: 2
    * @bodyParam degree_id integer ID del grado policial. Example: 4
    * @bodyParam unit_id integer ID de unidad de destino. Example: 7
    * @bodyParam category_id integer ID de categoría. Example: 9
    * @bodyParam pension_entity_id integer ID de entidad de pensiones. Example: 1
    * @bodyParam registration string Matrícula. Example: 870914VBW
    * @bodyParam type string Tipo de destino (Comando, Batallón). Example: Comando
    * @bodyParam second_name string Segundo nombre. Example: ROBERTO
    * @bodyParam mothers_last_name string Apellido materno. Example: ROJAS
    * @bodyParam surname_husband string Apellido de casada. Example: PAREDES
    * @bodyParam date_entry date Fecha de ingreso a la policía. Example: 1980-05-20
    * @bodyParam date_death date Fecha de fallecimiento. Example: 2018-09-21
    * @bodyParam death_certificate_number date Fecha de certificado de defunción. Example: 2018-09-23
    * @bodyParam reason_death string Causa de fallecimiento. Example: Embolia
    * @bodyParam date_derelict date Fecha de baja de la policía. Example: 2017-12-30
    * @bodyParam reason_derelict string Causa de baja de la policía. Example: Proceso administrativo
    * @bodyParam due_date date Fecha de vencimiento del CI. Example: 2018-01-05
    * @bodyParam is_duedate_undefined boolean Si la fecha de vencimiento de CI es indefinido . Example: 0
    * @bodyParam change_date date Fecha de cambio. Example: 2015-02-03
    * @bodyParam phone_number integer Número de teléfono fijo. Example: 2254101
    * @bodyParam cell_phone_number array Números de celular. Example: [76543210,65432101]
    * @bodyParam afp boolean Si el afiliado aporta a AFP(1) o SENASIR(0). Example: 1
    * @bodyParam nua integer Número NUA. Example: 26271503
    * @bodyParam item integer Número de ítem policial. Example: 32706
    * @bodyParam service_years integer Años de servicio. Example: 6
    * @bodyParam service_months integer Meses de servicio. Example: 4
    * @bodyParam affiliate_registration_number integer Número único de registro de afiliado. Example: 10512
    * @bodyParam file_code string Código de folder de afiliado. Example: AFW-12
    * @authenticated
    * @response
    * {
    *     "first_name": "JUAN",
    *     "last_name": "PINTO",
    *     "gender": "M",
    *     "birth_date": "1980-05-02",
    *     "city_birth_id": 10,
    *     "city_identity_card_id": 4,
    *     "civil_status": "C",
    *     "identity_card": "165134-1L",
    *     "affiliate_state_id": 2,
    *     "degree_id": 4,
    *     "unit_id": 7,
    *     "category_id": 9,
    *     "pension_entity_id": 1,
    *     "registration": "870925VBW",
    *     "type": "Comando",
    *     "second_name": "ROBERTO",
    *     "mothers_last_name": "ROJAS",
    *     "surname_husband": "PAREDES",
    *     "date_entry": "1980-05-20",
    *     "date_death": "2018-09-21",
    *     "reason_death": "EMBOLIA",
    *     "date_derelict": "2017-12-30",
    *     "reason_derelict": "Proceso administrativo",
    *     "due_date": "2018-01-05",
    *     "is_duedate_undefined": false,
    *     "change_date": "2015-02-03",
    *     "phone_number": 2254101,
    *     "cell_phone_number": "[76543210,65432101]",
    *     "afp": true,
    *     "nua": 26271503,
    *     "item": 32706,
    *     "updated_at": "2020-02-07 15:31:31",
    *     "created_at": "2020-02-07 15:31:31",
    *     "id": 54
    * }
    */
    public function update(AffiliateForm $request, $id)
    {
        $affiliate = Affiliate::findOrFail($id);
        if (!Auth::user()->can('update-affiliate-primary') && ($request->has('phone_number') || $request->has('cell_phone_number'))) {
            $update = $request->only(['phone_number', 'cell_phone_number']);
        } else {
            $update = $request->all();
        }
        $affiliate->fill($update);
        $affiliate->save();
        return  $affiliate;
    }

    /**
    * Eliminar afiliado
    * Elimina un afiliado solo en caso de que no hubiese iniciado ningún trámite
    * @urlParam id required ID de afiliado. Example: 54
    * @authenticated
    * @response
    * {
    *     "id": 58662,
    *     "user_id": null,
    *     "affiliate_state_id": 2,
    *     "city_identity_card_id": 4,
    *     "city_birth_id": 10,
    *     "degree_id": 4,
    *     "unit_id": 7,
    *     "category_id": 9,
    *     "pension_entity_id": 1,
    *     "identity_card": "165134-1L",
    *     "registration": "870925VBW",
    *     "type": "Comando",
    *     "last_name": "PINTO",
    *     "mothers_last_name": "ROJAS",
    *     "first_name": "JUAN",
    *     "second_name": "ROBERTO",
    *     "surname_husband": "PAREDES",
    *     "gender": "M",
    *     "civil_status": "C",
    *     "birth_date": "1980-05-02",
    *     "date_entry": "1980-05-20",
    *     "date_death": "2018-09-21",
    *     "reason_death": "EMBOLIA",
    *     "date_derelict": "2017-12-30",
    *     "reason_derelict": "Proceso administrativo",
    *     "change_date": "2015-02-03",
    *     "phone_number": "2254101",
    *     "cell_phone_number": "[76543210,65432101]",
    *     "afp": true,
    *     "nua": 26271503,
    *     "item": 32706,
    *     "created_at": "2020-02-07 15:31:31",
    *     "updated_at": "2020-02-07 15:31:31",
    *     "deleted_at": null,
    *     "service_years": null,
    *     "service_months": null,
    *     "death_certificate_number": null,
    *     "due_date": "2018-01-05",
    *     "is_duedate_undefined": false,
    *     "affiliate_registration_number": null,
    *     "file_code": null
    * }
    */
    public function destroy($id)
    {
        //
        $affiliate = Affiliate::findOrFail($id);
        $affiliate->delete();
        return $affiliate;
    }

    /**
    * Finalizar captura de huellas
    * Finaliza la captura de huellas en el dispositivo biométrico y envía un mensaje con el estado mediante sockets en el canal: fingerprint; el ejemplo es el de la respuesta con código 201
    * @urlParam id required ID de afiliado. Example: 2
    * @queryParam user_id required ID de usuario que realizó la captura. Example: 23
    * @response 200
    * {
    *     "message": "Message broadcasted"
    * }
    * @response 201
    * {
    *    "data": {
    *        "affiliate_id": 2,
    *        "user_id": 23,
    *        "success": true
    *    }
    * }
    */
    public function fingerprint_saved(Request $request, $id)
    {
        $affiliate = Affiliate::findOrFail($id);
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'success' => 'required|boolean'
        ]);
        $user = User::findOrFail($request->user_id);
        if ($user->active) {
            event(new FingerprintSavedEvent($affiliate, $user, $request->success));
            return response()->json([
                'message' => 'Message broadcasted'
            ], 200);
        } else {
            abort(401);
        }
    }

    /**
    * Registrar huellas
    * Inicia la captura de huellas en el dispositivo biométrico, la respuesta es enviada también mediante sockets en el canal: record; dicha difusión contiene la misma información que la respuesta de ejemplo
    * @urlParam id required ID de afiliado. Example: 2
    * @response
    * {
    *     "id": 4726,
    *     "user_id": 127,
    *     "record_type_id": 5,
    *     "recordable_id": 2,
    *     "recordable_type": "affiliates",
    *     "action": "[Datos Personales] El usuario admin inició la captura de huellas. Afiliado: RAFAEL GONZALO BORJA LLANOS",
    *     "created_at": "2020-02-10 09:15:15",
    *     "updated_at": "2020-02-10 09:15:15"
    * }
    */
    public function update_fingerprint($id)
    {
        $affiliate = Affiliate::findOrFail($id);
        if ($record_type) {
            $affiliate->records()->create([
                'user_id' => Auth::user()->id,
                'record_type_id' => RecordType::whereName('datos-personales')->first()->id,
                'action' => 'inició la captura de huellas'
            ]);
            return $affiliate->records()->latest()->first();
        }
        abort(404);
    }

    // Append additional to affiliate
    private function append_data($affiliate) {
        $affiliate->picture_saved = $affiliate->picture_saved;
        $affiliate->fingerprint_saved = $affiliate->fingerprint_saved;
        $affiliate->full_name = $affiliate->full_name;
    }

    /**
    * Imagen perfil afiliado
    * Devuelve el listado con los nombres de los archivos de imagen, el contenido en base64 y el formato
    * @urlParam id required ID de afiliado. Example: 2
    * @response
    * [
    *     {
    *         "name": "2_perfil.jpg",
    *         "content": "4AAQSkZJR...",
    *         "format": "image/jpeg"
    *     }
    * ]
    */
    public function get_profile_images(Request $request, $id)
    {
        $files = [];
        $base_path = 'picture/';
        $fingerprint_files = ['_perfil.jpg'];
        foreach ($fingerprint_files as $file) {
            if (Storage::disk('ftp')->exists($base_path . $id . $file)) {
                array_push($files, [
                    'name' => $id . $file,
                    'content' => base64_encode(Storage::disk('ftp')->get($base_path . $id . $file)),
                    'format' => Storage::disk('ftp')->mimeType($base_path . $id . $file)
                ]);
            }
        }
        return $files;
    }

    /**
    * Imagen huellas afiliado
    * Devuelve el listado con los nombres de los archivos de imagen, el contenido en base64 y el formato
    * @urlParam id required ID de afiliado. Example: 2
    * @response
    * [
    *     {
    *         "name": "2_left_four.png",
    *         "content": "iVBORw0K...",
    *         "format": "image/png"
    *     },
    *     {
    *         "name": "2_thumbs.png",
    *         "content": "AIMcAAQA...",
    *         "format": "image/png"
    *     }, {}
    * ]
    */
    public function get_fingerprint_images($id)
    {
        $files = [];
        $base_path = 'picture/';
        $fingerprint_files = ['_left_four.png', '_right_four.png', '_thumbs.png'];
        foreach ($fingerprint_files as $file) {
            if (Storage::disk('ftp')->exists($base_path . $id . $file)) {
                array_push($files, [
                    'name' => $id . $file,
                    'content' => base64_encode(Storage::disk('ftp')->get($base_path . $id . $file)),
                    'format' => Storage::disk('ftp')->mimeType($base_path . $id . $file)
                ]);
            }
        }
        return $files;
    }

    /** @group INDEFINIDO (TODO) */
    public function picture_save(Request $request, $id)
    {
    //$picture=$request->all();
    $base_path = 'picture/';
    $affiliate = Affiliate::findOrFail($id);
    $code = $affiliate->id;
    $image = $request->image;   
    $image = str_replace('data:image/jpeg;base64,', '', $image);
    $image = str_replace(' ', '+', $image);
    $imageName = $code.'_perfil.'.'jpg';

    Storage::disk('ftp')->put($base_path.$imageName, base64_decode($image));

    }

    /**
    * Cónyugue
    * Devuelve los datos del o la cónyugue en caso de que el afiliado hubiese fallecido
    * @urlParam id required ID de afiliado. Example: 12
    * @response
    * {
    *     "id": 42,
    *     "user_id": 47,
    *     "affiliate_id": 12,
    *     "city_identity_card_id": 2,
    *     "identity_card": "1048652",
    *     "registration": "",
    *     "last_name": "FORTUN",
    *     "mothers_last_name": null,
    *     "first_name": "MARIA",
    *     "second_name": "LUISA",
    *     "surname_husband": "VDA. DE VILLALBA",
    *     "civil_status": "V",
    *     "birth_date": "1947-06-08",
    *     "date_death": null,
    *     "reason_death": "",
    *     "created_at": "2017-06-08 11:56:17",
    *     "updated_at": "2019-06-14 11:25:21",
    *     "deleted_at": null,
    *     "city_birth_id": 2,
    *     "death_certificate_number": "",
    *     "due_date": null,
    *     "is_duedate_undefined": true,
    *     "official": "600",
    *     "book": "0024-64",
    *     "departure": "143",
    *     "marriage_date": "1964-12-12"
    * }
    */
    public function get_spouse($id) {
        $spouse = Spouse::where('affiliate_id',$id)->first();
        return response()->json($spouse);
    }

    /**
    * Obtener direcciones
    * Devuelve la lista de direcciones del afiliado
    * @urlParam id required ID de afiliado. Example: 1
    * @response
    * [
    *     {
    *         "id": 6291,
    *         "city_address_id": 2,
    *         "zone": "CENTRAL",
    *         "street": "URB. LOYOLA",
    *         "number_address": "2",
    *         "created_at": "2019-06-12 17:08:45",
    *         "updated_at": "2019-06-12 17:08:45",
    *         "latitude": 0,
    *         "longitude": 0,
    *         "pivot": {
    *             "addressable_id": 1,
    *             "address_id": 6291,
    *             "addressable_type": "affiliates",
    *             "created_at": "2019-06-12 17:08:45",
    *             "updated_at": "2019-06-12 17:08:45"
    *         }
    *     }, {}
    * ]
    */
    public function get_addresses($id) {
        $affiliate = Affiliate::findOrFail($id);
        return $affiliate->addresses()->orderByDesc('created_at')->get();
    }

    /**
    * Actualizar direcciones
    * Actualiza el listado de direcciones de un afiliado
    * @urlParam id required ID de afiliado. Example: 12
    * @queryParam addresses required Lista de IDs de direcciones. Example: [12,17]
    * @response
    * {
    *     "attached": [
    *         12,
    *         17
    *     ],
    *     "detached": [
    *         6291
    *     ],
    *     "updated": []
    * }
    */
    public function update_addresses(Request $request, $id) {
        $affiliate = Affiliate::findOrFail($id);
        $request->validate([
            'addresses' => 'required|array|min:1',
            'addresses.*' => 'exists:addresses,id'
        ]);
        return $affiliate->addresses()->sync($request->addresses);
    }

    /**
    * Boletas de pago
    * Devuelve el listado de las boletas de pago de un afiliado, si se envía el ID de ciudad además devuelve un booleano para identificar si la petición contiene las últimas boletas y la diferencia de meses que se utilizó para la operación
    * @urlParam id required ID de afiliado. Example: 1
    * @queryParam city_id ID de la ciudad de solicitud. Example: 4
    * @queryParam sortBy Vector de ordenamiento. Example: [month_year]
    * @queryParam sortDesc Vector de orden descendente(true) o ascendente(false). Example: [true]
    * @queryParam per_page Número de datos por página. Example: 3
    * @queryParam page Número de página. Example: 1
    * @authenticated
    * @response
    * {
    *     "valid": false,
    *     "diff_months": 1,
    *     "current_page": 1,
    *     "data": [
    *         {
    *             "id": 4461601,
    *             "user_id": 1,
    *             "affiliate_id": 1,
    *             "degree_id": 7,
    *             "unit_id": 1,
    *             "breakdown_id": 10,
    *             "category_id": 8,
    *             "month_year": "2012-10-01",
    *             "item": "27446",
    *             "type": "Planilla",
    *             "base_wage": "3195.00",
    *             "seniority_bonus": "3195.00",
    *             "study_bonus": "639.00",
    *             "position_bonus": "0.00",
    *             "border_bonus": "0.00",
    *             "east_bonus": "0.00",
    *             "public_security_bonus": "470.00",
    *             "deceased": "0",
    *             "natality": "0",
    *             "lactation": "0",
    *             "prenatal": "0",
    *             "subsidy": "0.00",
    *             "gain": "7499.00",
    *             "payable_liquid": "5157.20",
    *             "quotable": "7029.00",
    *             "retirement_fund": "130.04",
    *             "mortuary_quota": "45.69",
    *             "subtotal": null,
    *             "interest": null,
    *             "total": "175.73",
    *             "created_at": "2017-06-02 17:04:59",
    *             "updated_at": "2017-06-02 17:04:59",
    *             "deleted_at": null,
    *             "contribution_type_id": null,
    *             "valid": true
    *         }, {}
    *     ],
    *     "first_page_url": "http://127.0.0.1/api/v1/affiliate/1/contribution?page=1",
    *     "from": 1,
    *     "last_page": 55,
    *     "last_page_url": "http://127.0.0.1/api/v1/affiliate/1/contribution?page=55",
    *     "next_page_url": "http://127.0.0.1/api/v1/affiliate/1/contribution?page=2",
    *     "path": "http://127.0.0.1/api/v1/affiliate/1/contribution",
    *     "per_page": "3",
    *     "prev_page_url": null,
    *     "to": 3,
    *     "total": 165
    * }
    */
    public function get_contributions(Request $request, $id)
    {
        $affiliate = Affiliate::findOrFail($id);
        $filters = [
            'affiliate_id' => $affiliate->id
        ];
        $contributions = Util::search_sort(new Contribution(), $request, $filters);
        if ($request->has('city_id')) {
            $is_latest = false;
            $city = City::findOrFail($request->city_id);
            $offset_day = LoanGlobalParameter::latest()->first()->offset_day;
            $now = CarbonImmutable::now();
            if ($now->day <= $offset_day || $city->name != 'LA PAZ') {
                $before_month = 2;
            } else {
                $before_month = 1;
            }
            $current_ticket = CarbonImmutable::parse($contributions[0]->month_year);
            if ($now->startOfMonth()->diffInMonths($current_ticket->startOfMonth()) <= $before_month) {
                foreach ($contributions as $i => $ticket) {
                    $is_latest = true;
                    if ($ticket != $contributions->last()) {
                        $current_ticket = CarbonImmutable::parse($ticket->month_year);
                        $next_ticket = CarbonImmutable::parse($contributions[$i+1]->month_year);
                        if ($current_ticket->startOfMonth()->diffInMonths($next_ticket->startOfMonth()) !== 1) {
                            $is_latest = false;
                            break;
                        }
                    }
                }
            } else {
                $is_latest = false;
            }
            $contributions = collect([
                'valid' => $is_latest,
                'diff_months' => $before_month
            ])->merge($contributions);
        }
        return $contributions;
    }

    /**
    * Categoría
    * Devuelve la categoría policial del afiliado
    * @urlParam id required ID de afiliado. Example: 12
    * @authenticated
    * @response
    * {
    *     "id": 8,
    *     "from": 29,
    *     "to": 100,
    *     "name": "100%",
    *     "percentage": "1.00"
    * }
    */
    public function get_category($id)
    {
        $affiliate = Affiliate::findOrFail($id);
        return $affiliate->category;
    }

    /**
    * Grado
    * Devuelve el grado policial del afiliado
    * @urlParam id required ID de afiliado. Example: 12
    * @authenticated
    * @response
    * {
    *     "id": 8,
    *     "hierarchy_id": 2,
    *     "code": "03",
    *     "name": "TENIENTE CORONEL",
    *     "shortened": "TCNL.",
    *     "correlative": 8
    * }
    */
    public function get_degree($id)
    {
        $affiliate = Affiliate::findOrFail($id);
        return $affiliate->degree;
    }

    /**
    * Unidad
    * Devuelve la unidad policial donde está destinado el afiliado
    * @urlParam id required ID de afiliado. Example: 12
    * @authenticated
    * @response
    * {
    *     "id": 1,
    *     "breakdown_id": 10,
    *     "district": "CHUQUISACA",
    *     "code": "10182",
    *     "name": "COMANDO DEPARTAMENTAL CHUQUISACA",
    *     "shortened": "C.D.PN.CH.",
    *     "created_at": "2017-06-01 10:41:04",
    *     "updated_at": "2017-06-01 10:41:04",
    *     "deleted_at": null
    * }
    */
    public function get_unit($id)
    {
        $affiliate = Affiliate::findOrFail($id);
        return $affiliate->unit;
    }

    /** @group Préstamos
    * Préstamos por afiliado
    * Devuelve la lista de préstamos o garantías del afiliado
    * @urlParam id required ID de afiliado. Example: 12
    * @queryParam guarantor required Préstamos para el afiliado como garante(1) o como titular(0). Example: 1
    * @queryParam state ID de loan_state_id para filtrar por estado de préstamos. Example: 3
    * @authenticated
    * @response
    * {
    *     "current_page": 1,
    *     "data": [
    *         {
    *             "id": 1,
    *             "code": "PMTO1",
    *             "disbursable_id": 1,
    *             "disbursable_type": "affiliates",
    *             "procedure_modality_id": 32,
    *             "disbursement_date": "2018-11-23",
    *             "parent_loan_id": 0,
    *             "parent_reason": null,
    *             "request_date": "2018-11-23",
    *             "amount_requested": 2000,
    *             "city_id": 1,
    *             "loan_interest_id": 1,
    *             "loan_state_id": 3,
    *             "amount_approved": 2000,
    *             "loan_term": 2,
    *             "disbursement_type_id": 1,
    *             "created_at": null,
    *             "updated_at": null,
    *             "balance": 2000,
    *             "estimated_quota": 1045.22,
    *             "defaulted": true,
    *             "lenders": [
    *                 {
    *                     "id": 3,
    *                     "pivot": {
    *                         "loan_id": 1,
    *                         "affiliate_id": 3,
    *                         "payment_percentage": 100
    *                     }
    *                 }, {}
    *             ],
    *             "guarantors": [
    *                 {
    *                     "id": 7,
    *                     "pivot": {
    *                         "loan_id": 1,
    *                         "affiliate_id": 7,
    *                         "payment_percentage": 100
    *                     }
    *                 }, {}
    *             ],
    *             "state": {
    *                 "id": 3
    *             }
    *         }
    *     ],
    *     "first_page_url": "http://127.0.0.1/api/v1/affiliate/5/loan?page=1",
    *     "from": 1,
    *     "last_page": 1,
    *     "last_page_url": "http://127.0.0.1/api/v1/affiliate/5/loan?page=1",
    *     "next_page_url": null,
    *     "path": "http://127.0.0.1/api/v1/affiliate/5/loan",
    *     "per_page": 10,
    *     "prev_page_url": null,
    *     "to": 1,
    *     "total": 1
    * }
    */
    public function get_loans(Request $request, $id)
    {
        $affiliate = Affiliate::findOrFail($id);
        $request->validate([
            'guarantor' => 'required|boolean',
            'state' => 'integer'
        ]);
        $type = Util::get_bool($request->guarantor) ? 'guarantors' : 'lenders';
        $relations[$type] = [
            'affiliate_id' => $affiliate->id
        ];
        if ($request->has('state')) {
            $relations['state'] = [
                'id' => $request->state
            ];
        }
        $data = Util::search_sort(new Loan(), $request, [], $relations, ['id']);
        foreach ($data as $loan) {
            $loan->balance = $loan->balance;
            $loan->estimated_quota = $loan->estimated_quota;
            $loan->defaulted = $loan->defaulted;
            $loan->lenders = $loan->lenders;
            $loan->guarantors = $loan->guarantors;
        }
        return $data;
    }

    /**
    * Estado
    * Devuelve el estado policial del afiliado
    * @urlParam id required ID de afiliado. Example: 5
    * @authenticated
    * @response
    * {
    *     "id": 4,
    *     "affiliate_state_type_id": 2,
    *     "name": "Fallecido",
    *     "affiliate_state_type": {
    *         "id": 2,
    *         "name": "Pasivo"
    *     }
    * }
    */
    public function get_state($id)
    {
        $affiliate = Affiliate::findOrFail($id);
        if ($affiliate->affiliate_state) $affiliate->affiliate_state->affiliate_state_type;
        return response()->json($affiliate->affiliate_state);
    }

    /** @group Préstamos
    * Modalidad por afiliado
    * Devuelve la modalidad de trámite evaluando al afiliado y el tipo de trámite
    * @urlParam id required ID de afiliado. Example: 5
    * @queryParam procedure_type_id required ID de tipo de trámite. Example: 9
    * @queryParam external_discount Booleano de descuento en otras entidades. Example: 0
    * @authenticated
    * @response
    * {
    *     "id": 33,
    *     "procedure_type_id": 9,
    *     "name": "Anticipo sector pasivo",
    *     "shortened": "ANT-SP",
    *     "is_valid": true,
    *     "loan_modality_parameter": {
    *         "id": 2,
    *         "procedure_modality_id": 33,
    *         "debt_index": "90",
    *         "quantity_ballots": 1,
    *         "guarantors": 0,
    *         "created_at": "2020-02-04 16:25:48",
    *         "updated_at": "2020-02-04 16:25:48"
    *     }
    * }
    */
    public function get_loan_modality(Request $request, $id) {
        $request->validate([
            'procedure_type_id' => 'required|integer|exists:procedure_types,id',
            'external_discount' => 'boolean|nullable',
        ]);
        $affiliate = Affiliate::findOrFail($id);
        $modality = ProcedureType::findOrFail($request->procedure_type_id);
        return Loan::get_modality($modality->name, $affiliate);
    }
}