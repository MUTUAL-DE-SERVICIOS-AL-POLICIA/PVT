<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class MultipleSheetsExportDeployed implements FromArray, WithTitle, WithColumnWidths
{
    protected $data;
    protected $name;

    public function __construct(array $data, $name)
    {
        $this->data = $data;
        $this->name = $name;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function title(): string
    {
        return $this->name;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 20,
            'C' => 15,
            'D' => 20,
            'E' => 20,
            'F' => 15,
            'G' => 15,
            'H' => 20,
            'I' => 20,
            'J' => 20,
        ];
    }
}
