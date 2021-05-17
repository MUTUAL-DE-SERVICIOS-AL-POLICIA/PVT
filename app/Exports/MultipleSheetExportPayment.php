<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\SheetExportPayment;

class MultipleSheetExportPayment implements WithMultipleSheets
{   protected $data_uno;
    protected $data_dos;

    protected $name1;
    protected $name2;

    public function __construct(array $data_uno, array $data_dos,$name1,$name2)
    {
        $this->data_uno = $data_uno;
        $this->data_dos = $data_dos;

        $this->name1= $name1;
        $this->name2= $name2;
    }

     /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [
            "data_uno" => new SheetExportPayment($this->data_uno,  $this->name1),
            "data_dos" => new SheetExportPayment($this->data_dos, $this->name2)
        ]; 

        return $sheets;
    }
}
