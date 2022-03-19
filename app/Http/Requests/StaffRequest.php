<?php

namespace App\Http\Requests;

use App\Repositories\UserRepositoryEloquent;
use Efriandika\LaravelSettings\Facades\Settings;

class StaffRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    private $userRepository;

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
        $this->userRepository = new UserRepositoryEloquent(app());
        $minimum_characters = Settings::get('minimum_characters');
        $max_upload_file_size = 101024;

        switch ($this->method()) {
            case 'GET':
            case 'DELETE':{
                    return [];
                }
            case 'POST':{
                    return [
                        'first_name' => 'required|min:' . $minimum_characters . '|max:50',
                        'last_name' => 'required|min:' . $minimum_characters . '|max:50',
                        'email' => 'required|email|unique:users,email',
                        'password' => 'required|min:3|max:32|confirmed',
                        'password_confirmation' => 'required|same:password',
                        'user_avatar_file' => 'image|max:' . $max_upload_file_size,
                        'phone_number' => 'required|min:8',
                    ];
                }
            case 'PUT':
            case 'pluck':{
                    if (preg_match("/\/(\d+)$/", $this->url(), $mt)) {
                        $user = $this->userRepository->find($mt[1]);
                    }

                    return [
                        'first_name' => 'required|min:' . $minimum_characters . '|max:50',
                        'last_name' => 'required|min:' . $minimum_characters . '|max:50',
                        'email' => 'required|email|unique:users,email,' . $user->id,
                        'password' => 'min:3|max:32|confirmed',
                        'password_confirmation' => 'same:password',
                        'user_avatar_file' => 'image|max:' . $max_upload_file_size,
                        'phone_number' => 'required|min:8',
                    ];
                }
            default:
                break;
        }

        return [

        ];
    }

	public function messages()
	{
		return [
			'phone_number.regex' => 'Phone number can be only numbers',
		];
	}
}
