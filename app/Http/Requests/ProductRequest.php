<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class ProductRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $max_upload_file_size = 1000000;
        return [
            'product_name' => "required",
            'sale_price' => "required|regex:/^\d{1,12}(\.\d{1,2})?$/",
            'quantity_on_hand' => "required|integer|min:0",
            'quantity_available' => "required|integer|min:0",
            'category_id' => "required",
            'product_type' => "required",
            'status' => "required",
            'product_image_file' => 'image|max:'.$max_upload_file_size,
        ];
    }
    public function messages()
    {
        return [
          'category_id.required'=>'The category field is required.',
        ];
    }
}
