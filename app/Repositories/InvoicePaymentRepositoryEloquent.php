<?php namespace App\Repositories;


use App\Models\InvoiceReceivePayment;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class InvoicePaymentRepositoryEloquent extends BaseRepository implements InvoicePaymentRepository
{
    private $userRepository;

    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return InvoiceReceivePayment::class;
    }

    /**
     * Boot up the repository, pushing criteria.
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function generateParams()
    {
        $this->userRepository = new UserRepositoryEloquent(app());
    }

    public function getAll()
    {
        $models = $this->model;
        return $models;
    }

    public function createPayment(array $data)
    {
        $this->generateParams();
        $user = $this->userRepository->getUser();

        $data['user_id'] = $user->id;
        $team = collect($data)->toArray();
        $invoice_payment = $this->create($team);

        return $invoice_payment;
    }

    public function getAllForCustomer($customer_id)
    {
        $models = $this->model->whereHas('customer', function ($q) use ($customer_id) {
            $q->where('customer_id', $customer_id);
        });

        return $models;
    }
}