<?php namespace App\Repositories;


use Prettus\Repository\Contracts\RepositoryInterface;

interface CallRepository extends RepositoryInterface
{
    public function getAll();

    public function getAllLeads();

    public function getAllOppotunity();

    public function create(array $data);
}