<?php

namespace App\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

interface InvoiceRepository extends RepositoryInterface
{
    public function getAll();

    public function createInvoice(array $data);

    public function updateInvoice(array $data,$invoice_id);

    public function getAllOpen();

    public function getAllOverdue();

    public function getAllPaid();

    public function getAllForCustomer($customer_id);

    public function getAllOpenForCustomer($customer_id);

    public function getAllOverdueForCustomer($customer_id);

    public function getAllPaidForCustomer($customer_id);

    public function getAllOpenMonth();

    public function getAllOverdueMonth();

    public function getAllPaidMonth();


}