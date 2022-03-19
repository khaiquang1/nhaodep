<?php

namespace App\Http\Requests;

use Efriandika\LaravelSettings\Facades\Settings;

class ProfileChangeRequest extends Request {

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
		return [
			'last_name' => 'required|min:3|max:50|alpha',
			'first_name' => 'required|min:3|max:50|alpha',
			'user_avatar' => 'mimes:'.Settings::get('allowed_extensions').'|image|max:'.Settings::get('max_upload_file_size'),
			'password' => 'min:6|confirmed',
			'phone_number' => 'regex:/^\d{5,15}?$/',
        ];
	}

	public function messages()
	{
		return [
			'phone_number.regex' => 'Phone number can be only numbers',
		];
	}

}
