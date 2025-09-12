<?php

namespace App\Exports;

use App\Loan;
use Carbon\Carbon;
use Util;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithTitle;

// Opción B: autosize + eventos de hoja
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class LoansSheetExport implements
    FromQuery,
    WithMapping,
    WithHeadings,
    WithChunkReading,
    ShouldQueue,
    WithTitle,
    ShouldAutoSize,
    WithEvents
{
    private $sheetTitle;
    private $stateId;
    private $finalDate;

    // caches
    private $catNameCache = [];
    private $affiliateCache = [];
    private $spouseOfAffiliateCache = [];
    private $loanModalityTermCache = [];

    // roles (para usuarios sin N+1)
    private $rolePlatformId;
    private $roleQualId;

    public function __construct($sheetTitle, $stateId, $finalDate)
    {
        $this->sheetTitle = $sheetTitle;
        $this->stateId    = (int) $stateId;
        $this->finalDate  = $finalDate;

        // Resolver IDs de roles una sola vez
        $this->rolePlatformId = \App\Role::where('module_id', 6)->whereDisplayName('Plataforma')->value('id');
        $this->roleQualId     = \App\Role::where('module_id', 6)->whereDisplayName('Calificación')->value('id');
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function headings(): array
    {
        return [
            "NRO DE PRÉSTAMO","FECHA DE SOLICITUD","FECHA DESEMBOLSO","FECHA DE VENCIMIENTO",
            "SECTOR","PRODUCTO","PLAZO DEL PRÉSTAMO","TASA ANUAL DE INTERES","CUOTA","CIUDAD",
            "USUARIO DE PLATAFORMA","USUARIO DE CALIFICACIÓN","***",
            "CI PRESTATARO","MATRICULA PRESTATARIO","AP. PATERNO PRESTATARIO","AP. MATERNO PRESTATARIO","AP. CASADA PRESTATARIO",
            "1er NOMPRE PRESTATARIO","2DO NOMBRE PRESTATARIO","GRADO PRESTATARIO","Nro CELULAR","FECHA DE NACIMIENTO","FECHA DE INGRESO",
            "CATEGORIA","UNIDAD","FECHA DE DESVINCULACIÓN","FECHA DE FALLECIMIENTO","CAUSA DE FALLECIMIENTO","***",
            "CI GAR 1","MATRICULA GAR 1","APELLIDO PATERNO GAR 1","APELLIDO MATERNO GAR 1","APE. CASADA GAR 1","1er NOMPRE GAR 1","2DO NOMBRE GAR 1","GRADO GAR 1",
            "Nro CELULAR GAR 1","CATEGORIA GAR 1","UNIDAD GAR 1","FECHA DE DESVINCULACIÓN GAR 1","FECHA DE FALLECIMIENTO GAR 1","***",
            "CI GAR 2","MATRICULA GAR 2","APELLIDO PATERNO GAR 2","APELLIDO MATERNO GAR 2","APE. CASADA GAR 2","1er NOMPRE GAR 2","2DO NOMBRE GAR 2","GRADO GAR 2",
            "Nro CELULAR GAR 2","CATEGORIA GAR 2","UNIDAD GAR 2","FECHA DE DESVINCULACIÓN GAR 2","FECHA DE FALLECIMIENTO GAR 2","***",
            "MONTO DESEMBOLSADO","MONTO REFINANCIADO","AMPLIACION","LIQUIDO DESEMBOLSADO","REPROGRAMACIÓN","MONTO REPROGRAMADO","SALDO A LA FECHA DE CORTE",
            "SALDO SEGÚN PLAN DE PAGOS","CAPITAL PAGADO A FECHA DE CORTE","ESTADO PTMO","FECHA ULTIMO PAGO DE INTERES","INDICE DE ENDEUDAMIENTO",
            "NRO. CBTE. CONTABLE","NRO CUENTA SIGEP","FECHA DE CORTE"
        ];
    }

    public function query()
    {
        $fd = ($this->finalDate instanceof Carbon) ? $this->finalDate : Carbon::parse($this->finalDate);

        return Loan::query()
            ->select([
                'id','code','request_date','disbursement_date',
                'loan_term','interest_id','city_id','state_id',
                'affiliate_id','procedure_modality_id',
                'amount_approved',
                'num_accounting_voucher','number_payment_type',
                'parent_reason',
            ])

            // === ÚLTIMO REGISTRO DE PAGO SEGÚN estimated_date <= $fd ===
            ->selectSub(function($q) use ($fd) {
                $q->from('loan_payments')
                  ->select('estimated_date')
                  ->whereColumn('loan_payments.loan_id','loans.id')
                  ->where('estimated_date','<=',$fd)
                  ->orderBy('estimated_date','desc')
                  ->orderBy('id','desc')
                  ->limit(1);
            }, 'last_estimated_date')
            ->selectSub(function($q) use ($fd) {
                $q->from('loan_payments')
                  ->select('previous_balance')
                  ->whereColumn('loan_payments.loan_id','loans.id')
                  ->where('estimated_date','<=',$fd)
                  ->orderBy('estimated_date','desc')
                  ->orderBy('id','desc')
                  ->limit(1);
            }, 'last_prev_balance_est')
            ->selectSub(function($q) use ($fd) {
                $q->from('loan_payments')
                  ->select('capital_payment')
                  ->whereColumn('loan_payments.loan_id','loans.id')
                  ->where('estimated_date','<=',$fd)
                  ->orderBy('estimated_date','desc')
                  ->orderBy('id','desc')
                  ->limit(1);
            }, 'last_capital_payment_est')
            ->selectSub(function($q) use ($fd) {
                $q->from('loan_payments')
                  ->select('loan_payment_date')
                  ->whereColumn('loan_payments.loan_id','loans.id')
                  ->where('estimated_date','<=',$fd)
                  ->orderBy('estimated_date','desc')
                  ->orderBy('id','desc')
                  ->limit(1);
            }, 'last_real_payment_date')

            // === Usuarios (evitar métodos por fila) ===
            ->selectSub(function($q){
                $q->from('records')
                  ->join('users','users.id','=','records.user_id')
                  ->select('users.username')
                  ->whereColumn('records.recordable_id','loans.id')
                  ->where('records.recordable_type','loans')
                  ->where('records.action','ilike','%registró%')
                  ->where('records.role_id', $this->rolePlatformId)
                  ->orderBy('records.created_at','desc')
                  ->limit(1);
            }, 'platform_username')
            ->selectSub(function($q){
                $q->from('records')
                  ->join('users','users.id','=','records.user_id')
                  ->select('users.username')
                  ->whereColumn('records.recordable_id','loans.id')
                  ->where('records.recordable_type','loans')
                  ->where('records.action','ilike','%de Calificación%')
                  ->where('records.role_id', $this->roleQualId)
                  ->orderBy('records.created_at','desc')
                  ->limit(1);
            }, 'qualification_username')

            // === Garantes (hasta 2) como JSON (evita 1 query por préstamo) ===
            ->selectSub(<<<'SQL'
                SELECT COALESCE(
                  json_agg(row_to_json(t) ORDER BY t.id),
                  '[]'::json
                )
                FROM (
                  SELECT id_affiliate, type_affiliate_spouse_loan, id
                  FROM view_loan_guarantors
                  WHERE id_loan = loans.id
                  ORDER BY id
                  LIMIT 2
                ) t
                SQL
                , 'guarantors_json')

            ->where('state_id', $this->stateId)
            ->where('disbursement_date', '<=', $fd)
            ->orderBy('id')

            ->with([
                'state:id,name',
                'city:id,name',
                'interest:id,annual_interest',

                // producto / tipo
                'modality' => function ($q) { $q->select('id','procedure_type_id'); },
                'modality.procedure_type:id,name',

                // borrower único
                'one_borrower' => function ($q) {
                    $q->select('id','loan_id','type','indebtedness_calculated','degree_id','affiliate_state_id');
                },
                'one_borrower.degree:id,name',
                'one_borrower.affiliate_state.affiliate_state_type:id,name',

                // datos del afiliado titular + cónyuges
                'affiliate:id,date_entry,date_derelict,date_death,reason_death,degree_id,category_id,unit_id,cell_phone_number,identity_card,registration,first_name,second_name,last_name,mothers_last_name,surname_husband,birth_date',
                'affiliate.degree:id,name',
                'affiliate.unit:id,name',
                'affiliate.spouses' => function ($q) {
                    $q->select('id','affiliate_id','identity_card','registration','first_name','second_name','last_name','mothers_last_name','surname_husband','cell_phone_number')
                      ->orderBy('id','desc');
                },

                // para computeEstimatedQuota()
                'loan_procedure',
                'loan_procedure.loan_global_parameter',
            ]);
    }

    private function cleanPhone($v)
    {
        if (!$v) return '';
        return preg_replace('/\D+/', '', $v);
    }

    private function getAffiliateById($id)
    {
        if (!$id) return null;
        if (!array_key_exists($id, $this->affiliateCache)) {
            $this->affiliateCache[$id] = \App\Affiliate::with([
                'degree:id,name',
                'unit:id,name',
            ])->select(
                'id','identity_card','registration',
                'first_name','second_name','last_name','mothers_last_name','surname_husband',
                'cell_phone_number','degree_id','category_id','unit_id',
                'date_derelict','date_death'
            )->find($id);
        }
        return $this->affiliateCache[$id];
    }

    // Primer cónyuge de un Affiliate (hasMany), cacheado
    private function getSpouseOfAffiliate($affiliateId)
    {
        if (!$affiliateId) return null;
        if (!array_key_exists($affiliateId, $this->spouseOfAffiliateCache)) {
            $aff = \App\Affiliate::select('id')->find($affiliateId);
            $this->spouseOfAffiliateCache[$affiliateId] = $aff
                ? $aff->spouses()->select(
                    'id','affiliate_id',
                    'identity_card','registration',
                    'first_name','second_name','last_name','mothers_last_name','surname_husband',
                    'cell_phone_number'
                )->orderBy('id','desc')->first()
                : null;
        }
        return $this->spouseOfAffiliateCache[$affiliateId];
    }

    // Nombre de categoría (en Affiliate es accessor), cacheada por category_id
    private function catName($affiliate)
    {
        if (!$affiliate) return '';
        $cid = \Illuminate\Support\Arr::get($affiliate, 'category_id');
        if ($cid) {
            if (!array_key_exists($cid, $this->catNameCache)) {
                $cat = \App\Category::select('id','name')->find($cid);
                $this->catNameCache[$cid] = $cat ? $cat->name : '';
            }
            return $this->catNameCache[$cid];
        }
        $cat = \Illuminate\Support\Arr::get($affiliate, 'category'); // accessor
        return $cat ? (\Illuminate\Support\Arr::get($cat, 'name', '') ?: '') : '';
    }

    // Cálculo local de la cuota estimada (evita accessor y valida nulos)
    private function computeEstimatedQuota($loan)
    {
        try {
            $lgp = data_get($loan, 'loan_procedure.loan_global_parameter');
            if (!$lgp) return 0;

            $num = isset($lgp->numerator) ? (float)$lgp->numerator : null;
            $den = isset($lgp->denominator) ? (float)$lgp->denominator : null;
            if (!$num || !$den || $den == 0.0) return 0;

            $parameter = $num / $den;

            $pmId = $loan->procedure_modality_id ?: null;
            if (!$pmId) return 0;

            if (!array_key_exists($pmId, $this->loanModalityTermCache)) {
                $row = \App\LoanModalityParameter::where('procedure_modality_id', $pmId)
                    ->select('loan_month_term')->first();
                $this->loanModalityTermCache[$pmId] = $row ? (int)$row->loan_month_term : null;
            }
            $loan_month_term = $this->loanModalityTermCache[$pmId];
            if (!$loan_month_term) return 0;

            $interest = data_get($loan, 'interest');
            if (!$interest || !method_exists($interest, 'monthly_current_interest')) return 0;

            $monthly_interest = $interest->monthly_current_interest($parameter, $loan_month_term);
            if (!$monthly_interest || !(int)$loan->loan_term) return 0;

            $denominator = (1 - 1 / pow((1 + $monthly_interest), (int)$loan->loan_term));
            if ($denominator == 0.0) return 0;

            return \Util::round2($monthly_interest * (float)$loan->amount_approved / $denominator);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function map($loan): array
    {
        $fmtDate = function ($v) {
            return $v ? \Carbon\Carbon::parse($v)->format('d/m/Y') : '';
        };
        $fmtMoney = function ($v) {
            return \Util::money_format($v ?? 0);
        };

        // ¿prestatario afiliado o cónyuge?
        $isAffiliate = (data_get($loan, 'one_borrower.type') === 'affiliates');

        // Persona principal
        $person = $isAffiliate
            ? data_get($loan, 'affiliate')
            : (data_get($loan, 'affiliate.spouses.0') ?: null);

        // Usuarios (subconsultas en query)
        $pu = $loan->platform_username ?: '';
        $qu = $loan->qualification_username ?: '';

        // Sector / Producto / Índice endeudamiento
        $sector   = data_get($loan, 'one_borrower.affiliate_state.affiliate_state_type.name', '');
        $producto = data_get($loan, 'modality.procedure_type.name', '');
        $indebt   = (float) data_get($loan, 'one_borrower.indebtedness_calculated', 0);

        // Último pago por estimated_date (aliases)
        $lpEstDate    = $loan->last_estimated_date ? \Carbon\Carbon::parse($loan->last_estimated_date) : null;
        $prevEst      = (float) ($loan->last_prev_balance_est ?? 0);
        $capEst       = (float) ($loan->last_capital_payment_est ?? 0);
        $capitalPaid  = $lpEstDate ? ($loan->amount_approved - $prevEst + $capEst) : 0;
        $remaining    = $lpEstDate ? ($prevEst - $capEst) : ($loan->amount_approved ?? 0);
        $fechaUltPago = $fmtDate($loan->last_estimated_date);

        // Refinanciado / Neto
        $refinanced   = $loan->balance_parent_refi();
        $netDisbursed = ($loan->amount_approved ?? 0) - ($refinanced ?? 0);

        // Cuota estimada (cálculo local)
        $quota = $this->computeEstimatedQuota($loan);

        // Datos del prestatario (solo para afiliados)
        $gradoTitular     = $isAffiliate ? data_get($person, 'degree.name', '') : '';
        $categoriaTitular = $isAffiliate ? $this->catName($person)            : '';
        $unidadTitular    = $isAffiliate ? data_get($person, 'unit.name', '') : '';

        // Garantes (desde JSON ya traído)
        $gjson = $loan->guarantors_json ? json_decode($loan->guarantors_json, true) : [];
        $g1 = isset($gjson[0]) ? $gjson[0] : null;
        $g2 = isset($gjson[1]) ? $gjson[1] : null;

        $type_g1 = $g1 && ( ($g1['type_affiliate_spouse_loan'] ?? '') === 'affiliates' );
        $type_g2 = $g2 && ( ($g2['type_affiliate_spouse_loan'] ?? '') === 'affiliates' );

        $person_g1 = $g1 ? ( $type_g1
            ? $this->getAffiliateById($g1['id_affiliate'])
            : $this->getSpouseOfAffiliate($g1['id_affiliate']) )
            : null;

        $person_g2 = $g2 ? ( $type_g2
            ? $this->getAffiliateById($g2['id_affiliate'])
            : $this->getSpouseOfAffiliate($g2['id_affiliate']) )
            : null;

        $gradoG1 = $type_g1 ? data_get($person_g1, 'degree.name', '') : '';
        $catG1   = $type_g1 ? $this->catName($person_g1)              : '';
        $uniG1   = $type_g1 ? data_get($person_g1, 'unit.name', '')   : '';

        $gradoG2 = $type_g2 ? data_get($person_g2, 'degree.name', '') : '';
        $catG2   = $type_g2 ? $this->catName($person_g2)              : '';
        $uniG2   = $type_g2 ? data_get($person_g2, 'unit.name', '')   : '';

        return [
            // Cabecera préstamo
            $loan->code,
            $fmtDate($loan->request_date),
            $fmtDate($loan->disbursement_date),
            method_exists($loan, 'expiration_date') ? $loan->expiration_date() : '',

            // Sector / Producto / Plazo / Tasa / Cuota / Ciudad
            $sector,
            $producto,
            $loan->loan_term,
            data_get($loan, 'interest.annual_interest', ''),
            $fmtMoney($quota),
            data_get($loan, 'city.name', ''),

            // Usuarios
            $pu,
            $qu,

            '***',

            // Prestatario
            data_get($person, 'identity_card', ''),
            data_get($person, 'registration', ''),
            data_get($person, 'last_name', ''),
            data_get($person, 'mothers_last_name', ''),
            data_get($person, 'surname_husband', ''),
            data_get($person, 'first_name', ''),
            data_get($person, 'second_name', ''),
            $gradoTitular,
            $this->cleanPhone(data_get($person, 'cell_phone_number', '')),
            $fmtDate(data_get($person, 'birth_date')),
            $isAffiliate ? $fmtDate(data_get($loan, 'affiliate.date_entry')) : '',
            $categoriaTitular,
            $unidadTitular,
            $isAffiliate ? $fmtDate(data_get($loan, 'affiliate.date_derelict')) : '',
            $isAffiliate ? $fmtDate(data_get($loan, 'affiliate.date_death'))   : '',
            $isAffiliate ? data_get($loan, 'affiliate.reason_death', '')       : '',

            '***',

            // Garante 1
            data_get($person_g1, 'identity_card', ''),
            data_get($person_g1, 'registration', ''),
            data_get($person_g1, 'last_name', ''),
            data_get($person_g1, 'mothers_last_name', ''),
            data_get($person_g1, 'surname_husband', ''),
            data_get($person_g1, 'first_name', ''),
            data_get($person_g1, 'second_name', ''),
            $gradoG1,
            $this->cleanPhone(data_get($person_g1, 'cell_phone_number', '')),
            $catG1,
            $uniG1,
            $type_g1 ? $fmtDate(data_get($person_g1, 'date_derelict')) : '',
            $type_g1 ? $fmtDate(data_get($person_g1, 'date_death'))    : '',

            '***',

            // Garante 2
            data_get($person_g2, 'identity_card', ''),
            data_get($person_g2, 'registration', ''),
            data_get($person_g2, 'last_name', ''),
            data_get($person_g2, 'mothers_last_name', ''),
            data_get($person_g2, 'surname_husband', ''),
            data_get($person_g2, 'first_name', ''),
            data_get($person_g2, 'second_name', ''),
            $gradoG2,
            $this->cleanPhone(data_get($person_g2, 'cell_phone_number', '')),
            $catG2,
            $uniG2,
            $type_g2 ? $fmtDate(data_get($person_g2, 'date_derelict')) : '',
            $type_g2 ? $fmtDate(data_get($person_g2, 'date_death'))    : '',

            '***',

            // Montos / Saldos / Estado / Fechas / Otros
            $fmtMoney($loan->amount_approved),                       // MONTO DESEMBOLSADO
            $fmtMoney($refinanced),                                  // MONTO REFINANCIADO
            ($loan->parent_reason === 'REFINANCIAMIENTO') ? 'SI' : 'NO', // AMPLIACION
            $fmtMoney($netDisbursed),                                // LIQUIDO DESEMBOLSADO
            ($loan->parent_reason === 'REPROGRAMACION') ? 'SI' : 'NO',   // REPROGRAMACIÓN
            ($loan->parent_reason === 'REPROGRAMACION') ? $fmtMoney($loan->amount_approved) : '0,00', // MONTO REPROGRAMADO
            $fmtMoney($remaining),                                   // SALDO A LA FECHA DE CORTE
            $fmtMoney($loan->balance),                               // SALDO SEGÚN PLAN DE PAGOS
            $fmtMoney($capitalPaid),                                 // CAP. PAGADO A FECHA DE CORTE
            data_get($loan, 'state.name', ''),                       // ESTADO PTMO
            $fechaUltPago,                                           // FECHA ULTIMO PAGO DE INTERES (estimada)
            $fmtMoney($indebt),                                      // INDICE DE ENDEUDAMIENTO
            $loan->num_accounting_voucher,                           // NRO. CBTE. CONTABLE
            $loan->number_payment_type,                              // NRO CUENTA SIGEP
            $fmtDate($this->finalDate),                              // FECHA DE CORTE
        ];
    }

    // ===== Opción B: helper de rango cabecera + eventos =====

    private function headerRange(): string
    {
        $lastCol = Coordinate::stringFromColumnIndex(count($this->headings()));
        return "A1:{$lastCol}1";
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Refuerza autosize columna por columna (además de ShouldAutoSize)
                $cols = count($this->headings());
                for ($i = 1; $i <= $cols; $i++) {
                    $col = Coordinate::stringFromColumnIndex($i);
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Cabecera: centrado + wrap
                $sheet->getStyle($this->headerRange())
                      ->getAlignment()
                      ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                      ->setVertical(Alignment::VERTICAL_CENTER)
                      ->setWrapText(true);

            },
        ];
    }
}
