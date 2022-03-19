<?php

namespace App\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;


interface DeviceTokenRepository extends RepositoryInterface
{
    public function getAll();
}