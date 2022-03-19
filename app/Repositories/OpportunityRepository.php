<?php namespace App\Repositories;


use Prettus\Repository\Contracts\RepositoryInterface;

interface OpportunityRepository extends RepositoryInterface
{
    public function getAll(array $with = []);

    public function create(array $data);

    public function getAllForCustomer($company_id);

    public function getAllForUser($user_id);
}