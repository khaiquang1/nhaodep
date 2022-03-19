<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\UserController;
use App\Http\Requests\PayRequest;
use App\Repositories\InvoicePaymentRepository;
use App\Repositories\InvoiceRepository;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Http\Request;
use Srmklive\PayPal\Facades\PayPal;

class PaymentController extends UserController
{
    public $invoice;

    private $invoiceRepository;

    private $invoicePaymentRepository;

    public function __construct(
        InvoiceRepository $invoiceRepository,
        InvoicePaymentRepository $invoicePaymentRepository
    )
    {
        parent::__construct();
        $this->invoiceRepository = $invoiceRepository;
        $this->invoicePaymentRepository = $invoicePaymentRepository;
        $payment_method = ["paypal" => "Paypal", "stripe" => "Stripe"];
        view()->share('payment_method', $payment_method);

        view()->share('type', 'payment');
    }

    public function pay($invoice)
    {
        $invoice = $this->invoiceRepository->find($invoice);
        $title = trans('payment.pay_invoice');
        return view('customers/payment.pay', compact('title','invoice'));
    }

    public function paypal(PayRequest $request,$invoice)
    {
        $invoice = $this->invoiceRepository->find($invoice);
        $provider = PayPal::setProvider('express_checkout');
        $invoiceCount = $this->invoiceRepository->all();
        $invoiceCount = $invoiceCount->count()+1;
        $now = now();
        $data = [];
        $data['items'] = [
            [
                'name' => $invoice->invoice_number,
                'price' => $invoice->unpaid_amount,
                'qty' => 1
            ]
        ];
        $data['subscription_desc'] = "Invoice #{$invoiceCount}";
        $data['invoice_id'] = $now;
        $data['invoice_description'] = "Invoice #{$invoiceCount}";
        $data['cancel_url'] = url('customers/payment/'.$invoice->id.'/paypal_cancel');
        $data['return_url'] = url('customers/payment/'.$invoice->id.'/paypal_success');

        $total = 0;
        foreach($data['items'] as $item) {
            $total += $item['price']*$item['qty'];
        }

        $data['total'] = $total;
        $currency = Settings::get('currency');
        $response = $provider->setCurrency($currency)->setExpressCheckout($data);
        // if there is no link redirect back with error message
        if (!$response['paypal_link']) {
            return redirect('/')->with(['code' => 'danger', 'message' => 'Something went wrong with PayPal']);
            // For the actual error message dump out $response and see what's in there
        }

        // redirect to paypal
        // after payment is done paypal
        // will redirect us back to $this->expressCheckoutSuccess
        return redirect($response['paypal_link']);
    }

    public function paypalSuccess(Request $request,$invoice)
    {
        $invoice = $this->invoiceRepository->find($invoice);
        $provider = PayPal::setProvider('express_checkout');
        $token = $request->token;
        $PayerID = $request->PayerID;
        $invoiceCount = $this->invoiceRepository->all();
        $invoiceCount = $invoiceCount->count()+1;
        $now = now();
        $data = [];
        $data['items'] = [
            [
                'name' => $invoice->invoice_number,
                'price' => $invoice->unpaid_amount,
                'qty' => 1
            ]
        ];
        $data['subscription_desc'] = "Invoice #{$invoiceCount}";
        $data['invoice_id'] = $now;
        $data['invoice_description'] = "Invoice #{$invoiceCount}";

        $total = 0;
        foreach($data['items'] as $item) {
            $total += $item['price']*$item['qty'];
        }

        $data['total'] = $total;
        $currency = Settings::get('currency');
        $response = $provider->setCurrency($currency)->doExpressCheckoutPayment($data, $token, $PayerID);
        if (!isset($response)){
            return $response;
        }

        $total_fields = $this->invoicePaymentRepository->getAll()->orderBy('id', 'desc')->first();
        $start_number = Settings::get('invoice_payment_start_number');
        $quotation_no = Settings::get('invoice_payment_prefix') . (is_int($start_number)?$start_number:0 + (isset($total_fields) ? $total_fields->id : 0) + 1);

        $invoiceRepository = $this->invoicePaymentRepository->create([
            'invoice_id' => $invoice->id,
            'payment_date' => $now,
            'payment_method' => "Pay pal",
            'payment_received' => $invoice->unpaid_amount,
            'payment_number' => $quotation_no,
            'paykey' => $token,
            'user_id' => $this->user->id,
            'customer_id' => $this->user->id
        ]);

        $unpaid_amount_new = $invoice->unpaid_amount - $invoiceRepository->payment_received;

        if ($unpaid_amount_new <= '0') {
            $invoice_data = [
                'unpaid_amount' => $unpaid_amount_new,
                'status' => 'Paid Invoice',
            ];
        } else {
            $invoice_data = [
                'unpaid_amount' => $unpaid_amount_new,
            ];
        }

        $invoice->update($invoice_data);

        return redirect('customers/payment/success');
    }

    public function stripe(Request $request,$invoice)
    {
        $invoice = $this->invoiceRepository->find($invoice);
        $total_fields = $this->invoicePaymentRepository->getAll()->orderBy('id', 'desc')->first();
        $start_number =Settings::get('invoice_payment_start_number');
        $quotation_no = Settings::get('invoice_payment_prefix') . (is_int($start_number)?$start_number:0 + (isset($total_fields) ? $total_fields->id : 0) + 1);
        $invoiceRepository = $this->invoicePaymentRepository->create([
            'invoice_id' => $invoice->id,
            'payment_date' => date(config('settings.date_time_format')),
            'payment_method' => "Stripe",
            'payment_received' => $invoice->unpaid_amount,
            'payment_number' => $quotation_no,
            'paykey' => $request->stripeToken,
            'user_id' => $this->user->id,
            'customer_id' => $this->user->id
        ]);
        $unpaid_amount_new = $invoice->unpaid_amount - $invoiceRepository->payment_received;
        if ($unpaid_amount_new <= '0') {
            $invoice_data = [
                'unpaid_amount' => $unpaid_amount_new,
                'status' => 'Paid Invoice',
            ];
        } else {
            $invoice_data = [
                'unpaid_amount' => $unpaid_amount_new,
            ];
        }
        $stripe_secret_key = Settings::get('stripe_secret');
        \Stripe\Stripe::setApiKey($stripe_secret_key);

        $token = $request->stripeToken;
        $charge = \Stripe\Charge::create(array(
            "amount" => $invoice->unpaid_amount*100,
            "currency" => "usd",
            "description" => "Example charge",
            "source" => $token,
        ));

        $invoice->update($invoice_data);

        return redirect('customers/payment/success');
    }

    public function success()
    {
        $title = trans('payment.payment_finish');
        return view('customers.payment.success', compact('title'));
    }

    public function cancel()
    {
        return redirect('customers');
    }


}
