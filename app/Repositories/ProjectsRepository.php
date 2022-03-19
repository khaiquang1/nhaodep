<?php
namespace App\Repositories;
use Prettus\Repository\Contracts\RepositoryInterface;

interface ProjectsRepository extends RepositoryInterface

{
    public function create(array $data);
    public function getAll();
}