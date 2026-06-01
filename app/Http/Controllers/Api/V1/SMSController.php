<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Util;
use App\Loan;
use App\Jobs\ProcessNotificationSMS;

/** @group Notificaciones
* Datos de los trámites de préstamos y sus relaciones
*/


class SMSController extends Controller
{
    /**
    * Notificación mediante SMS
    * Devuelve una respuesta de confirmación
    * @bodyParam loan_id integer required id del préstamo. Example:4925
    * @bodyParam user_id integer required id del usuario. Example: 876
    * @responseFile responses/notification/sms_contracts.200.json
    */
    public function send_sms_for_contract(Request $request) {
        $loan_id = $request->loan_id;
        $user_id = $request->user_id;
        $message = "SE SOLICITA SE APERSONE POR NUESTRAS OFICINAS DE LA MUSERPOL EN EL HORARIO 8:30 A 16:30, A OBJETO DEL RECOJO Y/O FIRMA DEL CONTRATO DE SU PRESTAMO";
        $notification_type = 5; // tipo de notificación: Contrato de préstamo

        $cell_phone_number = Loan::find($loan_id)->borrower->first()->cell_phone_number;
        $cell_phone_number = Util::remove_special_char($cell_phone_number);
        ProcessNotificationSMS::dispatch($cell_phone_number, $message, $loan_id, $user_id, $notification_type);
        return response()->json([
            'error' => false,
            'message' => 'Se esta notificando al afiliado!',
            'data' => []
        ]);
    }
}
