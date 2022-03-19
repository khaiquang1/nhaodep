<?php

namespace App\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

interface DomainTrackingRepository extends RepositoryInterface
{
    public function getAll();

    public function create(array $data);

}