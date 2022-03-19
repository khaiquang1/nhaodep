<?php namespace App\Repositories;

use Maatwebsite\Excel\Excel;

class ExcelRepositoryDefault implements ExcelRepository
{
    /**
     * @var Excel
     */
    private $excel;

    /**
     * ExcelRepositoryDefault constructor.
     * @param Excel $excel
     */
    public function __construct(Excel $excel)
    {
        $this->excel = $excel;
    }

    public function load($filePath)
    {
        return $this->excel->load($filePath);
    }

    public function getAllRows($filePath)
    {
        return $this->excel->load($filePath)->all();
    }
}