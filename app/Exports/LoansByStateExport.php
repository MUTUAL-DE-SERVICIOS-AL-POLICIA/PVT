<?php

namespace App\Exports;

use App\LoanState;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LoansByStateExport implements WithMultipleSheets
{
    /**
     * @var mixed
     */
    private $finalDate;

    public function __construct($finalDate)
    {
        $this->finalDate = $finalDate;
    }

    public function sheets(): array
    {
        $vigenteId   = LoanState::where('name', 'Vigente')->value('id');
        $liquidadoId = LoanState::where('name', 'Liquidado')->value('id');

        return [
            new LoansSheetExport('PRE-VIGENTE',   $vigenteId,   $this->finalDate),
            new LoansSheetExport('PRE-LIQUIDADO', $liquidadoId, $this->finalDate),
        ];
    }
}
