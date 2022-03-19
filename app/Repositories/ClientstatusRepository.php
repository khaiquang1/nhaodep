<?php namespace App\Repositories;


use Prettus\Repository\Contracts\RepositoryInterface;

interface ClientstatusRepository extends RepositoryInterface
{
    public function getAll();
}