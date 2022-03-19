<?php namespace App\Repositories;


use Prettus\Repository\Contracts\RepositoryInterface;

interface GroupclientRepository extends RepositoryInterface
{
    public function getAll();
}