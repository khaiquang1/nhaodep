<?php namespace App\Repositories;

use App\Models\Lead;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use Sentinel;

class LeadRepositoryEloquent extends BaseRepository implements LeadRepository
{
    private $userRepository;

    public function model()
    {
        return Lead::class;
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

    public function store(array $data)
    {
        $this->generateParams();
        $user_id = $this->userRepository->getUser()->id;
        $user = $this->userRepository->find($user_id);
        $lead = $user->leads()->create($data);
        return $lead;
    }

    public function getAllForCustomer($company_id)
    {
	    return $this->model->where('customer_id', $company_id);
    }

	public function getAllForUser($customer_id)
	{
		$models = $this->model->whereHas('user', function ($q) use ($customer_id) {
			$q->where('customer_id', $customer_id);
		});
		return $models;
    }
}