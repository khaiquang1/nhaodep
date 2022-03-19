<?php namespace App\Repositories;

use App\Models\Contract;
use App\Models\User;
use Sentinel;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ContractRepositoryEloquent implements ContractRepository
{
    /**
     * @var Contract
     */
    private $model;
    private $user;

    /**
     * ContractRepositoryEloquent constructor.
     * @param Contract $model
     */
    public function __construct(Contract $model)
    {
        $this->model = $model;

    }

    public function getAll()
    {
//	    $user = User::find(Sentinel::getUser()->id);
        $models = $this->model;
//        ->whereHas('user', function ($q)  use ($user){
//            $q->where(function ($query) use ($user) {
//                $query
//                    ->orWhere('id', $user->parent->id)
//                    ->orWhere('users.user_id',$user->parent->id);
//            });
//        });

        return $models;
    }

    public function create(array $data)
    {
	    $user = User::find(Sentinel::getUser()->id);
       $user->contracts()->create($data);
    }

    public function uploadRealSignedContract(UploadedFile $file)
    {
        $destinationPath = public_path() . '/uploads/contract/';
        $extension = $file->getClientOriginalExtension() ?: 'png';
        $fileName = str_random(10) . '.' . $extension;
        return $file->move($destinationPath, $fileName);
    }

    public function getAllForCustomer($companies)
    {
        $company_ids = array();
        foreach ($companies as $company)
        {
            $company_ids[] = $company->id;
        }

        $models = $this->model->whereIn('company_id',$company_ids);

        return $models;
    }

}