<?php namespace App\Repositories;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Support\Str;

use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use Sentinel;

class PartnerRepositoryEloquent extends BaseRepository implements PartnerRepository
{
    private $userRepository;

    /**
     * Specify Model class name.
     *
     * @return string
     */

    public function model()
    {
        return Partner::class;
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
        $partner = $this->model;
        return $partner;
    }

    public function generateParams(){
    }


    public function createPartner(array $data)
    {
        $this->generateParams();
        $partData = collect($data)->toArray();
        $partner = $this->create($partData);
    }

    public function updatePartner(array $data,$partner_id)
    {
        $this->generateParams();
        $partner= collect($data)->toArray();
        $partnerList = $this->update($partner,$partner_id);
    }

    public function deletePartner($deletepartner)
    {
        $this->generateParams();
        $this->delete($deletepartner);
    }


    public function findPartner($partner_id)
    {
        $partner=$this->find($partner_id);
        return $partner;
    }

}