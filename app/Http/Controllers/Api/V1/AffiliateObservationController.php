<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ObservationForm;
use App\Affiliate;
use App\Module;
use App\AffiliateRecordPVT;
use App\ObservationType;
use Carbon;
use Illuminate\Support\Facades\Auth;
use Util;

class AffiliateObservationController extends Controller
{
    /** @group Observaciones de Afiliado
    * Lista de observaciones
    * Devuelve el listado de observaciones del afiliado
    * @urlParam affiliate required ID del afiliado. Example: 5012
    * @queryParam trashed Booleano para obtener solo observaciones eliminadas. Example: 1
    * @authenticated
    * @responseFile responses/affiliate_observation/index.200.json
    */
    public function index(Request $request, Affiliate $affiliate)
    {
        $query = $affiliate->observations();
        if ($request->boolean('trashed')) $query = $query->onlyTrashed();
        $observations = $query->get();
        foreach ($observations as $observation){
            $observation->observation_type = ObservationType::find($observation->observation_type_id);
            $observation->user;
        }
        return $observations;
    }

    /** @group Observaciones de Afiliado
    * Nueva observación
    * Inserta una nueva observación asociada al afiliado
    * @urlParam affiliate required ID del afiliado. Example: 5012
    * @bodyParam observation_type_id integer required ID de tipo de observación. Example: 2
    * @bodyParam message string required Mensaje adjunto a la observación. Example: Subsanable en una semana
    * @authenticated
    * @responseFile responses/affiliate_observation/store.200.json
    */
    public function store(ObservationForm $request, Affiliate $affiliate)
    {
        $observation = $affiliate->observations()->make([
            'message' => $request->message,
            'observation_type_id' => $request->observation_type_id,
            'date' => Carbon::now()
        ]);
        $observation->user()->associate(Auth::user());
        $observation->save();
        /*************records*************/
        $observation_type = ObservationType::find($observation->observation_type_id);
        $record = new AffiliateRecordPVT();
        $record->user_id = Auth::user()->id;
        $record->affiliate_id = $affiliate->id;
        $record->message = "El usuario " . Auth::user()->username  . " creó la observación " . $observation_type->name;
        $record->save();
        /*************end records*************/
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
    * @bodyParam update.message string Mensaje de la observación a actualizar. Example: "la nueva observación"
    * @authenticated
    * @responseFile responses/affiliate_observation/update.200.json
    */
    public function update(ObservationForm $request, Affiliate $affiliate)
    {
        $observation = $affiliate->observations();
        foreach (collect($request->original)->only('user_id', 'observation_type_id', 'message', 'date', 'enabled')->put('observable_id', $affiliate->id)->put('observable_type', 'affiliates') as $key => $value) {
            $observation = $observation->where($key, $value);
        }
        if ($observation->count() === 1) {
            $observation->update(collect($request->update)->only('observation_type_id', 'message', 'enabled')->toArray());

            /*************records*************/
            $observation_type = ObservationType::find($request->original['observation_type_id']);
            $isDirty = false;
            $message =  "El usuario ".Auth::user()->username.' '."modificó la observación " . $observation_type->name . " :";
            $record = new AffiliateRecordPVT();
            $record->user_id = Auth::user()->id;
            $record->affiliate_id = $affiliate->id;
            if (isset($request->update['message']) && $request->original['message'] != $request->update['message']){
                $message = $message . ' Mensaje de - ' . $request->original['message'] . ' - a - ' . $request->update['message']. ', ';
                $isDirty = true;
            }
            if (isset($request->update['enabled']) && $request->original['enabled'] != $request->update['enabled']){
                $message = $message . ' de ' . Util::getEnabledLabel($request->original['enabled']) . ' a ' . Util::getEnabledLabel($request->update['enabled']) . ', ';
                $isDirty = true;
            }
            $record->message = $message . ".";
            if($isDirty)
                $record->save();
            /*************end records*************/

            return $affiliate->observations;
        }
        else {
            abort(403, 'La observación no se puede encontrar');
        }
    }
    /** @group Observaciones de Afiliado
    * Eliminar observación
    * Elimina una observación del afiliado siempre y cuando exista
    * @urlParam affiliate required ID del afiliado. Example: 2
    * @bodyParam user_id integer required ID de usuario que creó la observación. Example: 123
    * @bodyParam observation_type_id integer required ID de tipo de observación. Example: 2
    * @bodyParam message string required Mensaje de la observación. Example: Subsanable en una semana
    * @bodyParam date required Fecha de la observación. Example: 2020-04-14 21:16:52
    * @bodyParam enabled boolean required Estado de la observación. Example: false
    * @authenticated
    * @responseFile responses/affiliate_observation/destroy.200.json
    */
    public function destroy(ObservationForm $request, Affiliate $affiliate)
    {
        $request->request->add(['observable_type' => 'affiliates', 'observable_id' => $affiliate->id]);
        $observation = $affiliate->observations();
        foreach ($request->except('created_at','updated_at','deleted_at','role_id') as $key => $value) {
            $observation = $observation->where($key, $value);
        }
        if($observation->count() == 1) {
            $observation->delete();
            /*************records*************/
            $observation_type = ObservationType::find($request->observation_type_id);
            $message = "El usuario " . Auth::user()->username  . " eliminó la observación " . $observation_type->name . ".";
            $record = new AffiliateRecordPVT();
            $record->user_id = Auth::user()->id;
            $record->affiliate_id = $affiliate->id;
            $record->message = $message;
            $record->save();
            /*************end records*************/
            return $affiliate->observations;
        }else{
            abort(403, 'La observación no existe, no se puede eliminar');
        }
    }
    /**
    * Tipos de observaciones asociados al módulo y afiliado
    * Devuelve la lista de tipos de observaciones asociados a un módulo y afiliado
    * @urlParam module required ID del módulo. Example: 6
    * @urlParam affiliate required ID del Afiliado. Example: 3
    * @authenticated
    * @responseFile responses/module/get_observation_types.200.json
    */
    public function get_observation_types_affiliate(Module $module,Affiliate $affiliate)
    {
        $observations = $affiliate->observations()->get();
        $observation_types_all= ObservationType::where('module_id',$module->id)->where('type','like','A%')->get();
        $observation_types= collect([]);
        foreach($observation_types_all as $observation_type){
            $is = false;
            foreach($observations as $observation){
               if($observation_type->id == $observation->observation_type_id)
               $is = true;
            }
            if(!$is)
            $observation_types->push($observation_type);
        }
        return  $observation_types;
    }
}
