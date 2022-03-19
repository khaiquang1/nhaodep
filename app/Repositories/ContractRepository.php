<?php namespace App\Repositories;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface ContractRepository
{
    public function getAll();

    public function create(array $data);

    public function uploadRealSignedContract(UploadedFile $file);

    public function getAllForCustomer($company_id);
}