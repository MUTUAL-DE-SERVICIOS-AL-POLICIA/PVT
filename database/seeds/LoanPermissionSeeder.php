<?php

use Illuminate\Database\Seeder;
use App\Permission;

class LoanPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            [
                'name' => 'create-loan',
                'display_name' => 'Crear trámites de préstamo'
            ], [
                'name' => 'update-loan',
                'display_name' => 'Editar trámites de préstamo'
            ], [
                'name' => 'show-loan',
                'display_name' => 'Ver trámites de préstamo para el rol'
            ], [
                'name' => 'show-all-loan',
                'display_name' => 'Ver todos los trámites de préstamo'
            ], [
                'name' => 'delete-loan',
                'display_name' => 'Anular trámites de préstamo'
            ], [
                'name' => 'print-contract-loan',
                'display_name' => 'Imprimir contrato de préstamo'
            ], [
                'name' => 'show-deleted-loan',
                'display_name' => 'Ver trámites anulados de préstamo'
            ], [
                'name' => 'print-payment-plan',
                'display_name' => 'Imprimir plan de pagos'
            ],  [
                'name' => 'print-payment-kardex-loan',
                'display_name' => 'Imprimir kardex de pagos de préstamo'
            ],  [
                'name' => 'create-payment-loan',
                'display_name' => 'Crear tramites de cobranzas'
            ],  [
                'name' => 'update-payment-loan',
                'display_name' => 'Editar tramites de cobranzas'
            ],  [
                'name' => 'show-payment-loan',
                'display_name' => 'Ver trámites de cobranzas para el rol'
            ],  [
                'name' => 'delete-payment-loan',
                'display_name' => 'Anular trámites de cobranzas'
            ],  [
                'name' => 'show-all-payment-loan',
                'display_name' => 'Ver todos los trámites de cobranzas'
            ],  [
                'name' => 'print-payment-loan',
                'display_name' => 'Imprimir registro de pago de cobranzas'
            ],  [
                'name' => 'show-payment',
                'display_name' => 'Ver cobros de tesorería'
            ],  [
                'name' => 'create-payment',
                'display_name' => 'Crear cobros de tesorería'
            ],  [
                'name' => 'update-payment',
                'display_name' => 'Editar cobros de tesorería'
            ],  [
                'name' => 'delete-payment',
                'display_name' => 'Anular cobros de tesorería'
            ],  [
                'name' => 'disbursement-loan',
                'display_name' => 'Permisos de desembolso de préstamo'
            ],  [
                'name' => 'print-payment-voucher',
                'display_name' => 'Imprimir registro de pago de tesorería'
            ], [
                'name' => 'show-deleted-payment',
                'display_name' => 'Ver trámites anulados de cobros'
            ], [
                'name' => 'validate-submitted-documents',
                'display_name' => 'Validar documentos presentados'
            ], [
                'name' => 'release-loan-user',
                'display_name' => 'Liberar usuario de préstamo'
            ], [
                'name' => 'update-accounting-voucher',
                'display_name' => 'Editar certificación presupuestaria contable'
            ],  [
                'name' => 'update-refinancing-balance',
                'display_name' => 'Actualizar saldo de refinanciamiento'
            ], [
                'name' => 'registration-delivery-return-contracts',
                'display_name' => 'Registro entrega/retorno de Contrato'
            ],[
                'name' => 'update-documents-requirements',
                'display_name' => 'Actualizar documentos y requisitos'
            ],[
                'name' => 'update-loan-calculations',
                'display_name' => 'Actualizar los calculos del préstamo'
            ],[
                'name' => 'print-qualification-form',
                'display_name' => 'Imprimir formulario de calificación'
            ],[
                'name' => 'show-history-loan',
                'display_name' => 'Ver historial préstamos'
            ],[
                'name' => 'update-reference-cosigner',
                'display_name' => 'Actualizar p. referencias y codeudores'
            ],[
                'name' => 'update-warranty-hipotecary',
                'display_name' => 'Actualizar Garantia hipotecaria'
            ],[
                'name' => 'update-values-commercial-rescue',
                'display_name' => 'Actualizar valor comercial/rescate hipoteca'
            ],[
                'name' => 'change-disbursement-date',
                'display_name' => 'Cambiar la fecha de desembolso'
            ],[
                'name' => 'show-list-loans-generated',
                'display_name' => 'Ver generador de reporte de prestamos'
            ],[
                'name' => 'show-list-payments-generated',
                'display_name' => 'Ver generador de reporte de cobros'
            ]
        ];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate($permission);
        }
    }
}