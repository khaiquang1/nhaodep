<?php

namespace App\Repositories;

use App\Models\Country;
use App\Models\deviceToken;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class DeviceTokenRepositoryEloquent extends BaseRepository implements DeviceTokenRepository
{
    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return DeviceToken::class;
    }

    /**
     * Boot up the repository, pushing criteria.
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function getAll()
    {
        return $this->model;
    }
}
