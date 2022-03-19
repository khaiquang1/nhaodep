<?php namespace App\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

interface SalesOrderRepository extends RepositoryInterface
{
    public function getAll();

    public function createSalesOrder(array $data);

    public function updateSalesOrder(array $data,$saleorder_id);

    public function getAllToday();
    public function getAllYesterday();
    public function getAllWeek();
    public function getAllMonth();
    public function getAllForCustomer($customer_id);
    public function getAllForLead($lead_id);

}