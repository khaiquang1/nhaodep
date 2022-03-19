<?php

namespace App\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

interface LeadRepository extends RepositoryInterface
{
    public function getAll();

    public function store(array $data);

    public function getAllForCustomer($company_id);

    public function getAllForUser($customer_id);

  
}