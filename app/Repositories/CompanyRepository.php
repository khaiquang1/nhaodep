<?php namespace App\Repositories;


use Prettus\Repository\Contracts\RepositoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface CompanyRepository extends RepositoryInterface
{
    public function getAll();

    public function create(array $data);

    public function uploadAvatar(UploadedFile $file);
}