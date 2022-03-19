<?php namespace App\Repositories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use Sentinel;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProductRepositoryEloquent extends BaseRepository implements ProductRepository
{
    private $userRepository;
    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return Product::class;
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
        $this->generateParams();
        $products = $this->model;
        return $products;
        /*
        $user_id = $this->userRepository->getUser()->id;
        $user = $this->userRepository->find($user_id);
        return $user->products; */
    }

    public function create(array $data)
    {
        $this->generateParams();
        $user_id = $this->userRepository->getUser()->id;
        $user = $this->userRepository->find($user_id);
        $product = $user->products()->create($data);
        return $product;
    }

    public function uploadProductImage(UploadedFile $file)
    {
        $destinationPath = public_path() . '/uploads/products/';
        $extension = $file->getClientOriginalExtension();
        $filename = $file->getClientOriginalName();
        $picture = Str::slug(substr($filename, 0, strrpos($filename, "."))) . '_' . time() . '.' . $extension;
        return $file->move($destinationPath, $picture);
    }
}