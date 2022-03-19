<?php

namespace App\Http\Requests;

use App\Repositories\CategoryRepositoryEloquent;

class CategoryRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    private  $categoryRepository;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $this->categoryRepository = new CategoryRepositoryEloquent(app());
        switch($this->method())
        {
            case 'GET':
            case 'DELETE':
            {
                return [];
            }
            case 'POST':
            {
                return [
                    'name'      => 'required|min:3|unique:categories,name',
                ];
            }
            case 'PUT':
            case 'PATCH':
            {
                if (preg_match("/\/(\d+)$/", $this->url(), $mt)) {
                    $category = $this->categoryRepository->find($mt[1]);
                }
                return [
                    'name'      => 'required|min:3|unique:categories,name,'.$category->id,
                ];
            }
            default:break;
        }
    }
    public function messages()
    {
        return [
            'name.required'    => 'The Category name field is required',
        ];
    }
}
