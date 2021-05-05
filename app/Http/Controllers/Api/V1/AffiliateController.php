<?php

namespace App\Http\Controllers\Api\V1;

use \Waavi\Sanitizer\Sanitizer;
use Util;
use Carbon;
use App\Affiliate;
use App\Spouse;
use App\RecordType;
use App\User;
use App\Category;
use App\Degree;
use App\City;
use App\Hierarchy;
use App\AffiliateState;
use App\AffiliateStateType;
use App\Contribution;
use App\AidContribution;
use App\Unit;
use App\Loan;
use App\LoanGlobalParameter;
use App\ProcedureType;
use App\ProcedureModality;
use App\Http\Requests\AffiliateForm;
use App\Http\Requests\AffiliateFingerprintForm;
use App\Http\Requests\ObservationForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Events\FingerprintSavedEvent;
use Illuminate\Support\Facades\Storage;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\V1\CalculatorController;
use App\Rules\LoanIntervalTerm;

/** @group Afiliados
* Datos de los afiliados y métodos para obtener y establecer sus relaciones
*/
class AffiliateController extends Controller
{
    public static function append_data(Affiliate $affiliate, $with_category = false)
    {
        $affiliate->full_name = $affiliate->full_name;
        $affiliate->civil_status_gender = $affiliate->civil_status_gender;
        if($affiliate->affiliate_state !=null) $affiliate->dead = $affiliate->dead;
        $affiliate->identity_card_ext = $affiliate->identity_card_ext;
        $affiliate->picture_saved = $affiliate->picture_saved;
        $affiliate->fingerprint_saved = $affiliate->fingerprint_saved;
        $affiliate->defaulted_lender = $affiliate->defaulted_lender;
        $affiliate->defaulted_guarantor = $affiliate->defaulted_guarantor;
        $affiliate->cpop = $affiliate->cpop;
        if($affiliate->spouse){
            $affiliate->spouse = $affiliate->spouse;
            }else
            {$affiliate->spouse = [];
            }
        if ($with_category) $affiliate->category = $affiliate->category;
        if($affiliate->affiliate_state !=null) $affiliate->affiliate_state;
        return $affiliate;
    }

    /**
    * Lista de afiliados
    * Devuelve el listado con los datos paginados
    * @queryParam search Parámetro de búsqueda. Example: TORRE
    * @queryParam sortBy Vector de ordenamiento. Example: [last_name]
    * @queryParam sortDesc Vector de orden descendente(true) o ascendente(false). Example: [0]
    * @queryParam per_page Número de datos por página. Example: 8
    * @queryParam page Número de página. Example: 1
    * @authenticated
    * @responseFile responses/affiliate/index.200.json
    */
    public function index(Request $request)
    {
        $data = Util::search_sort(new Affiliate(), $request);
        $data->getCollection()->transform(function ($affiliate) {
            return self::append_data($affiliate, true);
        });
        return $data;
    }

    /**
    * Nuevo afiliado
    * Inserta nuevo afiliado
    * @bodyParam first_name string required Primer nombre. Example: JUAN
    * @bodyParam last_name string Apellido paterno. Example: PINTO
    * @bodyParam gender string required Género (M,F). Example: M
    * @bodyParam birth_date date required Fecha de nacimiento (AÑO-MES-DÍA). Example: 1980-05-02
    * @bodyParam city_birth_id integer required ID de ciudad de nacimiento. Example: 10
    * @bodyParam city_identity_card_id integer required ID de ciudad del CI. Example: 4
    * @bodyParam civil_status string required Estado civil (S,C,D,V). Example: C
    * @bodyParam identity_card string required Carnet de identidad. Example: 165134-1L
    * @bodyParam affiliate_state_id integer required ID de estado de afiliado. Example: 2
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
    * @bodyParam account_number integer required Numero de Cuenta del afiliado. Example: 5412132113
    * @bodyParam financial_entity_id required integer Entidad financiera de la cuenta del afiliado. Example: 1
    * @bodyParam sigep_status string required Estado del SIGEP. Example: ACTIVO
    * @bodyParam unit_police_description string Descripcion de la unidad policial mas especifiico Example: Epi turistica de La Paz
    * @authenticated
    * @responseFile responses/affiliate/store.200.json
    */
    public function store(AffiliateForm $request)
    {
        return Affiliate::create($request->all());
    }

    /**
    * Detalle de afiliado
    * Devuelve el detalle de un afiliado mediante su ID
    * @urlParam affiliate required ID de afiliado. Example: 54
    * @authenticated
    * @responseFile responses/affiliate/show.200.json
    */
    public function show(Affiliate $affiliate)
    {
        return self::append_data($affiliate, true);
    }

    /**
    * Actualizar afiliado
    * Actualizar datos personales de afiliado
    * @urlParam affiliate required ID de afiliado. Example: 54
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
    * @bodyParam account_number integer Numero de Cuenta del afiliado. Example: 5412132113
    * @bodyParam financial_entity_id integer Entidad financiera de la cuenta del afiliado. Example: 1
    * @bodyParam sigep_status string Estado del SIGEP. Example: ACTIVO
    * @bodyParam unit_police_description string Descripcion de la unidad policial mas especifiico Example: Epi turistica de La Paz
    * @authenticated
    * @responseFile responses/affiliate/update.200.json
    */
    public function update(AffiliateForm $request, Affiliate $affiliate)
    {
        if (!Auth::user()->can('update-affiliate-primary')) {
            $update = $request->except('first_name', 'second_name', 'last_name', 'mothers_last_name', 'surname_husband', 'identity_card');
        } else {
            $update = $request->all();
        }
        $affiliate->fill($update);
        $affiliate->save();
        if($request->has('financial_entity_id')&& $request->financial_entity_id != null || $request->has('account_number') && $request->account_number!= null){
            $affiliate->update([
                'sigep_status' => 'ACTIVO'
            ]);
         }
        return  $affiliate;
    }

    /**
    * Eliminar afiliado
    * Elimina un afiliado solo en caso de que no hubiese iniciado ningún trámite
    * @urlParam affiliate required ID de afiliado. Example: 54
    * @authenticated
    * @responseFile responses/affiliate/destroy.200.json
    */
    public function destroy(Affiliate $affiliate)
    {
        $affiliate->delete();
        return $affiliate;
    }

    /** @group Biométrico
    * Finalizar captura de huellas
    * Finaliza la captura de huellas en el dispositivo biométrico y envía un mensaje con el estado mediante sockets en el canal: fingerprint; el ejemplo de socket es el del código 201
    * @urlParam affiliate required ID de afiliado. Example: 2
    * @queryParam user_id required ID de usuario que realizó la captura. Example: 23
    * @queryParam success required Estado de la captura, 1 para exitoso y 0 para error. Example: 1
    * @responseFile 200 responses/affiliate/fingerprint_saved.200.json
    * @responseFile 201 responses/affiliate/fingerprint_saved.201.json
    */
    public function fingerprint_saved(AffiliateFingerprintForm $request, Affiliate $affiliate)
    {
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

    /** @group Biométrico
    * Registrar huellas
    * Inicia la captura de huellas en el dispositivo biométrico, la respuesta es enviada también mediante sockets en el canal: record; dicha difusión contiene la misma información que la respuesta de ejemplo
    * @urlParam affiliate required ID de afiliado. Example: 2
    * @responseFile responses/affiliate/update_fingerprint.200.json
    */
    public function update_fingerprint(Affiliate $affiliate)
    {
        $record_type = RecordType::whereName('datos-personales')->first();
        if ($record_type) {
            $affiliate->records()->create([
                'user_id' => Auth::user()->id,
                'record_type_id' => $record_type->id,
                'action' => 'inició la captura de huellas'
            ]);
            return $affiliate->records()->latest()->first();
        }
        abort(404);
    }

    /** @group Biométrico
    * Imagen perfil afiliado
    * Devuelve el listado con los nombres de los archivos de imagen, el contenido en base64 y el formato
    * @urlParam affiliate required ID de afiliado. Example: 2
    * @responseFile responses/affiliate/get_profile_images.200.json
    */
    public function get_profile_images(Request $request, $affiliate)
    {
        $files = [];
        try {
            $base_path = 'picture/';
            $fingerprint_files = ['_perfil.jpg'];
            foreach ($fingerprint_files as $file) {
                if (Storage::disk('ftp')->exists($base_path . $affiliate . $file)) {
                    array_push($files, [
                        'name' => $affiliate . $file,
                        'content' => base64_encode(Storage::disk('ftp')->get($base_path . $affiliate . $file)),
                        'format' => Storage::disk('ftp')->mimeType($base_path . $affiliate . $file)
                    ]);
                }
            }
        } catch (\Exception $e) {}
        return $files;
    }

    /** @group Biométrico
    * Imagen huellas afiliado
    * Devuelve el listado con los nombres de los archivos de imagen, el contenido en base64 y el formato
    * @urlParam affiliate required ID de afiliado. Example: 2
    * @responseFile responses/affiliate/get_fingerprint_images.200.json]
    */
    public function get_fingerprint_images($affiliate)
    {
        $files = [];
        try {
            $base_path = 'picture/';
            $fingerprint_files = ['_left_four.png', '_right_four.png', '_thumbs.png'];
            foreach ($fingerprint_files as $file) {
                if (Storage::disk('ftp')->exists($base_path . $affiliate . $file)) {
                    array_push($files, [
                        'name' => $affiliate . $file,
                        'content' => base64_encode(Storage::disk('ftp')->get($base_path . $affiliate . $file)),
                        'format' => Storage::disk('ftp')->mimeType($base_path . $affiliate . $file)
                    ]);
                }
            }
        } catch (\Exception $e) {}
        return $files;
    }

    /** @group Biométrico
    * Actualizar imagen perfil afiliado
    * Actualiza la imagen de perfil de afiliado capturada por una cámara en formato base64
    * @urlParam affiliate required ID de afiliado. Example: 2
    * @bodyParam image string required Imágen jpeg. Example: data:image/jpeg;base64,154AF...
    * @responseFile responses/affiliate/picture_save.200.json]
    */
    public function picture_save(Request $request, Affiliate $affiliate)
    {
        $request->validate([
            'image' => 'required|string'
        ]);
    $base_path = 'picture/';
    $code = $affiliate->id;
    $image = $request->image;   
    $image = str_replace('data:image/jpeg;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = $code.'_perfil.'.'jpg';
        try {
            Storage::disk('ftp')->put($base_path.$imageName, base64_decode($image));
            return response()->json([
                'message' => 'Fotografía actualizada'
            ], 200);
        } catch (\Exception $e) {
            abort(500, 'Error en la conexión con el servidor FTP');
        }
    }

    /**
    * Cónyugue
    * Devuelve los datos del o la cónyugue en caso de que el afiliado hubiese fallecido
    * @urlParam affiliate required ID de afiliado. Example: 12
    * @authenticated
    * @responseFile responses/affiliate/get_spouse.200.json
    */
    public function get_spouse(Affiliate $affiliate) {
        return response()->json($affiliate->spouse);
    }

    /**
    * Obtener direcciones
    * Devuelve la lista de direcciones del afiliado
    * @urlParam affiliate required ID de afiliado. Example: 1
    * @authenticated
    * @responseFile responses/affiliate/get_addresses.200.json
    */
    public function get_addresses(Affiliate $affiliate) {
        return $affiliate->addresses;
    }

    /**
    * Actualizar direcciones
    * Actualiza el listado de direcciones de un afiliado
    * @urlParam affiliate required ID de afiliado. Example: 12
    * @queryParam addresses required Lista de IDs de direcciones. Example: [12,17]
    * @queryParam addresses_valid Id de la dirección valida para los contratos si no se envia obtiene el ultimo actualizado o creado. Example: 12
    * @authenticated
    * @responseFile responses/affiliate/update_addresses.200.json
    */
    public function update_addresses(Request $request, Affiliate $affiliate) {
        $request->validate([
            'addresses' => 'array',
            'addresses.*' => 'exists:addresses,id',
            'addresses_valid'=>'nullable|integer'
            //'addresses_valid'=>'nullable|integer|exists:addresses,id'
        ]);

        if($request->addresses !=[]){
            if($request->has('addresses_valid') && $request->addresses_valid != 0){
                $affiliate->addresses()->sync($request->addresses);
                $affiliate->addresses()->sync([$request->addresses_valid => ['validated' => true]]);
                return $affiliate->addresses()->sync($request->addresses);
            }
            else{
                $affiliate->addresses()->sync($request->addresses);
                $affiliate->addresses()->sync([$affiliate->addresses->first()->id => ['validated' => true]]);
                return $affiliate->addresses()->sync($request->addresses);
            }
        }else{
            return "No hay direcciones por añadir";
        }
    }

    /**
    * Boletas de pago
    * Devuelve el listado de las boletas de pago de un afiliado, si se envía el ID de ciudad además devuelve un booleano para identificar si la petición contiene las últimas boletas y la diferencia de meses que se utilizó para la operación
    * @urlParam affiliate required ID de afiliado. Example: 1
    * @queryParam city_id ID de la ciudad de solicitud. Example: 4
    * @queryParam choose_diff_month Valor booleano para escoger(true) o no(false) la dirferencia en meses. Example: [1]
    * @queryParam number_diff_month Valor numerico que determina el numero de meses hacia a tras a considerar. Example: 3
    * @queryParam sortBy Vector de ordenamiento. Example: [month_year]
    * @queryParam sortDesc Vector de orden descendente(true) o ascendente(false). Example: [1]
    * @queryParam per_page Número de datos por página. Example: 3
    * @queryParam page Número de página. Example: 1
    * @authenticated
    * @responseFile responses/affiliate/get_contributions.200.json
    */
    public function get_contributions(Request $request, Affiliate $affiliate)
    {
        $request->validate([
            'number_diff_month'=>'integer'
        ]);

        if($request->has('choose_diff_month'))
            $choose_diff_month = $request->boolean('choose_diff_month');
        else 
            $choose_diff_month =false;
            
        if($request->has('number_diff_month'))
            $number_diff_month = intval($request->number_diff_month);
        else 
            $number_diff_month = 1;

        $state_affiliate = $affiliate->affiliate_state->affiliate_state_type->name;
        $filters = [
            'affiliate_id' => $affiliate->id
        ];

       $table_contribution=null;
       $verify=false;
       $is_latest=false;
       if ($state_affiliate == 'Activo' &&  $affiliate->affiliate_state->name !=  'Comisión' ){
            $contributions = Util::search_sort(new Contribution(), $request, $filters);
            $table_contribution='contributions';
            $verify=true;
        }else{
            if ($state_affiliate == 'Pasivo'){
            $contributions = Util::search_sort(new AidContribution(), $request, $filters);
            $table_contribution ='aid_contributions';
            $verify=true;
            }else{
                if ($affiliate->affiliate_state->name ==  'Comisión'){
                    $table_contribution=null;
                    $verify=false;
                    $is_latest=false;
                    $state_affiliate=$affiliate->affiliate_state->name;
                }
            }
        }
        if($verify == true && count($affiliate->$table_contribution)>0){
            if ($request->has('city_id')) {
                $is_latest = false;
                $city = City::findOrFail($request->city_id);
                $offset_day = LoanGlobalParameter::latest()->first()->offset_ballot_day;
                $now = CarbonImmutable::now();
                if($choose_diff_month == true && $request->has('number_diff_month')){
                    $before_month=$number_diff_month;
                    $before_month=$before_month;
                }else{
                    if($now->day <= $offset_day ){
                        if ($city->name == 'LA PAZ') $before_month = 2;//
                        else $before_month = 3;
                    }else{
                        if ($city->name == 'LA PAZ') $before_month = 1;//
                        else $before_month = 2;
                    }
                }
                $current_ticket = CarbonImmutable::parse($contributions[0]->month_year);
                $current_ticket_true = $now->startOfMonth()->subMonths($before_month);
                $current_ticket_true_inter = $now->startOfMonth()->subMonths($before_month+2);
                $filterAffiliate['affiliate_id'] = $affiliate->id;
                    if($request->per_page == 3)
                    $filters['month_year'] = $current_ticket_true_inter->toDateString();
                    if($request->per_page == 1)
                    $filters['month_year'] = $current_ticket_true->toDateString();
               
               if ($state_affiliate == 'Activo' &&  $affiliate->affiliate_state->name !=  'Comisión' ){
                       $contributions = Util::search_sort_contribution(new Contribution(), $request, $filters,$filterAffiliate);
                }else{
                    if ($state_affiliate == 'Pasivo'){
                        $contributions = Util::search_sort_contribution(new AidContribution(), $request, $filters,$filterAffiliate);
                    }
                }
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
                    'diff_months' => $before_month,
                    'state_affiliate'=>$state_affiliate,
                    'name_table_contribution'=>$table_contribution,
                    'current_date'=>$now->toDateTimeString(),
                    'offset_day'=>$offset_day,
                    'current_tiket'=> $current_ticket_true->toDateTimeString(),
                    'affiliate_id'=>$affiliate->id
                ])->merge($contributions);
            }
            return $contributions;
        }else{
            $offset_day = LoanGlobalParameter::latest()->first()->offset_ballot_day;
            $now = CarbonImmutable::now();
            $before_month=0;
            
            if ($request->has('city_id')) {
                $city = City::findOrFail($request->city_id);
                if($choose_diff_month == true && $request->has('number_diff_month')){
                    $before_month=$number_diff_month;
                }else{
                    if($now->day <= $offset_day ){
                        if ($city->name == 'LA PAZ') $before_month = 2;
                        else $before_month = 3;
                    }else{
                        if ($city->name == 'LA PAZ') $before_month = 1;
                        else $before_month = 2;
                    }
                }
            }
            $current_ticket = $now->startOfMonth()->subMonths($before_month);
            $now->startOfMonth()->diffInMonths($current_ticket->startOfMonth());
            $contributions = collect([
                'valid' => $is_latest,
                'diff_months' => $before_month,
                'state_affiliate'=>$state_affiliate,
                'name_table_contribution'=>$table_contribution,
                'current_date'=>$now->toDateTimeString(),
                'offset_day'=>$offset_day,
                'current_tiket'=> $current_ticket->toDateTimeString(),
                'affiliate_id'=>$affiliate->id
            ]);
            return $contributions;
        }
    }

    /** @group Préstamos
    * Préstamos por afiliado
    * Devuelve la lista de préstamos o garantías del afiliado
    * @urlParam affiliate required ID de afiliado. Example: 59210
    * @queryParam guarantor required Préstamos para el afiliado como garante(1) o como titular(0). Example: 1
    * @queryParam state ID de state_id para filtrar por estado de préstamos. Example: 3
    * @authenticated
    * @responseFile responses/affiliate/get_loans.200.json
    */
    public function get_loans(Request $request, Affiliate $affiliate)
    {
        $request->validate([
            'guarantor' => 'required|boolean',
            'state' => 'integer'
        ]);
        $type = $request->boolean('guarantor') ? 'guarantors' : 'lenders';
        $relations[$type] = [
            'affiliate_id' => $affiliate->id
        ];
        if ($request->has('state')) {
            $relations['state'] = [
                'id' => $request->state
            ];
        }
        $data = Util::search_sort(new Loan(), $request, [], $relations, ['id']);
        $data->getCollection()->transform(function ($loan) {
            return LoanController::append_data($loan, true);
        });
        return $data;
    }

    /**
    * Estado
    * Devuelve el estado policial del afiliado
    * @urlParam affiliate required ID de afiliado. Example: 5
    * @authenticated
    * @responseFile responses/affiliate/get_state.200.json
    */
    public function get_state(Affiliate $affiliate)
    {
        if ($affiliate->affiliate_state) $affiliate->affiliate_state->affiliate_state_type;
        return $affiliate->affiliate_state;
    }

   /** @group Préstamos
   * Modalidad por afiliado
   * Devuelve la modalidad de trámite evaluando al afiliado y el tipo de trámite
   * @urlParam affiliate required ID de afiliado. Example: 5
   * @queryParam procedure_type_id required ID de tipo de trámite. Example: 11
   * @bodyParam type_sismu boolean Tipo sismu. Example: true
   * @bodyParam cpop_sismu boolean El afiliado es cpop del sismu. Example: true
   * @bodyParam cpop_affiliate boolean Para cambiar el affiliado a cpop. Example: false
   * @authenticated
   * @responseFile responses/affiliate/get_loan_modality.200.json
   */
    public function get_loan_modality(Request $request, Affiliate $affiliate) {
        $request->validate([
            'procedure_type_id' => 'required|integer|exists:procedure_types,id',
            'type_sismu' => 'boolean',
            'cpop_sismu' => 'boolean',
            'cpop_affiliate' => 'boolean'
        ]);
        if(!$affiliate->affiliate_state) abort(403, 'Debe actualizar el estado del afiliado');
        $modality = ProcedureType::findOrFail($request->procedure_type_id);
        $type_sismu = $request->input('type_sismu',false);
        $cpop_sismu = $request->input('cpop_sismu',false);
        $cpop_affiliate = $request->input('cpop_affiliate',false);
        $affiliate_modality= Loan::get_modality($modality, $affiliate, $type_sismu, $cpop_sismu, $cpop_affiliate);
        return $affiliate_modality;
    }

    /** @group Préstamos
    * Verificar garante
    * Devuelve si un afiliado puede garantizar acorde a su categoria, estado y cantidad garantias de préstamos.
    * @bodyParam identity_card required Número de carnet de identidad del afiliado. Example: 1379734
    * @bodyParam procedure_modality_id ID de la modalidad de trámite. Example: 32
    * @authenticated
    * @responseFile responses/affiliate/test_guarantor.200.json
    */
    public function test_guarantor(Request $request){
        $message['validate'] = false;
        $request->validate([
            'identity_card' => 'required|string',
            'procedure_modality_id' => 'integer|exists:procedure_modalities,id'
        ]);
        $spouse = null;
        $affiliate = null;
        $sw = false;
        $validation =false;
        if(Affiliate::where('identity_card', $request->identity_card)->orWhere('registration', $request->identity_card)->first()){//caso de que el afiliado exista
            $affiliate = Affiliate::where('identity_card', $request->identity_card)->orWhere('registration', $request->identity_card)->first();
        }
        if(Spouse::where('identity_card', $request->identity_card)->orWhere('registration', $request->identity_card)->first()){//caso de que sea viuda
            $spouse = Spouse::where('identity_card', $request->identity_card)->orWhere('registration', $request->identity_card)->first();
            if($affiliate)
                $sw = true;
            $affiliate = $spouse->affiliate;
        }
            if($affiliate){
                if(!$sw){
                    $request->affiliate_id = $affiliate->id;
                    return self::test_spouse_guarantor($request);
                }
                else
                {
                    $affiliate->spouse = $affiliate->spouse;
                    $affiliate->category = $affiliate->category;
                    $affiliate->affiliate_state = $affiliate->affiliate_state;
                    $own_affiliate = Affiliate::where('identity_card', $request->identity_card)->orWhere('registration', $request->identity_card)->first();
                    $own_affiliate->category = $own_affiliate->category;
                    $own_affiliate->affiliate_state = $own_affiliate->affiliate_state;
                    return array(
                        "double_perception" => $sw,
                        "affiliate" => $affiliate,
                        "own_affiliate" => $own_affiliate,
                        "guarantor" => false,
                        "active_guarantees_quantity"=> 0,
                        "guarantor_information"=> false,
                        "loans_sismu" => 0,
                        "guarantees_sismu"=> 0

                    );
                }
            }
            else
                $message['validate'] = "No se encontraron coincidencias";
       return $message;
    }

    /** @group Préstamos
    * Verificar garante para doble percepcion
    * Devuelve si un afiliado puede garantizar acorde a su categoria, estado y cantidad garantias de préstamos.
    * @bodyParam Procedure_modality_id required Número de carnet de identidad del afiliado. Example: 1379734
    * @bodyParam affiliate_id required Id del afiliado. Example: 57955
    * @bodyParam type boolean required estado 1 afiliado o 0 false esposa. Example:1
    * @authenticated
    * @responseFile responses/affiliate/test_spouse_guarantor.200.json
    */
    public function test_spouse_guarantor(request $request){
        $affiliate = Affiliate::whereId($request->affiliate_id)->first();
        $validation = false;
        $modality_names = ProcedureModality::where('name','like', '%Pasivo%')->where('name','like', '%Largo Plazo%')->orWhere('name','like','%Pasivo%')->where('name','like','Largo Plazo%')->get();
        //return $modality_names;
        foreach($modality_names as $modality)
            if($modality->id == $request->procedure_modality_id)
                $validation = true;
        if($affiliate->spouse){
            if($validation){
                if($affiliate->pension_entity->name == null){
                    if($affiliate->pension_entity->name == "SENASIR")
                    {
                            if($affiliate->affiliate_state != null)
                            {
                                if($affiliate->affiliate_state->name == "Fallecido")
                                {
                                    if($affiliate->spouse->city_birth && $affiliate->spouse->city_identity_card && $affiliate->spouse->birth_date){
                                        if($affiliate->spouse->address)
                                            return $affiliate->test_guarantor($request->procedure_modality_id, $request->type);
                                        else
                                            return $message['validate'] = "debe actualizar la dirección del afiliado";
                                    }
                                    else{
                                        return $message['validate'] = "Actualizar datos de la viuda";
                                    }
                                }
                                else{
                                    return $message['validate'] = "Debe actualizar el estado del afiliado";    
                                }
                            }
                            else{
                                return $message['validate'] = "Debe colocar el estado del afiliado";
                            }
                    }
                    else{
                        return $message['validate'] = "No puede ser garante por el ente gestor";
                    }
                }else{
                    return  $message['validate'] = "Actualize los datos de su Ente Gestor";
                }
            }else{
                return $message['validate'] = 'No corresponde con la modalidad';
            }
        }
        else{
            if($affiliate->birth_date && $affiliate->city_identity_card && $affiliate->city_birth)
            {
                if($affiliate->address)
                    return $affiliate->test_guarantor($request->procedure_modality_id, $request->type);
                else
                    return $message['validate'] = "debe actualizar la dirección del afiliado";
            }
            else{
                return $message['validate']= "debe actualizar los datos del afiliado";
            }
        }
    }

    /** @group Observaciones de Afiliado
    * Lista de observaciones
    * Devuelve el listado de observaciones del afiliado
    * @urlParam affiliate required ID del afiliado. Example: 5012
    * @queryParam trashed Booleano para obtener solo observaciones eliminadas. Example: 1
    * @authenticated
    * @responseFile responses/affiliate/get_observations.200.json
    */
    public function get_observations(Request $request, Affiliate $affiliate)
    {
        $query = $affiliate->observations();
        if ($request->boolean('trashed')) $query = $query->onlyTrashed();
        return $query->get();
    }

    /** @group Observaciones de Afiliado
    * Nueva observación
    * Inserta una nueva observación asociada al afiliado
    * @urlParam affiliate required ID del afiliado. Example: 5012
    * @bodyParam observation_type_id integer required ID de tipo de observación. Example: 2
    * @bodyParam message string required Mensaje adjunto a la observación. Example: Subsanable en una semana
    * @authenticated
    * @responseFile responses/affiliate/set_observation.200.json
    */
    public function set_observation(ObservationForm $request, Affiliate $affiliate)
    {
        $observation = $affiliate->observations()->make([
            'message' => $request->message ?? null,
            'observation_type_id' => $request->observation_type_id,
            'date' => Carbon::now()
        ]);
        $observation->user()->associate(Auth::user());
        $observation->save();
        return $observation;
    }

    /** @group Observaciones de Afiliado
    * Actualizar observación
    * Actualiza los datos de una observación asociada al afiliado
    * @urlParam affiliate required ID del afiliado. Example: 5012
    * @bodyParam original.user_id integer required ID de usuario que creó la observación. Example: 123
    * @bodyParam original.observation_type_id integer required ID de tipo de observación original. Example: 2
    * @bodyParam original.message string required Mensaje de la observación original. Example: Subsanable en una semana
    * @bodyParam original.date date required Fecha de la observación original. Example: 2020-04-14 21:16:52
    * @bodyParam original.enabled boolean required Estado de la observación original. Example: false
    * @bodyParam update.enabled boolean Estado de la observación a actualizar. Example: true
    * @authenticated
    * @responseFile responses/affiliate/update_observation.200.json
    */
    public function update_observation(ObservationForm $request, Affiliate $affiliate)
    {
        $observation = $affiliate->observations();
        foreach (collect($request->original)->only('user_id', 'observation_type_id', 'message', 'date', 'enabled')->put('observable_id', $affiliate->id)->put('observable_type', 'affiliates') as $key => $value) {
            $observation = $observation->where($key, $value);
        }
        if ($observation->count() === 1) {
            $obs = $observation->first();
            if (isset($request->update['enabled'])) {
                if ($request->update['enabled']) {
                    $message = 'subsanó observación: ';
                } else {
                    $message = 'observó: ';
                }
            } else {
                $message = 'modificó observación: ';
            }
            Util::save_record($obs, 'observaciones', $message . $obs->message, $obs->observable);
            $observation->update(collect($request->update)->only('observation_type_id', 'message', 'enabled')->toArray());
        }
        return $affiliate->observations;
    }

    /** @group Observaciones de Afiliado
    * Eliminar observación
    * Elimina una observación del afiliado siempre y cuando no haya sido modificada
    * @urlParam affiliate required ID del préstamo. Example: 2
    * @bodyParam user_id integer required ID de usuario que creó la observación. Example: 123
    * @bodyParam observation_type_id integer required ID de tipo de observación. Example: 2
    * @bodyParam message string required Mensaje de la observación. Example: Subsanable en una semana
    * @bodyParam date required Fecha de la observación. Example: 2020-04-14 21:16:52
    * @bodyParam enabled boolean required Estado de la observación. Example: false
    * @authenticated
    * @responseFile responses/affiliate/unset_observation.200.json
    */
    public function unset_observation(ObservationForm $request, Affiliate $affiliate)
    {
        $request->request->add(['observable_type' => 'affiliates', 'observable_id' => $affiliate->id]);
        $observation = $affiliate->observations();
        foreach ($request->except('created_at','updated_at','deleted_at') as $key => $value) {
            $observation = $observation->where($key, $value);
        }
        $observation = $observation->whereColumn('created_at','updated_at');
        if ($observation->count() == 1) {
            $observation->delete();
            return $affiliate->observations;
        } else {
            abort(404, 'La observación fue modificada, no se puede eliminar');
        }
    }


    /**
    * Existencia del afiliado
    * Devuelve si la persona esta afiliado a Muserpol
    * @bodyParam identity_card string required Carnet de identidad. Example: 165134
    * @authenticated
    * @responseFile responses/affiliate/get_existence.200.json
    */
    public function get_existence(Request $request)
    {
        $request->validate([
            'identity_card' => 'required|string'
        ]);
        $b = array();
        $affiliate = Affiliate::whereIdentity_card($request->identity_card)->first();
        if(isset($affiliate)){
            $is_valid_information = Affiliate::verify_information($affiliate);
            $b["state"]=true;
            $b["information"]=$is_valid_information;
            $b["affiliate"]=$affiliate;
            return $b;
            //return self::append_data($affiliate, true);
        }else{
            $affiliate = Spouse::whereIdentity_card($request->identity_card)->first();
            if(isset($affiliate)){
                $b["state"]=true;
                $b["affiliate"]=$affiliate;
                return $b;
            }
            else{
                $b["state"]=false;
                return $b;
            }
        }
    }

     /**
    * Verificación de cantidad de préstamos que puede acceder un afiliado
    * Verifica si un afiliado tiene prestamos vigentes maximo dos, y préstamos en proceso maximo uno.
    * @urlParam affiliate required ID de afiliado. Example: 22773
    * @authenticated
    * @responseFile responses/affiliate/evaluate_maximum_loans.200.json
    */
    public function evaluate_maximum_loans(Affiliate $affiliate)
    {
        $maximum = false;
        $loan_global_parameter = LoanGlobalParameter::latest()->first();
        $process = $affiliate->process_loans;
        $disbursement = $affiliate->disbursement_loans;
        if(count($process)<$loan_global_parameter->max_loans_process && count($disbursement)<$loan_global_parameter->max_loans_active) $maximum = true;
        return response()->json([
            'process_loans' => count($process),
            'disbursement_loans' => count($disbursement),
            'is_valid_maximum' => $maximum
        ]);
    }

    /**
    * Historial de afiliados
    * Devuelve el afiliado y/o esposa.
    * @queryParam ci string required Carnet de identidad o matricula. Example:1700723
    * @authenticated
    * @responseFile responses/affiliate/affiliate_record.200.json
    */
    public function affiliate_record(Request $request)
    {
        if($request->ci){
            //
            $affiliate = null;
            $spouse = null;
            if(Affiliate::where('identity_card', $request->ci)->orWhere('registration', $request->ci)->first()){
                $affiliate = Affiliate::where('identity_card', $request->ci)->orWhere('registration', $request->ci)->first();
                $affiliate->category = $affiliate->category;
                $affiliate->state = $affiliate->affiliate_state;
                $affiliate->degree = $affiliate->degree;
                $affiliate->unit = $affiliate->unit;
                $affiliate->origin = "affiliate";
                if(Affiliate::where('identity_card', $request->ci)->orWhere('registration', $request->ci)->first()->spouse){
                    $spouse = Affiliate::where('identity_card', $request->ci)->orWhere('registration', $request->ci)->first()->spouse;
                    $spouse->origin = "spouse";
                }
            }
            if(Spouse::where('identity_card', $request->ci)->orWhere('registration', $request->ci)->first()){
                if(!$affiliate){
                    $spouse = Spouse::where('identity_card', $request->ci)->orWhere('registration', $request->ci)->first();
                    $spouse->origin = "spouse";
                    $affiliate = $spouse->affiliate;
                    $affiliate->category = $affiliate->category;
                    $affiliate->state = $affiliate->affiliate_state;
                    $affiliate->degree = $affiliate->degree;
                    $affiliate->unit = $affiliate->unit;
                    $affiliate->origin = "affiliate";
                }
                else{
                    $spouse = Spouse::where('identity_card', $request->ci)->orWhere('registration', $request->ci)->first()->affiliate;
                    $spouse->category = $spouse->category;
                    $spouse->state = $spouse->affiliate_state;
                    $spouse->degree = $spouse->degree;
                    $spouse->unit = $spouse->unit;
                    $spouse->origin = "affiliate";
                }
            }
            if($affiliate || $spouse){
                $data = [
                    "affiliate" => $affiliate,
                    "spouse" => $spouse,
                    "observables" => null
                ];
            }
            else{
                $data = [
                    "affiliate" => null,
                    "spouse" => null,
                    "observables" => $this->observables($request->ci)
                ];
            }
            return $data;
        }
    }

    /**
    * Historial de Tramites
    * Devuelve el Historio de tramites de un afiliado.
    * @bodyParam affiliate_id integer required ID de afiliado. Example: 22773
    * @bodyParam $type boolean required estado true afiliado o  false esposa. Example:true
    * @authenticated
    * @responseFile responses/affiliate/affiliate_history.200.json
    */
    public function loans_guarantees(request $request){
        $data = [
            "loans" => $this->get_mixed_loans($request->affiliate_id, $request->type),
            "guarantees" => $this->get_mixed_guarantees($request->affiliate_id, $request->type),
        ];
        return $data;
    }

    public function get_mixed_loans($id, $type){
        if($type){
            $loans = Loan::where('disbursable_id', $id)->where('disbursable_type', 'affiliates')->get();
            $ci=Affiliate::whereId($id)->first()->identity_card;
        }
        else{
            $loans = Loan::where('disbursable_id', $id)->where('disbursable_type', 'spouses')->get();
            $ci=Spouse::whereId($id)->first()->identity_card;
        }
        $data = array();
            foreach($loans as $loan){
                $data_loan = array(
                    "id" => $loan->id,
                    "code" => $loan->code,
                    "disbursement_date" => $loan->disbursement_date,
                    "request_date" => $loan->request_date,
                    "estimated_quota" => $loan->estimated_quota,
                    "loan_term" => $loan->loan_term,
                    "state" => $loan->state->name,
                    "amount" => $loan->amount_approved,
                    "balance" => $loan->balance,
                    "modality" => $loan->modality->name,
                    "shortened" => $loan->modality->shortened,
                    "disbursable" => $loan->disbursable_type,
                    "origin" => "PVT"
                );
                array_push($data, $data_loan);
            }
        $query = "SELECT Prestamos.IdPrestamo, trim(Prestamos.PresNumero) as PresNumero, Prestamos.PresFechaPrestamo, Prestamos.PresFechaDesembolso, Prestamos.PresCuotaMensual, Prestamos.PresMeses, trim(EstadoPrestamo.PresEstDsc) as PresEstDsc, Prestamos.PresMntDesembolso, Prestamos.PresSaldoAct, trim(Producto.PrdDsc) as PrdDsc, trim(Padron.PadCedulaIdentidad) as PadCedulaIdentidad, trim(Padron.PadMatricula) as PadMatricula, trim(Padron.PadMatriculaTit) as PadMatriculaTit
                    FROM Prestamos
                    join Padron ON Prestamos.idPadron = Padron.idPadron
                    join Producto ON Prestamos.PrdCod = Producto.PrdCod
                    join EstadoPrestamo ON Prestamos.PresEstPtmo = EstadoPrestamo.PresEstPtmo
                    where trim(Padron.PadCedulaIdentidad) = '$ci'
                    and Prestamos.PresEstPtmo <> 'N'
                    OR trim(Padron.PadMatricula) = '$ci'
                    and Prestamos.PresEstPtmo <> 'N'";
        $prestamos = DB::connection('sqlsrv')->select($query);
        foreach($prestamos as $prestamo){
            $short = explode(" ", $prestamo->PrdDsc);
            $shortened ="";
            foreach($short as $sh)
                $shortened = $shortened.$sh[0];
            $data_prestamos = array(
                "id" => $prestamo->IdPrestamo,
                "code" => $prestamo->PresNumero,
                "request_date" => $prestamo->PresFechaPrestamo,
                "disbursement_date" => $prestamo->PresFechaDesembolso,
                "estimated_quota" => $prestamo->PresCuotaMensual,
                "loan_term" => $prestamo->PresMeses,
                "state" => $prestamo->PresEstDsc,
                "amount" => $prestamo->PresMntDesembolso,
                "balance" => $prestamo->PresSaldoAct,
                "modality" => $prestamo->PrdDsc,
                "shortened" => $shortened,
                "disbursable" => $type ? 'affiliates': 'spouses',
                "origin" => "SISMU"
            );
            array_push($data, $data_prestamos);
        }
        return $data;
    }
    public function get_mixed_guarantees($id, $type){
        if($type){
            $affiliate = Affiliate::whereId($id)->first();
            $ci = $affiliate->identity_card;
        }
        else{
            $affiliate = Spouse::whereId($id)->first()->affiliate;
            $ci = Spouse::whereId($id)->first()->identity_card;
        }
        $loans = $affiliate->guarantees;
        $data = array();
        $loans_guarantees = $affiliate->active_guarantees();
        foreach($loans_guarantees as $loan){
                $data_loan = array(
                    "id" => $loan->id,
                    "code" => $loan->code,
                    "disbursement_date" => $loan->disbursement_date,
                    "request_date" => $loan->request_date,
                    "estimated_quota" => $loan->estimated_quota,
                    "loan_term" => $loan->loan_term,
                    "state" => $loan->state->name,
                    "amount" => $loan->amount_approved,
                    "balance" => $loan->balance,
                    "modality" => $loan->modality->name,
                    "shortened" => $loan->modality->shortened,
                    "disbursable" => $type ? 'affiliates': 'spouses',
                    "origin" => "PVT"
                );
                array_push($data, $data_loan);
        }
        $query = "SELECT Prestamos.IdPrestamo, trim(Prestamos.PresNumero) as PresNumero, Prestamos.PresFechaPrestamo, Prestamos.PresFechaDesembolso, Prestamos.PresCuotaMensual, Prestamos.PresMeses, trim(EstadoPrestamo.PresEstDsc) as PresEstDsc, Prestamos.PresMntDesembolso, Prestamos.PresSaldoAct, trim(Producto.PrdDsc) as PrdDsc, trim(Padron.PadCedulaIdentidad) as PadCedulaIdentidad, trim(Padron.PadMatricula) as PadMatricula, trim(Padron.PadMatriculaTit) as PadMatriculaTit
                    FROM Prestamos
                    join PrestamosLevel1 ON Prestamos.IdPrestamo = PrestamosLevel1.IdPrestamo
                    join Padron ON Padron.IdPadron = PrestamosLevel1.IdPadronGar
                    join Producto ON Prestamos.PrdCod = Producto.PrdCod
                    join EstadoPrestamo ON Prestamos.PresEstPtmo = EstadoPrestamo.PresEstPtmo
                    where trim(Padron.padCedulaIdentidad) = '$ci'
                    and Prestamos.PresEstPtmo <> 'N'";
        $loans = DB::connection('sqlsrv')->select($query);
        foreach($loans as $loan){
            $short = explode(" ", $loan->PrdDsc);
            $shortened ="";
            foreach($short as $sh)
                $shortened = $shortened.$sh[0];
            $data_prestamos = array(
                "id" => $loan->IdPrestamo,
                "code" => $loan->PresNumero,
                "request_date" => $loan->PresFechaPrestamo,
                "disbursement_date" => $loan->PresFechaDesembolso,
                "estimated_quota" => $loan->PresCuotaMensual,
                "loan_term" => $loan->PresMeses,
                "state" => $loan->PresEstDsc,
                "amount" => $loan->PresMntDesembolso,
                "balance" => $loan->PresSaldoAct,
                "modality" => $loan->PrdDsc,
                "shortened" => $shortened,
                "disbursable" => $type ? 'affiliates': 'spouses',
                "origin" => "SISMU"
            );
            array_push($data, $data_prestamos);
        }
        return $data;
    }

    public function observables($ci)
    {
        $query = "SELECT * from Padron where Padron.PadCedulaIdentidad = '$ci' or Padron.PadMatricula = '$ci'";
        $query2 = "SELECT * from Padron where Padron.PadCedulaIdentidad like '$ci%' or Padron.PadMatricula like '$ci%'";
        $loans = DB::connection('sqlsrv')->select($query);
        $loans2 = DB::connection('sqlsrv')->select($query2);
        $data = [
            "exactos" => $loans,
            "aproximaciones" => $loans2
        ];
        return $data;
    }

    public function get_observables($ci){
        $query = "SELECT Prestamos.IdPrestamo, Prestamos.PresNumero, Prestamos.PresCuotaMensual, Prestamos.PresMeses, EstadoPrestamo.PresEstDsc, Prestamos.PresMntDesembolso, Prestamos.PresSaldoAct, Producto.PrdDsc, trim(Padron.PadCedulaIdentidad) as PadCedulaIdentidad, trim(Padron.PadMatricula) as PadMatricula, trim(Padron.PadMatriculaTit) as PadMatriculaTit
                    FROM Prestamos
                    join PrestamosLevel1 ON Prestamos.IdPrestamo = PrestamosLevel1.IdPrestamo
                    join Padron ON Padron.IdPadron = PrestamosLevel1.IdPadronGar
                    join Producto ON Prestamos.PrdCod = Producto.PrdCod
                    join EstadoPrestamo ON Prestamos.PresEstPtmo = EstadoPrestamo.PresEstPtmo
                    where Padron.padMatricula like '$ci%'
                    or Padron.padCedulaIdentidad like '$ci%'
            EXCEPT 
                    SELECT Prestamos.IdPrestamo, Prestamos.PresNumero, Prestamos.PresCuotaMensual, Prestamos.PresMeses, EstadoPrestamo.PresEstDsc, Prestamos.PresMntDesembolso, Prestamos.PresSaldoAct, Producto.PrdDsc, trim(Padron.PadCedulaIdentidad) as PadCedulaIdentidad, trim(Padron.PadMatricula) as PadMatricula, trim(Padron.PadMatriculaTit) as PadMatriculaTit
                    FROM Prestamos
                    join PrestamosLevel1 ON Prestamos.IdPrestamo = PrestamosLevel1.IdPrestamo
                    join Padron ON Padron.IdPadron = PrestamosLevel1.IdPadronGar
                    join Producto ON Prestamos.PrdCod = Producto.PrdCod
                    join EstadoPrestamo ON Prestamos.PresEstPtmo = EstadoPrestamo.PresEstPtmo
                    where Padron.padMatricula = '$ci'
                    or Padron.padCedulaIdentidad = '$ci'";
        $loans = DB::connection('sqlsrv')->select($query);
        return $loans;
    }

    /**
    * Alerta afiliado(a) viudo(a)
    * verificacion si tambien es viuda
    * Devuelve si el/la afiliado(a) tambien es viudo(a)
    * @urlParam affiliate required ID de afiliado. Example: 45120
    * @authenticated
    * @responseFile responses/affiliate/verify_affiliate_spouse.200.json
    */

    public function verify_affiliate_spouse(Affiliate $affiliate){
        if(count(Spouse::where('identity_card', $affiliate->identity_card)->get())>0 && Spouse::where('identity_card', $affiliate->identity_card)->first()->affiliate->dead){
            return $message=[
                'message' => 'Affiliado tambien es viudo(a)',
                'verify' => true
            ];
        }
        else{
            return $message=[
                'message' => 'Es solo afiliado',
                'verify' => false
            ];
        }
    }
    /**
    * Buscar affiliado para prestamos
    * Devuelve las modalidades de prestamos para el afiliado, con sus montos maximos.
    * @bodyParam identity_card string required Carnet de identidad. Example:492562
    * @authenticated
    * @responseFile responses/affiliate/affiliate_evaluate_loans.200.json
    */
    public function search_loan(Request $request){
        // return $request;
         $request->validate([
             'identity_card' => 'required|string'
         ]);
         $message = array();
         $ci=$request->identity_card;
         $affiliate = Affiliate::whereIdentity_card($ci)->first();
         $state_affiliate=$affiliate->affiliate_state->affiliate_state_type->name;
         $state_affiliate_sub=$affiliate->affiliate_state->name;
         $evaluate=false;
         $state_affiliate_concat=$state_affiliate.' - '.$affiliate->affiliate_state->name;//agregooo
         $before_month=2;
         $modalities_all= collect();
   
         if($state_affiliate_sub=='Servicio'||$state_affiliate_sub=='Disponibilidad'){
             $now = CarbonImmutable::now();
             if(count($affiliate->contributions)>0){
                 $contributions=$affiliate->contributions->sortByDesc('month_year',SORT_NATURAL);
                 $contributions=$contributions->values()->all();
                 $current_ticket = CarbonImmutable::parse($contributions[0]->month_year);
                 $current_ticket_true = $now->startOfMonth()->subMonths($before_month);
                 if ($now->startOfMonth()->diffInMonths($current_ticket->startOfMonth()) <= 1000){
                  $modality_ida= ProcedureType::where('name','=','Préstamo Anticipo')->first()->id;
                  $modality_idb = ProcedureType::where('name','=','Préstamo a Corto Plazo')->first()->id;
                  $modality_idc = ProcedureType::where('name','=','Préstamo a Largo Plazo')->first()->id;
                  $ids_modalities=[$modality_ida,$modality_idb,$modality_idc];
                  $i= 0;
                  while ($i < count($ids_modalities)) {
                      $modality = ProcedureType::findOrFail($ids_modalities[$i]);
                     $affiliate_modality= Loan::get_modality_search($modality->name, $affiliate);
                     //return $affiliate_modality;
                     //return $affiliate_modality;/////
                     $amount_max=0;$liquid_calification=0;$quota_calculator=0; $interest=null;
                     if($affiliate_modality != []){
                          $modality_ballots=$affiliate_modality->loan_modality_parameter->quantity_ballots;
                          $months_term=$affiliate_modality->loan_modality_parameter->months_term;
                          $interval_modality= $affiliate_modality->loan_modality_parameter;
                          //cantidad de contributions
                          $number_ballots=0;
                          $contri = collect();
                          $add_payable_liquid=0;$quota_calculator=0;
                          $position_bonus=$border_bonus=$public_security_bonus=$east_bonus=0;
                          foreach ($contributions as $cont) {
                              if($number_ballots!=$modality_ballots){
                              $contri->push($cont);
                              $number_ballots++;
                              }
                          }
                          $avg_payable_liquid=$contri->avg('payable_liquid');
                          $avg_position_bonus=$contri->avg('position_bonus');
                          $avg_border_bonus=$contri->avg('border_bonus');
                          $avg_public_security_bonus=$contri->avg('public_security_bonus');
                          $avg_east_bonus=$contri->avg('east_bonus');
      
                          $liquid_calification=$avg_payable_liquid-$avg_position_bonus-$avg_border_bonus-$avg_public_security_bonus-$avg_east_bonus;//liquido para califica
                          $liquid_calification = Util::round($liquid_calification);
                          $amount_max = CalculatorController::maximum_amount($affiliate_modality, $interval_modality->maximum_term_modality,$liquid_calification);
                      
                          $quota_calculator=CalculatorController::quota_calculator($affiliate_modality,$interval_modality->maximum_term_modality,$amount_max);
                          $quota_calculator= Util::round($quota_calculator);
                          //$modalities_all->push($affiliate_modality,$amount_max,$quota_calculator);  
                          $interest=$affiliate_modality->current_interest;
                     }
                          $data_modalities= array(
                              "name_procedure_modality"=>$modality->name,
                              "modality_affiliate"=>$affiliate_modality,
                              "amount_max" => $amount_max,
                              "quota_calculated" => $quota_calculator,
                              "liquid_calification"=>$liquid_calification,
                              "interest"=>$interest
                          );
                     //modalities
                     $modalities_all->push($data_modalities);
                     $i++;
  
                  }
                  $evaluate=true;
                  $message['accomplished'] = 'Realizado con éxito';
                 }else{
                  $message['updated_ballots'] = 'No tiene boletas actualizadas';
                     //abort(403, 'No tiene boletas actualizadas');
                 }
             }else{
                  $message['no_contributions'] = 'No tiene contribucione';
                  //abort(403, 'No tiene contribuciones');
             }
         }else{
             $evaluate=false;
             $message['accomplished'] = 'Se debe realizar la evaluación de préstamos de forma perzonalizada por encontrarse el afiliado en estado: '.''.$state_affiliate_sub;
         }
         $data = array(  //data 
          "evaluate"=>$evaluate,
          "affiliate" => $affiliate->affiliate_fullName(),
          "affiliate_identity_card"=>$affiliate->getIdentityCardExtAttribute(),
          //"state_affiliate" => $affiliate->affiliate_state,
          "state_affiliate" =>$state_affiliate_concat,
          "modalities" => $modalities_all,
          "message"=>$message
          );
         return $data;
     }

    public function demo($ci){
        $id_overdue = 2;
        $in_process_id = 16;
        $user = User::first();
        $date = Carbon::now();
        $date = $date->subMonth()->endOfMonth()->format('Ymd');
    
        //$loans = DB::connection('sqlsrv')->select("SELECT dbo.Prestamos.IdPrestamo, dbo.Prestamos.PresNumero, dbo.Padron.IdPadron, DATEDIFF(month, Amortizacion.AmrFecPag, '" . $date . "') as Overdue from dbo.Prestamos join dbo.Padron on Prestamos.IdPadron = Padron.IdPadron join dbo.Producto on Prestamos.PrdCod = Producto.PrdCod join dbo.Amortizacion on (Prestamos.IdPrestamo = Amortizacion.IdPrestamo and Amortizacion.AmrNroPag = (select max(AmrNroPag) from Amortizacion where Amortizacion.IdPrestamo = Prestamos.IdPrestamo and Amortizacion.AMRSTS <>'X' )) where Prestamos.PresEstPtmo = 'V' and dbo.Prestamos.PresSaldoAct > 0 and Amortizacion.AmrFecPag <  cast('" . $date . "' as datetime) and DATEDIFF(month, Amortizacion.AmrFecPag, '" . $date . "') >= 2;");
        $loans = DB::connection('sqlsrv')->select("SELECT dbo.Prestamos.IdPrestamo, dbo.Prestamos.PresNumero, dbo.Padron.IdPadron, DATEDIFF(month, Amortizacion.AmrFecPag, '" . $date . "') as Overdue from dbo.Prestamos join dbo.Padron on Prestamos.IdPadron = Padron.IdPadron join dbo.Producto on Prestamos.PrdCod = Producto.PrdCod join dbo.Amortizacion on (Prestamos.IdPrestamo = Amortizacion.IdPrestamo and Amortizacion.AmrNroPag = (select max(AmrNroPag) from Amortizacion where Amortizacion.IdPrestamo = Prestamos.IdPrestamo and Amortizacion.AMRSTS <>'X' )) where Prestamos.PresEstPtmo = 'V' and dbo.Prestamos.PresSaldoAct > 0 and Amortizacion.AmrFecPag <  cast('" . $date . "' as datetime) and dbo.Padron.PadCedulaIdentidad = '$ci';");
    
        $count = 0;
        $eco_count = 0;
        $message = [];
    
        foreach ($loans as $loan) {
          $padron = DB::connection('sqlsrv')->table('Padron')->where('IdPadron', $loan->IdPadron)->first();
    
          if (!$padron) {
            array_push($message, ' ID de padrón: ' . $loan->IdPadron . ' inexistente');
          }
    
          $loan->affiliate = true;
          $loan->PadSpouseCedulaIdentidad = null;
    
          if (trim($padron->PadMatriculaTit) != '' and $padron->PadMatriculaTit != null and trim($padron->PadMatriculaTit) != '0' and strlen(trim($padron->PadMatriculaTit)) > 4) {
            $loan->affiliate = false;
            $loan->PadSpouseCedulaIdentidad = $padron->PadCedulaIdentidad;
            $padron_holder = DB::connection('sqlsrv')->table('Padron')->where('PadMatricula', $padron->PadMatriculaTit)->first();
            if ($padron_holder) {
              $padron = $padron_holder;
            } else {
              array_push($message, ' Matrícula de padrón: ' . $padron->PadMatriculaTit . ' inexistente');
            }
          }
    
          $loan->PadCedulaIdentidad = utf8_encode(trim($padron->PadCedulaIdentidad));
          $loan->PadMatricula = utf8_encode(trim($padron->PadMatricula));
          $loan->PadName = implode(' ', [utf8_encode(trim($padron->PadPaterno)), utf8_encode(trim($padron->PadMaterno)), utf8_encode(trim($padron->PadNombres))]);
    
          $affiliate = Affiliate::where('identity_card', $loan->PadCedulaIdentidad)->first();
          if (!$affiliate and !$loan->affiliate) {
            $spouse = Spouse::where('identity_card', $loan->PadSpouseCedulaIdentidad)->first();
            if ($spouse) {
              $affiliate = $spouse->affiliate;
            }
          }
          if (!$affiliate) {
            array_push($message, ' Afiliado con CI: ' . $loan->PadCedulaIdentidad . ' inexistente');
            $affiliates = Affiliate::where('identity_card', 'like', $loan->PadCedulaIdentidad . '%')->get();
            $affiliates->merge(Spouse::where('identity_card', 'like', $loan->PadCedulaIdentidad . '%')->get());
            if ($affiliates->count() > 0) {
              $names = [];
              $db_name = "platform";
              foreach ($affiliates as $option) {
                $names[] = [
                  $db_name,
                  $option->id,
                  $option->identity_card,
                  implode(' ', [$option->last_name ?? '', $option->mothers_last_name ?? '', $option->first_name ?? '', $option->second_name ?? ''])
                ];
              }
              array_push($message, ' Posibles opciones para el CI: ' . $loan->PadCedulaIdentidad );
              $id = array();
              foreach ($names as $name) {
                array_push($message, $loan->PadCedulaIdentidad . ' ' . $loan->PadName . ' => ' . $name[2] . ' ' . $name[3] . ' - id: ' . $name[1]);
                array_push($id, $name[1]);
              }
              $message['id'] = $id;
            }
            //break;return $names;
          }
          //$observation = ObservationType::find($id_overdue);
        }
        //return sizeof($message);
        return $message;
    }
}