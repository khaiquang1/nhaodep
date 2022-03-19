<?php

namespace App\Repositories;


use App\Models\EmailTemplate;
use App\Models\User;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use Sentinel;

class EmailTemplateRepositoryEloquent extends BaseRepository implements EmailTemplateRepository
{
    private $userRepository;
    /**
     * Specify Model class name.
     */
    public function model()
    {
        return EmailTemplate::class;
    }

    /**
     * Boot up the repository, pushing criteria.
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function generateParams(){


        $this->userRepository = new UserRepositoryEloquent(app());
    }

    public function getAll()
    {
        $models = $this->model;
        return $models;
    }

    public function getAllForUser()
    {
        $this->generateParams();
        $user_id = $this->userRepository->getUser()->id;
        $user = $this->userRepository->find($user_id);
        $emailTemplates = $user->emailTemplates;
        return $emailTemplates;
    }
}