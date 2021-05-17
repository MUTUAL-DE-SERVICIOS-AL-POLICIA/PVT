<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\MultipleSheetsReport;

class FileWithMultipleSheetsDefaulted implements WithMultipleSheets
{
    protected $command_sheet;
    protected $senasir_sheet;

    public function __construct(array $command_sheet, array $senasir_sheet)
    {
        $this->command_sheet = $command_sheet;
        $this->senasir_sheet = $senasir_sheet;
    }

     /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [
            "command" => new MultipleSheetsReport($this->command_sheet, 'comando'),
            "senasir" => new MultipleSheetsReport($this->senasir_sheet, 'senasir')
        ];
        return $sheets;
    }
}
