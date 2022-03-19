<?php namespace App\Repositories;

use App\Models\User;
use Prettus\Repository\Contracts\RepositoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;


/**
 * Interface UserRepository
 * @package app\Repositories
 */
interface UserRepository extends RepositoryInterface
{
    public function getUser();

    public function getAll();

    public function getAllNew();

    public function getStaff();

    public function getCustomers();

    public function getParentStaff();

    public function getParentCustomers();

    public function uploadAvatar(UploadedFile $file);

    public function generateThumbnail($file);

    public function create(array $data, $activate = false);

    public function assignRole(User $user, $roleName);

    public function getAllForCustomer();

    public function getUsersAndStaffs();

    public function usersWithTrashed($email);

    public function getAllStaffOfUser($user);


}