<?php namespace App\Repositories;


use App\Models\Quotation;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class QuotationRepositoryEloquent extends BaseRepository implements QuotationRepository
{
    private $userRepository;
    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return Quotation::class;
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

    public function createQuotation(array $data){
        $this->generateParams();
        $user = $this->userRepository->getUser();
        $data['user_id']= $user->id;

        $team = collect($data)->except('product_list','taxes','product_id','description','quantity','price','sub_total')->toArray();
        $quotation = $this->create($team);
        $list =[];

        foreach ($data['product_id'] as $key =>$product){
            if ($product != "") {
                $temp['quantity'] = $data['quantity'][$key];
                $temp['price'] = $data['price'][$key];
                $list[$data['product_id'][$key]] = $temp;
            }
        }

        $quotation->quotationProducts()->attach($list);
    }

    public function updateQuotation(array $data,$quotation_id)
    {
        $this->generateParams();
        $team = collect($data)->except('product_list','taxes','product_id','description','quantity','price','sub_total')->toArray();
        $quotation = $this->update($team,$quotation_id);
        $list =[];

        foreach ($data['product_id'] as $key =>$product){
            if ($product != "") {
                $temp['quantity'] = $data['quantity'][$key];
                $temp['price'] = $data['price'][$key];
                $list[$data['product_id'][$key]] = $temp;
            }
        }

        $quotation->quotationProducts()->sync($list);
    }



    public function getAllToday()
    {
        $models = $this->model
            ->where('date', strtotime(date('Y-m-d')));
        return $models;
    }

    public function getAllYesterday()
    {
        $models = $this->model
            ->where('date', strtotime(date('Y-m-d', strtotime("-1 days"))));
        return $models;
    }

    public function getAllWeek()
    {
        $models = $this->model
            ->whereBetween('date',
                array(strtotime((date('D') != 'Mon') ? date('Y-m-d', strtotime('last Monday')) : date('Y-m-d')),
                    strtotime((date('D') != 'Sat') ? date('Y-m-d', strtotime('next Saturday')) : date('Y-m-d'))));
        return $models;
    }

    public function getAllMonth()
    {
        $models = $this->model
            ->whereBetween('date',
                array(date('d-m-Y', strtotime('first day of this month')),
                    date('d-m-Y', strtotime('last day of this month'))));
        return $models;
    }


    public function getAllForCustomer($customer_id)
    {
        $models = $this->model->whereHas('customer', function ($q) use ($customer_id) {
            $q->where('customer_id', $customer_id);
        });

        return $models;
    }
}