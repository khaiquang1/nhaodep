<?php namespace App\Repositories;

use App\Models\DomainTracking;
use App\Models\User;
use Illuminate\Support\Str;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use Sentinel;

class DomainTrackingRepositoryEloquent extends BaseRepository implements DomainTrackingRepository
{
    private $userRepository;
    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return DomainTracking::class;
    }

    /**
     * Boot up the repository, pushing criteria.
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function generateParams(){
    }


    public function getAll()
    {
        $this->generateParams();
        return $user->DomainTrackings;
    }

    public function create(array $data)
    {
        $DomainTracking = DomainTracking::save($data);
        return $DomainTracking;
    }
    
}