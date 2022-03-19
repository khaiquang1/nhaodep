<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class MailboxRequest extends Request
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
            'assign_customer_id' => 'required_if:to_email_id,null',
            'to_email_id' => 'required_if:assign_customer_id,null',
            'subject' => 'required',
            'message' => 'required',
        ];
    }
}
