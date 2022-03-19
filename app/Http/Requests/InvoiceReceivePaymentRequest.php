<?php

namespace App\Http\Requests;

class InvoiceReceivePaymentRequest extends Request
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
            'invoice_id' => "required",
            'payment_date' => 'required|date_format:"Y-m-d H:i'.'"',
            'payment_method' => "required",
            'payment_received' => "required|regex:/^\d{1,20}(\.\d{1,2})?$/",
        ];
    }
}
