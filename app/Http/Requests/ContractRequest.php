<?php

namespace App\Http\Requests;

use Efriandika\LaravelSettings\Facades\Settings;

class ContractRequest extends Request
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
	    $max_upload_file_size = Settings::get('max_upload_file_size');
        return [
            'start_date' => 'required|date_format:"'.config('settings.date_format').'"',
            'end_date' => 'required|date_format:"'.config('settings.date_format').'"',
            'description' => 'required|max:255',
            'resp_staff_id' => 'required',
            'company_id' => 'required',
            'real_signed_contract_file' => 'image|max:' . $max_upload_file_size,
        ];
    }
}
