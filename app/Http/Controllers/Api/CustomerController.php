<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Customer;
use App\Repositories\ContractRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\LeadRepository;
use App\Repositories\OpportunityRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Qtemplate;
use App\Models\Quotation;
use App\Models\Saleorder;
use Dingo\Api\Routing\Helpers;
use Validator;
use JWTAuth;
use DB;

/**
 * Customer endpoints, can be accessed only with role "customer"
 *
 * @Resource("Customer", uri="/customer")
 */
class CustomerController extends Controller
{
    use Helpers;

    private $user;
	/**
	 * @var InvoiceRepository
	 */
	private $invoiceRepository;
	/**
	 * @var ContractRepository
	 */
	private $contractRepository;
	/**
	 * @var OpportunityRepository
	 */
	private $opportunityRepository;
	/**
	 * @var LeadRepository
	 */
	private $leadRepository;
	/**
	 * @var UserRepository
	 */
	private $userRepository;

	/**
	 * CustomerController constructor.
	 *
	 * @param InvoiceRepository $invoiceRepository
	 * @param ContractRepository $contractRepository
	 * @param OpportunityRepository $opportunityRepository
	 * @param LeadRepository $leadRepository
	 * @param UserRepository $userRepository
	 */
	public function __construct(InvoiceRepository $invoiceRepository,ContractRepository $contractRepository,
		OpportunityRepository $opportunityRepository, LeadRepository $leadRepository, UserRepository $userRepository)
	{
		$this->invoiceRepository = $invoiceRepository;
		$this->contractRepository = $contractRepository;
		$this->opportunityRepository = $opportunityRepository;
		$this->leadRepository = $leadRepository;
		$this->userRepository = $userRepository;
	}

    /**
     * Get all contract
     *
     * @Get("/contract")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo"}),
     *      @Response(200, body={
    "company": {
    {
    "id": 1,
    "start_date": "2015-11-12",
    "end_date": "2015-11-15",
    "description": "Description",
    "company": "Company name",
    "user": "User name",
    }
    }
    }),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function contract()
    {
    	$this->user = JWTAuth::parseToken()->authenticate();
	    $companies = Company::where('main_contact_person', $this->user->id)->pluck('id');
        $contracts = Contract::where(function ($q) use ($companies) {
	        $q->whereIn('company_id', $companies);
        })
            ->with('company', 'user','responsible')
            ->get()
            ->map(function ($contract) {
                return [
                    'id' => $contract->id,
                    'start_date' => $contract->start_date,
                    'end_date' => $contract->end_date,
                    'description' => $contract->description,
                    'company' => isset($contract->company)?$contract->company->name:"",
                    'user' => isset($contract->responsible)?$contract->responsible->full_name:"",
                ];
            })
            ;

        return response()->json(['contracts' => $contracts], 200);
    }

    /**
     * Get all invoices
     *
     * @Get("/invoices")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo"}),
     *      @Response(200, body={
    "invoices": {
    {
    "id": 1,
    "invoice_number": "I0056",
    "invoice_date": "2015-11-11",
    "customer": "Customer Name",
    "due_date": "2015-11-12",
    "grand_total": "15.2",
    "unpaid_amount": "15.2",
    "status": "Status",
    }
    }
    }),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function invoices()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $invoices = Invoice::whereHas('user', function ($q) {
            $q->where('customer_id', $this->user->id);
        })->with('customer')
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'invoice_date' => $invoice->invoice_date,
                    'customer' => isset($invoice->customer) ? $invoice->customer->full_name : '',
                    'due_date' => $invoice->due_date,
                    'grand_total' => $invoice->grand_total,
                    'unpaid_amount' => $invoice->unpaid_amount,
                    'status' => $invoice->status,
                ];
            });

        return response()->json(['invoices' => $invoices], 200);
    }
	/**
	 * Get invoice item
	 *
	 * @Get("/invoice")
	 * @Versions({"v1"})
	 * @Transaction({
	 *       @Request({"token": "foo", "invoice_id":"1"}),
	 *       @Response(200, body={"invoice": {
	"id" : 1,
	"order_id" : 0,
	"customer_id" : 3,
	"sales_person_id" : "2",
	"sales_team_id" : 1,
	"invoice_number" : "I0001",
	"invoice_date" : "08.12.2015. 00:00",
	"due_date" : "24.12.2015. 00:00",
	"payment_term" : "10",
	"status" : "Open Invoice",
	"total" : 1221.0,
	"tax_amount" : 195.36,
	"grand_total" : 1416.36,
	"discount" : 10,
	"final_price" : 1216.36,
	"unpaid_amount" : 1173.06,
	"user_id" : 1,
	"created_at" : "2015-12-23 18:05:35",
	"updated_at" : "2015-12-28 19:21:48",
	"deleted_at" : null,
	},"products": {
	"product" : "product",
	"description" : "description",
	"quantity" : 3,
	"unit_price" : 1.95,
	"taxes" : 1.55,
	"subtotal" : 195.36,
	}}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *    })
	 * })
	 */
	public function invoice(Request $request)
	{
		$this->user = JWTAuth::parseToken()->authenticate();
		$data = array(
			'invoice_id' => $request->input('invoice_id'),
		);
		$rules = array(
			'invoice_id' => 'required',
		);
		$validator = Validator::make($data, $rules);
		if ($validator->passes()) {
			$invoice = Invoice::where('id', $request->get('invoice_id'))
			                  ->with('salesPerson', 'customer','salesTeam')
			                  ->get()
			                  ->map(function ($invoice) {
				                  return [
					                  'id' => $invoice->id,
					                  'order_id' => $invoice->order_id,
					                  'customer' => isset($invoice->customer)?$invoice->customer->name:"",
					                  'sales_person' => isset($invoice->salesPerson)?$invoice->salesPerson->full_name:"",
					                  'salesteam' => isset($invoice->salesTeam)?$invoice->salesTeam->salesteam:"",
					                  'invoice_number' => $invoice->invoice_number,
					                  'invoice_date' => $invoice->invoice_date,
					                  'due_date' => $invoice->due_date,
					                  'payment_term' => $invoice->payment_term,
					                  'status' => $invoice->status,
					                  'total' => $invoice->total,
					                  'tax_amount' => $invoice->tax_amount,
					                  'grand_total' => $invoice->grand_total,
					                  'discount' => $invoice->discount,
					                  'final_price' => $invoice->final_price,
					                  'unpaid_amount' => $invoice->unpaid_amount
				                  ];
			                  });
			$products = array();
			$invoiceNew = Invoice::find($request->get('invoice_id'));
			if ($invoiceNew->products->count() > 0) {
				foreach ($invoiceNew->products as $index => $variants) {
					$products[] = ['product' => $variants->product_name,
					               'description' => $variants->description,
					               'quantity' => $variants->quantity,
					               'unit_price' => $variants->price,
					               'taxes' => number_format($variants->quantity * $variants->price * floatval(Settings::get('sales_tax')) / 100, 2,
						               '.', ''),
					               'subtotal' => $variants->sub_total];
				}
			}
			return response()->json(['invoice' => $invoice, 'products' => $products], 200);
		} else {
			return response()->json(['error' => 'not_valid_data'], 500);
		}
	}


    /**
     * Get all quotations
     *
     * @Get("/quotation")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo"}),
     *      @Response(200, body={
    "quotations": {
    {
    "id": 1,
    "quotations_number": "Q002",
    "date": "2015-11-11",
    "customer": "customer name",
    "person": "person name",
    "grand_total": "12",
    "status": "Draft quotation",
    }
    }
    }),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function quotations()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $quotations = Quotation::whereHas('customer', function ($q) {
            $q->where('customer_id', $this->user->id);
        })
            ->with('user', 'customer')
            ->get()
            ->map(function ($quotation) {
                return [
                    'id' => $quotation->id,
                    'quotations_number' => $quotation->quotations_number,
                    'date' => $quotation->date,
                    'customer' => isset($quotation->customer) ?$quotation->customer->full_name : '',
                    'person' => isset($quotation->user) ?$quotation->user->full_name : '',
                    'grand_total' => $quotation->grand_total,
                    'status' => $quotation->status
                ];
            });

        return response()->json(['quotations' => $quotations], 200);
    }
	/**
	 * Get quotation item
	 *
	 * @Get("/quotation")
	 * @Versions({"v1"})
	 * @Transaction({
	 *       @Request({"token": "foo", "quotation_id":"1"}),
	 *       @Response(200, body={"quotation": {
	"id" : 1,
	"quotations_number" : "Q0001",
	"customer_id" : 3,
	"qtemplate_id" : 0,
	"date" : "08.12.2015. 00:00",
	"exp_date" : "30.12.2015.",
	"payment_term" : "10",
	"sales_person_id" : 2,
	"sales_team_id" : 1,
	"terms_and_conditions" : "dff dfg dfg",
	"status" : "Draft Quotation",
	"total" : 333.0,
	"tax_amount" : 53.28,
	"grand_total" : 386.28,
	"discount" : 11.28,
	"final_price" : 289.28,
	"user_id" : 1,
	"created_at" : "2015-12-23 18:39:12",
	"updated_at" : "2015-12-23 18:39:12",
	"deleted_at" : null
	},"products": {
	"product" : "product",
	"description" : "description",
	"quantity" : 3,
	"unit_price" : 1.95,
	"taxes" : 1.55,
	"subtotal" : 195.36
	}}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *    })
	 * })
	 */
	public function quotation(Request $request)
	{
		$this->user = JWTAuth::parseToken()->authenticate();
		$data = array(
			'quotation_id' => $request->input('quotation_id'),
		);
		$rules = array(
			'quotation_id' => 'required',
		);
		$validator = Validator::make($data, $rules);
		if ($validator->passes()) {
			$quotation = Quotation::where('id', $request->quotation_id)
			                      ->with('customer','salesPerson','salesTeam')
			                      ->get()
			                      ->map(function ($quotation) {
				                      return [
					                      'id' => $quotation->id,
					                      'quotations_number' => $quotation->quotations_number,
					                      'company' => isset($quotation->customer)?$quotation->customer->full_name:"",
					                      'qtemplate' => Qtemplate::find($quotation->qtemplate_id)->quotation_template,
					                      'date' => $quotation->date,
					                      'exp_date' => $quotation->exp_date,
					                      'payment_term' => $quotation->payment_term,
					                      'sales_person' => isset($quotation->salesPerson)?$quotation->salesPerson->full_name:"",
					                      'salesteam' => isset($quotation->salesTeam)?$quotation->salesTeam->salesteam:"",
					                      'terms_and_conditions' => $quotation->terms_and_conditions,
					                      'status' => $quotation->status,
					                      'total' => $quotation->total,
					                      'tax_amount' => $quotation->tax_amount,
					                      'grand_total' => $quotation->grand_total,
					                      'discount' => $quotation->discount,
					                      'final_price' => $quotation->final_price
				                      ];
			                      });
			$products = array();
			$quotationNew = Quotation::find($request->quotation_id);
			if ($quotationNew->products->count() > 0) {
				foreach ($quotationNew->products as $index => $variants) {
					$products[] = ['product' => $variants->product_name,
					               'description' => $variants->description,
					               'quantity' => $variants->quantity,
					               'unit_price' => $variants->price,
					               'taxes' => number_format($variants->quantity * $variants->price * floatval(Settings::get('sales_tax')) / 100, 2,
						               '.', ''),
					               'subtotal' => $variants->sub_total];
				}
			}
			return response()->json(['quotation' => $quotation, 'products' => $products], 200);
		} else {
			return response()->json(['error' => 'not_valid_data'], 500);
		}
	}

    /**
     * Get all sales orders
     *
     * @Get("/sales_orders")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo"}),
     *      @Response(200, body={
    "salesorder": {
    {
    "id": 1,
    "sale_number": "S002",
    "date": "2015-11-11",
    "customer": "customer name",
    "person": "sales person name",
    "grand_total": "12.53",
    "status": "Draft sales order",
    }
    }
    }),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function salesOrders()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $sales_orders = Saleorder::whereHas('customer', function ($q) {
            $q->where('customer_id', $this->user->id);
        })->with('user', 'customer')
            ->get()
            ->map(function ($sales_order) {
                return [
                    'id' => $sales_order->id,
                    'sale_number' => $sales_order->sale_number,
                    'date' => $sales_order->date,
                    'customer' => isset($sales_order->customer) ?$sales_order->customer->full_name : '',
                    'person' => isset($sales_order->user) ?$sales_order->user->full_name : '',
                    'grand_total' => $sales_order->grand_total,
                    'status' => $sales_order->status
                ];
            });

        return response()->json(['salesorder' => $sales_orders], 200);
    }


	/**
	 * Get salesorder item
	 *
	 * @Get("/salesorder")
	 * @Versions({"v1"})
	 * @Transaction({
	 *       @Request({"token": "foo", "salesorder_id":"1"}),
	 *       @Response(200, body={"salesorder": {
	"id" : 1,
	"sale_number" : "S0001",
	"customer_id" : 3,
	"qtemplate_id" : 0,
	"date" : "15.12.2015.",
	"exp_date" : "15.12.2015.",
	"payment_term" : "15",
	"sales_person_id" : 2,
	"sales_team_id" : 1,
	"terms_and_conditions" : "drtret",
	"status" : "Draft sales order",
	"total" : 1221.0,
	"tax_amount" : 195.36,
	"grand_total" : 1416.36,
	"discount" : 11.28,
	"final_price" : 289.28,
	"user_id" : 1,
	"created_at" : "2015-12-23 17:12:39",
	"updated_at" : "2015-12-23 17:12:39",
	"deleted_at" : null
	},"products": {
	"product" : "product",
	"description" : "description",
	"quantity" : 3,
	"unit_price" : 1.95,
	"taxes" : 1.55,
	"subtotal" : 195.36
	}}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *    })
	 * })
	 */
	public function salesOrder(Request $request)
	{
		$this->user = JWTAuth::parseToken()->authenticate();
		$data = array(
			'salesorder_id' => $request->input('salesorder_id'),
		);
		$rules = array(
			'salesorder_id' => 'required',
		);
		$validator = Validator::make($data, $rules);
		if ($validator->passes()) {
			$salesorder = Saleorder::where('id', $request->salesorder_id)
			                       ->with('customer', 'salesTeam','salesPerson')
			                       ->get()
			                       ->map(function ($salesorder) {
				                       return [
					                       'id' => $salesorder->id,
					                       'sale_number' => $salesorder->sale_number,
					                       'customer' => isset($salesorder->customer)?$salesorder->customer->full_name:"",
					                       'date' => $salesorder->date,
					                       'exp_date' => $salesorder->exp_date,
					                       'payment_term' => $salesorder->payment_term,
					                       'sales_person' => isset($salesorder->salesPerson)?$salesorder->salesPerson->full_name:"",
					                       'salesteam' => isset($salesorder->salesTeam)?$salesorder->salesTeam->salesteam:"",
					                       'terms_and_conditions' => $salesorder->terms_and_conditions,
					                       'status' => $salesorder->status,
					                       'total' => $salesorder->total,
					                       'tax_amount' => $salesorder->tax_amount,
					                       'grand_total' => $salesorder->grand_total,
					                       'discount' => $salesorder->discount,
					                       'final_price' => $salesorder->final_price,
				                       ];
			                       });

			$products = array();
			$salesorderNew = Saleorder::find($request->salesorder_id);
			if ($salesorderNew->products->count() > 0) {
				foreach ($salesorderNew->products as $index => $variants) {
					$products[] = ['product' => $variants->product_name,
					               'description' => $variants->description,
					               'quantity' => $variants->quantity,
					               'unit_price' => $variants->price,
					               'taxes' => number_format($variants->quantity * $variants->price * floatval(Settings::get('sales_tax')) / 100, 2,
						               '.', ''),
					               'subtotal' => $variants->sub_total];
				}
			}
			return response()->json(['salesorder' => $salesorder, 'products' => $products], 200);
		} else {
			return response()->json(['error' => 'not_valid_data'], 500);
		}
	}
	/**
	 * Get dashboard data
	 *
	 * @Get("/dashboard")
	 * @Versions({"v1"})
	 * @Transaction({
	 *      @Request({"token": "foo"}),
	 *      @Response(200, body={
	{
	"invoices_by_month": {
	{
	"month": "Feb",
	"year": "2016",
	"invoices": null,
	"contracts": 0,
	"opportunity": 0,
	"leads": 0
	},
	{
	"month": "Mar",
	"year": "2016",
	"invoices": null,
	"contracts": 0,
	"opportunity": 0,
	"leads": 0
	},
	{
	"month": "Apr",
	"year": "2016",
	"invoices": null,
	"contracts": 0,
	"opportunity": 0,
	"leads": 0
	},
	{
	"month": "May",
	"year": "2016",
	"invoices": null,
	"contracts": 0,
	"opportunity": 0,
	"leads": 0
	},
	{
	"month": "Jun",
	"year": "2016",
	"invoices": null,
	"contracts": 0,
	"opportunity": 0,
	"leads": 0
	},
	{
	"month": "Jul",
	"year": "2016",
	"invoices": null,
	"contracts": 0,
	"opportunity": 0,
	"leads": 0
	},
	{
	"month": "Aug",
	"year": "2016",
	"invoices": null,
	"contracts": 0,
	"opportunity": 0,
	"leads": 0
	},
	{
	"month": "Sep",
	"year": "2016",
	"invoices": null,
	"contracts": 0,
	"opportunity": 0,
	"leads": 0
	},
	{
	"month": "Oct",
	"year": "2016",
	"invoices": null,
	"contracts": 0,
	"opportunity": 0,
	"leads": 0
	},
	{
	"month": "Nov",
	"year": "2016",
	"invoices": null,
	"contracts": 0,
	"opportunity": 0,
	"leads": 0
	},
	{
	"month": "Dec",
	"year": "2016",
	"invoices": null,
	"contracts": 0,
	"opportunity": 0,
	"leads": 0
	},
	{
	"month": "Jan",
	"year": "2017",
	"invoices": null,
	"contracts": 0,
	"opportunity": 0,
	"leads": 0
	}
	}
	}
	}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *       })
	 * })
	 */
	public function dashboard(Request $request) {
		$this->user = JWTAuth::parseToken()->authenticate();

		if ( $this->user->id ) {

			$companies = Company::where( 'main_contact_person', $this->user->id )->get();
			$customer = Customer::where('user_id', $this->user->id)->first();

			$data = array ();
			for ( $i = 11; $i >= 0; $i -- ) {
				$data[] =
					array (
						'month'       => Carbon::now()->subMonth( $i )->format( 'M' ),
						'year'        => Carbon::now()->subMonth( $i )->format( 'Y' ),
						'invoices'    => $this->invoiceRepository->getAllForCustomer( $this->user->id )->where( 'created_at', 'LIKE',
							Carbon::now()->subMonth( $i )->format( 'Y-m' ) . '%' )->sum( 'grand_total' ),
						'contracts'   => $this->contractRepository->getAllForCustomer( $companies )->where( 'created_at', 'LIKE',
							Carbon::now()->subMonth( $i )->format( 'Y-m' ) . '%' )->count(),
						'opportunity' => $this->opportunityRepository->getAllForCustomer( $customer->company_id )->where( 'created_at', 'LIKE',
							Carbon::now()->subMonth( $i )->format( 'Y-m' ) . '%' )->count(),
						'leads'       => $this->leadRepository->getAllForCustomer( $customer->company_id )->where( 'created_at', 'LIKE',
							Carbon::now()->subMonth( $i )->format( 'Y-m' ) . '%' )->count()
					);
			}

			return response()->json( [ 'invoices_by_month' => $data ], 200 );
		} else {
			return response()->json( [ 'error' => 'not_valid_data' ], 500 );
		}
	}

	/**
	 * Get all staff
	 *
	 * @Get("/contacts")
	 * @Versions({"v1"})
	 * @Transaction({
	 *      @Request({"token": "foo"}),
	 *      @Response(200, body={
	"staffs": {
	{
	"id": 1,
	"full_name": "product name"
	}
	}
	}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *       })
	 * })
	 */
	public function contacts()
	{
		$this->user = JWTAuth::parseToken()->authenticate();
		$staffs = $this->userRepository->getAll()
		                               ->get()
		                               ->filter(function ($user) {
			                               return ($user->inRole('staff') && $user->id!=$this->user->id);
		                               })->map(function ($user) {
				return [
					'id' => $user->id,
					'full_name' => $user->full_name
				];
			});

		return response()->json(['staffs' => $staffs], 200);
	}

}
