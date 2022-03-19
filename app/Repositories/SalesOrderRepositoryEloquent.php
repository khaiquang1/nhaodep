<?php namespace App\Repositories;

use App\Models\Saleorder;
use App\Models\User;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use Sentinel;

class SalesOrderRepositoryEloquent extends BaseRepository implements SalesOrderRepository
{
    private $userRepository;
    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return Saleorder::class;
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

    public function createSalesOrder(array $data){
        $this->generateParams();
        $user = $this->userRepository->getUser();
        $data['user_id']= $user->id;
        $team = collect($data)->except('product_list','taxes','product_id','description','quantity','price','sub_total')->toArray();
        $salesorders = $this->create($team);
        //Saleorder::insert($arrayupdate);

        $list =[];

        foreach ($data['product_id'] as $key =>$product){
            if ($product != "") {
                $temp['quantity'] = $data['quantity'][$key];
                $temp['price'] = $data['price'][$key];
                $temp['taxes'] = $data['taxes'][$key];
                $list[$data['product_id'][$key]] = $temp;
            }
        }

        $salesorders->salesOrderProducts()->attach($list);
    }
    
    public function updateSalesOrder(array $data,$saleorder_id){
        $this->generateParams();
        $team = collect($data)->except('product_list', 'product_id','taxes','prod uct_id','description','quantity','price','sub_total')->toArray();
        $salesorders = $this->update($team,$saleorder_id);
        $list =[];

        foreach ($data['product_id'] as $key =>$product){
            if ($product != "") {
                $temp['quantity'] = $data['quantity'][$key];
                $temp['price'] = $data['price'][$key];
                $temp['taxes'] = $data['taxes'][$key];
                $list[$data['product_id'][$key]] = $temp;
            }
        }

        $salesorders->salesOrderProducts()->sync($list);
    }

    public function getAllToday()
    {
        $models = $this->model;
        return $models;
    }

    public function getAllYesterday()
    {
        $models = $this->model;
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
    public function getAllForLead($lead_id)
    {
        $models = $this->model->whereHas('leads', function ($q) use ($lead_id) {
            $q->where('leads_id', $lead_id);
        });

        return $models;
    }
}