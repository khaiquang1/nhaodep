<?php

namespace App\Http\Requests;

class CallRequest extends Request
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
        return [
            'date' => 'required|date_format:"Y-m-d"',
            'call_summary' => 'required',
            'resp_staff_id' => 'required',
            'duration' => "required|integer|min:1",
//            'company_id' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'resp_staff_id.required' => 'The team lead field is required.',
            'company_id.required' => 'The Company field is required.'
        ];
    }
}
