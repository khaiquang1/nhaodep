<?php namespace App\Repositories;


use Prettus\Repository\Contracts\RepositoryInterface;

interface GetdataRepository extends RepositoryInterface
{
    public function getAll();
}