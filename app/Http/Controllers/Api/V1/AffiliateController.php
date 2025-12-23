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
use App\LoanModalityParameter;
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
use App\Module;
use App\LoanBorrower;
use App\LoanState;
use App\LoanProcedure;
use App\LoanPaymentState;
use App\Observation;

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
        //$affiliate->defaulted_guarantor = $affiliate->defaulted_guarantor;
        $affiliate->cpop = $affiliate->cpop;
        $affiliate->retirement_fund_average = $affiliate->retirement_fund_average();
        $affiliate->default_alert_date_import = $affiliate->default_alert_date_import();
        if($affiliate->spouse){
            $affiliate->spouse = $affiliate->spouse;
            $affiliate->dead_spouse = $affiliate->spouse->dead;
            }else
            {$affiliate->spouse = [];
            $affiliate->dead_spouse = null;
            }
        if ($with_category){
            if($affiliate->category){
                $affiliate->category = $affiliate->category;
            }
        }
        if($affiliate->affiliate_state !=null) $affiliate->affiliate_state;
        return $affiliate;
    }

    public static function append_data_list_affiliate(Affiliate $affiliate, $with_category = false)
    {
        $affiliate->full_name = $affiliate->full_name;
        $affiliate->civil_status_gender = $affiliate->civil_status_gender;
        $affiliate->picture_saved = $affiliate->picture_saved;
        $affiliate->fingerprint_saved = $affiliate->fingerprint_saved;
        if($affiliate->affiliate_state !=null) $affiliate->dead = $affiliate->dead;
        $affiliate->identity_card_ext = $affiliate->identity_card_ext;
        if($affiliate->affiliate_state !=null) $affiliate->affiliate_state;
        if ($with_category){
            if($affiliate->category){
                $affiliate->category = $affiliate->category;
            }
        }
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
            return self::append_data_list_affiliate($affiliate, true);
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
        $request=$request->all();
        $request['user_id']=Auth::id();
        return Affiliate::create($request);
    }

    /**
    * Detalle de afiliado biometrico
    * Devuelve el detalle de un afiliado mediante su ID
    * @urlParam affiliate required ID de afiliado. Example: 54
    * @authenticated
    * @responseFile responses/affiliate/show.200.json
    */
    public function show(Affiliate $affiliate)
    {
        return $affiliate;
    }

    /**
    * Detalle de afiliado prestamos
    * Devuelve el detalle de un afiliado mediante su ID
    * @urlParam affiliate required ID de afiliado. Example: 54
    * @authenticated
    * @responseFile responses/affiliate/show.200.json
    */
    public function affiliate_show(Affiliate $affiliate)
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
        $sigep_old = $affiliate->sigep_status;
        try{
            DB::beginTransaction();
            if (!Auth::user()->can('update-affiliate-primary')) {
                $update = $request->except('first_name', 'second_name', 'last_name', 'mothers_last_name', 'surname_husband', 'identity_card');
            } else {
                $update = $request->all();
            }
            $affiliate->fill($update);
            $affiliate->save();
            // verificacion si se encuentra con el rol prestamos
            $id_prestamos = Module::where('name', 'prestamos')->first()->id;
            if(in_array($id_prestamos, Auth::user()->modules, true))
            {
                foreach ($affiliate->processloans as $loan) 
                {
                    $borrower = LoanBorrower::find($loan->borrower[0]->id);
                    if ($borrower->type == 'affiliates')
                    {
                        $borrower->degree_id = $request['degree_id'];
                        $borrower->unit_id = $request['unit_id'];
                        $borrower->category_id = $request['category_id'];
                        $borrower->type_affiliate = $request['type'];
                        $borrower->unit_police_description = $request['unit_police_description'];
                        $request->has('affiliate_state_id') ? $borrower->affiliate_state_id = $request['affiliate_state_id']:'';
                        $borrower->identity_card = $request['identity_card'];
                        $borrower->city_identity_card_id = $request['city_identity_card_id'];
                        $borrower->city_birth_id = $request['city_birth_id'];
                        $borrower->registration = $request['registration'];
                        $borrower->last_name = $request['last_name'];
                        $request->has('first_name') ? $borrower->first_name = $request['first_name']:'';
                        $borrower->second_name = $request['second_name'];
                        $borrower->mothers_last_name = $request['mothers_last_name'];
                        $borrower->surname_husband = $request['surname_husband'];
                        $request->has('gender') ? $borrower->gender = $request['gender']:'';
                        $borrower->civil_status = $request['civil_status'];
                        $borrower->pension_entity_id = $request['pension_entity_id'];
                        $borrower->availability_info = $request['availability_info'];
                    }
                    $loan->number_payment_type = $request['account_number'];
                    $loan->save();
                    $borrower->phone_number = $request['phone_number'];
                    $borrower->cell_phone_number = $request['cell_phone_number'];
                    $borrower->address_id = $affiliate->address->id;
                    $borrower->update();
                }
                $guarantees = $affiliate->guarantees;
                foreach($guarantees as $guarantee)
                {
                    if($guarantee->loan != null && $guarantee->loan->state_id == LoanState::where('name','En Proceso')->first()->id && $guarantee->type == 'affiliates')
                    {
                        $guarantee->update([
                            'degree_id' => $request['degree_id'],
                            'unit_id' => $request['unit_id'],
                            'category_id' => $request['category_id'],
                            'type_affiliate' => $request['type'],
                            'unit_police_description' => $request['unit_police_description'],
                            'affiliate_state_id' => $request['affiliate_state_id'],
                            'identity_card' => $request['identity_card'],
                            'city_identity_card_id' => $request['city_identity_card_id'],
                            'city_birth_id' => $request['city_birth_id'],
                            'registration' => $request['registration'],
                            'last_name' => $request['last_name'],
                            'first_name' => $request['first_name'],
                            'second_name' => $request['second_name'],
                            'mothers_last_name' => $request['mothers_last_name'],
                            'surname_husband' => $request['surname_husband'],
                            'gender' => $request['gender'],
                            'civil_status' => $request['civil_status'],
                            'phone_number' => $request['phone_number'],
                            'cell_phone_number' => $request['cell_phone_number'],
                            'address_id' => $affiliate->address->id,
                            'pension_entity_id' => $request['pension_entity_id'],
                        ]);
                    }
                }
            }
            DB::commit();
            return  $affiliate;
            $affiliate->sigep_status = $sigep_old;
            $affiliate->update();            
        }catch(Exception $e){
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
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
    * Imagen huellas afiliado
    * Elimina las huellas dactilares del afiliado
    */
    public function fingerprint_delete($affiliate)
    {
        $files = [];
        try {
            $base_path = 'picture/';
            $fingerprint_files = ['_left_four.png', '_right_four.png', '_thumbs.png'];
            foreach ($fingerprint_files as $file) {
                if (Storage::disk('ftp')->exists($base_path . $affiliate . $file)) {
                    Storage::disk('ftp')->delete($base_path . $affiliate . $file);
                }
            }

            $base_path = 'fingerprint/';
            $fingerprint_files = ['_left_index.wsq', '_left_little.wsq', '_left_middle.wsq', '_left_ring.wsq', '_left_thumb.wsq', '_right_index.wsq', '_right_little.wsq', '_right_middle.wsq', '_right_ring.wsq', '_right_thumb.wsq'];
            foreach ($fingerprint_files as $file) {
                if (Storage::disk('ftp')->exists($base_path . $affiliate . $file)) {
                    Storage::disk('ftp')->delete($base_path . $affiliate . $file);
                }
            }
        } catch (\Exception $e) {}
        return response()->json([
            'message' => 'Huellas dactilares eliminadas'
        ], 200);
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
    * Boletas de pago del affiliado
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
       $table_contribution=null;
       $verify=false;
       $is_latest=false;

        if ($request->has('city_id')) {
            $is_latest = false;
            $city = City::findOrFail($request->city_id);
            $offset_day = LoanProcedure::where('is_enable', true)->first()->loan_global_parameter->offset_ballot_day;
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

            $current_ticket_true = $now->startOfMonth()->subMonths($before_month);
            if($request->per_page > 1){
                $current_ticket_true_inter = $current_ticket_true->startOfMonth()->subMonths($request->per_page - 1);//retrocede 2 meses 
                //return $current_ticket_true_inter;
            }else{
                $current_ticket_true_inter = $current_ticket_true;
            }
        }

       if ($state_affiliate == 'Activo' &&  $affiliate->affiliate_state->name !=  'Comisión' ){
            $contributions = Contribution::where('affiliate_id',$affiliate->id)->whereBetween('month_year', [$current_ticket_true_inter->toDateString(), $current_ticket_true->toDateString()])->orderBy('month_year','asc')->paginate($request->per_page);
            $table_contribution='contributions';
            $verify=true;
        }else{
            if ($state_affiliate == 'Pasivo'){
            $contributions = AidContribution::where('affiliate_id',$affiliate->id)->whereBetween('month_year', [$current_ticket_true_inter->toDateString(), $current_ticket_true->toDateString()])->orderBy('month_year','asc')->paginate($request->per_page);
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
             //verifica si son consecutivos los meses de las boletas de pago
                if($request->per_page == count($contributions)){
                    $is_latest = true;
                }else{
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
            return $contributions;
        }else{
            $contributions = collect([
                'valid' => $is_latest,
                'diff_months' => $before_month,
                'state_affiliate'=>$state_affiliate,
                'name_table_contribution'=>$table_contribution,
                'current_date'=>$now->toDateTimeString(),
                'offset_day'=>$offset_day,
                'current_tiket'=> $current_ticket_true->toDateTimeString(),
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
        $filter['affiliate_id'] = $affiliate->id;
        if ($request->has('state')) {
            $relations['state'] = [
                'id' => $request->state
            ];
        }
        $data = Util::search_sort(new Loan(), $request, $filter,[], ['id']);
        $data->getCollection()->transform(function ($loan) {
            return LoanController::append_data($loan, true);
        });
            return $data;
    }

    public function get_loans_affiliate(Request $request, Affiliate $affiliate)
    {
        $data = $affiliate->active_loans_query()->paginate(10);
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
   * @bodyParam remake_loan boolean Rehacer el préstamo obtiene modalidad. Example: true
   * @bodyParam cpop_affiliate boolean Para cambiar el affiliado a cpop. Example: false
   * @authenticated
   * @responseFile responses/affiliate/get_loan_modality.200.json
   */
    public function get_loan_modality(Request $request, Affiliate $affiliate) {
        $request->validate([
            'procedure_type_id' => 'required|integer|exists:procedure_types,id',
            'type_sismu' => 'boolean',
            //'cpop_sismu' => 'boolean',
            'cpop_affiliate' => 'boolean',
            'remake_loan' => 'boolean'
        ]);
        if(!$affiliate->affiliate_state) abort(403, 'Debe actualizar el estado del afiliado');
        $modality = ProcedureType::findOrFail($request->procedure_type_id);
        $type_sismu = $request->input('type_sismu',false);
       // $cpop_sismu = $request->input('cpop_sismu',false);
        $cpop_affiliate = $request->input('cpop_affiliate',false);
        $remake_loan = $request->input('remake_loan',false);
        $affiliate_modality= Loan::get_modality($modality, $affiliate, $type_sismu, $cpop_affiliate, $remake_loan);
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
        $message=[];
        $affiliate = Affiliate::whereId($request->affiliate_id)->first();
        $validation = false;
        $modality_names = ProcedureModality::where('name','like', '%Pasivo%')->where('name','like', '%Largo Plazo%')->orWhere('name','like','%Pasivo%')->where('name','like','Largo Plazo%')->get();
        //return $modality_names;
        foreach($modality_names as $modality)
            if($modality->id == $request->procedure_modality_id)
                $validation = true;
        if($affiliate->spouse && $affiliate->dead){
            if($validation){
                if($affiliate->pension_entity != null && $affiliate->pension_entity->name != null){
                    if($affiliate->pension_entity->name == "SENASIR")
                    {
                            if($affiliate->affiliate_state != null)
                            {
                                if($affiliate->affiliate_state->name == "Fallecido")
                                {
                                    if($affiliate->spouse->city_birth && $affiliate->spouse->city_identity_card && $affiliate->spouse->birth_date){
                                        if($request->remake_evaluation && $request->remake_loan_id != null){
                                            return $affiliate->test_guarantor($request->procedure_modality_id, $request->type, $request->remake_evaluation, $request->remake_loan_id);
                                        }
                                        else{
                                            return $affiliate->test_guarantor($request->procedure_modality_id, $request->type);
                                        }
                                    }
                                    else{
                                        $message['validate'] = "Actualizar datos de la viuda";
                                    }
                                }
                                else{
                                    $message['validate'] = "Debe actualizar el estado del afiliado";    
                                }
                            }
                            else{
                                $message['validate'] = "Debe colocar el estado del afiliado";
                            }
                    }
                    else{
                        $message['validate'] = "No puede ser garante por el Ente Gestor ser: ".$affiliate->pension_entity->name;
                    }
                }else{
                    $message['validate'] = "Actualize los datos de su Ente Gestor";
                }
            }else{
                $message['validate'] = 'No corresponde con la modalidad';
            }
            return $message;
        }
        else{
            if($request->remake_evaluation && $request->remake_loan_id != null){
                return $affiliate->test_guarantor($request->procedure_modality_id, $request->type, $request->remake_evaluation, $request->remake_loan_id);
            }
            else{
                return $affiliate->test_guarantor($request->procedure_modality_id, $request->type);
            }
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
        $loan_global_parameter = LoanProcedure::where('is_enable', true)->first()->loan_global_parameter;
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
            //$loans = Loan::where('affiliate_id', $id)->get();
            $loans = Affiliate::whereId($id)->first()->loans;
            $ci=Affiliate::whereId($id)->first()->identity_card;
        }
        else{
            $loans = Spouse::whereId($id)->first()->spouse_loans();
            //$loans = Loan::where('disbursable_id', $id)->where('disbursable_type', 'spouses')->get();
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
            $loans_guarantees = $affiliate->active_guarantees();
            $ci = $affiliate->identity_card;
        }
        else{
            $affiliate = Spouse::whereId($id)->first()->affiliate;
            $loans_guarantees = $affiliate->spouse->spouse_guarantees();
            $ci = Spouse::whereId($id)->first()->identity_card;
        }
        $loans = $affiliate->guarantees;
        $data = array();
        //$loans_guarantees = $affiliate->active_guarantees();
        foreach($loans_guarantees as $loan){
                $data_loan = array(
                    "id" => $loan->id,
                    "code" => $loan->code,
                    "full_name" => $loan->getBorrowers()[0]->full_name_borrower,
                    "identity_card" => $loan->getBorrowers()[0]->identity_card_borrower,
                    "registration" => $loan->getBorrowers()[0]->registration_borrower,
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
        $query = "SELECT Prestamos.IdPrestamo, trim(p.PadPaterno) as tit_paterno, trim(p.PadMaterno) as tit_materno, trim(p.PadNombres) as tit_nombres, trim(p.PadApellidoCasada) as tit_ape_casada, trim(p.PadCedulaIdentidad) as ci_titular, trim(p.PadMatricula) as mat_titular,trim(Prestamos.PresNumero) as PresNumero, Prestamos.PresFechaPrestamo, Prestamos.PresFechaDesembolso, Prestamos.PresCuotaMensual, Prestamos.PresMeses, trim(EstadoPrestamo.PresEstDsc) as PresEstDsc, Prestamos.PresMntDesembolso, Prestamos.PresSaldoAct, trim(Producto.PrdDsc) as PrdDsc, trim(Padron.PadCedulaIdentidad) as PadCedulaIdentidad, trim(Padron.PadMatricula) as PadMatricula, trim(Padron.PadMatriculaTit) as PadMatriculaTit
                    FROM Prestamos
                    join Padron p ON p.IdPadron = Prestamos.IdPadron
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
                "full_name" => $loan->tit_paterno." ".$loan->tit_materno." ".$loan->tit_nombres." ".$loan->tit_ape_casada,
                "identity_card" => $loan->ci_titular,
                "registration" => $loan->mat_titular,
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
        $request->validate([
            'identity_card' => 'required|string'
        ]);
        $message = array();
        $ci=$request->identity_card;
        $affiliate = '';
        if(Affiliate::where('identity_card', $ci)->first())
            $affiliate = Affiliate::where('identity_card', $ci)->first();
        elseif(Spouse::where('identity_card', $ci)->first())
            $affiliate = Spouse::where('identity_card', $ci)->first()->affiliate;
        if($affiliate != '')
        {
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
                 }
             }else{
                  $message['no_contributions'] = 'No tiene contribucione';
             }
         }else{
             $evaluate=false;
             $message['accomplished'] = 'Se debe realizar la evaluación de préstamos de forma perzonalizada por encontrarse el afiliado en estado: '.''.$state_affiliate_sub;
         }
         $data = array(  //data 
          "evaluate"=>$evaluate,
          "affiliate" => $affiliate->affiliate_fullName(),
          "affiliate_identity_card"=>$affiliate->getIdentityCardExtAttribute(),
          "state_affiliate" =>$state_affiliate_concat,
          "modalities" => $modalities_all,
          "message"=>$message
          );
        }
        else{
            $data = array(  //data 
                "evaluate"=>'',
                "affiliate" => '',
                "affiliate_identity_card"=>'',
                "state_affiliate" => '',
                "modalities" => '',
                "message"=>$message['existence'] = 'no existe'
                );
        }
         return $data;
     }

    public function demo($ci){
        $id_overdue = 2;
        $in_process_id = 16;
        $user = User::first();
        $date = Carbon::now();
        $date = $date->subMonth()->endOfMonth()->format('Ymd');
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
          }
        }
        return $message;
    }

    /**
    * existencia del afiliado
    * Devuelve el id del afiliado, si es viuda devuelve el id del titular o si es alguien de doble precepción.
    * @bodyParam identity_card string required Carnet de identidad. Example:492562
    * @authenticated
    * @responseFile responses/affiliate/affiliate_existence.200.json
    */
    public function existence(request $request)
    {
        $request->validate([
            'identity_card' => 'required|string'
        ]);
        $double_perception = false;
        $affiliate = null;
        $spouse = null;
        $type = null;
        $existence = false;
        if(Affiliate::where('identity_card', $request->identity_card)->first())
        {
            $existence = true;
            $affiliate = Affiliate::where('identity_card', $request->identity_card)->first();
            $spouse = null;
            $double_perception = false;
            if(!$affiliate->dead || $affiliate->dead && $affiliate->spouse == null)
                $type = 'affiliate';
            elseif($affiliate->spouse)
                $type = 'spouse';
        }
        if(Spouse::where('identity_card', $request->identity_card)->first())
        {
            $existence = true;
            $spouse = Spouse::where('identity_card', $request->identity_card)->first();
            if($affiliate)
            {
                $double_perception = true;
                $deceased_affiliate = $spouse->affiliate->id;
            }
            else
            {
                $affiliate = $spouse->affiliate;
                $type = 'affiliate';
                if(!$spouse->dead)
                    $type = 'spouse';
                else
                    $type = 'affiliate';
            }
        }
        if($affiliate && $affiliate->spouse)
        {
            if($affiliate->dead && $affiliate->spouse->dead)
                $existence = false;
        }
        return $data = array(
            "existence"=>$existence,
            "affiliate"=>$existence ? $affiliate->id : null,
            "type"=>$type,
            "double_perception"=>$double_perception,
            "deceased_affiliate" => $double_perception ? $deceased_affiliate : null,
            );
    }

    /**
    * validacion del garante
    * Devuelve si el afiliadopuede ser garante acorde al estado y la modalidad.
    * @bodyParam affiliate_id integer required id del afiliado. Example:12900
    * @bodyParam procedure_modality_id integer required id de la modalidad. Example:50
    * @bodyParam remake_loan_id integer required id del prestamo a rehacer, si es nuevo enviar 0. Example:9
    * @authenticated
    * @responseFile responses/affiliate/validate_guarantor.200.json
    */
    public function validate_guarantor(request $request)
    {
        $affiliate = Affiliate::find($request->affiliate_id);
        $modality = ProcedureModality::whereId($request->procedure_modality_id)->first();
        $guarantor = false;
        $message = "OK";
        $information = "";
        $id_activo = array();
        $module_id = Module::where('name', 'prestamos')->first()->id;
        $observations = Observation::select('observables.*')
                        ->join('observation_types as ot', 'ot.id', '=', 'observables.observation_type_id')
                        ->where('ot.module_id', '=', $module_id)
                        ->where('observable_type', '=', 'affiliates')
                        ->where('observable_id', '=', $affiliate->id)
                        ->where('type', 'ilike', '%A%')
                        ->get();
        foreach (ProcedureModality::where('name', 'like', '%Activo%')->orWhere('name', 'like', '%Oportuno%')->get() as $procedure) {
            array_push($id_activo, $procedure->id);
        }
        $id_senasir = array();
        foreach (ProcedureModality::where('name', 'like', '%SENASIR')->get() as $procedure) {
            array_push($id_senasir, $procedure->id);
        }
        $id_afp = array();
        foreach (ProcedureModality::where('name', 'like', '%AFP')->get() as $procedure) {
            array_push($id_afp, $procedure->id);
        }
        $id_gestora = array();
        foreach (ProcedureModality::where('name', 'like', '%Gestora%')->get() as $procedure) {
            array_push($id_gestora, $procedure->id);
        }
        $id_com_disp = array();
        foreach (ProcedureModality::where('name', 'like', '%Disponibilidad%')->get() as $procedure) {
            array_push($id_com_disp, $procedure->id);
        }
        if($affiliate->category_id == null)
        {
            $guarantor = false;
            $message = "El afiliado no tiene registrada su categoria";
        }
        elseif($modality->loan_modality_parameter->min_guarantor_category <= $affiliate->category->percentage && $affiliate->category->percentage <= $modality->loan_modality_parameter->max_guarantor_category && $observations->count() == 0)
        {
            switch($request->procedure_modality_id){
                case (in_array($request->procedure_modality_id, $id_activo)):
                    if($affiliate->affiliate_state->affiliate_state_type->name == "Activo" && $affiliate->affiliate_state->name == "Servicio")
                        $guarantor = true;
                    else
                    {
                        if($affiliate->affiliate_state->affiliate_state_type->name != "Activo")
                            $message = "Afiliado no pertenece al servicio activo";
                        else
                        {
                            if($affiliate->affiliate_state->name != "Servicio")
                            $message = "Afiliado se encuentra en comisión o disponibilidad";
                        }
                    }
                    if($affiliate->category == null)
                        $affiliate->category_name = null;
                    else
                        $affiliate->category_name = $affiliate->category->name;
                    break;
                case (in_array($request->procedure_modality_id, $id_senasir)):
                    if($affiliate->affiliate_state->affiliate_state_type->name == "Activo" && $affiliate->affiliate_state->name == "Servicio")
                        $guarantor = true;
                    else
                    {
                        if($affiliate->pension_entity != null)
                        {
                            if(strpos($affiliate->pension_entity->name, "AFP") === 0)
                                $message = "Afiliado AFP o gestora no puede garantizar prestamos";
                            else
                                $guarantor = true;
                        }
                        else
                        {
                            $message = "El afiliado es del sector pasivo y no tiene registrado su ente gestor";
                        }
                    }
                    if($affiliate->category == null)
                        $affiliate->category_name = null;
                    else
                        $affiliate->category_name = $affiliate->category->name;
                    break;
                case (in_array($request->procedure_modality_id, $id_afp)):
                    if($affiliate->affiliate_state->affiliate_state_type->name == "Activo" && $affiliate->affiliate_state->name == "Servicio")
                    {
                        if($affiliate->category == null)
                            $message = "El afiliado no tiene registrado su categoria";
                        else
                        {
                            if(LoanModalityParameter::where('procedure_modality_id',$request->procedure_modality_id)->where('loan_procedure_id',LoanProcedure::where('is_enable', true)->first()->id)->first()->min_guarantor_category <= $affiliate->category->percentage && $affiliate->category->percentage <= LoanModalityParameter::where('procedure_modality_id',$request->procedure_modality_id)->where('loan_procedure_id',LoanProcedure::where('is_enable', true)->first()->id)->first()->max_guarantor_category)
                                $guarantor = true;
                            else
                                $message = "El afiliado no se encuentra en la categoria necesaria";
                        }
                    }
                    else
                    {
                        //if($affiliate->affiliate_state->affiliate_state_type->name != "Activo")
                        if($affiliate->pension_entity->name == 'SENASIR')
                            $guarantor = true;
                        else
                            $message = "Afiliado AFP o gestora no puede garantizar a un AFP";

                    }
                    if($affiliate->category == null)
                        $affiliate->category_name = null;
                    else
                        $affiliate->category_name = $affiliate->category->name;
                    break;
                // Caso gestora
                case (in_array($request->procedure_modality_id, $id_gestora)):
                    if($affiliate->affiliate_state->affiliate_state_type->name == "Activo" && $affiliate->affiliate_state->name == "Servicio")
                    {
                        if($affiliate->category == null)
                            $message = "El afiliado no tiene registrado su categoria";
                        else
                        {
                            if(LoanModalityParameter::where('procedure_modality_id',$request->procedure_modality_id)->where('loan_procedure_id',LoanProcedure::where('is_enable', true)->first()->id)->first()->min_guarantor_category <= $affiliate->category->percentage && $affiliate->category->percentage <= LoanModalityParameter::where('procedure_modality_id',$request->procedure_modality_id)->where('loan_procedure_id',LoanProcedure::where('is_enable', true)->first()->id)->first()->max_guarantor_category)
                                $guarantor = true;
                            else
                                $message = "El afiliado no se encuentra en la categoria necesaria";
                        }
                    }
                    else
                    {
                        if($affiliate->pension_entity->name == 'SENASIR')
                            $guarantor = true;
                        else
                            $message = "Afiliado AFP o GESTORA no puede garantizar en esta modalidad";

                    }
                    if($affiliate->category == null)
                        $affiliate->category_name = null;
                    else
                        $affiliate->category_name = $affiliate->category->name;
                    break;
                //
                case (in_array($request->procedure_modality_id, $id_com_disp)):
                    if($affiliate->affiliate_state->affiliate_state_type->name == "Activo" && $affiliate->affiliate_state->name == "Servicio")
                    {
                        if($affiliate->category == null)
                            $message = "El afiliado no tiene registrado su categoria";
                        else
                        {
                            if(LoanModalityParameter::where('procedure_modality_id',$request->procedure_modality_id)->first()->min_guarantor_category <= $affiliate->category->percentage && $affiliate->category->percentage <= LoanModalityParameter::where('procedure_modality_id',$request->procedure_modality_id)->first()->max_guarantor_category)
                                $guarantor = true;
                            else
                                $message = "El afiliado no se encuentra en la categoria necesaria";
                        }
                    }
                    else
                    {
                        $message = "Afiliado no pertenece al sector activo";
                    }
                    break;
                default:
                    $message = "no corresponde con la modalidad";
                    if($affiliate->category == null)
                        $affiliate->category_name = null;
                    else
                        $affiliate->category_name = $affiliate->category->name;
                    break;
            }
        }
        elseif($observations->count() > 0)
        {
            $guarantor = false;
            $message = "El afiliado se encuentra observado, por lo cual no puede ser garante";
            $affiliate->observations = $affiliate->observations;
        }else{
            $guarantor = false;
            $message = "El afiliado se encuentra con categoria 0%";
            $affiliate->category_name = $affiliate->category->name;
        }
        if($affiliate->spouse != null && $affiliate->spouse->dead == false)
        {
            $affiliate->spouse = $affiliate->spouse;
            $loans = $affiliate->spouse->spouse_active_guarantees();
            $loans_sismu = $affiliate->spouse->active_guarantees_sismu();
        }
        else
        {
            $loans = $affiliate->active_guarantees();
            $loans_sismu = $affiliate->active_guarantees_sismu();
        }
        $data = array();
        foreach($loans as $loan)
        {
            if($loan->id != $request->remake_loan_id)
            {
                $loans_pvt = array(
                    "id" => $loan->id,
                    "code" => $loan->code,
                    "lender" => $loan->borrower->first()->full_name,
                    "quota" => $loan->borrowerguarantors->where('affiliate_id',$affiliate->id)->first()->quota_treat,
                    "quota_loan" => $loan->estimated_quota,
                    "state" => $loan->state->name,
                    "type" => "PVT",
                    "eval_quota" => $loan->borrowerguarantors->where('affiliate_id',$affiliate->id)->first()->eval_quota,
                    "modality" => $loan->modality->name
                );
                array_push($data, $loans_pvt);
            }
        }
        foreach($loans_sismu as $loan)
        {
            $loans_pvt = array(
                "id" => $loan->IdPrestamo,
                "code" => $loan->PresNumero,
                "lender" => $loan->PadNombres.' '.$loan->PadPaterno.' '.$loan->PadMaterno.' '.$loan->PadApellidoCasada,
                "quota" => $loan->PresCuotaMensual / $loan->quantity_guarantors,
                "quota_loan" => $loan->PresCuotaMensual,
                "state" => $loan->PresEstPtmo == "V" ? "Vigente" : "Pendiente",
                "type" => "SISMU",
                "eval_quota" => Util::round2($loan->PresCuotaMensual / $loan->quantity_guarantors),
                "modality" => $loan->PrdDsc
            );
            array_push($data, $loans_pvt);
        }
        $affiliate->active_loans = count($affiliate->active_loans())+count($affiliate->active_loans_sismu());
        if($affiliate->affiliate_state == null)
            $information = $information." estado del afiliado,";
        if($affiliate->city_birth == null)
            $information = $information." ciudad de nacimiento,";
        if($affiliate->address == null)
            $information = $information."direccion";
        $max_guarantees = 0;
        $loan_procedure = LoanProcedure::where('is_enable', true)->first()->id;
        if($affiliate->affiliate_state != null)
        {
            if($affiliate->affiliate_state->affiliate_state_type->name == "Activo")
                $max_guarantees = LoanGlobalParameter::where('loan_procedure_id', $loan_procedure)->first()->max_guarantor_active;
            elseif($affiliate->affiliate_state->affiliate_state_type->name == "Pasivo")
                $max_guarantees = LoanGlobalParameter::where('loan_procedure_id', $loan_procedure)->first()->max_guarantor_passive;
        }
        if($affiliate->affiliate_state->name != 'Servicio' && in_array($request->procedure_modality_id, $id_activo))
        {
            $guarantor = false;
            $message = "no corresponde con la modalidad";
        }
        return $data = array(
            "guarantor"=>$guarantor,
            "message"=>$message,
            "information"=>$affiliate->verify_information($affiliate),
            "information_missing"=>$information,
            "affiliate"=>$affiliate,
            "guarantees"=>$data,
            "max_guarantees"=>$max_guarantees,
            );
    }

    /**
    * Prestamos pagados por sus garantes
    * Devuelve si el afiliado cuenta con prestamos pagados por sus garantes mediante su ID
    * @urlParam affiliate required ID de afiliado. Example: 54
    */
    public function loans_paid_by_guarantors(Affiliate $affiliate)
    {
        $state = false;
        foreach($affiliate->loans as $loan)
        {
            if($loan->payments->where('paid_by', 'G')->where('validated', true)->where('state_id', LoanPaymentState::where('name', 'Pagado')->first()->id)->count() > 0)
                $state = true;
            else
                $state = false;
        }
        return $state;
    }

    /**
    * Sub Modalidad del Afiliado 
    * Devuelve las sub modalidades a la que el afiliado puede acceder según la modalidad seleccionada
    * @urlParam affiliate required ID del afiliado. Example: 41064
    * @urlParam procedure_type required ID de la modalidad. Example: 12
    * @authenticated
    */
    public function get_sub_modality_affiliate(Affiliate $affiliate, ProcedureType $procedure_type, Request $request)
    {
        $affiliate_state      = $affiliate->affiliate_state;          // Estado del Afiliado en la Policía
        $affiliate_state_type = $affiliate_state->affiliate_state_type; // Tipo del Estado del Afiliado

        // Validar si tiene préstamos activos en la misma modalidad
        foreach ($affiliate->loans as $loan) {
            if ($loan->state->id === 3 && $loan->modality->procedure_type->id === $procedure_type->id) {
                abort(403, 'El afiliado tiene préstamos activos en la modalidad: ' . $procedure_type->name);
            }
        }

        $a = 'procedure_type_id';
        $b = 'procedure_modality_id';

        $salud_sector_active = ProcedureModality::where('name', 'Salud Sector Activo')->first();
        $salud_sector_pasive_senasir = ProcedureModality::where('name', 'Salud Sector Pasivo SENASIR')->first();
        $salud_disponibilidad = ProcedureModality::where('name', 'Salud en Disponibilidad')->first();
        $salud_sector_pasive_gestora = ProcedureModality::where('name', 'Salud Sector Pasivo Gestora Pública')->first();
        $hogar_digno_sector_active = ProcedureModality::where('name', 'Hogar Digno con Garantía Personal para el Sector Activo')->first();
        $refinanciamiento_hogar_digno = ProcedureModality::where('name', 'Refinanciamiento Hogar Digno con Garantía Personal para el Sector Activo')->first();
        $primer_prestamo_sector_active = ProcedureModality::where('name', 'Mi Primer Préstamo con Garantía Personal Sector Activo')->first();
        // Colección de préstamos para afiliados al sector activo
        $sector_active = collect([
            $request->refinancing ? null : [$a => 9,  $b => 32],  // Anticipo Sector Activo
            $request->refinancing ? null : [$a => 10, $b => 36],  // Corto Plazo Sector Activo
            $request->refinancing ? null : [$a => 12, $b => 81],  // Largo Plazo con Garantía Personal Sector Activo (1 garante)
            $request->refinancing ? null : [$a => 12, $b => 43],  // Largo Plazo con Garantía Personal Sector Activo (2 garantes)
            $request->refinancing ? null : [$a => 12, $b => 46],  // Largo Plazo con Pago Oportuno
            $request->refinancing ? null : [$a => 28, $b => 93],  // Préstamo Sector Activo c/ Garantía FRPS Menor
            $request->refinancing ? null : [$a => 28, $b => 94],  // Préstamo Sector Activo c/ Garantía FRPS Mayor
            $request->refinancing ? null : [$a => $hogar_digno_sector_active->procedure_type->id, $b => $hogar_digno_sector_active->id], // Préstamo Hogar Digno c/ Garantía Personal Sector Activo
            $request->refinancing ? null : [$a => $salud_sector_active->procedure_type->id, $b => $salud_sector_active->id], // Préstamo Salud Sector Activo

            $request->refinancing ? [$a => 10, $b => 40] : null,  // Refinanciamiento Corto Plazo Sector Activo
            $request->refinancing ? [$a => 12, $b => 82] : null,  // Refinanciamiento Largo Plazo (1 garante)
            $request->refinancing ? [$a => 12, $b => 47] : null,  // Refinanciamiento Largo Plazo (2 garantes)
            $request->refinancing ? [$a => 12, $b => 50] : null,  // Refinanciamiento Largo Plazo con Pago Oportuno
            $request->refinancing ? [$a => $refinanciamiento_hogar_digno->procedure_type->id, $b => $refinanciamiento_hogar_digno->id] : null, // Refinanciamiento Hogar Digno

            $request->reprogramming ? [$a => 10, $b => 73] : null, // Reprogramación Corto Plazo Sector Activo
            $request->reprogramming ? [$a => 12, $b => 83] : null, // Reprogramación Largo Plazo (1 garante)
            $request->reprogramming ? [$a => 12, $b => 84] : null, // Reprogramación Largo Plazo (2 garantes)
            $request->reprogramming ? [$a => 12, $b => 85] : null, // Reprogramación Largo Plazo con Pago Oportuno
        ])->filter(); // Quitamos todos los null

        // Validación para "Mi Primer Préstamo"
        if (($affiliate->category->percentage * 100) <= 0 && !$request->refinancing) {
            $sector_active->push([$a => $primer_prestamo_sector_active->procedure_type->id, $b => $primer_prestamo_sector_active->id]); // Mi Primer Préstamo Sector Activo
        }

        // Colección de préstamos para afiliados en disponibilidad
        $sector_availability = collect([
            [$a => 9,  $b => 33],                    // Anticipo en Disponibilidad
            [$a => 10, $b => 37],                    // Corto Plazo en Disponibilidad
            [$a => 12, $b => 97],                    // Largo Plazo (1 garante)
            [$a => 12, $b => 65],                    // Largo Plazo (2 garantes)
            [$a => 28, $b => 93],                    // Préstamo Activo c/ Garantía FRPS Menor
            [$a => 28, $b => 94],                    // Préstamo Activo c/ Garantía FRPS Mayor
            [$a => $salud_disponibilidad->procedure_type->id, $b => $salud_disponibilidad->id],                   // Préstamo Salud en Disponibilidad
            $request->refinancing   ? [$a => 10, $b => 66] : null, // Refinanciamiento Corto Plazo en Disponibilidad
            $request->reprogramming ? [$a => 10, $b => 74] : null, // Reprogramación Corto Plazo en Disponibilidad
        ])->filter();

        // Colección de préstamos para afiliados al sector pasivo SENASIR
        $sector_pasive_senasir = collect([
            $request->refinancing ? null : [$a => 9,  $b => 35],  // Anticipo Sector Pasivo SENASIR
            $request->refinancing ? null : [$a => 10, $b => 39],  // Corto Plazo Sector Pasivo SENASIR
            $request->refinancing ? null : [$a => 12, $b => 45],  // Largo Plazo Sector Pasivo SENASIR
            $request->refinancing ? null : [$a => 29, $b => 96],  // Préstamo Estacional Sector Pasivo
            $request->refinancing ? null : [$a => 29, $b => 95],  // Préstamo Estacional Sector Pasivo con Cónyuge
            $request->refinancing ? null : [$a => $salud_sector_pasive_senasir->procedure_type->id, $b => $salud_sector_pasive_senasir->id], // Préstamo Salud Sector Pasivo SENASIR

            $request->refinancing   ? [$a => 10, $b => 42] : null, // Refinanciamiento Corto Plazo SENASIR
            $request->refinancing   ? [$a => 12, $b => 49] : null, // Refinanciamiento Largo Plazo SENASIR
            $request->reprogramming ? [$a => 10, $b => 76] : null, // Reprogramación Corto Plazo SENASIR
            $request->reprogramming ? [$a => 12, $b => 87] : null, // Reprogramación Largo Plazo SENASIR
        ])->filter();

        // Colección de préstamos para afiliados al sector pasivo Gestora
        $sector_pasive_gestora = collect([
            $request->refinancing ? null : [$a => 9,  $b => 67],  // Anticipo Sector Pasivo Gestora Pública
            $request->refinancing ? null : [$a => 10, $b => 68],  // Corto Plazo Sector Pasivo Gestora Pública
            $request->refinancing ? null : [$a => 12, $b => 70],  // Largo Plazo Sector Pasivo Gestora Pública
            $request->refinancing ? null : [$a => 29, $b => 96],  // Préstamo Estacional Sector Pasivo
            $request->refinancing ? null : [$a => 29, $b => 95],  // Préstamo Estacional Sector Pasivo con Cónyuge
            $request->refinancing ? null : [$a => $salud_sector_pasive_gestora->procedure_type->id, $b => $salud_sector_pasive_gestora->id], // Préstamo Salud Sector Pasivo Gestora

            $request->refinancing   ? [$a => 10, $b => 69] : null, // Refinanciamiento Corto Plazo Gestora
            $request->refinancing   ? [$a => 12, $b => 71] : null, // Refinanciamiento Largo Plazo Gestora
            $request->reprogramming ? [$a => 10, $b => 75] : null, // Reprogramación Corto Plazo Gestora
            $request->reprogramming ? [$a => 12, $b => 86] : null, // Reprogramación Largo Plazo Gestora
        ])->filter();

        $data = collect();

        // Determinar colección según estado del afiliado
        if ($affiliate_state->id === 1) { // Activo - Servicio
            $data = $sector_active;
        } elseif ($affiliate_state->id === 3) { // Activo - Disponibilidad
            $data = $sector_availability;
        } elseif ($affiliate_state_type->id === 2) { // Pasivo
            if ($affiliate->pension_entity->id === 5) {          // Pasivo - SENASIR
                $data = $sector_pasive_senasir;
            } elseif ($affiliate->pension_entity->id === 8) {    // Pasivo - Gestora
                $data = $sector_pasive_gestora;
            }
        }

        // Filtrar por la modalidad recibida como parámetro
        $data = $data->filter(function ($loan) use ($procedure_type) {
            return $loan['procedure_type_id'] === $procedure_type->id;
        });

        // Submodalidades definidas en el procedimiento
        $sub_modalities = collect(json_decode($procedure_type->procedure_modalities, true));
        $sub_modalities_and_parameters = [];

        // Intersección entre submodalidades disponibles y submodalidades según sector
        $sub_modalities->each(function ($item1) use ($data, &$sub_modalities_and_parameters) {
            $exists = $data->contains(function ($item2) use ($item1) {
                return $item2['procedure_modality_id'] === $item1['id'];
            });

            if ($exists) {
                $sub_modality = ProcedureModality::findOrFail($item1['id']);
                $sub_modality->loan_modality_parameter = $sub_modality->loan_modality_parameter;
                $sub_modality->procedure_type;
                $sub_modalities_and_parameters[] = $sub_modality;
            }
        });

        return $sub_modalities_and_parameters;
    }


    /**
    * Promedio de fondo de retiro
    * devuelve el promedio del fondo de retiro correspondiente al afiliado
    * @urlParam affiliate required ID del afiliado. Example: 100
    * @authenticated
    */
    public function get_retirement_fund_average(Affiliate $affiliate)
    {
        if($affiliate->retirement_fund_average())
            $data = array(
                "state" => true,
                "message" => $affiliate->retirement_fund_average()->retirement_fund_average
                );
        else
            $data = array(
                "state" => false,
                "message" => "no tiene la categoria necesaria"
                );
        return $data;
    }

    /**
    * Validación de parametros de la Sub Modalidad selecionada con el Afiliado 
    * @urlParam affiliate required ID del afiliado. Example: 41064
    * @urlParam procedure_modality required ID de la sub modalidad. Example: 93
    * @authenticated
    */
    public function validate_affiliate_modality(Affiliate $affiliate,ProcedureModality $procedure_modality)
    {   
        $percentage = $affiliate->category->percentage;
        $minLenderCategory = $procedure_modality->loan_modality_parameter->min_lender_category;
        $maxLenderCategory = $procedure_modality->loan_modality_parameter->max_lender_category;

        if(!($percentage >= $minLenderCategory && $percentage <= $maxLenderCategory))
            abort(403, 'El afiliado no tiene la categoria suficiente para esta modalidad');

        return response()->json(['status' => true, 'message' => 'Validations passed successfully'], 200);
    }
}