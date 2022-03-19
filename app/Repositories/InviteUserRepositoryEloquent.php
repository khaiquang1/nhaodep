<?php

namespace App\Repositories;

use App\Models\InviteUser;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;

class InviteUserRepositoryEloquent extends BaseRepository implements InviteUserRepository
{
    private $userRepository;

    public function model()
    {
        return InviteUser::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function generateParams()
    {
        $this->userRepository = new UserRepositoryEloquent(app());
    }

    public function createInvite(array $data)
    {
        $this->generateParams();
        $user_id = $this->userRepository->getUser()->id;
        $user = $this->userRepository->find($user_id);
        $data['code'] = bin2hex(openssl_random_pseudo_bytes(16));
        return $user->invite()->create($data);
    }


}