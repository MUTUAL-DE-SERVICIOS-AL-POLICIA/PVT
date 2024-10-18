<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Util;
use App\User;
use App\LoanPaymentPeriod;
use Carbon;
use App\Http\Requests\LoanPaymentPeriodForm;

/** @group Periodos de cobros 
* Periodos de cobros para la importación
*/

class LoanPaymentPeriodController extends Controller
{
    /**
    * Listar periodos
    * Devuelve el listado de los roles disponibles en el sistema
    * @queryParam search Parámetro de búsqueda. Example: 2020
    * @queryParam sortBy Vector de ordenamiento. Example: []
    * @queryParam sortDesc Vector de orden descendente(true) o ascendente(false). Example: [false]
    * @queryParam per_page Número de datos por página. Example: 8
    * @queryParam page Número de página. Example: 1
    * @authenticated
    * @responseFile responses/periods/index.200.json
    */
    public function index(Request $request)
    {
        return Util::search_sort(new LoanPaymentPeriod(), $request);
    }

    /**
     * Nuevo registro de periodo
     * Inserta el periodo 
     * @bodyParam year numeric Año del periodo. Example: 2021
     * @bodyParam month numeric mes de la boleta es requerido. Example: 2
     * @bodyParam importation_type enum required (SENASIR, COMANDO) Tipo de importacion. Example: SENASIR
     * @bodyParam import_senasir boolean mes de . Example: false
     * @bodyParam description string Descripcion del periodo. Example: Periodo de descripción
     * @authenticated
     * @responseFile responses/periods/store.200.json
     */
    public function store(Request $request)
    {
        $last_period_senasir = LoanPaymentPeriod::orderBy('id')->where('importation_type','SENASIR')->get()->last();
        $last_period_comand = LoanPaymentPeriod::orderBy('id')->where('importation_type','COMANDO')->get()->last();
        $last_period_season = LoanPaymentPeriod::orderBy('id')->where('importation_type','ESTACIONAL')->get()->last();
        $create_period = false;

        $request->validate([
            'importation_type' => 'string|required|in:COMANDO,SENASIR,ESTACIONAL',
        ]);
       $result = [];
        if($request->importation_type == "COMANDO"){
            if(!$last_period_comand){
            $estimated_date = Carbon::now()->endOfMonth();
            $create_period = true;
            }else{  
                $last_date = Carbon::parse($last_period_comand->year.'-'.$last_period_comand->month); 
                if($last_period_comand->importation){    
                    $estimated_date = $last_date->addMonth();   
                    $create_period = true;
                }else{
                $result['message'] = "Para realizar la creación de un nuevo periodo, debe realizar la confirmación de los pagos de Comando del periodo de ".$last_date->isoFormat('MMMM');
                }
            } 
        }
        elseif($request->importation_type == "SENASIR"){
            if(!$last_period_senasir){
                $estimated_date = Carbon::now()->endOfMonth();
                $create_period = true;
            }else{  
                $last_date = Carbon::parse($last_period_senasir->year.'-'.$last_period_senasir->month); 
                if($last_period_senasir->importation){    
                    $estimated_date = $last_date->addMonth();   
                    $create_period = true;
                }else{
                $result['message'] = "Para realizar la creación de un nuevo periodo, debe realizar la confirmación de los pagos de Senasir del periodo de ".$last_date->isoFormat('MMMM');
                }
            } 
        }
        elseif($request->importation_type == "ESTACIONAL"){
            if(!$last_period_season){
                $estimated_date = Carbon::now();
                $semester = ($estimated_date->quarter <= 2) ? 1 : 2;
                if($semester == 1)
                    $estimated_date->startOfYear()->addMonth(6)->endOfMonth();
                else
                    $estimated_date->endOfYear()->endOfMonth();
                $create_period = true;
            }else{
                $last_date = Carbon::parse($last_period_season->year.'-'.$last_period_season->month); 
                if($last_period_season->importation){    
                    $estimated_date = $last_date->startOfMonth()->addMonth(6)->endOfMonth();
                    $create_period = true;
                }else{
                    $result['message'] = "Para realizar la creación de un nuevo periodo, debe realizar la confirmación de los pagos de Senasir del periodo de ".$last_date->isoFormat('MMMM');
                }
            } 
        }   
        if($create_period){
            $loan_payment_period = new LoanPaymentPeriod;
                $loan_payment_period->year = $estimated_date->year;
                $loan_payment_period->month = $estimated_date->month;
                $loan_payment_period->description = $request->description;
                $loan_payment_period->importation_type = $request->importation_type;
                $loan_payment_period->importation = false;          
                return LoanPaymentPeriod::create($loan_payment_period->toArray());
            }
        return $result;      
    }

    /**
     * Detalle de los periodos
     * Devuelve el detalle de un periodo 
     * @urlParam id required ID del periodo. Example: 1
     * @responseFile responses/periods/show.200.json
     * @response
     */
    public function show($id)
    {
        $loan_payment_period = LoanPaymentPeriod::find($id);
        return $loan_payment_period;
    }

    /**
     * Actualizar periodo
     * Actualizar periodo para cobranzas
     * @urlParam period ID de periodo. Example: 591292
     * @bodyParam year numeric required Año del periodo. Example: 2021
     * @bodyParam month numeric required mes de la boleta es requerido. Example: 2
     * @bodyParam amount_conciliation numeric Monto de conciliacion. Example: 1255.5
     * @bodyParam description string Descripcion del periodo. Example: Periodo de descripción
     * @authenticated
     * @responseFile responses/periods/update.200.json
     */
    public function update(LoanPaymentPeriodForm $request,LoanPaymentPeriod $loan_payment_period)
    {
        $loan_payment_period->fill($request->all());
        $loan_payment_period->save();
        return  $loan_payment_period;
    }

    /**
     * Eliminar periodo.
     * @urlParam period ID de periodo. Example: 2
     * @authenticated
     * @responseFile responses/periods/destroy.200.json
     */
    public function destroy(LoanPaymentPeriod $loan_payment_period)
    {
        $loan_payment_period->delete();
        return $loan_payment_period;
    }

    /**
     * Listar los meses de un año
     * @queryParam year required Año de busqueda. Example: 2020
     * @authenticated
     * @responseFile responses/periods/get_list_month.200.json
     */
    public function get_list_month(Request $request)
    {
        $request->validate([
            'year' => 'required|exists:loan_payment_periods,year',
            'importation_type' => 'string|required|in:COMANDO,SENASIR,ESTACIONAL',
        ]);
        $loan_payment_period = LoanPaymentPeriod::where('year',$request->year)->where('importation_type',$request->importation_type)->orderBy('month', 'asc')->get();
        return $loan_payment_period;
    }

     /**
     * Listar los años registrados en la tabla periodos
     * @authenticated
     * @responseFile responses/periods/get_list_year.200.json
     */
    public function get_list_year(Request $request)
    {
        $loan_payment_period = LoanPaymentPeriod::select('year')->distinct()->orderBy('year', 'asc')->get();
        return $loan_payment_period;
    }

}
