<?php

namespace App\Http\Requests;

class MeetingRequest extends Request
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
            'meeting_subject' => 'required',
            'starting_date' => 'required|date_format:"'.config('settings.date_format').' H:i'.'"',
            'ending_date' => 'required|date_format:"'.config('settings.date_format').' H:i'.'"',
            'responsible_id' => "required",
            'company_attendees' => "required",
            'location' => "required"
        ];
    }

    /**
     * Get the validator instance for the request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function getValidatorInstance()
    {
        $this->merge(['attendees' => implode(',', $this->get('attendees', []))]);
        return parent::getValidatorInstance();
    }
}
