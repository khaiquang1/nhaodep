<?php namespace App\Repositories;


use Prettus\Repository\Contracts\RepositoryInterface;

interface QuotationRepository extends RepositoryInterface
{
    public function getAll();

    public function createQuotation(array $data);

    public function updateQuotation(array $data,$quotation_id);

    public function getAllToday();
    public function getAllYesterday();
    public function getAllWeek();
    public function getAllMonth();
    public function getAllForCustomer($customer_id);

}