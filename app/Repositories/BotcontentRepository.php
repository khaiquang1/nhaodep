<?php namespace App\Repositories;


use Prettus\Repository\Contracts\RepositoryInterface;

interface BotcontentRepository extends RepositoryInterface
{
    public function getAll();
}