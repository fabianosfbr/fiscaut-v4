<?php

namespace App\Imports;

use App\Models\Layout;
use Rap2hpoutre\FastExcel\FastExcel;

class OptimizedExcelImport
{
    private $data = [];

    private $headers = [];

    public function __construct(
        protected Layout $layout,
        protected string $file
    ) {
        $this->getHeaders();
    }

    private function getHeaders(): array
    {
        $this->headers = (new FastExcel)->import($this->file, function ($row) {
            // Apenas retorna os cabeçalhos na primeira linha
            // $row é um array associativo
            return array_keys($row);
        }, 1)->first();

        return $this->headers;
    }

    public function validateExcelColumns(): array
    {
        $missingColumns = [];
        $requiredHeaders = $this->layout->layoutColumns;
        $normalizedHeader = array_map(function ($columnName) {
            return strtolower(trim($columnName));
        }, $this->headers);

        foreach ($requiredHeaders as $column) {
            // Normaliza o nome da coluna do layout: remove espaços e converte para minúsculas
            $normalizedColumnName = strtolower(trim($column->excel_column_name));

            if (! in_array($normalizedColumnName, $normalizedHeader)) {
                $missingColumns[] = $column->excel_column_name; // Mantém o nome original para exibir ao usuário
            }
        }

        return $missingColumns;
    }

    public function getData(): array
    {
        $collection = (new FastExcel)->import($this->file);

        $this->data = $collection->toArray();

        return $this->data;
    }
}
