<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class SettingRequest extends Request
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
            'sales_tax' => 'required|numeric',
            'payment_term1' => 'required|numeric',
            'payment_term2' => 'required|numeric',
            'payment_term3' => 'required|numeric',
            'opportunities_reminder_days' => 'required|numeric',
            'contract_renewal_days' => 'required|numeric',
            'invoice_reminder_days' => 'required|numeric',
            'allowed_extensions' => 'required',
            'max_upload_file_size' => 'required',
            'quotation_start_number' => 'numeric',
            'sales_start_number' => 'numeric',
            'invoice_start_number' => 'numeric',
            'invoice_payment_start_number' => 'numeric',
            'quotation_prefix' => 'required',
            'sales_prefix' => 'required',
            'invoice_prefix' => 'required',
            'invoice_payment_prefix' => 'required',
            'site_logo_file' => 'image|max:2000',
            'email_driver' => 'required',
            'email_host' => 'required_if:email_driver,smtp',
            'email_port' => 'required_if:email_driver,smtp',
            'email_username' => 'required_if:email_driver,smtp',
            'email_password' => 'required_if:email_driver,smtp',
        ];
    }
}
