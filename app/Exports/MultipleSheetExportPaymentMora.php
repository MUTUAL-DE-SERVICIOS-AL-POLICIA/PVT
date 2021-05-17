<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\SheetExportPayment;
use App\Exports\MultipleSheetsExport;

class MultipleSheetExportPaymentMora implements WithMultipleSheets
{   protected $data_uno;
    protected $data_dos;
    protected $data_tres;

    //protected $name1;
    //protected $name2;
    //protected $name3;

    public function __construct(array $data_uno, array $data_dos,array $data_t)
    {
        $this->data_uno = $data_uno;
        $this->data_dos = $data_dos;
        $this->data_dos = $data_t;

        /*$this->name1= $name1;
        $this->name2= $name2;
        $this->name3= $name3;*/
    }

     /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [
            "data_uno" => new SheetExportPayment($this->data_uno, 'MORA TOTAL'),
            "data_dos" => new SheetExportPayment($this->data_dos, 'MORA PARCIAL'),  
            "data" => new SheetExportPayment($this->data_dos, 'MORA'),
        ]; 

        return $sheets;
    }
}
