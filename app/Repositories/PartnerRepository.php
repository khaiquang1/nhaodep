<?php

namespace App\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

interface PartnerRepository extends RepositoryInterface
{
    public function getAll();

    public function createPartner(array $data);

    public function updatePartner(array $data,$partner_id);

    public function deletePartner($deleteteam);

    public function findPartner($partner_id);
}