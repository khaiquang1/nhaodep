<?php namespace App\Repositories;

use App\Models\Opportunity;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class OpportunityRepositoryEloquent extends BaseRepository implements OpportunityRepository
{
    private $userRepository;
    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return Opportunity::class;
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

    public function getAll(array $with = [])
    {
        $models = $this->model;
        return $models;
    }

    public function create(array $data)
    {
        $this->generateParams();
        $user_id = $this->userRepository->getUser()->id;
        $user = $this->userRepository->find($user_id);
	    $user->opportunities()->create($data);
    }

    public function getAllForCustomer($company_id)
    {
	    return $this->model->where('customer_id', $company_id);
    }

	public function getAllForUser($user_id)
	{
		return $this->model->where('user_id', $user_id);
	}
}