<?php namespace App\Repositories;


use Prettus\Repository\Contracts\RepositoryInterface;

interface InvoicePaymentRepository extends RepositoryInterface
{
    public function getAll();
    public function createPayment(array $data);
    public function getAllForCustomer($customer_id);
}