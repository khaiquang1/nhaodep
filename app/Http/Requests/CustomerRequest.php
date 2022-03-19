<?php

namespace App\Http\Requests;

use App\Repositories\CustomerRepositoryEloquent;
use Efriandika\LaravelSettings\Facades\Settings;

class CustomerRequest extends Request
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
        $this->customerRepository = new CustomerRepositoryEloquent(app());
        $minimum_characters = Settings::get('minimum_characters');
        $max_upload_file_size = 1000000;
        $allowed_extensions = Settings::get('allowed_extensions');

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
                    'first_name' => 'required|min:'.$minimum_characters.'|max:50|alpha',
                    'last_name' => 'required|min:'.$minimum_characters.'|max:50|alpha',
                    'title' => 'required',
                    'company_id'=>'required',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required|min:3|max:32|confirmed',
                    'password_confirmation' => 'required|same:password',
                    'phone_number' => 'required|regex:/^\d{5,15}?$/',
                    'sales_team_id' => 'required',
                    'fax' => 'regex:/^\d{5,15}?$/',
                    'user_avatar_file' => 'image|max:'.$max_upload_file_size,
                ];
            }
            case 'PUT':
            case 'pluck':
            {
                if (preg_match("/\/(\d+)$/", $this->url(), $mt)) {
                    $customer = $this->customerRepository->find($mt[1]);
                    $user = $customer->user;
                }
                $user = isset($user) ? $user : '';

                return [
                    'first_name' => 'required|min:'.$minimum_characters.'|max:50|alpha',
                    'last_name' => 'required|min:'.$minimum_characters.'|max:50|alpha',
                    'email' => 'required|email|unique:users,email,'.$user->id,
//                    'password' => 'required|min:3|max:32|confirmed',
                    'website' => 'url',
                    'phone_number' => 'required|regex:/^\d{5,15}?$/',
                    'sales_team_id' => 'required',
//                    'mobile' => 'required|regex:/^\d{5,15}?$/',
                    'fax' => 'regex:/^\d{5,15}?$/',
                    'user_avatar_file' => 'image|max:'.$max_upload_file_size,
                ];
            }
            default:break;
        }

        return [

        ];
    }

	public function messages()
	{
		return [
			'phone_number.regex' => 'Phone number can be only numbers',
			'mobile.regex' => 'Mobile number can be only numbers',
			'fax.regex' => 'Fax number can be only numbers',
            'company_id.required' => 'The company name field is required.',
            'sales_team_id.required' => 'The Sales team field is required.',
		];
	}
}
