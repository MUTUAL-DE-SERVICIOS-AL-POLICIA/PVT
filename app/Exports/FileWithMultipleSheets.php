<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\MultipleSheetsExport;

class FileWithMultipleSheets implements WithMultipleSheets
{
    protected $data_command_deployed;
    protected $data_senasir_deployed;
    protected $data_command;
    protected $data_senasir;

    public function __construct(array $data_command_deployed, array $data_senasir_deployed, array $data_command, array $data_senasir)
    {
        $this->data_command_deployed = $data_command_deployed;
        $this->data_senasir_deployed = $data_senasir_deployed;
        $this->data_command = $data_command;
        $this->data_senasir = $data_senasir;
    }

     /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [
            "command_deployed" => new MultipleSheetsExport($this->data_command, 'comando desplegado'),
            "senasir_deployed" => new MultipleSheetsExport($this->data_senasir, 'senasir desplegado'),
            "command" => new MultipleSheetsExport($this->data_command, 'comando'),
            "senasir" => new MultipleSheetsExport($this->data_senasir, 'senasir')
        ];

        return $sheets;
    }
}
