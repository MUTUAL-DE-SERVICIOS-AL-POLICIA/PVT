<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\MultipleSheetsReport;

class FileWithMultipleSheetsReport implements WithMultipleSheets
{
    protected $command_sheet_later;
    protected $command_sheet_before;
    protected $senasir_sheet_later;
    protected $senasir_sheet_before;

    public function __construct(array $command_sheet_later, array $command_sheet_before, array $senasir_sheet_later, array $senasir_sheet_before)
    {
        $this->command_sheet_later = $command_sheet_later;
        $this->command_sheet_before = $command_sheet_before;
        $this->senasir_sheet_later = $senasir_sheet_later;
        $this->senasir_sheet_before = $senasir_sheet_before;
    }

     /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [
            "command_later" => new MultipleSheetsReport($this->command_sheet_later, 'comando posterior 15'),
            "command_before" => new MultipleSheetsReport($this->command_sheet_before, 'comando anterior 15'),
            "senasir_later" => new MultipleSheetsReport($this->senasir_sheet_later, 'senasir posterior 15'),
            "senasir_before" => new MultipleSheetsReport($this->senasir_sheet_before, 'senasir anterior 15')
        ];

        return $sheets;
    }
}
