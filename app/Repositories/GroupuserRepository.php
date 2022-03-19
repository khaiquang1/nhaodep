<?php namespace App\Repositories;


use Prettus\Repository\Contracts\RepositoryInterface;

interface GroupuserRepository extends RepositoryInterface
{
    public function getAll();
}