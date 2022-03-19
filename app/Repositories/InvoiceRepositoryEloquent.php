<?php namespace App\Repositories;


use App\Models\Invoice;
use App\Models\User;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use Cartalyst\Sentinel\Users\EloquentUser;
use Sentinel;

class InvoiceRepositoryEloquent extends BaseRepository implements InvoiceRepository
{
    private $userRepository;
    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return Invoice::class;
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

    public function createInvoice(array $data){
        $this->generateParams();
        $user = $this->userRepository->getUser();
        $data['user_id']= $user->id;

        $team = collect($data)->except('product_list','taxes','product_id','description','quantity','price','sub_total')->toArray();
        $invoices = $this->create($team);
        $list =[];

        foreach ($data['product_id'] as $key =>$product){
            if ($product != "") {
                $temp['quantity'] = $data['quantity'][$key];
                $temp['price'] = $data['price'][$key];
                $list[$data['product_id'][$key]] = $temp;
            }
        }

        $invoices->invoiceProducts()->attach($list);
    }

    public function updateInvoice(array $data,$invoice_id){

        $team = collect($data)->except('product_list','taxes','product_id','description','quantity','price','sub_total')->toArray();
        $invoices = $this->update($team,$invoice_id);
        $list =[];
        foreach ($data['product_id'] as $key =>$product){
            if ($product != "") {
                $temp['quantity'] = $data['quantity'][$key];
                $temp['price'] = $data['price'][$key];
                $list[$data['product_id'][$key]] = $temp;
            }
        }
        $invoices->invoiceProducts()->sync($list);
    }

    public function getAllOpen()
    {
        $models = $this->model
        ->where('invoices.status', 'Open Invoice');
        return $models;
    }

    public function getAllOverdue()
    {
        $models = $this->model
        ->where('invoices.status', 'Overdue Invoice');

        return $models;
    }

    public function getAllPaid()
    {
        $models = $this->model
            ->where('invoices.status', 'Paid Invoice');
        return $models;
    }

    public function getAllForCustomer($customer_id)
    {
        $models = $this->model->whereHas('user', function ($q) use ($customer_id) {
            $q->where('customer_id', $customer_id);
        });

        return $models;
    }

    public function getAllOpenForCustomer($customer_id)
    {
        $models = $this->model->whereHas('user', function ($q) use ($customer_id) {
            $q->where('invoices.status', 'Open Invoice')
                ->where('customer_id', $customer_id);
        });

        return $models;
    }

    public function getAllOverdueForCustomer($customer_id)
    {
        $models = $this->model->whereHas('user', function ($q) use ($customer_id) {
            $q->where('invoices.status', 'Overdue Invoice')
                ->where('customer_id', $customer_id);
        });

        return $models;
    }

    public function getAllPaidForCustomer($customer_id)
    {
        $models = $this->model->whereHas('user', function ($q) use ($customer_id) {
            $q->where('invoices.status', 'Paid Invoice')
                ->where('customer_id', $customer_id);
        });

        return $models;
    }

    public function getAllOpenMonth()
    {
        $models = $this->model
            ->where('invoices.status', 'Open Invoice')
            ->where('invoice_date', 'LIKE', date('Y-m') . '%');
        return $models;
    }

    public function getAllOverdueMonth()
    {
        $models = $this->model
            ->where('invoices.status', 'Overdue Invoice')
            ->where('invoice_date', 'LIKE', date('Y-m') . '%');
        return $models;
    }

    public function getAllPaidMonth()
    {
        $models = $this->model
            ->where('invoices.status', 'Paid Invoice')
            ->where('invoice_date', 'LIKE', date('Y-m') . '%');
        return $models;
    }
}