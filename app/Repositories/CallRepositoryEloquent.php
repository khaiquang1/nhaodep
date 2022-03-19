<?php namespace App\Repositories;

use App\Models\Call;
use App\Models\User;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use Sentinel;

class CallRepositoryEloquent extends BaseRepository implements CallRepository
{
    private $userRepository;
    /**
     * Specify Model class name.
     */
    public function model()
    {
        return Call::class;
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
        $calls = $this->model;

        return $calls;
    }

    public function getAllLeads()
    {
        $calls = $this->model->where('call_type', 'leads');
        return $calls;
    }

    public function getAllOppotunity()
    {
        $calls = $this->model->where('call_type', 'opportunities');
        return $calls;
    }

    public function create(array $data)
    {
        $this->generateParams();
        $user_id = $this->userRepository->getUser()->id;
        $user = $this->userRepository->find($user_id);
        $call = $user->calls()->create($data);
        return $call;
    }


}