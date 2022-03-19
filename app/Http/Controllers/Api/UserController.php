<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Mail\StaffInvite;
use App\Models\Call;
use App\Models\User;
use App\Models\Category;
use App\Models\City;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Email;
use App\Models\EmailTemplate;
use App\Models\Invoice;
use App\Models\InvoiceProduct;
use App\Models\InvoiceReceivePayment;
use App\Models\Lead;
use App\Models\Meeting;
use App\Models\Opportunity;
use App\Models\Option;
use App\Models\Product;
use App\Models\Qtemplate;
use App\Models\QtemplateProduct;
use App\Models\Quotation;
use App\Models\QuotationProduct;
use App\Models\Saleorder;
use App\Models\SaleorderProduct;
use App\Models\Salesteam;
use App\Models\State;
use App\Models\Task;
use App\Models\TaskReport;
use App\Models\Tag;
use App\Models\History;
use App\Models\Contacts;
use App\Models\LeadFollowUp;
use App\Models\CallActionStatus;
use App\Models\Logs;
use App\Models\PartnerUser;
use App\Models\NextTimeFollow;
use App\Models\Partner;
use App\Models\Notification;
use App\Repositories\CompanyRepository;
use App\Repositories\EmailTemplateRepository;
use App\Repositories\InviteUserRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\LeadRepository;
use App\Repositories\OpportunityRepository;
use App\Repositories\OptionRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ContractRepository;
use App\Repositories\SalesTeamRepository;
use App\Models\GroupLead;
use App\Models\GroupUser;
use App\Models\LeadAssignStatus;
use App\Models\LeadTags;

use App\Models\LeadStatus;
use App\Models\Chatbox;
use App\Models\Branch;
use App\Models\ReportStaff;
use App\Models\ReportTags;

use App\Models\CameraLogs;
use App\Models\CheckIn;

use App\Models\TaskAccept;



use Cache;



use Carbon\Carbon;
use Dingo\Api\Routing\Helpers;
use Efriandika\LaravelSettings\Facades\Settings;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Validator;
use JWTAuth;
use Cartalyst\Sentinel\Checkpoints\NotActivatedException;
use Cartalyst\Sentinel\Checkpoints\ThrottlingException;
use DB;
use Illuminate\Pagination\Paginator;


use App\Repositories\UserRepository;

/**
 * User and staff endpoints, can be accessed only with role "user" or "staff"
 *
 * @Resource("User", uri="/user")
 */
class UserController extends Controller
{
    use Helpers;

    private $user;

    /**
     * @var UserRepository
     */
    private $userRepository;
    private $invoiceRepository;
	/**
	 * @var CompanyRepository
	 */
	private $companyRepository;
	/**
	 * @var ContractRepository
	 */
	private $contractRepository;
	/**
	 * @var OpportunityRepository
	 */
	private $opportunityRepository;
	/**
	 * @var ProductRepository
	 */
	private $productRepository;
	/**
	 * @var LeadRepository
	 */
	private $leadRepository;
	/**
	 * @var OptionRepository
	 */
	private $optionRepository;
	/**
	 * @var EmailTemplateRepository
	 */
	private $emailTemplateRepository;
	/**
	 * @var InviteUserRepository
	 */
	private $inviteUserRepository;
    /**
	 * @var SalesTeamRepository
	 */
	private $salesTeamRepository;
	/**
	 * UserController constructor.
	 *
	 * @param UserRepository $userRepository
	 * @param InvoiceRepository $invoiceRepository
	 * @param CompanyRepository $companyRepository
	 * @param ContractRepository $contractRepository
	 * @param OpportunityRepository $opportunityRepository
	 * @param ProductRepository $productRepository
	 * @param LeadRepository $leadRepository
	 * @param OptionRepository $optionRepository
	 * @param EmailTemplateRepository $emailTemplateRepository
	 * @param InviteUserRepository $inviteUserRepository
	 */
	public function __construct(UserRepository $userRepository, InvoiceRepository $invoiceRepository,
		CompanyRepository $companyRepository, ContractRepository $contractRepository,
	    OpportunityRepository $opportunityRepository, ProductRepository $productRepository,
		LeadRepository $leadRepository, OptionRepository $optionRepository,
		EmailTemplateRepository $emailTemplateRepository, InviteUserRepository $inviteUserRepository, SalesTeamRepository $salesTeamRepository)
    {
        $this->userRepository = $userRepository;
        $this->invoiceRepository = $invoiceRepository;
	    $this->companyRepository = $companyRepository;
	    $this->contractRepository = $contractRepository;
	    $this->opportunityRepository = $opportunityRepository;
	    $this->productRepository = $productRepository;
	    $this->leadRepository = $leadRepository;
	    $this->optionRepository = $optionRepository;
	    $this->emailTemplateRepository = $emailTemplateRepository;
        $this->inviteUserRepository = $inviteUserRepository;
        $this->salesTeamRepository = $salesTeamRepository;
    }

    private $events = [];

    /**
     * Get all calendar items
     *
     * @Get("/calendar")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo"}),
     *      @Response(200, body={
							    "salesteam": {
								    {
									    "id": 1,
									    "title": "Name of team",
									    "start_date": "2016-12-12",
									    "end_date": "2016-12-12",
                                        "all_day": true,
									    "type": "quotation",
								    }
							    }
            }),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function calendar()
    {
        $date = strtotime(date(Settings::get('date_format')));
        $this->user = JWTAuth::parseToken()->authenticate();

        $quotations = Quotation::whereHas('user', function ($q) {
            $q->where(function ($query) {
                $query
                    ->orWhere('id', $this->user->parent->id)
                    ->orWhere('users.user_id', $this->user->parent->id);
            });
        })->where('exp_date', '>', $date)
            ->with('user', 'customer')
            ->get()
            ->map(function ($quotation) {
                return [
                    'id' => $quotation->id,
                    'title' => $quotation->quotations_number,
                    'start_date' => $quotation->exp_date,
                    'end_date' => $quotation->exp_date,
                    'type' => 'quotation'
                ];
            });
        $this->add_events_to_list($quotations);

        $meetings = Meeting::whereHas('user', function ($q) {
            $q->where(function ($query) {
                $query
                    ->orWhere('id', $this->user->parent->id)
                    ->orWhere('users.user_id', $this->user->parent->id);
            });
        })->where('starting_date', '>', $date)
            ->with('responsible')
            ->latest()->get()->map(function ($meeting) {
                return [
                    'id' => $meeting->id,
                    'title' => $meeting->meeting_subject,
                    'start_date' => $meeting->starting_date,
                    'end_date' => $meeting->ending_date,
                    'type' => 'meeting'
                ];
            });
        $this->add_events_to_list($meetings);

        $invoices = Invoice::whereHas('user', function ($q) {
            $q->where(function ($query) {
                $query
                    ->orWhere('id', $this->user->parent->id)
                    ->orWhere('users.user_id', $this->user->parent->id);
            });
        })->where('due_date', '>', $date)
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'title' => $invoice->invoice_number,
                    'start_date' => $invoice->invoice_date,
                    'end_date' => $invoice->invoice_date,
                    'type' => 'invoice'
                ];
            });
        $this->add_events_to_list($invoices);

        $contracts = Contract::whereHas('user', function ($q) {
            $q->where(function ($query) {
                $query
                    ->orWhere('id', $this->user->parent->id)
                    ->orWhere('users.user_id', $this->user->parent->id);
            });
        })->where('end_date', '>', $date)
            ->with('company', 'user')
            ->get()
            ->map(function ($contract) {
                return [
                    'id' => $contract->id,
                    'title' => $contract->description,
                    'start_date' => $contract->start_date,
                    'end_date' => $contract->end_date,
                    'type' => 'contract'
                ];
            });
        $this->add_events_to_list($contracts);

        $opportunities = Opportunity::whereHas('user', function ($q) {
            $q->where(function ($query) {
                $query
                    ->orWhere('id', $this->user->parent->id)
                    ->orWhere('users.user_id', $this->user->parent->id);
            });
        })->where('next_action', '>', $date)
            ->with('salesteam', 'calls', 'meetings')
            ->get()
            ->map(function ($opportunity) {
                return [
                    'id' => $opportunity->id,
                    'title' => $opportunity->opportunity,
                    'start_date' => $opportunity->next_action,
                    'end_date' => $opportunity->expected_closing,
                    'type' => 'opportunity'
                ];
            });
        $this->add_events_to_list($opportunities);

        return response()->json(['events' => $this->events], 200);
    }

    // Get all countries
    public function countries()
    {
        $countries = Country::orderBy("name", "asc")->pluck('name', 'id');

        return response()->json(['countries' => $countries], 200);
    }

    // Get particular states of given country
    public function states(Request $request)
    {
        $states = State::where('country_id', $request->id)->orderBy("name", "asc")->pluck('name', 'id');

        return response()->json(['states' => $states], 200);
    }

    // Get particular cities of given state
    public function cities(Request $request)
    {
        $cities = City::where('state_id', $request->id)->orderBy("name", "asc")->pluck('name', 'id');

        return response()->json(['cities' => $cities], 200);

    }

	/**
	 * Get settings
	 *
	 * @Get("/settings")
	 * @Versions({"v1"})
	 * @Transaction({
	 *      @Request({"token": "foo"}),
	 *      @Response(200, body={
						"settings": {
							"site_name": "lcrm",
							"site_logo": "591d9416b9e91.jpg",
							"site_email": "admin@crm.com",
							"allowed_extensions": "gif,jpg,jpeg,png,pdf,txt",
							"backup_type": "local",
							"email_driver": "mail",
							"minimum_characters": "",
							"date_format": "m/d/Y",
							"time_format": "g:i A",
							"currency": "USD",
							"email_host": "",
							"email_port": "",
							"email_username": "",
							"email_password": "",
							"address1": "",
							"address2": "",
							"phone": "",
							"fax": "",
							"currency_position": "right",
							"max_upload_file_size": "1000",
							"sales_tax": "0",
							"payment_term1": "1",
							"payment_term2": "2",
							"payment_term3": "3",
							"opportunities_reminder_days": "10",
							"contract_renewal_days": "10",
							"invoice_reminder_days": "123",
							"quotation_prefix": "q_",
							"quotation_start_number": "1",
							"quotation_template": "quotation_red_green",
							"sales_prefix": "s_",
							"sales_start_number": "2",
							"saleorder_template": "saleorder_red_green",
							"invoice_prefix": "i",
							"invoice_start_number": "3",
							"invoice_template": "invoice_red_green",
							"invoice_payment_prefix": "",
							"invoice_payment_start_number": "",
							"pusher_app_id": "",
							"pusher_key": "",
							"pusher_secret": "",
							"paypal_username": "",
							"paypal_password": "",
							"paypal_signature": "",
							"stripe_secret": "",
							"stripe_publishable": "",
							"pdf_logo": "logo_1492175789.png",
							"jquery_date": "MM/DD/GGGG",
							"jquery_date_time": "MM/DD/GGGG h:mm A",
						},
				        "logo" : "http://crmsmart.io/image.jpg",
						"pdf_logo" : "http://crmsmart.io/image.jpg",
						"max_upload_file_size": {
							"1000": "1MB",
							"2000": "2MB",
							"3000": "3MB",
							"4000": "4MB",
							"5000": "5MB",
							"6000": "6MB",
							"7000": "7MB",
							"8000": "8MB",
							"9000": "9MB",
							"10000": "10MB"
						},
						"currency": {
							"USD": "USD",
							"EUR": "EUR"
						},
						"backup_type": {
								{
									"text": "Local",
									"id": "local"
								},
								{
									"text": "Dropbox",
									"id": "dropbox"
								},
								{
									"text": "Amazon S3",
									"id": "s3"
								}
						}
					}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *       })
	 * })
	 */
    public function settings()
    {
        $max_upload_file_size = array(
            '1000' => '1MB',
            '2000' => '2MB',
            '3000' => '3MB',
            '4000' => '4MB',
            '5000' => '5MB',
            '6000' => '6MB',
            '7000' => '7MB',
            '8000' => '8MB',
            '9000' => '9MB',
            '10000' => '10MB',
        );
        $user = Sentinel::getUser();
        $currency = Option::where('category', 'currency')
            ->get()
            ->map(
                function ($title) {
                    return [
                        'text' => $title->title,
                        'id' => $title->value,
                    ];
                }
            )->pluck('text', 'id');

        $backup_type = Option::where('category', 'backup_type')
            ->get()
            ->map(
                function ($title) {
                    return [
                        'text' => $title->value,
                        'id' => $title->title,
                    ];
                }
            );
        $settings = Settings::getAll();
	    unset($settings['logo']);
	    unset($settings['token']);
        return response()->json(['settings' => $settings,
                                 'max_upload_file_size' => $max_upload_file_size,
                                 'currency' => $currency,
                                 'logo' => asset('uploads/site/'. Settings::get('site_logo')),
                                 'pdf_logo' => asset('uploads/site/' . Settings::get('pdf_logo')),
                                'backup_type' => $backup_type], 200);
    }
	/**
	 * Post call
	 *
	 * @Post("/update_settings")
	 * @Versions({"v1"})
	 * @Transaction({
	 *      @Request({"token": "foo", "logo":"base64:image", "pdf_logo":"base64:image","date_format_custom": "d-m-Y", "time_format_custom":"H:m","site_name":"LCRM","address1":"Address1","address2":"Address2","site_email":"email@email.com", "currency":"USD"}),
	 *      @Response(200, body={"success":"success"}),
	 *       @Response(403, body={"error":"no_permissions"}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *       })
	 * })
	 */
    public function updateSettings(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    if (!is_null($request->logo)) {
		    $output_file = str_random(10)  . ".jpg";
		    $ifp = fopen(public_path() . '/uploads/site/' . $output_file, "wb");
		    fwrite($ifp, base64_decode($request->logo));
		    fclose($ifp);
		    $request->merge(['site_logo' => $output_file]);
	    }
	    if (!is_null($request->pdf_logo)) {
		    $output_file = str_random(10) . ".jpg";
		    $ifp = fopen(public_path() . '/uploads/site/' . $output_file, "wb");
		    fwrite($ifp, base64_decode($request->pdf_logo));
		    fclose($ifp);
		    $request->merge(['pdf_logo' => $output_file]);
	    }

        Settings::set('modules', []);
        $request->date_format = $request->date_format_custom;
        $request->time_format = $request->time_format_custom;
        if ($request->date_format == "") {
            $request->date_format = 'd.m.Y';
        }
        if ($request->time_format == "") {
            $request->time_format = 'H:i';
        }
        $request->merge([
            'jquery_date' => $this->dateformat_PHP_to_jQueryUI($request->date_format),
            'jquery_date_time' => $this->dateformat_PHP_to_jQueryUI($request->date_format . ' ' . $request->time_format),
        ]);

        foreach ($request->except('_token', 'site_logo_file', 'pdf_logo_file', 'date_format_custom', 'time_format_custom', 'pages') as $key => $value) {
            Settings::set($key, $value);
        }
        return response()->json(['success' => 'success'], 200);
    }

    function dateformat_PHP_to_jQueryUI($php_format)
    {
        $SYMBOLS_MATCHING = array(
            // Day
            'd' => 'DD',
            'D' => 'ddd',
            'j' => 'D',
            'l' => 'dddd',
            'N' => 'do',
            'S' => 'do',
            'w' => 'd',
            'z' => 'DDD',
            // Week
            'W' => 'w',
            // Month
            'F' => 'MMMM',
            'm' => 'MM',
            'M' => 'MMM',
            'n' => 'M',
            't' => '',
            // Year
            'L' => '',
            'o' => '',
            'Y' => 'GGGG',
            'y' => 'GG',
            // Time
            'a' => 'a',
            'A' => 'A',
            'B' => '',
            'g' => 'h',
            'G' => 'H',
            'h' => 'hh',
            'H' => 'HH',
            'i' => 'mm',
            's' => 'ss',
            'u' => ''
        );


        $jqueryui_format = "";
        $escaping = false;
        for ($i = 0; $i < strlen($php_format); $i++) {
            $char = $php_format[$i];
            if ($char === '\\') // PHP date format escaping character
            {
                $i++;
                if ($escaping) $jqueryui_format .= $php_format[$i];
                else $jqueryui_format .= '\'' . $php_format[$i];
                $escaping = true;
            } else {
                if ($escaping) {
                    $jqueryui_format .= "'";
                    $escaping = false;
                }
                if (isset($SYMBOLS_MATCHING[$char]))
                    $jqueryui_format .= $SYMBOLS_MATCHING[$char];
                else
                    $jqueryui_format .= $char;
            }
        }
        return $jqueryui_format;
    }

    /**
     * @param $events_data
     */
    private function add_events_to_list($events_data)
    {
        foreach ($events_data as $d) {
            $event = [];
            $start_date = date(Settings::get('date_format'), (is_numeric($d['start_date']) ? $d['start_date'] : strtotime($d['start_date'])));
            $end_date = date(Settings::get('date_format'), (is_numeric($d['end_date']) ? $d['end_date'] : strtotime($d['end_date'])));
            $event['title'] = $d['title'];
            $event['id'] = $d['id'];
            $event['start'] = $start_date;
            $event['end'] = $end_date;
            $event['allDay'] = true;
	        $event['type'] = $d['type'];
            array_push($this->events, $event);
        }
    }


    /**
     * Get all calls
     *
     * @Get("/calls")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo"}),
     *      @Response(200, body={
    "calls": {
    {
    "id": 1,
    "date": "2015-10-15",
    "call_summary": "Call summary",
    "company": "Company",
    "user": "User",
    }
    }
    }),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function calls()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('logged_calls.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $calls = Call::whereHas('user', function ($q) {
            $q->where(function ($query) {
                $query
                    ->orWhere('id', $this->user->parent->id)
                    ->orWhere('users.user_id', $this->user->parent->id);
            });
        })
            ->with('user', 'company')
            ->get()
            ->map(function ($call) {
                return [
                    'id' => $call->id,
                    'date' => $call->date,
                    'call_summary' => $call->call_summary,
                    'company' => $call->company->name,
                    'user' => $call->user->full_name,
                ];
            });

        return response()->json(['calls' => $calls], 200);
    }

    /**
     * Get call item
     *
     * @Get("/call")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo", "call_id":"1"}),
     *       @Response(200, body={"call": {
			    "id" : 1,
			    "date": "2015-10-15",
			    "call_summary": "Call summary",
			    "duration" :"30",
			    "company": "Company",
			    "resp_staff": "User",
			    "user_id" : 1,
			    "created_at" : "2015-12-22 20:17:20",
			    "updated_at" : "2015-12-22 20:19:11",
			    "deleted_at" : null
			    }}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */
    public function call(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('logged_calls.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'call_id' => $request->input('call_id'),
        );
        $rules = array(
            'call_id' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $call = Call::find($request->call_id)->with('company', 'responsible')
                ->get()
                ->map(function ($call) {
                    return [
                        'id' => $call->id,
                        'date' => $call->date,
                        'call_summary' => $call->call_summary,
                        'duration' => $call->duration,
                        'company' => isset($call->company)?$call->company->name:"",
                        'resp_staff' => $call->responsible->full_name,
                    ];
                });
            return response()->json(['call' => $call], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Post call
     *
     * @Post("/post_call")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "date":"2015-10-11", "call_summary":"call summary","duration": "30", "company_id":"1","resp_staff_id":"12"}),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function postCall(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        if($this->user->inRole('staff')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'date' => $request->input('date'),
            'call_summary' => $request->input('call_summary'),
            'duration' => $request->input('duration'),
            'company_id' => $request->input('company_id'),
            'resp_staff_id' => $request->input('resp_staff_id'),
        );
        $rules = array(
            'company_id' => 'required',
            'call_summary' => 'required',
            'resp_staff_id' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $this->user->calls()->create($request->except('token'));

            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Edit call
     *
     * @Post("/edit_call")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo", "call_id":"1","date":"2015-10-11", "call_summary":"call summary","company_id":"1","resp_staff_id":"12"}),
     *       @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */
    public function editCall(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('logged_calls.write')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'call_id' => $request->input('call_id'),
            'date' => $request->input('date'),
            'call_summary' => $request->input('call_summary'),
            'company_id' => $request->input('company_id'),
            'resp_staff_id' => $request->input('resp_staff_id'),
        );
        $rules = array(
            'call_id' => 'required',
            'date' => 'required|date_format:"' . Settings::get('date_format') . '"',
            'call_summary' => 'required',
            'company_id' => 'required',
            'resp_staff_id' => 'required',
        );
        $validator = Validator::make($data, $rules);


        if ($validator->passes()) {
            $call = Call::find($request->call_id);
            $call->update($request->except('token', 'call_id'));

            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Delete call
     *
     * @Post("/delete_call")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "call_id":"1"}),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function deleteCall(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('logged_calls.delete')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'call_id' => $request->input('call_id'),
        );
        $rules = array(
            'call_id' => 'required|integer',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $call = Call::find($request->call_id);
            $call->delete();
            return response()->json(['success' => "success"], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Get all categories
     *
     * @Get("/categories")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo"}),
     *      @Response(200, body={
    "category": {
    {
    "id": 1,
    "name": "Category name",
    }
    }
    }),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function categories()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $categories = Category::whereHas('user', function ($q) {
            $q->where(function ($query) {
                $query
                    ->orWhere('id', $this->user->parent->id)
                    ->orWhere('users.user_id', $this->user->parent->id);
            });
        })
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                ];
            });

        return response()->json(['categories' => $categories], 200);
    }

    /**
     * Get category item
     *
     * @Get("/category")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo", "category_id":"1"}),
     *       @Response(200, body={"category": {
    "id" : 1,
    "name" : "Category",
    "user_id" : 1,
    "created_at" : "2015-12-23 16:58:25",
    "updated_at" : "2015-12-23 16:58:25",
    "deleted_at" : null
    }}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */
    public function category(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $data = array(
            'category_id' => $request->input('category_id'),
        );
        $rules = array(
            'category_id' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $category = Category::find($request->category_id);
            return response()->json(['category' => $category->toArray()], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Post category
     *
     * @Post("/post_category")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "name":"category name"}),
     *      @Response(200, body={"success":"success"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function postCategory(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $data = array(
            'name' => $request->input('name'),
        );
        $rules = array(
            'name' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {

            $this->user->categories()->create($request->except('token'));

            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Edit category
     *
     * @Post("/edit_category")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo", "category_id":"1","name":"category name"}),
     *       @Response(200, body={"success":"success"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */
    public function editCategory(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $data = array(
            'id' => $request->input('category_id'),
            'name' => $request->input('name'),
        );
        $rules = array(
            'id' => 'required',
            'name' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $category = Category::find($request->category_id);
            $category->update($request->except('token', 'category_id'));

            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Delete category
     *
     * @Post("/delete_category")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "call_id":"1"}),
     *      @Response(200, body={"success":"success"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function deleteCategory(Request $request)
    {
        $data = array(
            'id' => $request->input('category_id'),
        );
        $rules = array(
            'id' => 'required|integer',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $category = Category::find($request->category_id);
            $category->delete();
            return response()->json(['success' => "success"], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }


    /**
     * Get all companies
     *
     * @Get("/companies")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo"}),
     *      @Response(200, body={
    "companies": {
    {
    "id": 1,
    "name": "Name",
    "customer": "customer name",
    "phone": "634654165456",
    }
    }
    }),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function companies()
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('contacts.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
        $companies = Company::whereHas('user', function ($q) {
            $q->where(function ($query) {
                $query
                    ->orWhere('id', $this->user->parent->id)
                    ->orWhere('users.user_id', $this->user->parent->id);
            });
        })->latest()->get()->map(function ($comp) {
            return [
                'id' => $comp->id,
                'name' => $comp->name,
                'customer' => isset($comp->user)?$comp->user->full_name:"",
                'phone' => $comp->phone,
            ];
        });

        return response()->json(['companies' => $companies], 200);
    }

    /**
     * Get company item
     *
     * @Post("/company")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo", "company_id":"1"}),
     *       @Response(200, body={"company": {
    "id" : 2,
    "name" : "dg dfg",
    "email" : "user@crm.com",
    "password" : "",
    "lostpw" : "",
    "address" : "fdgdfg",
    "website" : "gfdgfdg",
    "phone" : "45454",
    "mobile" : "45",
    "fax" : "4545",
    "title" : "",
    "company_avatar" : "",
    "company_attachment" : "",
    "main_contact_person" : 3,
    "sales_team_id" : 1,
    "country_id" : 1,
    "state_id" : 43,
    "city_id" : 5914,
    "longitude" : "63.30929400000002",
    "latitude" : "35.6403478",
    "user_id" : 1,
    "created_at" : "2015-12-26 07:10:25",
    "updated_at" : "2015-12-26 07:10:25",
    "deleted_at" : null
    }}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */
    public function company(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('contacts.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
        $data = array(
            'company_id' => $request->input('company_id'),
        );
        $rules = array(
            'company_id' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $company = Company::find($request->company_id)
	            ->with('contactPerson','salesTeam')
                ->get()
                ->map(function ($company) {
                    return [
                        'id' => $company->id,
                        'name' => $company->name,
                        'email' => $company->email,
                        'address' => $company->address,
                        'website' => $company->website,
                        'phone' => $company->phone,
                        'mobile' => $company->mobile,
                        'fax' => $company->fax,
                        'title' => $company->title,
                        'company_avatar' => $company->company_avatar,
                        'main_contact_person' => isset($company->contactPerson)?$company->contactPerson->full_name:"",
                        'sales_team' => isset($company->salesTeam)?$company->salesTeam->salesteam:"",
                        'country_id' => $company->country_id,
                        'state_id' => $company->state_id,
                        'city_id' => $company->city_id,
                        'avatar' => $company->avatar,
                    ];
                });
            return response()->json(['company' => $company], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Post company
     *
     * @Post("/post_company")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "name":"Company name","email":"email@email.com","address":"first street,NY",
     *     "sales_team_id":"1","main_contact_person":"1","phone":"123132214","avatar":"base64_encoded_image" }),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function postCompany(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('contacts.write')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
        $data = array(
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'address' => $request->input('address'),
            'sales_team_id' => $request->input('sales_team_id'),
            'main_contact_person' => $request->input('main_contact_person'),
            'phone' => $request->input('phone'),
        );
        $rules = array(
            'name' => 'required|min:3|max:50',
            'email' => 'required|email',
            'address' => 'required',
            'sales_team_id' => 'required',
            'main_contact_person' => 'required',
            'phone' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {

            $company = $this->user->companies()->create($request->except('token', 'avatar'));
	        if (!is_null($request->avatar)) {
		        $output_file = str_random(10)  . ".jpg";
		        $ifp = fopen(public_path() . '/uploads/company/' . $output_file, "wb");
		        fwrite($ifp, base64_decode($request->avatar));
		        fclose($ifp);
		        $company->company_avatar = $output_file;
		        $company->save();
	        }
            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Edit company
     *
     * @Post("/edit_company")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo", "company_id":"1","name":"Company name","email":"email@email.com", "avatar":"base64_encoded_image"}),
     *       @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */
    public function editCompany(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('contacts.write')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
        $data = array(
            'company_id' => $request->input('company_id'),
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'address' => $request->input('address'),
            'sales_team_id' => $request->input('sales_team_id'),
            'main_contact_person' => $request->input('main_contact_person'),
            'phone' => $request->input('phone'),
        );
        $rules = array(
            'company_id' => 'required',
            'name' => 'required|min:3|max:50',
            'email' => 'required|email',
            'address' => 'required',
            'sales_team_id' => 'required',
            'main_contact_person' => 'required',
            'phone' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $company = Company::find($request->company_id);
	        if (!is_null($request->avatar)) {
		        $output_file = str_random(10)  . ".jpg";
		        $ifp = fopen(public_path() . '/uploads/company/' . $output_file, "wb");
		        fwrite($ifp, base64_decode($request->avatar));
		        fclose($ifp);
		        $company->company_avatar = $output_file;
	        }
            $company->update($request->except('token', 'company_id','avatar'));

            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Delete company
     *
     * @Post("/delete_company")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "company_id":"1"}),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function deleteCompany(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('contacts.delete')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'company_id' => $request->input('company_id'),
        );
        $rules = array(
            'company_id' => 'required|integer',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $company = Company::find($request->company_id);
            $company->delete();
            return response()->json(['success' => "success"], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }


    /**
     * Get all contracts
     *
     * @Get("/contracts")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo"}),
     *      @Response(200, body={
    "company": {
    {
    "id": 1,
    "start_date": "2015-11-12",
    "description": "Description",
    "name": "Company name",
    "user": "User name",
    }
    }
    }),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function contracts()
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('contracts.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
        $contracts = Contract::whereHas('user', function ($q) {
            $q->where(function ($query) {
                $query
                    ->orWhere('id', $this->user->parent->id)
                    ->orWhere('users.user_id', $this->user->parent->id);
            });
        })
            ->with('company', 'user')
            ->get()
            ->map(function ($contract) {
                return [
                    'id' => $contract->id,
                    'start_date' => $contract->start_date,
                    'description' => $contract->description,
                    'name' => isset($contract->company)?$contract->company->name:"",
                    'user' => isset($contract->user)?$contract->user->full_name:""
                ];
            });

        return response()->json(['contracts' => $contracts], 200);
    }

    /**
     * Get contract item
     *
     * @Get("/contract")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo", "contract_id":"1"}),
     *       @Response(200, body={"contract": {
    "id" : 1,
    "start_date" : "21.12.2015.",
    "end_date" : "23.12.2015.",
    "description" : "ffdgfdg",
    "company_id" : 1,
    "resp_staff_id" : 2,
    "real_signed_contract" : "",
    "user_id" : 1,
    "created_at" : "2015-12-22 20:27:37",
    "updated_at" : "2015-12-22 20:27:37",
    "deleted_at" : null
    }}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */
    public function contract(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('contracts.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
        $data = array(
            'contract_id' => $request->input('contract_id'),
        );
        $rules = array(
            'contract_id' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $contract = Contract::find($request->contract_id)
                ->with('company','responsible')
                ->get()
                ->map(function ($contract) {
                    return [
                        'id' => $contract->id,
                        'start_date' => $contract->start_date,
                        'end_date' => $contract->end_date,
                        'company' => isset($contract->company)?$contract->company->name:"",
                        'responsible' => isset($contract->responsible)?$contract->responsible->full_name:"",
                        'description' => $contract->description
                    ];
                });

            return response()->json(['contract' => $contract], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Post contract
     *
     * @Post("/post_contract")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "start_date":"2015-11-11","end_date":"2015-11-11","description": "Description",
     *     "company_id":"1","resp_staff_id":"1"}),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function postContract(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('contracts.write')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
        $data = array(
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'description' => $request->input('description'),
            'company_id' => $request->input('company_id'),
            'resp_staff_id' => $request->input('resp_staff_id'),
        );
        $rules = array(
            'start_date' => 'required|date_format:"' . Settings::get('date_format') . '"',
            'end_date' => 'required|date_format:"' . Settings::get('date_format') . '"',
            'description' => 'required',
            'company_id' => 'required',
            'resp_staff_id' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {

            $this->user->contracts()->create($request->except('token'));

            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Edit contract
     *
     * @Post("/edit_contract")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo", "company_id":"1","name":"Company name","email":"email@email.com"}),
     *       @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */
    public function editContract(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('contracts.write')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
        $data = array(
            'contract_id' => $request->input('contract_id'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'description' => $request->input('description'),
        );
        $rules = array(
            'contract_id' => 'required',
            'start_date' => 'required|date_format:"' . Settings::get('date_format') . '"',
            'end_date' => 'required|date_format:"' . Settings::get('date_format') . '"',
            'description' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $contract = Contract::find($request->contract_id);
            $contract->update($request->except('token', 'contract_id'));

            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Delete contract
     *
     * @Post("/delete_contract")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "company_id":"1"}),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function deleteContract(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('contracts.delete')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
        $data = array(
            'contract_id' => $request->input('contract_id'),
        );
        $rules = array(
            'contract_id' => 'required|integer',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $contract = Contract::find($request->contract_id);
            $contract->delete();
            return response()->json(['success' => "success"], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }


    /**
     * Get all customers
     *
     * @Get("/customers")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo"}),
     *      @Response(200, body={
    "customers": {
    {
    "user_id": 1,
    "customer_id": 2,
    "full_name": "full name",
    "email": "email@email.com",
    "created_at": "2015--11-11",
    "avatar": "http://avatar.com/avatar.jpg"
    }
    }
    }),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function customers()
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('contacts.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $staffs = collect();
	    $this->user->users()
		    ->with('users.users')
		    ->get()
		    ->each(function ($user) use ($staffs) {
			    foreach ($user->users as $u) {
				    $staffs->push($u);
			    }
		    });

	    $staffs = $staffs->filter(function ($user) {
		    return $user->inRole('customer');
	    });
	    $customers = $staffs->map(function ($user) {
		            return [
			            'user_id'            => $user->id,
			            'customer_id'   => $user->customer->id,
			            'full_name'     => $user->full_name,
			            'email'         => $user->email,
			            'avatar' => $user->avatar,
			            'created_at'    => $user->created_at->format( 'Y-d-m' )
		            ];
            });
        return response()->json(['customers' => $customers], 200);
    }

    /**
     * Get customer item
     *
     * @Get("/customer")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo", "customer_id":"1"}),
     *       @Response(200, body={"contract": {
    "id" : 1,
    "user_id" : 3,
    "belong_user_id" : 2,
    "address" : "",
    "website" : "",
    "job_position" : "",
    "mobile" : "5456",
    "fax" : "",
    "title" : "",
    "company_id" : 0,
    "sales_team_id" : 0,
    "created_at" : "2015-12-22 19:26:19",
    "avatar": "http://avatar.com/avatar.jpg"
    }}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */
    public function customer(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('contacts.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
        $data = array(
            'customer_id' => $request->input('customer_id'),
        );
        $rules = array(
            'customer_id' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {

            $customer = Customer::find($request->customer_id)
	            ->with('salesTeam','company')
                ->get()
                ->map(function ($customer) {
                    return [
                        'id' => $customer->id,
                        'belong_user_id' => $customer->belong_user_id,
                        'user_id' => $customer->user_id,
                        'full_name' => $customer->user->full_name,
                        'email' => $customer->user->email,
                        'website' => $customer->website,
                        'mobile' => $customer->mobile,
                        'company' => isset($customer->company)?$customer->company->name:"",
                        'salesteam' => isset($customer->salesTeam)?$customer->salesTeam->salesteam:"",
                        'address' => $customer->address,
                        'job_position' => $customer->job_position,
                        'title' => $customer->title,
                        'avatar' => $customer->avatar,
                        'created_at' => $customer->created_at->format('Y-d-m H:i:s')
                    ];
                });
            return response()->json(['customer' => $customer], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Post customer
     *
     * @Post("/post_customer")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "first_name":"first name", "last_name":"last name","email":"email@email.com","password":"password","password_confirmation":"password","phone_number":"+54212425", "sales_team_id":1, "company_id":1,"address": "address", "job_position":"developer", "mobile" :" +545231","fax" : "+2314521", "title" : "mr"}),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function postCustomer(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('contacts.write')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
        $data = array(
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'password_confirmation' => $request->input('password_confirmation'),
            'phone_number' => $request->input('phone_number'),
            'sales_team_id' => $request->input('sales_team_id'),
            'company_id' => $request->input('company_id'),
            'address' => $request->input('address'),
            'job_position' => $request->input('job_position'),
            'mobile' => $request->input('mobile'),
            'fax' => $request->input('fax'),
            'title' => $request->input('title'),
        );
        $rules = array(
            'first_name' => 'required|min:3|max:50',
            'last_name' => 'required|min:3|max:50',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required|min:6',
            'phone_number' => 'required',
            'sales_team_id' => 'required',
            'company_id' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {

            $user = Sentinel::registerAndActivate($request->only('first_name', 'last_name', 'phone_number', 'email', 'password'));

	        $role = Sentinel::findRoleBySlug('customer');
	        $role->users()->attach($user);

	        $user = User::find($user->id);
	        $user->phone_number = $request->phone_number;
	        if (!is_null($request->avatar)) {
		        $output_file = str_random(10)  . ".jpg";
		        $ifp = fopen(public_path() . '/uploads/avatar/' . $output_file, "wb");
		        fwrite($ifp, base64_decode($request->avatar));
		        fclose($ifp);
		        $user->user_avatar = $output_file;
	        }
	        $user->save();

            $customer = new Customer($request->except('first_name', 'avatar', 'last_name', 'phone_number', 'email', 'password', 'user_avatar', 'password_confirmation'));
            $customer->user_id = $user->id;
            $customer->belong_user_id = $this->user->id;
            $customer->save();

	        $currentUser = $this->user;
	        $currentUser->users()->save($user);

            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Edit customer
     *
     * @Post("/edit_customer")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo", "customer_id":"1","first_name":"first name", "last_name":"last name","email":"email@email.com","sales_team_id":1, "company_id":1,"address":"address", "phone_number":"+54212425", "address", "job_position":"developer", "mobile" :" +545231","fax" : "+2314521", "title" : "mr"}),
     *       @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */
    public function editCustomer(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('contacts.write')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
        $data = array(
            'customer_id' => $request->input('customer_id'),
            'sales_team_id' => $request->input('sales_team_id'),
            'company_id' => $request->input('company_id'),
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'email' => $request->input('email'),
            'address' => $request->input('address'),
            'job_position' => $request->input('job_position'),
            'mobile' => $request->input('mobile'),
            'fax' => $request->input('fax'),
            'title' => $request->input('title'),
        );
        $rules = array(
            'customer_id' => 'required',
            'sales_team_id' => 'required',
            'company_id' => 'required',
            'first_name' => 'required|min:3|max:50',
            'last_name' => 'required|min:3|max:50',
            'email' => 'required|email|unique:users,email',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {

            $customer = Customer::find($request->customer_id);
            $customer->update($request->except('first_name', 'last_name', 'avatar','phone_number', 'email', 'password', 'user_avatar', 'password_confirmation'));

            $user = User::find($customer->user->id);
	        if (!is_null($request->avatar)) {
		        $output_file = str_random(10)  . ".jpg";
		        $ifp = fopen(public_path() . '/uploads/avatar/' . $output_file, "wb");
		        fwrite($ifp, base64_decode($request->avatar));
		        fclose($ifp);
		        $user->user_avatar = $output_file;
	        }
            $user->update($request->only('first_name', 'last_name', 'phone_number', 'email', 'password'));
            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Delete customer
     *
     * @Post("/delete_customer")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "customer_id":"1"}),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function deleteCustomer(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('contacts.delete')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'customer_id' => $request->input('customer_id'),
        );
        $rules = array(
            'customer_id' => 'required|integer',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $customer = Customer::find($request->customer_id);
	        $user = User::find($customer->user->id);
	        $user->delete();
            $customer->delete();
            return response()->json(['success' => "success"], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
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
    "invoice_number": "1465456",
    "invoice_date": "2015-11-11",
    "customer": "Customer Name",
    "unpaid_amount": "15.2",
    "status": "Status",
    "due_date": "2015-11-11",
    }
    },
     "month_overdue": 1,
     "month_paid": 5,
     "month_open": 3
    }),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function invoices()
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('invoices.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
        $invoices = Invoice::with('customer')->whereHas('user', function ($q) {
            $q->where(function ($query) {
                $query
                    ->orWhere('id', $this->user->parent->id)
                    ->orWhere('users.user_id', $this->user->parent->id);
            });
        })->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'invoice_date' => $invoice->invoice_date,
                    'customer' => isset($invoice->customer)?$invoice->customer->full_name:"",
                    'unpaid_amount' => $invoice->unpaid_amount,
                    'status' => $invoice->status,
                    'due_date' => $invoice->due_date,
                ];
            });
	    $month_overdue = round($this->invoiceRepository->getAllOverdueMonth()->sum('unpaid_amount'), 3);
	    $month_paid = round($this->invoiceRepository->getAllPaidMonth()->sum('final_price'), 3);
	    $month_open = round($this->invoiceRepository->getAllOpenMonth()->sum('final_price'), 3);

        return response()->json(['invoices' => $invoices,
                                 'month_overdue'=>$month_overdue,
                                 'month_paid'=>$month_paid,
                                 'month_open'=>$month_open], 200);
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
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */
    public function invoice(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('invoices.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
        $data = array(
            'invoice_id' => $request->input('invoice_id'),
        );
        $rules = array(
            'invoice_id' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $invoice = Invoice::find($request->get('invoice_id'))
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
     * Post invoice
     *
     * @Post("/post_invoice")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "customer_id":"5", "invoice_date":"2015-11-11","sales_person_id":"2","status":"status","total":"10.00","tax_amount":"01.10","grand_total":"11.10","discount":1.2,"final_price":9.85,"invoice_prefix":"I00","invoice_start_number":"0"}),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function postInvoice(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('invoices.write')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
        $data = array(
            'customer_id' => $request->input('customer_id'),
            'invoice_date' => $request->input('invoice_date'),
            'due_date' => $request->input('due_date'),
            'sales_team_id' => $request->input('sales_team_id'),
            'sales_person_id' => $request->input('sales_person_id'),
            'status' => $request->input('status'),
            'grand_total' => $request->input('grand_total'),
            'tax_amount' => $request->input('tax_amount'),
            'discount' => $request->input('discount'),
            'final_price' => $request->input('final_price'),
            'total' => $request->input('total'),
            'payment_term' => $request->input('payment_term'),
            'invoice_prefix' => $request->input('invoice_prefix'),
            'invoice_start_number' => $request->input('invoice_start_number'),
        );
        $rules = array(
            'customer_id' => 'required',
            'invoice_date' => 'required',
            'due_date' => 'required',
            'sales_person_id' => 'required',
            'sales_team_id' => 'required',
            'status' => 'required',
            'grand_total' => 'required',
            'tax_amount' => 'required',
            'discount' => 'required',
            'final_price' => 'required',
            'total' => 'required',
            'payment_term' => "required",
            'invoice_start_number' => "required",
            'invoice_prefix' => "required"
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {

            $total_fields = Invoice::whereNull('deleted_at')->orWhereNotNull('deleted_at')->orderBy('id', 'desc')->first();
            $invoice_no = $request->input('invoice_prefix') . ($request->input('invoice_start_number') + (isset($total_fields) ? $total_fields->id : 0) + 1);
            $exp_date = date(Settings::get('date_format'), strtotime(' + ' .
                isset($request->payment_term) ? $request->payment_term : Settings::get('invoice_reminder_days') . ' days'
                )
            );

            $invoice = new Invoice($request->only('customer_id', 'invoice_date', 'payment_term',
                'sales_person_id', 'sales_team_id', 'status', 'total',
                'tax_amount', 'grand_total', 'final_price', 'discount'));
            $invoice->invoice_number = $invoice_no;
            $invoice->unpaid_amount = $request->get('grand_total');
            $invoice->due_date = isset($request->due_date) ? $request->get('due_date') : strtotime($exp_date);
            $invoice->user_id = Sentinel::getUser()->id;
            $invoice->save();

            InvoiceProduct::where('invoice_id', $invoice->id)->delete();

            if (!empty($request->get('product_id'))) {
                foreach ($request->get('product_id') as $key => $item) {
                    if ($item != "" && $request->get('product_name')[$key] != "" && $request->get('description')[$key] != "" &&
                        $request->get('quantity')[$key] != "" && $request->get('price')[$key] != "" && $request->get('sub_total')[$key] != ""
                    ) {
                        $invoiceProduct = new InvoiceProduct();
                        $invoiceProduct->invoice_id = $invoice->id;
                        $invoiceProduct->product_id = $item;
                        $invoiceProduct->product_name = $request->get('product_name')[$key];
                        $invoiceProduct->description = $request->get('description')[$key];
                        $invoiceProduct->quantity = $request->get('quantity')[$key];
                        $invoiceProduct->price = $request->get('price')[$key];
                        $invoiceProduct->sub_total = $request->get('sub_total')[$key];
                        $invoiceProduct->save();
                    }
                }
            }

            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Edit invoice
     *
     * @Post("/edit_invoice")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo", "invoice_id":"1","customer_id":"5", "invoice_date":"2015-11-11","sales_person_id":"2","status":"status","total":"10.00","tax_total":"01.10","grand_total":"11.10","discount":"0.10","final_price":"9.10"}),
     *       @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */
    public function editInvoice(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('invoices.write')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
        $data = array(
            'invoice_id' => $request->input('invoice_id'),
            'customer_id' => $request->input('customer_id'),
            'invoice_date' => $request->input('invoice_date'),
            'due_date' => $request->input('due_date'),
            'sales_person_id' => $request->input('sales_person_id'),
            'status' => $request->input('status'),
            'grand_total' => $request->input('grand_total'),
            'total' => $request->input('total'),
            'discount' => $request->input('discount'),
            'final_price' => $request->input('final_price'),
            'payment_term' => $request->input('payment_term'),
        );
        $rules = array(
            'invoice_id' => 'required',
            'customer_id' => 'required',
            'invoice_date' => 'required',
            'due_date' => 'required',
            'sales_person_id' => 'required',
            'status' => 'required',
            'grand_total' => 'required',
            'total' => 'required',
            'discount' => 'required',
            'final_price' => 'required',
            'payment_term' => "required",
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $invoice = Invoice::find($request->get('invoice_id'));

            $exp_date = date(Settings::get('date_format'), strtotime(' + ' . isset($request->payment_term) ? $request->payment_term : 0 . ' days'));

            $payments = InvoiceReceivePayment::where('invoice_id', $invoice->id);

            $invoice->unpaid_amount = $request->get('grand_total') - (($payments->count() > 0) ? $payments->sum('payment_received') : 0);
            $invoice->due_date = isset($request->due_date) ? $request->get('due_date') : strtotime($exp_date);
            $invoice->update($request->only('customer_id', 'invoice_date', 'payment_term',
                'sales_person_id', 'sales_team_id', 'status', 'total', 'final_price', 'discount',
                'tax_amount', 'grand_total'));
            InvoiceProduct::where('invoice_id', $invoice->id)->delete();

            if (!empty($request->get('product_id'))) {
                foreach ($request->get('product_id') as $key => $item) {
                    if ($item != "" && $request->get('product_name')[$key] != "" && $request->get('description')[$key] != "" &&
                        $request->get('quantity')[$key] != "" && $request->get('price')[$key] != "" && $request->get('sub_total')[$key] != ""
                    ) {
                        $invoiceProduct = new InvoiceProduct();
                        $invoiceProduct->invoice_id = $invoice->id;
                        $invoiceProduct->product_id = $item;
                        $invoiceProduct->product_name = $request->get('product_name')[$key];
                        $invoiceProduct->description = $request->get('description')[$key];
                        $invoiceProduct->quantity = $request->get('quantity')[$key];
                        $invoiceProduct->price = $request->get('price')[$key];
                        $invoiceProduct->sub_total = $request->get('sub_total')[$key];
                        $invoiceProduct->save();
                    }
                }
            }
            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Delete invoice
     *
     * @Post("/delete_invoice")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "invoice_id":"1"}),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function deleteInvoice(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('invoices.delete')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'invoice_id' => $request->input('invoice_id'),
        );
        $rules = array(
            'invoice_id' => 'required|integer',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $invoice = Invoice::find($request->get('invoice_id'));
            $invoice->delete();
            return response()->json(['success' => "success"], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }


    /**
     * Get all invoice_payments
     *
     * @Get("/invoice_payments")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo"}),
     *      @Response(200, body={
    "invoice_payments": {
    {
    "id": 1,
    "payment_number": "P002",
    "payment_received": "1525.26",
    "payment_method": "Paypal",
    "payment_date": "2015-11-11",
    "customer": "Customer Name",
    "person": "Person Name"
    }
    }
    }),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function invoicePayments()
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('invoices.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
        $invoice_payments = InvoiceReceivePayment::whereHas('user', function ($q) {
            $q->where(function ($query) {
                $query
                    ->orWhere('id', $this->user->parent->id)
                    ->orWhere('users.user_id', $this->user->parent->id);
            });
        })->with('invoice.customer', 'invoice.salesPerson')
            ->get()->map(function ($ip) {
                return [
                    'id' => $ip->id,
                    'payment_number' => $ip->payment_number,
                    'payment_received' => $ip->payment_received,
                    'invoice_number' => isset($ip->invoice)?$ip->invoice->invoice_number:"-",
                    'payment_method' => $ip->payment_method,
                    'payment_date' => $ip->payment_date,
                    'customer' => isset($ip->invoice->customer)?$ip->invoice->customer->full_name:null,
                    'salesperson' => isset($ip->invoice->salesPerson)?$ip->invoice->salesPerson->full_name:null,
                ];
            });

        return response()->json(['invoice_payments' => $invoice_payments], 200);
    }

    /**
     * Get invoice_payment item
     *
     * @Get("/invoice_payment")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo"}),
     *      @Response(200, body={
    "invoice_payment": {
    {
    "id": 1,
    "payment_number": "P002",
    "payment_received": "1525.26",
    "payment_method": "Paypal",
    "payment_date": "2015-11-11",
    "customer": "Customer Name",
    "person": "Person Name"
    }
    }
    }),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */

    public function invoicePayment(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('invoices.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
        $invoice_payment = InvoiceReceivePayment::find($request->invoice_payment_id)
            ->with('invoice.customer', 'invoice.salesPerson')
            ->get()
            ->map(function ($invoice_payment) {
                return [
                    'id' => $invoice_payment->id,
                    'payment_number' => $invoice_payment->payment_number,
                    'payment_received' => $invoice_payment->payment_received,
                    'invoice_number' => isset($ip->invoice)?$ip->invoice->invoice_number:"-",
                    'payment_method' => $invoice_payment->payment_method,
                    'payment_date' => $invoice_payment->payment_date,
                    'customer' => isset($invoice_payment->invoice->customer)?$invoice_payment->invoice->customer->full_name:null,
                    'salesperson' => isset($invoice_payment->invoice->salesPerson)?$invoice_payment->invoice->salesPerson->full_name:null,
                ];
            });

        return response()->json(['invoice_payment' => $invoice_payment], 200);
    }

    /**
     * Post invoice_payment
     *
     * @Post("/post_invoice_payment")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "invoice_id":"5", "payment_date":"2015-11-11","payment_method":"2","payment_received":"555"}),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */

    public function postInvoicePayment(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('invoices.write')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
        $data = array(
            'invoice_id' => $request->input('invoice_id'),
            'payment_date' => $request->input('payment_date'),
            'payment_method' => $request->input('payment_method'),
            'payment_received' => $request->input('payment_received'),
        );
        $rules = array(
            'invoice_id' => 'required',
            'payment_date' => 'required',
            'payment_method' => 'required',
            'payment_received' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $invoice = Invoice::find($request->get('invoice_id'));

            $total_fields = InvoiceReceivePayment::orderBy('id', 'desc')->first();

            $quotation_no = Settings::get('invoice_payment_prefix') . (Settings::get('invoice_payment_start_number') + (isset($total_fields) ? $total_fields->id : 0) + 1);

            $payment_date = date(Settings::get('date_format'), strtotime(' + ' . $request->payment_date));

            $invoiceRepository = $this->invoiceRepository->create($request->except('token', 'invoice_id'));
            $invoiceRepository->invoice()->associate($invoice);
            $invoiceRepository->payment_number = $quotation_no;
            $invoiceRepository->payment_date = isset($request->payment_date) ? $request->payment_date : strtotime($payment_date);
            $invoiceRepository->save();

            $unpaid_amount_new = bcsub($invoice->unpaid_amount, $request->payment_received, 2);

            if ($unpaid_amount_new <= '0') {
                $invoice_data = array(
                    'unpaid_amount' => $unpaid_amount_new,
                    'status' => 'Paid Invoice',
                );
            } else {
                $invoice_data = array(
                    'unpaid_amount' => $unpaid_amount_new,
                );
            }

            $invoice->update($invoice_data);

            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
        }
    }


    /**
     * Get all leads
     *
     * @Get("/leads")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo"}),
     *      @Response(200, body={
    "leads": {
    {
    "id": 1,
    "register_time": "2015-12-22",
    "opportunity": "1.2",
    "contact_name": "Contact name",
    "email": "dsad@asd.com",
    "phone": "456469465",
    "salesteam": "Test team",
    }
    }
    }),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */

    public function leads(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('leads.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
        }  
        $status=$request->status;
        $keyword=$request->keyword;
        $type=$request->type; //1 đang chăm soc, 2 thành công, 3 ko thành công
        $chat=$request->chat;
        $typeId=$request->typeId;

         @file_put_contents("/home/crmsmart/web/api.salesdy.com/public_html/smartweb/public/log/log_".$type."-".$typeId.".txt", $request);

        $win=array(6,7);
        $drop=array(8,9,10,11,12);
        $follow=array(1,2,3,4,5);
       
        switch ($status) {
            case in_array($status,$win):
                $warning=2;
                $star=3;
                break;
            case in_array($status,$drop):
                $warning=1;
                $star=1;
                break;
            case in_array($status,$follow):
                $warning=3;
                $star=2;
                break;
            default:
                $warning=2;
                $star=2;
        }
        $item=20;
        $statuswhere=explode(",",trim($status));
        if((isset($type) && $type!="") || (isset($typeId) && $typeId!="")){

                $typeSearch=$type;//explode(",",$type);
                /*
                if($typeSearch==0){
                    $typeSearch=array(1,2,3);
                } */
                $leadsQuery = Lead::select('leads.id', 'leads.created_at', 'leads.product_id', 'leads.psid', 'leads.contact_name', 'leads.address', 'leads.email', 'leads.phone', 'leads.sales_person_id', 'leads.tags', 'leads.photos', 'leads.photos', 'leads.status', 'leads.function', 'products.product_name', 'products.product_image', 'users.first_name', 'users.last_name')
                ->leftJoin('products', 'products.id', '=', 'leads.product_id')
                ->leftJoin('users', 'users.id', '=', 'leads.sales_person_id')
                ->leftJoin('call_action_status','call_action_status.id','=','leads.status')
                ->where(function ($query1) {
                    $query1->where('leads.sales_person_id', $this->user->id)->orWhere('leads.user_id', $this->user->id);
                })->where(function ($query3) use($keyword) {
                    if($keyword!=""){
                        $query3->where('leads.title', 'like', '%'.$keyword.'%')->orWhere('leads.contact_name', 'like', '%'.$keyword.'%')->orWhere('leads.email', 'like', '%'.$keyword.'%')->orWhere('leads.opportunity', 'like', '%'.$keyword.'%')->orWhere('leads.phone', $keyword);
                    }
                })
                ->where(function ($query3) use($chat) {
                    if($chat==1){
                        $query3->where('leads.psid',"!=",0);
                        $query3->whereNotNull('leads.psid');
                        $query3->where('leads.psid', "!=", "");
                    }
                })
                ->where(function ($query4) use($typeSearch) {
                    if($typeSearch>0){
                        $query4->where('call_action_status.type_client', $typeSearch);
                    }
                })
                ->where(function ($query5) use($typeId) {
                    if($typeId>0){
                        $query5->where('leads.status', $typeId);
                    }
                })
                ->distinct('leads.id')
                ->orderBy("leads.updated_at", "desc");
                //$leadsQuery->count()
                $totalLead=$leadsQuery->count();
                $leadsPage=$leadsQuery->paginate($item)->appends(request()->query());
                $leads=$leadsPage;
               // ->orderBy("leads.updated_at", "desc")->get();
            //->get();
        }else{
            $leadsQuery = Lead::select('leads.id', 'leads.created_at', 'leads.product_id', 'leads.psid', 'leads.contact_name', 'leads.address', 'leads.email', 'leads.phone', 'leads.tags', 'leads.sales_person_id', 'leads.photos', 'leads.status', 'leads.function', 'products.product_name', 'products.product_image', 'users.first_name', 'users.last_name')
            ->leftJoin('products', 'products.id', '=', 'leads.product_id')
            ->leftJoin('users', 'users.id', '=', 'leads.sales_person_id')
            ->where(function ($query1) {
                $query1->where('leads.sales_person_id', $this->user->id)->orWhere('leads.user_id', $this->user->id);
            })->where(function ($query3) use($keyword) {
                if($keyword!=""){
                    $query3->where('leads.title', 'like', '%'.$keyword.'%')->orWhere('leads.contact_name', 'like', '%'.$keyword.'%')->orWhere('leads.email', 'like', '%'.$keyword.'%')->orWhere('leads.opportunity', 'like', '%'.$keyword.'%')->orWhere('leads.phone', $keyword);
                }
            })
            ->when($statuswhere, function ($query2) use ($statuswhere) {
                if(count($statuswhere)>0){
                    return $query2->whereIn('leads.status', $statuswhere);
                }
               
            })
            ->distinct('leads.id')
            ->orderBy("leads.updated_at", "desc");
           // ->whereIn('call_action_status.type_client', $typeSearch)
            //$leadsQuery->count()
            $totalLead=$leadsQuery->count();
            $leadsPage=$leadsQuery->paginate($item)->appends(request()->query());
            $leads=$leadsPage;

           // ->get();
        }
            $leadJson=[];
            if($leads){
                foreach($leads as $lead){
                    $tags="";
                    if($lead->tags!=""){
                        
                        $tags=" Tags: ".$lead->tags;
                    }
                    $leadJson[]=[
                        'id' => $lead->id,
                        'register_time' => $lead->created_at,
                        'opportunity' => (isset($lead->opportunity) && $lead->opportunity!="")?$lead->opportunity:"Chưa cập nhật",
                        'product_id' => $lead->product_id,
                        'product_name' => $lead->product_name,
                        'contact_name' => $lead->contact_name,
                        'address' => $lead->address,
                        'country' => "VN",//isset($lead->country->name)?$lead->country->name:"",
                        'email' => $lead->email,
                        'url_messenger' => "",//$lead->URL,
                        'phone' => $lead->phone,
                        'sales_person_id' => $lead->sales_person_id,
                        'sales_name' => $lead->first_name." ".$lead->last_name,
                        'user_id' => $lead->sales_person_id,
                        'psid' => $lead->psid,
                        'page_id' => $lead->page_id,
                        'warning'=>$warning,
                        'type_client'=>$lead->phone.$tags,
                        'star'=>$star, //1 trống, 2 vang, 3 xanh
                        'thumbnail'=>(isset($lead->photos)&&$lead->photos!="")?$lead->photos:"https://api.fastercrm.com/img/nophoto.png?=1",
                        'status' => $lead->status,
                        'source' => $lead->function,
                    ];
                 }
                 $page=round($totalLead/$item);
                return response()->json(['leads' => $leadJson, 'page'=>$page, 'total'=>$totalLead], 200);
            }else{
                return response()->json(['error' => 'not_valid_data'], 200);
            }
            //return response()->json(['leads' => $leads], 200);
    }

    /**
     * Get lead item
     *
     * @Get("/lead")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo", "lead_id":"1"}),
     *       @Response(200, body={"invoice": {
    "id" : 1,
    "opportunity" : "Lead",
    "company_name" : "sdfsdf sdf",
    "customer_id" : 1,
    "address" : "sd fsdfd",
    "country_id" : 1,
    "state_id" : 43,
    "city_id" : 5914,
    "sales_person_id" : 1,
    "sales_team_id" : 1,
    "contact_name" : "sdfsdf sdf sdf ",
    "title" : "Doctor",
    "email" : "user@crm.com",
    "function" : "asdasd sad asd ",
    "phone" : "1545",
    "mobile" : "545",
    "fax" : "1545",
    "tags" : "2,4",
    "priority" : "Low",
    "internal_notes" : "asd asd asd ",
    "assigned_partner_id" : 0,
    "user_id" : 1,
    "created_at" : "2015-12-22 19:56:54",
    "updated_at" : "2015-12-22 19:56:54",
    "deleted_at" : null
    }}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */
    public function lead(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
        $data = array(
            'lead_id' => $request->input('lead_id'),
        );
      
        $rules = array(
            'lead_id' => 'required',
        );

        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            /*
            $leadUpdateLead = Lead::select('leads.id', 'leads.id')
            ->where('leads.id', $request->input('lead_id'))
            ->where('leads.partner_id', $this->user->partner_id)
            ->where(function ($query) {
                $query->where('leads.sales_person_id', '=', 0);
            })->first();
            if($leadUpdateLead){
                $leadUpdateLead->sales_person_id=$this->user->id;
                $leadUpdateLead->update();
            } */
            $leadDetail = Lead::select('leads.*', 'products.product_name', 'products.product_image', 'call_action_status.title as status_name', 'call_action_status.type_client as type_client')
            ->leftJoin('products', 'products.id', '=', 'leads.product_id')
            ->leftJoin('call_action_status', 'leads.status', '=', 'call_action_status.id')
            ->where('leads.id', $request->input('lead_id'))
            ->where('leads.partner_id', $this->user->partner_id)
            /*
            ->where(function ($query) {
                $query->where('leads.user_id', '=', $this->user->id)->orWhere('leads.sales_person_id', '=', $this->user->id);
            }) */
            ->first();
            

            if($leadDetail){

                $partner_id=$this->user->partner_id;
                $groupid=$leadDetail->group_id;
                $tagsCache = cache('tagsCache1'.$partner_id.$groupid);
                if(isset($tagsCache) && $tagsCache!=""){
                    $tags=$tagsCache;
                }else{
                    $tags = Tag::where([['partner_id',$partner_id],['group_client_id',$groupid]])->orderBy('position','asc')->get();
                    Cache::put('tagsCache1'.$partner_id.$groupid, $tags, now()->addMinutes(100));
                } 

                $photo="https://api.fastercrm.com/uploads/avatar/user.png";
                if($leadDetail->photos!=""){
                    $photo=$leadDetail->photos;
                }
                $tagList=[];

                $tagListSelect=LeadTags::select('tag_id')->where('lead_id',$leadDetail->id)->get()
				->map( function ( $data ) {
					return [
						'value' => $data->tag_id,
					];
				} )->pluck('value')->toArray();

				if($tags){
					foreach($tags as $key=>$values){
						$select="";
						if(in_array($values["id"], $tagListSelect)){
							$tagList[]=array('id'=>$values["id"], 'title'=>$values["title"]);
						}
						
					}
				}

                /*
                if($leadDetail->tags!=""){
                    $tagID=explode(",",$leadDetail->tags);
                    $tagList = Tag::whereIn('id', $tagID)->orderBy("position", "DESC")
                    ->get()
                    ->map(function ($tags) {
                        return [
                            'id' => $tags->id,
                            'title' => $tags->title
                        ];
                    });
                } */
                /*
                $sourceList=[];
                if($leadDetail->source_id!=""){
                    $sourceList = $this->optionRepository->getAll()->where('id', $leadDetail->source_id)->where('partner_id','=',$this->user->partner_id)->where( 'category', 'function_type' )->get()
                    ->map( function ( $title ) {
                        return [
                            'title' => $title->title,
                            'id' => $title->id,
                        ];
                    });
                }*/
               // $lasttimechat=$leadDetail->last_update
                $chatnow=0;
                $psid=$leadDetail->psid;
                if($psid!="" && $psid!="0"){
                    $get23ago=date("Y-m-d H:i:s", time()-(59*60*24));
                    $logData=Chatbox::select('id', 'timechat')
                    ->where('date_create','>=', $get23ago)->where('sender_id', '=', $psid)
                    ->orderBy('chat_box.id', 'desc')->first();
                }
                $timenow=time();
				if(isset($logData) && $logData!=""){
					$chatnow=1;
				}
                $leadJson=[
                    'id' => $leadDetail->id,
                    'opportunity' => $leadDetail->opportunity,
                    'group_id' => $leadDetail->group_id,
                    'address' => $leadDetail->address,
                    'country' => $leadDetail->country?$leadDetail->country->name:null,
                    'state_id' => $leadDetail->state?$leadDetail->state->name:null,
                    'city_id' => $leadDetail->city?$leadDetail->city->name:null,
                    'salesteam' => $leadDetail->salesteam,
                    'sales_person_id' => $leadDetail->sales_person_id,
                    'sales_person' => $leadDetail->salesPerson?$leadDetail->salesPerson->full_name:null,
                    'product_id' => $leadDetail->product_id,
                    'product_name' => $leadDetail->product_name,
                    'contact_name' => $leadDetail->contact_name,
                    'title' => $leadDetail->title,
                    'email' => $leadDetail->email,
                    'next_follow_up' => $leadDetail->next_follow_up,
                    'function' => $leadDetail->function,
                    'phone' => (isset($leadDetail->phone) && $leadDetail->phone!="")?$leadDetail->phone:"",
                    'mobile' => $leadDetail->mobile,
                    'fax' => $leadDetail->fax,
                    'tags' => $tagList,
                    'priority' => $leadDetail->priority,
                    'internal_notes' => $leadDetail->internal_notes,
                    'psid' => (isset($leadDetail->psid) && $leadDetail->psid!="")?$leadDetail->psid:"",
                    'page_id' => $leadDetail->page_id,
                    'warning'=>0, //1 Red, 2 vang, 3 xanh internal_notes
                    'star'=>0, //1 trống, 2 vang, 3 xanh
                    'url_messenger' => $leadDetail->URL,
                    'thumbnail'=>$photo,
                    'status' => $leadDetail->status,
                    'type_client' => (isset($leadDetail->type_client) && $leadDetail->type_client!="")?$leadDetail->type_client:1,
                    'status_name' => $leadDetail->status_name,
                    'source' => $leadDetail->function,
                    'source_id' => $leadDetail->source_id,
                    'chat' => (isset($leadDetail->psid) && $leadDetail->psid!="" && $chatnow==1)?1:0,

                ];
                return response()->json(['lead' => $leadJson], 200);
            }else{
                return response()->json(['error' => 'Bạn không có quyền xem Khách hàng này'], 200);
            }
            
        } else {
            return response()->json(['error' => 'Không tìm thấy Khách hàng!'], 500);
        }
    }

    /**
     * Post lead
     *
     * @Post("/post_lead")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "opportunity":"125.5", "email":"test@test.com","customer_id":"12","sales_team_id":"1","tags":"Softwae","country_id":"15","sales_person_id":"12"}),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function postLead(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('leads.write')){
		    return response()->json(['error' => 'no_permissions'], 403);
        }
        $salesTeamID=0;
       // $salesTeam = User::leftJoin('sales_team_members','users.id','=','sales_team_members.user_id')->where('users.id','=',$this->user->id)->pluck('sales_team_members.salesteam_id')->first();
        $partner_id= $this->user->partner_id;
        $data = array(
            'opportunity' => $request->input('fullname'),
            'email' => $request->input('email'),
            'phone' =>  $request->input('phone'),
            'function'=>$request->input('lead_source'),
            'status'=>$request->input('status'),
            'product_id'=>$request->input('product_id'),
            'sales_person_id' =>$this->user->id,
            'user_id' =>$this->user->id,
            'tags' =>$request->input('tags'),
        );

        $rules = array(
            'phone' => 'required',
            'opportunity' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $sales_person_id=$request->sales_person_id;
            if(!isset($request->sales_person_id) || $request->sales_person_id==""){
                $sales_person_id=$this->user->id;
            }
            $status=$request->status;
            if(!isset($request->status) || $request->status==""){
                $status=0;
            }

            $lead_source=$request->lead_source;
            if(!isset($request->lead_source) || $request->lead_source==""){
                $lead_source="";
            }
            $tags=$request->tags;
            if(!isset($request->tags) || $request->tags==""){
                $tags="";
            }
            $lead = new Lead;
            $lead->opportunity = $request->fullname;
            if(isset($request->title) && $request->title!=""){
                $lead->title = $request->title;
            }
            if(isset($request->lead_source) && $request->lead_source!=""){
                $lead->function = $request->lead_source;
            }
            if($partner_id==32){
                $status="141";
            }
            $lead->email = $request->email;
            $lead->phone =  $request->phone;
            $lead->status=$status;
            $lead->user_id =$this->user->id;
            $lead->sales_person_id =$sales_person_id;
            $lead->contact_name =$request->fullname;
            $lead->next_follow_up = $request->next_time_follow;
            $lead->tags =$tags;
            $lead->sales_team_id =0;
            $lead->product_id =$request->input('product_id');
            $lead->partner_id =$partner_id;
            $lead->save();
            //
             // Add to log
            $dataLogs = array(
            'user_id' => $this->user->id,
            'logs'=>"@".$this->user->first_name." ".$this->user->last_name." đã thêm lead mới @".$request->fullname,
            'created_at'=> date("Y-m-d H:i:s"),
            'phone'=>$request->phone,
            'lead_id'=>$lead->id,
            );
            Logs::insert($dataLogs);
            // end add log
            if($lead->id){
                if($request->next_time_follow){
                    $data = array(
                        'lead_id' => $lead->id,
                        'staff_id'=>$sales_person_id,
                        'next_time_follow' => $request->next_time_follow,
                        'note' => "Đăng ký mới",
                        'date_create'=>date('Y-m-d H:i:s')
                    );
                    $rules = array(
                        'lead_id' => 'required',
                        'next_time_follow' => 'required',
                    );
                    $validator = Validator::make($data, $rules);
                    if ($validator->passes()) {
                        LeadFollowUp::insert($data);
                        NextTimeFollow::addUpdate($lead->id, $request->next_time_follow, 0);
                    }
                }
               
            }
            //$this->updatesyndataToMS();
            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Edit lead
     *
     * @Post("/edit_lead")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo","lead_id":1, "opportunity":"125.5", "email":"test@test.com","customer_id":"12","sales_team_id":"1","tags":"Softwae","country_id":"15"}),
     *       @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */
    public function editLead(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('leads.write')){
		    return response()->json(['error' => 'no_permissions'], 403);
        }
        $data = array(
            'lead_id' => $request->lead_id,
           // 'opportunity' => $request->input('fullname'),
            //'phone' => $request->input('phone'),
           // 'sales_person_id' => $request->input('sales_person_id'),
            //'function' => $request->input('source'),
            //'next_follow_up' => $request->input('next_follow_up'),
            //'status'=>$request->input('status'),
           // 'email' => $request->input('email')
        );
        $rules = array(
            'lead_id' => 'required',
        );
        
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $lead = Lead::where('id', $request->lead_id)
            ->where(function ($query) {
                    //$query1->orWhere('leads.updated_at','<=',$ending_date);
                $query->where('user_id', '=', $this->user->id)->orWhere('sales_person_id', '=', $this->user->id);
            })->first();
            
            $title="";
            $phone="";
            $email="";
            $product="";
            $status="";
            $tags="";
            $tagsList="";
            $leadName="";
            $listTagArray="";
            if($lead){
                //array_push($tagoldarray,$tags);
                //$tags=implode(", ",$tagoldarray);
                //$file = '/home/crmsmart/web/api.salesdy.com/public_html/smartweb/public/log/lead_detail.txt';
                //@file_put_contents($file, $request);
                if($request->tag_id!=""){
                    $tagsList=explode(",",$request->tag_id);
                    $listTagArray=Tag::select('title')->whereIn('id',$tagsList)->get()
                    ->map(function ($list) {
                        return [
                            'title' =>  $list->title,
                        ];
                    })
                    ->pluck('title')->toArray();
                }
                LeadTags::where(['lead_id'=>$lead->id, 'partner_id'=>$this->user->partner_id])->delete();
                $dataTag=[];
                if($tagsList!="" && count($tagsList)>0){
                    for ($j=0; $j < count($tagsList); $j++) {
                        $dataTag[] = [
                            'user_id' => $this->user->id,
                            'partner_id'=>$this->user->partner_id,
                            'lead_id'=>$lead->id,
                            'tag_id'=>$tagsList[$j],
                            'created_at'=>date("Y-m-d H:i:s"),
                        ];
                    }
                }
                if(count($dataTag)>0){
                    LeadTags::insert($dataTag);
                }
               // $lead = Lead::find($request->lead_id);
                if($listTagArray!="" && count($listTagArray)>0){
                    $lead->tags = implode(", ", $listTagArray);
                }
                
                if($request->title!=""){
                    $title= $request->title;
                    $lead->title =  $title;
                }
                if($request->fullname!=""){
                    $lead->opportunity = $request->fullname;
                    $lead->contact_name = $request->fullname;
                    $leadName=$request->fullname;
                }
                if($request->email!=""){
                    $email=$request->email;
                    $lead->email = $email;
                }
                if($request->phone!=""){
                    $phone=$request->phone;
                    $lead->phone = $phone;
                }
                if($request->sales_person_id!=""){
                    $lead->sales_person_id = $request->sales_person_id;
                }
                
                if($request->source_id!=""){
                    $lead->source_id = $request->source_id;
                    $sourceList = $this->optionRepository->getAll()->where('id', $request->source_id)->where('partner_id','=',$this->user->partner_id)->first();
                    $lead->function = $sourceList["title"];
                    $lead->UTM_Source = $sourceList["title"];
                }
                $internal_notes="";
                $titlenote="";
                if($request->internal_notes!=""){
                    $internal_notes=$request->internal_notes;
                    $titlenote="Ghi chú: ";
                }
                $lead->internal_notes = $internal_notes;
                
                
                if($request->status!=""){
                    $status=$request->status;
                    $lead->status = $status;
                    $getStatus=CallActionStatus::where('id',$status)->first();
                    if($getStatus!=""){
                        $groupclient=$getStatus["type"];
                        $lead->group_id = $groupclient;
                    }
                    
                }

                if($request->next_follow_up!=""){
                    $lead->next_follow_up = $request->next_follow_up;
                }
                if($request->product_id!=""){
                    $lead->product_id = $request->product_id;
                }
                if($request->product_name!=""){
                    $product=$request->product_name;
                    $lead->product_name = $product;
                }
                
                /*
                if(isset($this->user->partner_id) && $this->user->partner_id!="" && $this->user->partner_id!=0){
                    $lead->partner_id = $this->user->partner_id;
                } */
                
                $result=$lead->save();
                
                 // Add to log
                 $fullname=trim($this->user->first_name." ".$this->user->last_name);
				 $dataLogs = array(
                    'user_id' => $this->user->id,
                    'logs'=>"@".$fullname." đã sửa lead @".$request->fullname." ".$titlenote.$internal_notes,
                    'phone'=>$lead->phone,
                    'lead_id'=>$request->lead_id,
                    'created_at'=> date("Y-m-d H:i:s"),
                    );
                   Logs::insert($dataLogs);
                   // end add log
                   
                        $partner_id=$this->user->partner_id;
                   
                  // if( $partner_id!=""){
                    //$this->updatesyndataToMS($partner_id, $leadName, $title, $phone, $email, $product,$status,$tags);
                  // }

                return response()->json(['success' => 'success'], 200);
            }else{
                return response()->json(['error' => 'Bạn không có quyền sửa KH này'], 500);
            }
            
        } else {
            return response()->json(['error' => 'Không tìm thấy Khách hàng!'], 500);
        }
    }

    /**
     * Delete lead
     *
     * @Post("/delete_lead")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "lead_id":"1"}),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function deleteLead(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('leads.delete')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
        $data = array(
            'lead_id' => $request->input('lead_id'),
        );
        $rules = array(
            'lead_id' => 'required|integer',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $lead = Lead::find($request->lead_id);
	        $lead->calls()->delete();
            $lead->delete();
            return response()->json(['success' => "success"], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }
    
	/**
	 * Get all lead calls
	 *
	 * @Get("/lead_calls")
	 * @Versions({"v1"})
	 * @Transaction({
	 *      @Request({"token": "foo", "lead_id":"1"}),
	 *      @Response(200, body={
			"calls": {
				{
				"id": 1,
				"date": "2015-10-15",
				"call_summary": "Call summary",
				"company": "Company",
				"responsible": "User",
				}
			}
		}),
	 *       @Response(403, body={"error":"no_permissions"}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *       })
	 * })
	 */
	public function leadCalls(Request $request)
	{
		$this->user = JWTAuth::parseToken()->authenticate();
		if($this->user->inRole('staff') && !$this->user->authorized('leads.read')){
			return response()->json(['error' => 'no_permissions'], 403);
		}
		$data = array(
			'lead_id' => $request->input('lead_id'),
		);
		$rules = array(
			'lead_id' => 'required|integer',
		);
		$validator = Validator::make($data, $rules);
		if ($validator->passes()) {
			$lead = Lead::find($request->lead_id);
			$calls = $lead->calls()->with('responsible', 'company')
            ->get()
            ->map(function ($call) {
                return [
                    'id' => $call->id,
                    'date' => $call->date,
                    'call_summary' => $call->call_summary,
                    'company' => isset($call->company)?$call->company->name:"",
                    'responsible' => isset($call->responsible)?$call->responsible->full_name:""
                ];
            });

			return response()->json(['calls' => $calls], 200);
		} else {
			return response()->json(['error' => 'not_valid_data'], 500);
		}
	}


	/**
     * Get all lead call
     *
     * @Get("/lead_call")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "lead_id":"1"}),
     *      @Response(200, body={
    "calls": {
    {
    "id": 1,
    "date": "2015-10-15",
    "call_summary": "Call summary",
    "company": "Company",
    "responsible": "User",
    }
    }
    }),
	 *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function leadCall(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('leads.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
        $lead = Lead::find($request->lead_id);
        $calls = $lead->calls()
            ->with('responsible', 'company')
            ->get()
            ->map(function ($call) {
                return [
                    'id' => $call->id,
                    'date' => $call->date,
                    'call_summary' => $call->call_summary,
                    'company' => isset($call->company)?$call->company->name:"",
                    'responsible' => isset($call->responsible)?$call->responsible->full_name:""
                ];
            });

        return response()->json(['calls' => $calls], 200);
    }

    /**
     * Post lead call
     *
     * @Post("/post_lead_call")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "lead_id":"1","date":"2015-10-11", "call_summary":"call summary","company_id":"1","resp_staff_id":"12"}),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function postLeadCall(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('leads.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
        $data = array(
            'lead_id' => $request->input('lead_id'),
            'date' => $request->input('date'),
            'duration' => $request->input('duration'),
            'call_summary' => $request->input('call_summary'),
            'resp_staff_id' => $request->input('resp_staff_id'),
        );
        $rules = array(
            'lead_id' => "required",
            'call_summary' => 'required',
            'resp_staff_id' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {

            $lead = Lead::find($request->lead_id);
            $call = $lead->calls()->create($request->except('lead_id', 'token'), ['user_id' => $this->user->id]);
            $call->user_id = $this->user->id;
            $call->save();
            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Edit lead call
     *
     * @Post("/edit_lead_call")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo", "call_id":"1","lead_id":"1","date":"2015-10-11", "call_summary":"call summary","company_id":"1","resp_staff_id":"12"}),
     *       @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */
    public function editLeadCall(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('leads.write')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
        $data = array(
            'call_id' => $request->input('call_id'),
            'lead_id' => $request->input('lead_id'),
            'date' => $request->input('date'),
            'call_summary' => $request->input('call_summary'),
            'company_id' => $request->input('company_id'),
            'resp_staff_id' => $request->input('resp_staff_id'),
        );
        $rules = array(
            'call_id' => 'required',
            'lead_id' => "required",
            'date' => 'required|date_format:"' . Settings::get('date_format') . '"',
            'call_summary' => 'required',
            'company_id' => 'required',
            'resp_staff_id' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $call = Call::find($request->call_id);
            $call->update($request->except('token', 'lead_id', 'call_id'));

            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Delete lead call
     *
     * @Post("/delete_lead_call")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "call_id":"1"}),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function deleteLeadCall(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('leads.delete')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'call_id' => $request->input('call_id'),
        );
        $rules = array(
            'call_id' => 'required|integer',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $call = Call::find($request->call_id);
            $call->delete();
            return response()->json(['success' => "success"], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Get all meetings
     *
     * @Get("/meetings")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo"}),
     *      @Response(200, body={
    "meetings": {
    {
    "id": 1,
    "meeting_subject": "meeting subject",
    "starting_date": "2015-12-22",
    "ending_date": "2015-12-22",
    "responsible": "User name"
    }
    }
    }),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function meetings()
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('meetings.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
        $meetings = Meeting::whereHas('user', function ($q) {
            $q->where(function ($query) {
                $query
                    ->orWhere('id', $this->user->parent->id)
                    ->orWhere('users.user_id', $this->user->parent->id);
            });
        })
            ->with('responsible')
            ->latest()->get()->map(function ($meeting) {
                return [
                    'id' => $meeting->id,
                    'meeting_subject' => $meeting->meeting_subject,
                    'starting_date' => $meeting->starting_date,
                    'ending_date' => $meeting->ending_date,
                    'responsible' => isset($meeting->responsible)?$meeting->responsible->full_name:""
                ];
            });

        return response()->json(['meetings' => $meetings], 200);
    }

    /**
     * Get meeting item
     *
     * @Get("/meeting")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo", "meeting_id":"1"}),
     *       @Response(200, body={"meeting": {
    "id" : 2,
    "meeting_subject" : "Meeting",
    "attendees" : "1",
    "responsible_id" : 2,
    "starting_date" : "29.12.2015. 00:00",
    "ending_date" : "08.01.2016. 00:00",
    "all_day" : 0,
    "location" : "sdfsdf",
    "meeting_description" : "ftyf hgfhgfh",
    "privacy" : "Everyone",
    "show_time_as" : "Free",
    "duration" : "",
    "user_id" : 0,
    "created_at" : "2015-12-22 20:19:42",
    "updated_at" : "2015-12-26 15:03:37",
    "deleted_at" : null
    }}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */
    public function meeting(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('meetings.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'meeting_id' => $request->input('meeting_id'),
        );
        $rules = array(
            'meeting_id' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $meeting = Meeting::find($request->meeting_id);
            $meetingDetail=[
                        ['id' => $meeting->id,
                        'meeting_subject' => $meeting->meeting_subject,
//                        'attendees' =>$meeting->attendees,
                        'responsible' => isset($meeting->responsible)? $meeting->responsible->full_name:"",
                        'starting_date' => $meeting->starting_date,
                        'ending_date' => $meeting->ending_date,
                        'all_day' => $meeting->all_day,
                        'location' => $meeting->location,
                        'meeting_description' => $meeting->meeting_description,
                        'privacy' => $meeting->privacy,
                        'show_time_as' => $meeting->show_time_as,
                        'duration' => $meeting->duration]
            ];
            $attendees = array();
            $attendeesCompany=array();
            $attendeesArray = array();
            $attendeesArray[] = explode(",", $meeting->attendees);
            // Meting company
            $attendeesCompanyArray = array();
            $attendeesCompanyArray[] = explode(",", $meeting->company_attendees);

            //User show
            foreach ($attendeesArray as $key => $attendeeId) {
                do {
                    $attendees[] = User::find($attendeeId)->pluck('last_name');
                } while ($key == sizeof($attendeeId));
            }
            //User company show
            foreach ($attendeesCompanyArray as $key => $attendeeId) {
                do {
                    $attendeesCompany[] = Company::find($attendeeId)->pluck('name');
                } while ($key == sizeof($attendeeId));
            }

            return response()->json(['meeting' => $meetingDetail, 'attendees' => $attendees, 'attendeesCompany' => $attendeesCompany], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Post meeting
     *
     * @Post("/post_meeting")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "meeting_subject":"Subject", "starting_date":"2015-11-11","ending_date":"2015-11-11"}),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function postMeeting(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('meetings.write')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }

	    $data = array(
            'meeting_subject' => $request->input('meeting_subject'),
            'starting_date' => $request->input('starting_date'),
            'ending_date' => $request->input('ending_date'),
            'responsible_id' => $request->input('responsible_id'),
        );

        $rules = array(
            'meeting_subject' => 'required',
            'responsible_id' => 'required',
            'starting_date' => 'required',
            'ending_date' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {

            $request->merge([
                'attendees' => implode(',', $request->get('attendees', []))
            ]);
            if (Sentinel::getUser()) {
                $user =  Sentinel::getUser();
            }else{
                $user = JWTAuth::parseToken()->authenticate();
            }
            //$user = Sentinel::getUser();
            $user->meetings()->create($request->except('token'));

            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Edit meeting
     *
     * @Post("/edit_meeting")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo","meeting_id":1, "meeting_subject":"Subject", "starting_date":"2015-11-11","ending_date":"2015-11-11"}),
     *       @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */
    public function editMeeting(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('meetings.write')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }

	    $data = array(
            'meeting_id' => $request->input('meeting_id'),
            'meeting_subject' => $request->input('meeting_subject'),
            'responsible_id' => $request->input('responsible_id'),
            'starting_date' => $request->input('starting_date'),
            'ending_date' => $request->input('ending_date'),
        );
        $rules = array(
            'meeting_id' => 'required',
            'meeting_subject' => 'required',
            'responsible_id' => 'required',
            'starting_date' => 'required|date_format:"'.Settings::get('date_format').' '.Settings::get('time_format').'"',
            'ending_date' => 'required|date_format:"'.Settings::get('date_format').' '.Settings::get('time_format').'"',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {

            $meeting = Meeting::find($request->meeting_id);
            $meeting->attendees = implode(',', $request->get('attendees', []));
            $meeting->update($request->except('token', 'attendees', 'meeting_id'));

            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Delete meeting
     *
     * @Post("/delete_meeting")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "meeting_id":"1"}),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function deleteMeeting(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('meetings.delete')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }

	    $data = array(
            'meeting_id' => $request->input('meeting_id'),
        );
        $rules = array(
            'meeting_id' => 'required|integer',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $meeting = Meeting::find($request->meeting_id);
            $meeting->delete();
            return response()->json(['success' => "success"], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Get all opportunity calls
     *
     * @Get("/opportunity_calls")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "opportunity_id":"1"}),
     *      @Response(200, body={
    "calls": {
    {
    "id": 1,
    "date": "2015-10-15",
    "call_summary": "Call summary",
    "company": "Company",
    "responsible": "User",
    }
    }
    }),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function opportunityCalls(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('opportunities.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $opportunity = Opportunity::find($request->opportunity_id);
        $calls = $opportunity->calls()
            ->with('responsible', 'company')
            ->get()
            ->map(function ($call) {
                return [
                    'id' => $call->id,
                    'date' => $call->date,
                    'call_summary' => $call->call_summary,
                    'company' => isset($call->company)?$call->company->name:"",
                    'responsible' => isset($call->responsible)?$call->responsible->full_name:""
                ];
            });

        return response()->json(['calls' => $calls], 200);
    }

    /**
     * Get opportunity call
     *
     * @Get("/opportunity_call")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo", "opportunity_id":"1","call_id":"1"}),
     *       @Response(200, body={"calls": {
    "id": 1,
    "date": "2015-10-15",
    "call_summary": "Call summary",
    "company": "Company",
    "responsible": "User",
    }}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */

    public function opportunityCall(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('opportunities.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $opportunity = Opportunity::find($request->opportunity_id);
        $calls = $opportunity->calls()->where('call_id', $request->call_id)
            ->with('responsible', 'company')
            ->get()
            ->map(function ($call) {
                return [
                    'id' => $call->id,
                    'date' => $call->date,
                    'call_summary' => $call->call_summary,
                    'company' => isset($call->company)?$call->company->name:"",
                    'responsible' => isset($call->responsible)?$call->responsible->full_name:""
                ];
            });

        return response()->json(['calls' => $calls], 200);
    }

    /**
     * Post opportunity call
     *
     * @Post("/post_opportunity_call")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "opportunity_id":"1","date":"2015-10-11", "call_summary":"call summary","company_id":"1","resp_staff_id":"12"}),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function postOpportunityCall(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('opportunities.write')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }

	    $data = array(
            'opportunity_id' => $request->input('opportunity_id'),
            'date' => $request->input('date'),
            'call_summary' => $request->input('call_summary'),
            'company_id' => $request->input('company_id'),
            'resp_staff_id' => $request->input('resp_staff_id'),
        );
        $rules = array(
            'date' => 'required|date_format:"' . Settings::get('date_format') . '"',
            'opportunity_id' => "required",
            'call_summary' => 'required',
            'company_id' => 'required',
            'resp_staff_id' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {

            $opportunity = Opportunity::find($request->opportunity_id);
            $call = $opportunity->calls()->create($request->except('opportunity_id', 'token'), ['user_id' => $this->user->id]);
            $call->user_id = $this->user->id;
            $call->save();

            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Edit opportunity call
     *
     * @Post("/edit_opportunity_call")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo", "opportunity_id":"1","lead_id":"1","date":"2015-10-11", "call_summary":"call summary","company_id":"1","resp_staff_id":"12"}),
     *       @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */
    public function editOpportunityCall(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('opportunities.write')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'call_id' => $request->input('call_id'),
            'opportunity_id' => $request->input('opportunity_id'),
            'date' => $request->input('date'),
            'call_summary' => $request->input('call_summary'),
            'company_id' => $request->input('company_id'),
            'resp_staff_id' => $request->input('resp_staff_id'),
        );
        $rules = array(
            'call_id' => 'required',
            'opportunity_id' => "required",
            'date' => 'required|date_format:"' . Settings::get('date_format') . '"',
            'call_summary' => 'required',
            'company_id' => 'required',
            'resp_staff_id' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $call = Call::find($request->call_id);
            $call->update($request->except('token', 'call_id', 'opportunity_id'));

            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Delete opportunity call
     *
     * @Post("/delete_opportunity_call")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "call_id":"1"}),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function deleteOpportunityCall(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('opportunities.delete')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'call_id' => $request->input('call_id'),
        );
        $rules = array(
            'call_id' => 'required|integer',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $call = Call::find($request->call_id);
            $call->delete();
            return response()->json(['success' => "success"], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }


    /**
     * Get all opportunities
     *
     * @Get("/opportunities")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo"}),
     *      @Response(200, body={
    "opportunities": {
    {
    "id": 1,
    "opportunity": "Opportunity",
    "company": "Company",
    "next_action": "2015-12-22",
    "stages": "Stages",
    "expected_revenue": "Expected revenue",
    "probability": "probability",
    "salesteam": "salesteam",
    "calls": "5",
    "meetings": "5"
    }
    }
    }),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function opportunities()
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('opportunities.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $opportunities = Opportunity::whereHas('user', function ($q) {
            $q->where(function ($query) {
                $query
                    ->orWhere('id', $this->user->parent->id)
                    ->orWhere('users.user_id', $this->user->parent->id);
            });
            })
            ->with('salesteam', 'customer', 'calls', 'meetings')
            ->get()
            ->map(function ($opportunity) {
                return [
                    'id' => $opportunity->id,
                    'opportunity' => $opportunity->opportunity,
                    'company' => isset($opportunity->customer) ? $opportunity->customer->full_name : '',
                    'next_action' => $opportunity->next_action,
                    'stages' => $opportunity->stages,
                    'expected_revenue' => $opportunity->expected_revenue,
                    'probability' => $opportunity->probability,
                    'salesteam' => isset($opportunity->salesteam) ? $opportunity->salesTeam : '',
                    'calls' => $opportunity->calls->count(),
                    'meetings' => $opportunity->meetings->count()
                ];
            });

        return response()->json(['opportunities' => $opportunities], 200);
    }

    /**
     * Get opportunity item
     *
     * @Get("/opportunity")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo", "opportunity_id":"1"}),
     *       @Response(200, body={"opportunity": {
    "id" : 1,
    "opportunity" : "r dfgfdg dfg",
    "stages" : "New",
    "customer_id" : 1,
    "expected_revenue" : "sad asd ",
    "probability" : "0",
    "email" : "admin@gmail.com",
    "phone" : 787889,
    "sales_person_id" : 2,
    "sales_team_id" : 1,
    "next_action" : "21.12.2015.",
    "next_action_title" : "454545",
    "expected_closing" : "29.12.2015.",
    "priority" : "Low",
    "tags" : "1,3",
    "lost_reason" : "Too expensive",
    "internal_notes" : "ghkhjkhjk",
    "assigned_partner_id" : 1,
    "user_id" : 1,
    "created_at" : "2015-12-22 20:17:20",
    "updated_at" : "2015-12-22 20:19:11",
    "deleted_at" : null
    }}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */
    public function opportunity(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('opportunities.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'opportunity_id' => $request->input('opportunity_id'),
        );
        $rules = array(
            'opportunity_id' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $opportunity = Opportunity::find($request->opportunity_id)
                ->get()
                ->map(function ($opportunity) {
                    return [
                        'id' => $opportunity->id,
                        'opportunity' => $opportunity->opportunity,
                        'company' => isset(User::find($opportunity->customer_id)->full_name)?User::find($opportunity->customer_id)->full_name:"",
                        'next_action' => $opportunity->next_action,
                        'next_action_title' => $opportunity->next_action_title,
                        'email' => $opportunity->email,
                        'phone' => $opportunity->phone,
                        'priority' => $opportunity->priority,
                        'stages' => $opportunity->stages,
                        'expected_revenue' => $opportunity->expected_revenue,
                        'probability' => $opportunity->probability,
                        'salesteam' => isset(Salesteam::find($opportunity->sales_team_id)->salesteam)?Salesteam::find($opportunity->sales_team_id)->salesteam:"",
                        'calls' => $opportunity->calls->count(),
                        'meetings' => $opportunity->meetings->count(),
                        'sales_person' => isset(User::find($opportunity->sales_person_id)->full_name)? User::find($opportunity->sales_person_id)->full_name:""
                    ];
                });

            // $opportunity = Opportunity::find($request->opportunity_id);
            return ['opportunity' => $opportunity];
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Post opportunity
     *
     * @Post("/post_opportunity")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "opportunity":"Opportunity", "stages" : "New", "email":"email@email.com","customer":"1","sales_team_id":"1","next_action":"2015-11-11","expected_closing":"2015-11-11"}),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function postOpportunity(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('opportunities.write')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
        /*
	    $data = array(
            'opportunity' => $request->input('fullname'),
            'lead_id' => $request->input('lead_id'),
            'next_action' => $request->input('next_action')
        ); */
        $data = array(
            'opportunity' => $request->input('fullname'),
            'lead_id'=>$request->input('lead_id'),
            'email' => $this->leadRepository->findByField('id', $request->lead_id)->pluck('email')->first(),
            'stages' => "Won",
            'quantity' => $request->input('quantity'),
            'sales_person_id' => $this->leadRepository->findByField('id', $request->lead_id)->pluck('sales_person_id')->first(),
            'user_id' =>$this->user->id,
            'next_action'=>date('Y-m-d H:i:s'),
            'tags'=>$request->tags
        );
        $rules = array(
            'opportunity' => 'required',
            'next_action' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            //$opportunity = new Opportunity($request->except('token'));
            /*
            $opportunity->opportunity=$request->fullname;
            $opportunity->lead_id=$request->lead_id;
            $opportunity->email= $this->leadRepository->findByField('id', $request->lead_id)->pluck('email');
            $opportunity->stages="Won";
            $opportunity->quantity=$request->quantity;
            $opportunity->next_action=$request->next_action;
            $opportunity->sales_person_id=$request->sales_person_id;
            $opportunity->tags = $request->tags;//implode(',', $request->tags); */
            //}
            Opportunity::insert($data);
           // $this->user->opportunities()->save($opportunity);

            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Edit opportunity
     *
     * @Post("/edit_opportunity")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo","opportunity_id":1,"stages" : "New", "opportunity":"Opportunity", "email":"email@email.com","customer":"1","sales_team_id":"1","next_action":"2015-11-11","expected_closing":"2015-11-11"}),
     *       @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */
    public function editOpportunity(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('opportunities.write')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'opportunity_id' => $request->input('opportunity_id'),
            'opportunity' => $request->input('opportunity'),
            'stages' => $request->input('stages'),
            'email' => $request->input('email'),
            'customer_id' => $request->input('customer_id'),
            'sales_team_id' => $request->input('sales_team_id'),
            'next_action' => $request->input('next_action'),
            'expected_closing' => $request->input('expected_closing'),
        );
        $rules = array(
            'opportunity_id' => 'required',
            'opportunity' => 'required',
            'email' => 'required|email',
            'stages' => 'required',
            'customer_id' => 'required',
            'sales_team_id' => 'required',
            'next_action' => 'required',
            'expected_closing' => 'required'
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {

            $opportunity = Opportunity::find($request->opportunity_id);
            if (isset($request->tags)) {
                $opportunity->tags = implode(',', $request->tags);
            }
            $opportunity->update($request->except('token', 'tags', 'opportunity_id'));

            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Delete opportunity
     *
     * @Post("/delete_opportunity")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "opportunity_id":"1"}),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function deleteOpportunity(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('opportunities.delete')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'opportunity_id' => $request->input('opportunity_id'),
        );
        $rules = array(
            'opportunity_id' => 'required|integer',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $opportunity = Opportunity::find($request->opportunity_id);
	        $opportunity->calls()->delete();
	        $opportunity->meetings()->delete();
            $opportunity->delete();
            return response()->json(['success' => "success"], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }


    /**
     * Get all opportunity meetings
     *
     * @Get("/opportunity_meetings")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo","opportunity_id":"1"}),
     *      @Response(200, body={
				    "salesteam": {
								    {
									    "id": 1,
									    "meeting_subject": "meeting subject",
									    "starting_date": "2015-12-22",
									    "ending_date": "2015-12-22",
									    "responsible": "User name"
								    },
                                   {
									    "id": 1,
									    "meeting_subject": "meeting subject",
									    "starting_date": "2015-12-22",
									    "ending_date": "2015-12-22",
									    "responsible": "User name"
									}
				                }
            }),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function opportunityMeetings(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('meetings.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $opportunity = Opportunity::find($request->opportunity_id);
        $meetings = $opportunity->meetings()
            ->with('responsible')
            ->get()
            ->map(function ($meeting) {
                return [
                    'id' => $meeting->id,
                    'meeting_subject' => $meeting->meeting_subject,
                    'starting_date' => $meeting->starting_date,
                    'ending_date' => $meeting->ending_date,
                    'responsible' => isset($meeting->responsible) ? $meeting->responsible->full_name : 'N/A'
                ];
            });

        return response()->json(['meetings' => $meetings], 200);
    }

    /**
     * Get opportunity meeting
     *
     * @Get("/opportunity_meeting")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo", "opportunity_id":"1","meeting_id":"1"}),
     *       @Response(200, body={"meetings": {
    "id": 1,
    "meeting_subject": "meeting subject",
    "starting_date": "2015-12-22",
    "ending_date": "2015-12-22",
    "responsible": "User name"
    }}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */

    public function opportunityMeeting(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('meetings.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $opportunity = Opportunity::find($request->opportunity_id);
        $meetings = $opportunity->meetings()->where('meeting_id', $request->meeting_id)
            ->with('responsible')
            ->get()
            ->map(function ($meeting) {
                return [
                    'id' => $meeting->id,
                    'meeting_subject' => $meeting->meeting_subject,
                    'starting_date' => $meeting->starting_date,
                    'ending_date' => $meeting->ending_date,
                    'responsible' => isset($meeting->responsible) ? $meeting->responsible->full_name : 'N/A'
                ];
            });

        return response()->json(['meetings' => $meetings], 200);
    }

    /**
     * Post opportunity meeting
     *
     * @Post("/post_opportunity_meeting")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "opportunity_id":1, "meeting_subject":"Subject", "starting_date":"2015-11-11 10:15AM","ending_date":"2015-11-11 10:30AM","responsible_id":1}),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function postOpportunityMeeting(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('meetings.write')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'opportunity_id' => $request->input('opportunity_id'),
            'meeting_subject' => $request->input('meeting_subject'),
            'starting_date' => $request->input('starting_date'),
            'ending_date' => $request->input('ending_date'),
            'responsible_id' => $request->input('responsible_id'),
        );
        $rules = array(
            'opportunity_id' => 'required',
            'meeting_subject' => 'required',
            'responsible_id' => 'required',
            'starting_date' => 'required|date_format:"'.Settings::get('date_format').' '.Settings::get('time_format').'"',
            'ending_date' => 'required|date_format:"'.Settings::get('date_format').' '.Settings::get('time_format').'"',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {

            $opportunity = Opportunity::find($request->opportunity_id);
            $request->merge([
                'attendees' => implode(',', $request->get('attendees', []))
            ]);
            $opportunity->meetings()->create($request->except('opportunity_id', 'token'), ['user_id' => $this->user->id]);
            $opportunity->user_id = $this->user->id;
            $opportunity->save();

            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Edit opportunity meeting
     *
     * @Post("/edit_opportunity_meeting")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo","meeting_id":1, "opportunity_id":1, "meeting_subject":"Subject", "starting_date":"2015-11-11 10:15AM","ending_date":"2015-11-11 10:30AM","responsible_id":1}),
     *       @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */
    public function editOpportunityMeeting(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('meetings.write')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'meeting_id' => $request->input('meeting_id'),
            'opportunity_id' => $request->input('opportunity_id'),
            'meeting_subject' => $request->input('meeting_subject'),
            'starting_date' => $request->input('starting_date'),
            'ending_date' => $request->input('ending_date'),
            'responsible_id' => $request->input('responsible_id'),
        );
        $rules = array(
            'meeting_id' => 'required',
            'opportunity_id' => 'required',
            'meeting_subject' => 'required',
            'starting_date' => 'required|date_format:"'.Settings::get('date_format').' '.Settings::get('time_format').'"',
            'ending_date' => 'required|date_format:"'.Settings::get('date_format').' '.Settings::get('time_format').'"',
            'responsible_id' => 'required'
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {

            $meeting = Meeting::find($request->meeting_id);
            $meeting->attendees = implode(',', $request->get('attendees', []));
            $meeting->update($request->except('opportunity_id', 'attendees', 'meeting_id', 'token'));

            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Delete opportunity meeting
     *
     * @Post("/delete_opportunity_meeting")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "meeting_id":"1"}),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function deleteOpportunityMeeting(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('meetings.delete')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'meeting_id' => $request->input('meeting_id'),
        );
        $rules = array(
            'meeting_id' => 'required|integer',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $meeting = Meeting::find($request->meeting_id);
            $meeting->delete();
            return response()->json(['success' => "success"], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }


    /**
     * Get all products
     *
     * @Get("/products")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo"}),
     *      @Response(200, body={
    "products": {
    {
    "id": 1,
    "product_name": "product name",
    "name": "category",
    "product_type": "Type",
    "status": "1",
    "quantity_on_hand": "12",
    "quantity_available": "52"
    }
    }
    }),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function products(Request $request)
    {
        $partner_id=0;
        $this->user = JWTAuth::parseToken()->authenticate();
        
        $partner_id=$this->user->partner_id;
        /*
        $listPartnerUser=PartnerUser::select('partner_id')->where('user_id',$this->user->id)->pluck('partner_id')->toArray();
        if(!in_array($partner_id, $listPartnerUser)){
            array_push($listPartnerUser, $partner_id);
        } */
        
        $keyword=$request->keyword;
        $productsData = Product::with('category')->where('partner_id',$partner_id)
        ->where(function ($query) use ($keyword) {
                if($keyword!=""){
                    $query->where('product_name','like','%'.$keyword.'%');
                }
        })
        ->offset(0)
        ->limit(20)
        ->get();
        $products=[];
        if(isset($productsData) && count($productsData)>0){
            $products=$productsData->map(function ($p) {
                return [
                    'id' => $p->id,
                    'product_name' => $p->product_name,
                    'name' => $p->category->name,
                    'product_type' => $p->product_type,
                    'status' => $p->status,
                    'quantity_on_hand' => $p->quantity_on_hand,
                    'quantity_available' => $p->quantity_available,
                ];
            });
        }
           

        return response()->json(['products' => $products], 200);
    }

    public function productsList()
    {
        $partner_id=0;
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('products.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
        }
        $partner_id=$this->user->partner_id;
        $listPartnerUser=PartnerUser::select('partner_id')->where('user_id',$this->user->id)->pluck('partner_id')->toArray();
        if(!in_array($partner_id, $listPartnerUser)){
            array_push($listPartnerUser, $partner_id);
        }
	    $products = Product::with('category')->whereIn('partner_id',$listPartnerUser)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'product_name' => $p->product_name,
                    'name' => $p->category->name,
                    'product_type' => $p->product_type,
                    'status' => $p->status,
                    'quantity_on_hand' => $p->quantity_on_hand,
                    'quantity_available' => $p->quantity_available,
                ];
            });

        return response()->json(['products' => $products], 200);
    }

    /**
     * Get product item
     *
     * @Get("/product")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo", "product_id":"1"}),
     *       @Response(200, body={"product": {
    "id" : 1,
    "product_name" : "product",
    "product_image" : "",
    "category_id" : 1,
    "product_type" : "Consumable",
    "status" : "In Development",
    "quantity_on_hand" : 12,
    "quantity_available" : 22,
    "sale_price" : 1.0,
    "description" : "sdfdsfsdf",
    "description_for_quotations" : "sdfsdfsdfsdf",
    "user_id" : 1,
    "created_at" : "2015-12-23 16:58:51",
    "updated_at" : "2015-12-26 07:24:51",
    "deleted_at" : null
    }}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */
    public function product(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('products.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'product_id' => $request->input('product_id'),
        );
        $rules = array(
            'product_id' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $product = Product::find($request->get('product_id'))
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'product_name' => $product->product_name,
                        'product_image' => $product->product_image,
                        'category_id' => Category::find($product->category_id)->name,
                        'product_type' => $product->product_type,
                        'status' => $product->status,
                        'quantity_on_hand' => $product->quantity_on_hand,
                        'quantity_available' => $product->quantity_available,
                        'sale_price' => $product->sale_price,
                        'description' => $product->description,

                    ];
                });
            return response()->json(['product' => $product], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Post product
     *
     * @Post("/post_product")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo","product_name":"product name", "sale_price":"15.2","description":"sadsadsd","quantity_on_hand":"12","quantity_available":"11"}),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function postProduct(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('products.write')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'product_name' => $request->input('product_name'),
            'sale_price' => $request->input('sale_price'),
            'description' => $request->input('description'),
            'quantity_on_hand' => $request->input('quantity_on_hand'),
            'quantity_available' => $request->input('quantity_available'),
            'product_type' => $request->input('product_type'),
            'status' => $request->input('status'),
            'category_id' => $request->input('category_id'),
        );
        $rules = array(
            'product_name' => "required",
            'sale_price' => "required",
            'description' => "required",
            'quantity_on_hand' => "required",
            'quantity_available' => "required",
            'product_type' => "required",
            'status' => "required",
            'category_id' => "required",
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {

            $product = new Product($request->except('token'));
            $this->user->products()->save($product);

            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Edit product
     *
     * @Post("/edit_product")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo", "product_id":"1","product_name":"product name", "sale_price":"15.2","description":"sadsadsd","quantity_on_hand":"12","quantity_available":"11"}),
     *       @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */
    public function editProduct(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('products.write')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'product_id' => $request->input('product_id'),
            'product_name' => $request->input('product_name'),
            'sale_price' => $request->input('sale_price'),
            'description' => $request->input('description'),
            'quantity_on_hand' => $request->input('quantity_on_hand'),
            'quantity_available' => $request->input('quantity_available'),
            'product_type' => $request->input('product_type'),
            'status' => $request->input('status'),
            'category_id' => $request->input('category_id'),
        );
        $rules = array(
            'product_id' => 'required',
            'product_name' => "required",
            'sale_price' => "required",
            'description' => "required",
            'quantity_on_hand' => "required",
            'quantity_available' => "required",
            'product_type' => "required",
            'status' => "required",
            "category_id" => "required"
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $product = Product::find($request->get('product_id'));
            $product->update($request->except('token', 'product_id'));

            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Delete product
     *
     * @Post("/delete_product")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "product_id":"1"}),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function deleteProduct(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('products.delete')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'product_id' => $request->input('product_id'),
        );
        $rules = array(
            'product_id' => 'required|integer',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $product = Product::find($request->get('product_id'));
            $product->delete();
            return response()->json(['success' => "success"], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }


    /**
     * Get all qtemplates
     *
     * @Get("/qtemplates")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo"}),
     *      @Response(200, body={
    "qtemplates": {
    {
    "id": 1,
    "quotation_template": "product name",
    "quotation_duration": "10",
    }
    }
    }),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function qtemplates()
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    $qtemplates = Qtemplate::whereHas('user', function ($q) {
            $q->where(function ($query) {
                $query
                    ->orWhere('id', $this->user->parent->id)
                    ->orWhere('users.user_id', $this->user->parent->id);
            });
        })->select('id', 'quotation_template', 'quotation_duration')->get();

        return response()->json(['qtemplates' => $qtemplates], 200);
    }

    /**
     * Get qtemplate item
     *
     * @Get("/qtemplate")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo", "qtemplate_id":"1"}),
     *       @Response(200, body={"qtemplate": {
    "id" : 1,
    "quotation_template" : "testaa",
    "quotation_duration" : 19,
    "immediate_payment" : 0,
    "terms_and_conditions" : "sd f sdf 22",
    "total" : 2553.0,
    "tax_amount" : 408.48,
    "grand_total" : 2961.48,
    "user_id" : 1,
    "created_at" : "2015-12-23 18:45:58",
    "updated_at" : "2015-12-23 18:46:21",
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
    public function qtemplate(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $data = array(
            'qtemplate_id' => $request->input('qtemplate_id'),
        );
        $rules = array(
            'qtemplate_id' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $qtemplate = Qtemplate::find($request->qtemplate_id);
            $products = array();
            if ($qtemplate->products->count() > 0) {
                foreach ($qtemplate->products as $index => $variants) {
                    $products[] = ['product' => $variants->product_name,
                        'description' => $variants->description,
                        'quantity' => $variants->quantity,
                        'unit_price' => $variants->price,
                        'taxes' => number_format($variants->quantity * $variants->price * floatval(Settings::get('sales_tax')) / 100, 2,
                            '.', ''),
                        'subtotal' => $variants->sub_total];
                }
            }
            return response()->json(['qtemplate' => $qtemplate->toArray(), 'products' => $products], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Post qtemplate
     *
     * @Post("/post_qtemplate")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo","product_name":"product name", "sale_price":"15.2","description":"sadsadsd","quantity_on_hand":"12","quantity_available":"11","total":"10.00","tax_amount":"1.11","grand_total":"11.11"}),
     *      @Response(200, body={"success":"success"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function postQtemplate(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $data = array(
            'quotation_template' => $request->input('quotation_template'),
            'quotation_duration' => $request->input('quotation_duration'),
            'total' => $request->input('total'),
            'tax_amount' => $request->input('tax_amount'),
            'grand_total' => $request->input('grand_total'),
        );
        $rules = array(
            'quotation_template' => 'required',
            'quotation_duration' => "required",
            'total' => "required",
            'tax_amount' => "required",
            'grand_total' => "required",
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {

	        $qtemplate = new Qtemplate($request->only('quotation_template', 'quotation_duration', 'total',
		        'tax_amount', 'grand_total'));

            $this->user->qtemplates()->save($qtemplate);

            if (!empty($request->get('product_id'))) {
                foreach ($request->get('product_id') as $key => $item) {
	                if ($item != "" && $request->get('product_name')[$key] != "" && $request->get('description')[$key] != "" &&
	                    $request->get('quantity')[$key] != "" && $request->get('price')[$key] != "" && $request->get('sub_total')[$key] != ""
	                ) {
                        $qtemplateProduct = new QtemplateProduct();
                        $qtemplateProduct->qtemplate_id = $qtemplate->id;
                        $qtemplateProduct->product_id = $item;
                        $qtemplateProduct->product_name = $request->get('product_name')[$key];
                        $qtemplateProduct->description = $request->get('description')[$key];
                        $qtemplateProduct->quantity = $request->get('quantity')[$key];
                        $qtemplateProduct->price = $request->get('price')[$key];
                        $qtemplateProduct->sub_total = $request->get('sub_total')[$key];
                        $qtemplateProduct->save();
                    }
                }
            }

            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Edit qtemplate
     *
     * @Post("/edit_qtemplate")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo", "qtemplate_id":"1","product_name":"product name", "sale_price":"15.2","description":"sadsadsd","quantity_on_hand":"12","quantity_available":"11","total":"10.00","tax_amount":"1.11","grand_total":"11.11"}),
     *       @Response(200, body={"success":"success"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */
    public function editQtemplate(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $data = array(
            'qtemplate_id' => $request->input('qtemplate_id'),
            'quotation_template' => $request->input('quotation_template'),
            'quotation_duration' => $request->input('quotation_duration'),
            'total' => $request->input('total'),
            'tax_amount' => $request->input('tax_amount'),
            'grand_total' => $request->input('grand_total'),
        );
        $rules = array(
            'qtemplate_id' => 'required',
            'quotation_template' => 'required',
            'quotation_duration' => "required",
            'total' => "required",
            'tax_amount' => "required",
            'grand_total' => "required",
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $qtemplate = Qtemplate::find($request->qtemplate_id);
            $qtemplate->update($request->only('quotation_template', 'quotation_duration', 'total',
	            'tax_amount', 'grand_total'));

            QtemplateProduct::where('qtemplate_id', $qtemplate->id)->delete();

            if (!empty($request->get('product_id'))) {
                foreach ($request->get('product_id') as $key => $item) {
                    if ($item != "" && $request->get('product_name')[$key] != "" && $request->get('description')[$key] != "" &&
                        $request->get('quantity')[$key] != "" && $request->get('price')[$key] != "" && $request->get('sub_total')[$key] != ""
                    ) {
                        $qtemplateProduct = new QtemplateProduct();
                        $qtemplateProduct->qtemplate_id = $qtemplate->id;
                        $qtemplateProduct->product_id = $item;
                        $qtemplateProduct->product_name = $request->get('product_name')[$key];
                        $qtemplateProduct->description = $request->get('description')[$key];
                        $qtemplateProduct->quantity = $request->get('quantity')[$key];
                        $qtemplateProduct->price = $request->get('price')[$key];
                        $qtemplateProduct->sub_total = $request->get('sub_total')[$key];
                        $qtemplateProduct->save();
                    }
                }
            }

            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Delete qtemplate
     *
     * @Post("/delete_qtemplate")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "qtemplate_id":"1"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function deleteQtemplate(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $data = array(
            'qtemplate_id' => $request->input('qtemplate_id'),
        );
        $rules = array(
            'qtemplate_id' => 'required|integer',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $qtemplate = Qtemplate::find($request->qtemplate_id);
            $qtemplate->delete();
            return response()->json(['success' => "success"], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }


    /**
     * Get all quotations
     *
     * @Get("/quotations")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo"}),
     *      @Response(200, body={
    "quotations": {
    {
    "id": 1,
    "quotations_number": "4545",
    "date": "2015-11-11",
    "customer": "customer name",
    "person": "person name",
    "final_price": "12",
    "status": "1",
    }
    }
    }),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function quotations()
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('quotations.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $quotations = Quotation::whereHas('user', function ($q) {
            $q->where(function ($query) {
                $query
                    ->orWhere('id', $this->user->parent->id)
                    ->orWhere('users.user_id', $this->user->parent->id);
            });
        })
            ->with('user', 'customer')
            ->get()
            ->map(function ($quotation) {
                return [
                    'id' => $quotation->id,
                    'quotations_number' => $quotation->quotations_number,
                    'date' => $quotation->date,
                    'customer' => isset($quotation->customer)?$quotation->customer->full_name:"",
                    'person' => isset($quotation->user)?$quotation->user->full_name:"",
                    'final_price' => $quotation->final_price,
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
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */
    public function quotation(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('quotations.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'quotation_id' => $request->input('quotation_id'),
        );
        $rules = array(
            'quotation_id' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $quotation = Quotation::find($request->quotation_id)
	            ->with('customer','salesPerson','salesTeam')
                ->get()
                ->map(function ($quotation) {
                    return [
                        'id' => $quotation->id,
                        'quotations_number' => $quotation->quotations_number,
                        'company' => isset($quotation->customer)?$quotation->customer->full_name:"",
                        'qtemplate' => isset($quotation->qtemplate_id)?Qtemplate::find($quotation->qtemplate_id)->quotation_template:"",
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
     * Post quotation
     *
     * @Post("/post_quotation")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo","customer_id":"1", "date":"2015-11-11","qtemplate_id":"1","payment_term":"term","sales_person_id":"1","sales_team_id":"1","grand_total":"12.5","discount":"10.2","final_price":"10.25","quotation_prefix":"Q00","quotation_start_number":"0"}),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function postQuotation(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('quotations.write')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'customer_id' => $request->input('customer_id'),
            'date' => $request->input('date'),
            'exp_date' => $request->input('exp_date'),
            'qtemplate_id' => $request->input('qtemplate_id'),
            'payment_term' => $request->input('payment_term'),
            'sales_person_id' => $request->input('sales_person_id'),
            'sales_team_id' => $request->input('sales_team_id'),
            'status' => $request->input('status'),
            'grand_total' => $request->input('grand_total'),
            'discount' => $request->input('discount'),
            'final_price' => $request->input('final_price'),
            'total' => $request->input('total'),
            'tax_amount' => $request->input('tax_amount'),
            'quotation_prefix' => $request->input('quotation_prefix'),
            'quotation_start_number' => $request->input('quotation_start_number'),
        );
        $rules = array(
            'customer_id' => 'required',
            'exp_date' => 'date_format:"' . Settings::get('date_format') . '"',
            'date' => 'required|date_format:"' . Settings::get('date_format') . '"',
            'qtemplate_id' => 'required',
            'payment_term' => 'required',
            'sales_person_id' => 'required',
            'sales_team_id' => 'required',
            'status' => 'required',
            'grand_total' => 'required',
            'tax_amount' => 'required',
            'discount' => 'required',
            'final_price' => 'required',
            'total' => 'required',
            'quotation_prefix' => 'required',
            'quotation_start_number' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {

            $total_fields = Quotation::whereNull('deleted_at')->orWhereNotNull('deleted_at')->orderBy('id', 'desc')->first();
            $quotation_no = $request->quotation_prefix . ($request->quotation_start_number + (isset($total_fields) ? $total_fields->id : 0) + 1);
            $exp_date = date(Settings::get('date_format'), strtotime(' + ' . isset($request->payment_term) ? $request->payment_term : 0 . ' days'));


            $quotation = new Quotation($request->only('customer_id', 'qtemplate_id', 'date',
                'exp_date', 'payment_term', 'sales_person_id', 'sales_team_id', 'terms_and_conditions', 'status', 'total',
                'tax_amount', 'grand_total', 'discount', 'final_price'));
            $quotation->quotations_number = $quotation_no;
            $quotation->exp_date = isset($request->exp_date) ? $request->exp_date : strtotime($exp_date);
            $quotation->user_id = $this->user->id;
            $quotation->save();

            QuotationProduct::where('quotation_id', $quotation->id)->delete();
            if (!empty($request->get('product_id'))) {
                foreach ($request->get('product_id') as $key => $item) {
                    if ($item != "" && $request->get('product_name')[$key] != "" && $request->get('description')[$key] != "" &&
                        $request->get('quantity')[$key] != "" && $request->get('price')[$key] != "" && $request->get('sub_total')[$key] != ""
                    ) {
                        $quotationProduct = new QuotationProduct();
                        $quotationProduct->quotation_id = $quotation->id;
                        $quotationProduct->product_id = $item;
                        $quotationProduct->product_name = $request->get('product_name')[$key];
                        $quotationProduct->description = $request->get('description')[$key];
                        $quotationProduct->quantity = $request->get('quantity')[$key];
                        $quotationProduct->price = $request->get('price')[$key];
                        $quotationProduct->sub_total = $request->get('sub_total')[$key];
                        $quotationProduct->save();
                    }
                }
            }
            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Edit quotation
     *
     * @Post("/edit_quotation")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo", "quotation_id":"1","customer_id":"1", "date":"2015-11-11","qtemplate_id":"1","payment_term":"term","sales_person":"1","sales_team_id":"1","grand_total":"12.5","discount":"10.2","final_price":"10.25"}),
     *       @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */
    public function editQuotation(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('quotations.write')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'quotation_id' => $request->input('quotation_id'),
            'customer_id' => $request->input('customer_id'),
            'date' => $request->input('date'),
            'exp_date' => $request->input('exp_date'),
            'qtemplate_id' => $request->input('qtemplate_id'),
            'payment_term' => $request->input('payment_term'),
            'sales_person_id' => $request->input('sales_person_id'),
            'sales_team_id' => $request->input('sales_team_id'),
            'grand_total' => $request->input('grand_total'),
            'discount' => $request->input('discount'),
            'final_price' => $request->input('final_price'),
        );
        $rules = array(
            'quotation_id' => 'required',
            'customer_id' => 'required',
            'exp_date' => 'date_format:"' . Settings::get('date_format') . '"',
            'date' => 'required|date_format:"' . Settings::get('date_format') . '"',
            'qtemplate_id' => 'required',
            'payment_term' => 'required',
            'sales_person_id' => 'required',
            'sales_team_id' => 'required',
            'grand_total' => 'required',
            'discount' => 'required',
            'final_price' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {

            $quotation = Quotation::find($request->quotation_id);

            $quotation->update($request->only('customer_id', 'qtemplate_id', 'date',
                'exp_date', 'payment_term', 'sales_person_id', 'sales_team_id', 'terms_and_conditions', 'status', 'total',
                'tax_amount', 'grand_total', 'discount', 'final_price'));

            QuotationProduct::where('quotation_id', $quotation->id)->delete();
            if (!empty($request->get('product_id'))) {
                foreach ($request->get('product_id') as $key => $item) {
                    if ($item != "" && $request->get('product_name')[$key] != "" && $request->get('description')[$key] != "" &&
                        $request->get('quantity')[$key] != "" && $request->get('price')[$key] != "" && $request->get('sub_total')[$key] != ""
                    ) {
                        $quotationProduct = new QuotationProduct();
                        $quotationProduct->quotation_id = $quotation->id;
                        $quotationProduct->product_id = $item;
                        $quotationProduct->product_name = $request->get('product_name')[$key];
                        $quotationProduct->description = $request->get('description')[$key];
                        $quotationProduct->quantity = $request->get('quantity')[$key];
                        $quotationProduct->price = $request->get('price')[$key];
                        $quotationProduct->sub_total = $request->get('sub_total')[$key];
                        $quotationProduct->save();
                    }
                }
            }
            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Delete quotation
     *
     * @Post("/delete_quotation")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "quotation_id":"1"}),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function deleteQuotation(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('quotations.delete')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'quotation_id' => $request->input('quotation_id'),
        );
        $rules = array(
            'quotation_id' => 'required|integer',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $quotation = Quotation::find($request->quotation_id);
            $quotation->delete();
            return response()->json(['success' => "success"], 200);
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
    "salesorders": {
    {
    "id": 1,
    "quotations_number": "product name",
    "date": "2015-11-11",
    "customer": "customer name",
    "person": "sales person name",
    "final_price": "12.53",
    "status": "1",
    }
    }
    }),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function salesOrders()
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('sales_orders.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $salesorder = Saleorder::whereHas('user', function ($q) {
            $q->where(function ($query) {
                $query
                    ->orWhere('id', $this->user->parent->id)
                    ->orWhere('users.user_id', $this->user->parent->id);
            });
        })
            ->with('user', 'customer')
            ->get()
            ->map(function ($quotation) {
                return [
                    'id' => $quotation->id,
                    'quotations_number' => $quotation->quotations_number,
                    'date' => $quotation->date,
                    'customer' => is_null($quotation->customer)?"":$quotation->customer->full_name,
                    'person' => is_null($quotation->user)?"":$quotation->user->full_name,
                    'final_price' => $quotation->final_price,
                    'status' => $quotation->status
                ];
            });

        return response()->json(['salesorders' => $salesorder], 200);
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
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */
    public function salesorder(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('sales_orders.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'salesorder_id' => $request->input('salesorder_id'),
        );
        $rules = array(
            'salesorder_id' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $salesorder = Saleorder::find($request->salesorder_id)
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
     * Post Sales Order
     *
     * @Post("/post_sales_order")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo","customer_id":"1", "date":"2015-11-11","qtemplate_id":"1","payment_term":"term","sales_person_id":"1","sales_team_id":"1","grand_total":"12.5","discount":"10.2","final_price":"10.25","sales_prefix":"S00","sales_start_number":"0","status" : "Draft sales order"}),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function postSalesOrder(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('sales_orders.write')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'lead_id' => $request->input('lead_id'),
            'date' => date("Y-m-d H:i:s"),
            'payment_term' => $request->input('tags'),
            'sales_team_id' => 0,
            'status' => 1
            //"sales_prefix" => $request->input('sales_prefix'),
         //   "sales_start_number" => $request->input('sales_start_number'),
        );
        $rules = array(
            'lead_id' => 'required',
            'payment_term' => 'required',
            'status' => 'required',
          //  'sales_prefix' => 'required',
           // 'sales_start_number' => 'required'
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {

            $total_fields = Saleorder::whereNull('deleted_at')->orWhereNotNull('deleted_at')->orderBy('id', 'desc')->first();

        
            //$sale_no = $request->input('sales_prefix') . ($request->input('sales_start_number') + (isset($total_fields) ? $total_fields->id : 0) + 1);
            $sale_no = "FASTERCRM".((isset($total_fields) ? $total_fields->id : 0) + 1);
            
            $exp_date = date("Y-m-d", strtotime("+3 day"));
            
            $saleorder = new Saleorder();   
            $saleorder->sale_number = $sale_no;
            
            $saleorder->lead_id = $data["lead_id"];
            $saleorder->date = date("Y-m-d H:i:s");
            $saleorder->qtemplate_id = 0;
            $saleorder->payment_term = $data["payment_term"];
            $saleorder->sales_person_id = $this->user->id;
            $saleorder->terms_and_conditions = "";
            $saleorder->total = 0;
            $saleorder->tax_amount =0;
            $saleorder->grand_total = 0;
            $saleorder->discount = 0;
            $saleorder->final_price =0;
            $saleorder->status = 'Order from App';
            $saleorder->user_id = $this->user->id;
            $saleorder->partner_id = $this->user->partner_id;
			$saleorder->save();
            $saleorder_id = $saleorder->id;
            /*
            $saleorder = Saleorder::create([
				'sale_number' => $sale_no,
				'lead_id' => $data["lead_id"],
				'date' => date("Y-m-d H:i:s"),
				'qtemplate_id' => 0,
				'payment_term' => $data["payment_term"],
				"sales_person_id" => $this->user->id,
				"terms_and_conditions" => "",
				"total" => 0,
				"tax_amount" => 0,
				"grand_total" => 0,
				"discount" => 0,
				"final_price" =>0,
				'status' => 'Order from App',
                'user_id' => $this->user->id,
                'partner_id' => $this->user->partner_id
            ]); */
            
            SaleorderProduct::where('saleorder_id', $saleorder->id)->delete();

            if (!empty($request->input('product'))) {
                //foreach ($request->input('product_id') as $key => $item) {
                    $product = Product::where('id',$request->input('product'))->first();
                    $totalPrice=$product["sales_price"]*$request->input('quantity');
                    $vat=($totalPrice*$product["vat"]);
                    $saleorderProduct = new SaleorderProduct();
                    $saleorderProduct->saleorder_id = $saleorder->id;
                    $saleorderProduct->product_id = $product["id"];
                    $saleorderProduct->product_name = $product["product_name"];
                    $saleorderProduct->description = $product["description"];
                    $saleorderProduct->quantity = $request->input('quantity');
                    $saleorderProduct->price = $product["sales_price"];
                    $saleorderProduct->sub_total = $totalPrice;
                    $saleorderProduct->save();
               // }
               $saleorderUpdate = Saleorder::find($saleorder->id);
               $saleorderUpdate->grand_total=$totalPrice;
               $saleorderUpdate->discount=($totalPrice*$request->input('tax_amount'))/100;
               $saleorderUpdate->tax_amount=$vat;
               $saleorderUpdate->final_price=$totalPrice-($totalPrice*$request->input('tax_amount'))/100+$vat;
               $saleorderUpdate->total=$totalPrice-($totalPrice*$request->input('tax_amount'))/100+$vat;
               $saleorderUpdate->update();
            }

            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Edit quotation
     *
     * @Post("/edit_sales_order")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo", "sales_order_id":"1","customer_id":"1", "date":"2015-11-11","qtemplate_id":"1","payment_term":"term","sales_person_id":"1","sales_team_id":"1","grand_total":"12.5","discount":"10.2","final_price":"10.25","status" : "Draft sales order"}),
     *       @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */
    public function editSalesOrder(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('sales_orders.write')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'sales_order_id' => $request->input('sales_order_id'),
            'customer_id' => $request->input('customer_id'),
            'date' => $request->input('date'),
            'exp_date' => $request->input('exp_date'),
            'qtemplate_id' => $request->input('qtemplate_id'),
            'payment_term' => $request->input('payment_term'),
            'sales_person_id' => $request->input('sales_person_id'),
            'sales_team_id' => $request->input('sales_team_id'),
            'grand_total' => $request->input('grand_total'),
            'discount' => $request->input('discount'),
            'final_price' => $request->input('final_price'),
        );
        $rules = array(
            'sales_order_id' => 'required',
            'customer_id' => 'required',
            'date' => 'required|date_format:"' . Settings::get('date_format') . '"',
            'exp_date' => 'date_format:"' . Settings::get('date_format') . '"',
            'qtemplate_id' => 'required',
            'payment_term' => 'required',
            'sales_person_id' => 'required',
            'sales_team_id' => 'required',
            'grand_total' => 'required',
            'discount' => 'required',
            'final_price' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $sales_order = Saleorder::find($request->sales_order_id);
            $sales_order->update($request->only('customer_id', 'qtemplate_id', 'date',
                'exp_date', 'payment_term', 'sales_person_id', 'sales_team_id', 'terms_and_conditions', 'status', 'total',
                'tax_amount', 'grand_total', 'discount', 'final_price'));

            SaleorderProduct::where('order_id', $sales_order->id)->delete();
            if (!empty($request->get('product_id'))) {
                foreach ($request->get('product_id') as $key => $item) {
                    if ($item != "" && $request->get('product_name')[$key] != "" && $request->get('description')[$key] != "" &&
                        $request->get('quantity')[$key] != "" && $request->get('price')[$key] != "" && $request->get('sub_total')[$key] != ""
                    ) {
                        $saleorderProduct = new SaleorderProduct();
                        $saleorderProduct->order_id = $sales_order->id;
                        $saleorderProduct->product_id = $item;
                        $saleorderProduct->product_name = $request->get('product_name')[$key];
                        $saleorderProduct->description = $request->get('description')[$key];
                        $saleorderProduct->quantity = $request->get('quantity')[$key];
                        $saleorderProduct->price = $request->get('price')[$key];
                        $saleorderProduct->sub_total = $request->get('sub_total')[$key];
                        $saleorderProduct->save();
                    }
                }
            }
            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }
    public function ckeckInvoice(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    $data = array(
            'product_id' => $request->input('product_id'),
            'promotion' => $request->input('promotion'),
            'quanlity' => $request->input('quanlity'),
        );
        $rules = array(
            'product_id' => 'required',
            'quanlity' => 'required',
        );
        $reportType=0;
        $report="Có thể đặt";
        $vat=0;
        $talPromotion=0;
        $totalPrice=0;
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            if (!empty($request->input('product_id'))) {
                    $product = Product::where('id',$request->input('product_id'))->first();
                    if($product){
                        if($product["quantity_available"]>=$request->input('quantity')){
                            $totalPrice=$product["sales_price"]*$request->input('quantity');
                            $talPromotion=$totalPrice-(($totalPrice*$request->input('product_id'))/100);
                            $vat=($totalPrice*$product["vat"])/100;
                        }else{
                            $report="Hết hàng";
                            $reportType=1;
                        }
                    }else{
                        $reportType=2;
                        $report="Không tìm thấy sản phẩm";
                    }
                    
            }else{
                $reportType=2;
                $report="Không tìm thấy sản phẩm";
            }
            return response()->json(['success' => $report, "reportType"=>$reportType, 'totalPrice'=>$totalPrice, 'talPromotion'=>$talPromotion, 'vat'=>$vat], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }
    /**
     * Delete quotation
     *
     * @Post("/delete_sales_order")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "sales_order_id":"1"}),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function deleteSalesOrder(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('sales_orders.delete')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'sales_order_id' => $request->input('sales_order_id'),
        );
        $rules = array(
            'sales_order_id' => 'required|integer',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $sales_order = Saleorder::find($request->sales_order_id);
            $sales_order->delete();
            return response()->json(['success' => "success"], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }


    /**
     * Get all salesteams
     *
     * @Get("/salesteams")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo"}),
     *      @Response(200, body={
    "salesteam": {
    {
    "id": 1,
    "salesteam": "Name of team",
    "target": "111",
    "invoice_forecast": "1125",
    "actual_invoice": "205",
    }
    }
    }),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function salesTeams()
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('sales_team.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $salesteam = Salesteam::whereHas('user', function ($q) {
            $q->where(function ($query) {
                $query
                    ->orWhere('id', $this->user->parent->id)
                    ->orWhere('users.user_id', $this->user->parent->id);
            });
        })->latest()->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'salesteam' => $user->salesteam,
                'target' => $user->invoice_target,
                'invoice_forecast' => $user->invoice_forecast,
                'actual_invoice' => $user->actual_invoice,
            ];
        });

        return response()->json(['salesteams' => $salesteam], 200);
    }

    /**
     * Get salesteam item
     *
     * @Get("/salesteam")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo", "salesteam_id":"1"}),
     *       @Response(200, body={"salesteam": {
    "id" : 1,
    "salesteam" : "testera tim 1",
    "team_leader" : 2,
    "quotations" : false,
    "leads" : false,
    "opportunities" : false,
    "invoice_target" : 15,
    "invoice_forecast" : 22,
    "actual_invoice" : 0,
    "notes" : "dfg fdg dfg",
    "user_id" : 1,
    "created_at" : "2015-12-22 19:47:18",
    "updated_at" : "2015-12-22 19:47:29",
    "deleted_at" : null
    }}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */
    public function salesteam(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('sales_team.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'salesteam_id' => $request->input('salesteam_id'),
        );
        $rules = array(
            'salesteam_id' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            //$salesteam = Salesteam::find($request->salesteam_id);
            $salesteam = Salesteam::find($request->salesteam_id)
	            ->with('teamLeader')
                ->get()
                ->map(function ($salesteam) {
                    return [
                        'id' => $salesteam->id,
                        'salesteam' => $salesteam->salesteam,
                        'team_leader' => isset($salesteam->teamLeader)?$salesteam->teamLeader->full_name:"",
                        'quotations' => $salesteam->quotations,
                        'leads' => $salesteam->leads,
                        'opportunities' => $salesteam->opportunities,
                        'invoice_target' => $salesteam->invoice_target,
                        'invoice_forecast' => $salesteam->invoice_forecast,
                        'actual_invoice' => $salesteam->actual_invoice,
                        'notes' => $salesteam->notes
                    ];
                });
            return response()->json(['salesteam' => $salesteam], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Post salesteam
     *
     * @Post("/post_salesteam")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo","salesteam":"Title", "invoice_target":"8","invoice_forecast":"1","team_leader":"12","team_members":"1,2,5"}),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function postSalesTeam(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('sales_teams.write')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'salesteam' => $request->input('salesteam'),
            'invoice_target' => $request->input('invoice_target'),
            'invoice_forecast' => $request->input('invoice_forecast'),
            'team_leader' => $request->input('team_leader'),
            'team_members' => $request->input('team_members'),
        );
        $rules = array(
            'salesteam' => 'required',
            'invoice_target' => 'required',
            'invoice_forecast' => 'required',
            'team_leader' => 'required',
            'team_members' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {

            $salesteam = new Salesteam($request->except('token'));
            $salesteam->team_members = implode(',', array($request->get('team_members', [])));
            //$salesteam->register_time = strtotime(date('d F Y g:i a'));
            //$salesteam->ip_address = $request->server('REMOTE_ADDR');
            $salesteam->quotations = ($request->quotations) ? $request->quotations : 0;
            $salesteam->leads = ($request->leads) ? $request->leads : 0;
            $salesteam->opportunities = ($request->opportunities) ? $request->opportunities : 0;
            //  $salesteam->status = ($request->status) ? $request->status : 0;
            $this->user->salesTeams()->save($salesteam);

            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Edit salesteam
     *
     * @Post("/edit_salesteam")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo", "salesteam_id":"1","salesteam":"Title", "invoice_target":"8","invoice_forecast":"1","team_leader":"12","team_members":"1,2,5"}),
     *       @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */
    public function editSalesTeam(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('sales_teams.write')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'salesteam_id' => $request->input('salesteam_id'),
            'salesteam' => $request->input('salesteam'),
            'invoice_target' => $request->input('invoice_target'),
            'invoice_forecast' => $request->input('invoice_forecast'),
            'team_leader' => $request->input('team_leader'),
            'team_members' => $request->input('team_members'),
        );
        $rules = array(
            'salesteam_id' => 'required',
            'salesteam' => 'required',
            'invoice_target' => 'required',
            'invoice_forecast' => 'required',
            'team_leader' => 'required',
            'team_members' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $salesteam = Salesteam::find($request->salesteam_id);
            $salesteam->team_members = implode(',', array($request->get('team_members', [])));
            $salesteam->quotations = ($request->quotations) ? $request->quotations : 0;
            $salesteam->leads = ($request->leads) ? $request->leads : 0;
            $salesteam->opportunities = ($request->opportunities) ? $request->opportunities : 0;
//            $salesteam->status = ($request->status) ? $request->status : 0;
            $salesteam->update($request->except('token', 'team_members', 'quotations', 'leads', 'opportunities', 'status', 'salesteam_id'));

            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Delete salesteam
     *
     * @Post("/delete_salesteam")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "salesteam_id":"1"}),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function deleteSalesTeam(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('sales_teams.delete')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'salesteam_id' => $request->input('salesteam_id'),
        );
        $rules = array(
            'salesteam_id' => 'required|integer',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $salesteam = Salesteam::find($request->salesteam_id);
            $salesteam->delete();
            return response()->json(['success' => "success"], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }


    /**
     * Get all staff
     *
     * @Get("/staffs")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo"}),
     *      @Response(200, body={
    "staffs": {
    {
    "id": 1,
    "full_name": "product name",
    "email": "email@email.com",
    "created_at": "2015-11-11"
    }
    }
    }),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function staffs()
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
        $partner_id=$this->user->partner_id;
        /*
        $staffs=User::join('partner_user','partner_user.user_id','=','users.id')
                                ->select('users.id', 'users.first_name', 'users.last_name', 'users.user_avatar')
                                ->where('partner_user.partner_id','=',$partner_id)
                               // ->whereIn('users.id',$listUser)
                                ->get()
                                ->map( function ( $salesList ) {
                                    return [ 
                                        'id' => $salesList->id,
                                        'full_name' => $salesList->first_name." ".$salesList->last_name,
                                        'email' => $salesList->first_name." ".$salesList->last_name,
                                    ];
                                } )->pluck( 'title', 'value')
                                ->prepend(trans('lead.all'), '');
        $user = JWTAuth::parseToken()->authenticate(); */
        $branchesshow=array();
        $staffs=User::join('partner_user','partner_user.user_id','=','users.id')
                                ->select('users.id', 'users.first_name', 'users.last_name', 'users.user_avatar')
                                ->where('partner_user.partner_id','=',$partner_id)
                               // ->whereIn('users.id',$listUser)
                                ->get();

        $staffshow[]=array('id'=>0, 'title'=>"Tât cả");
        if($staffs){
            foreach($staffs as $key=>$values){
                $staffshow[]=array('id'=>$values["id"], 'title'=>$values["first_name"]." ".$values["last_name"]);
            }
        }
        return response()->json(['success' => 'Success', 'data'=>$staffshow], 200);
            /*
	    $staffs = $this->userRepository->getAll($this->user->id)
            ->get()
            ->filter(function ($user) {
                return ($user->inRole('staff') && $user->id!=$this->user->id);
            })->map(function ($user) {
                return [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'created_at' => $user->created_at->format(Settings::get('date_format')),
                ];
            }); */
       // return response()->json(['staffs' => $staffs], 200);
    }

    /**
     * Get single staff
     *
     * @Get("/staff")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo","staff_id":"33"}),
     *      @Response(200, body={
    "staff": {
    {
    "id": 1,
    "full_name": "full name",
    "first_name": "name",
    "last_name": "last name",
    "phone_number": "+564514368765",
    "email": "email@email.com",
    "created_at": "2015-11-11",
    "permissions": {
	    "sales_team.read": true,
	    "sales_team.write": true,
	    "leads.read": true,
	    "leads.write": true,
	    "opportunities.read": true,
	    "opportunities.write": true,
	    "logged_calls.read": true,
	    "logged_calls.write": true
     },
    }
    }
    }),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function staff(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
        if($this->user->inRole('staff') && !$this->user->authorized('staffs.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    } 
	    $staff_roles = User::with('roles')->find($request->staff_id);
        $roles = $staff_roles->roles;
        foreach ($roles as $role) {
            if ($role->slug == 'staff') {
                $staff = User::select('id', 'first_name', 'last_name', 'email', 'phone_number', 'user_id', 'user_avatar','permissions')->find($request->staff_id);
                return response()->json(['staff' => $staff], 200);
            } else {
                return response()->json(['error' => 'not_valid_data'], 500);
            }
        }
    }

    /**
     * Post staff
     *
     * @Post("/post_staff")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo","first_name":"first name", "last_name":"last name","email":"email@email.com","password":"1password","permissions":{"sales_team.read": true,"sales_team.write": true,"avatar":"base64_encoded_image" }}),
     *      @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function postStaff(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('staffs.write')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        );
        $rules = array(
            'first_name' => 'required|min:3|max:50',
            'last_name' => 'required|min:3|max:50',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {

            $user = Sentinel::registerAndActivate($request->only('first_name', 'last_name', 'email', 'password'));

            $role = Sentinel::findRoleBySlug('staff');
            $role->users()->attach($user);
            $user = User::find($user->id);

	        if (!is_null($request->avatar)) {
		        $output_file = str_random(10) . ".jpg";
		        $ifp = fopen(public_path() . '/uploads/avatar/' . $output_file, "wb");
		        fwrite($ifp, base64_decode($request->avatar));
		        fclose($ifp);
		        $user->user_avatar = $output_file;
	        }
	        if(!is_null($request->get('permissions'))) {
		        $permissions = explode( ",", $request->get( 'permissions' ) );
		        foreach ( $permissions as $permission ) {
			        $user->addPermission( $permission );
		        }
	        }

            $user->user_id = Sentinel::getUser()->user_id;
            $user->save();

            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Edit staff
     *
     * @Post("/edit_staff")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo", "staff_id":"1","first_name":"first name", "last_name":"last name","password":"1password","permissions":{"sales_team.read": true,"sales_team.write": true,"avatar":"base64_encoded_image"}}),
     *       @Response(200, body={"success":"success"}),
     *       @Response(403, body={"error":"no_permissions"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */
    public function editStaff(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('staffs.write')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'staff_id' => $request->input('staff_id'),
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'password' => $request->input('password'),
        );
        $rules = array(
            'staff_id' => 'required',
            'first_name' => 'required|min:3|max:50',
            'last_name' => 'required|min:3|max:50',
            'password' => 'required|min:6',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $staff = User::find($request->staff_id);
            foreach ($staff->getPermissions() as $key => $item) {
                $staff->removePermission($key);
            }
	        if (!is_null($request->avatar)) {
		        $output_file = str_random(10)  . ".jpg";
		        $ifp = fopen(public_path() . '/uploads/avatar/' . $output_file, "wb");
		        fwrite($ifp, base64_decode($request->avatar));
		        fclose($ifp);
		        $staff->user_avatar = $output_file;
	        }

            if(!is_null($request->get('permissions'))) {
	            $permissions = explode( ",", $request->get( 'permissions' ) );
	            foreach ( $permissions as $permission ) {
		            $staff->addPermission( $permission );
	            }
            }

            $staff->update($request->except('staff_id', 'email'));

            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Delete staff
     *
     * @Post("/delete_staff")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "staff_id":"1"}),
     *      @Response(200, body={"success":"success"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function deleteStaff(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('staffs.delete')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
	    $data = array(
            'staff_id' => $request->input('staff_id'),
        );
        $rules = array(
            'staff_id' => 'required|integer',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $staff = User::find($request->staff_id);
            $staff->delete();
            return response()->json(['success' => "success"], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Get all tasks
     *
     * @Get("/tasks")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo"}),
     *      @Response(200, body={
    "tasks": {
    {
    "id": 1,
    "task_from": "full_name",
    "finished": "0",
    "task_deadline": "2015-11-11",
    "task_description": "asasd"
    }
    }
    }),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function tasks(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $user=$this->user;
        $title = trans('task.tasks');
		$dateFormat = config('settings.date_format');
        $starting_date  = addslashes($request->starting_date);
        $ending_date  = addslashes($request->end_date);
		$sales_id = addslashes($request->sales_id) ;
        $status  = addslashes($request->status) ;
        //$type  = addslashes($request->type) ;
        $work_status  = addslashes($request->work_status) ;
        
        $keyword = addslashes($request->keyword);
        $linkfull=urlencode($request->fullUrl());

        if($work_status=="today"){
            $starting_date=date("Y-m-d 00:00:00");
			$ending_date=date("Y-m-d 23:59:59");
        }else{
            if($starting_date!="" && $end_date!=""){ 
                $starting_date=date("Y-m-d", strtotime($starting_date." -1 days"));
                $ending_date=date("Y-m-d", strtotime($ending_date." +1 days"));
                $date_select=$date;
            }else{
                $starting_date=date("Y-m-d",strtotime('today - 30 days'));
                $ending_date=date("Y-m-d",strtotime('today +30 days'));
                $date_select=date("m/d/Y",strtotime('today - 30 days'))." - ".date("m/d/Y");
            }
            if($work_status!=""){
                $status=$work_status;
            }
        }
        $usersList = "";
        $file = '/home/crmsmart/web/api.salesdy.com/public_html/smartweb/public/log/task_'.$starting_date."-".$ending_date."-".$work_status.'.txt';
        @file_put_contents($file, $request);
        $listUserSales=$this->userRepository->getAllStaffOfUser($user->id);
        $salesList="";
        if($listUserSales){
            $salesList=User::select('users.id', 'users.first_name', 'users.last_name', 'users.user_avatar')
            //->where('partner_user.partner_id','=',$user->partner_id)
            ->whereIn('users.id',$listUserSales)
            ->get()
            ->map( function ( $salesList ) {
                return [ 
                    'title' => $salesList->first_name." ".$salesList->last_name,
                    'value' => $salesList->id,
                ];
            } )->pluck( 'title', 'value')
            ->prepend(trans('lead.all'), '');
        }else{
            $salesList=array(''=>trans('lead.all'));
        }
		

        $dateFormat = config('settings.date_format');
        $date  = addslashes($request->starting_date);

        $taskQuery=Task::select('tasks.*', 'users.first_name as first_name', 'users.last_name as last_name', 'leads.contact_name as lead_name', 'leads.id as lead_id')
        ->leftJoin('users','users.id','=','tasks.user_id')
        ->leftJoin('leads','leads.id','=','tasks.lead_id')
        ->where('tasks.partner_id',$user->partner_id)
        ->where(function ($query)  use ($starting_date, $ending_date, $status, $keyword){
            if($keyword!=""){
                $query->where(function ($query1)  use ($keyword){
                    $query1->where('tasks.task_title', 'LIKE', "%{$keyword}%");
                    $query1->orWhere('tasks.task_description','LIKE', "%{$keyword}%");
                });
            }
           
            if($starting_date!=""){
                $query->where('tasks.task_start','>=',$starting_date);
            }
            if($ending_date!=""){
                $query->where('tasks.task_end','<=',$ending_date);
            }
            if($status!=""){
                $query->where('tasks.work_status',$status);
            }

        })->where(function ($query)  use ($sales_id, $listUserSales, $user){
            if($sales_id!="" && $sales_id!="0"){
                $query->where('tasks.user_id','=',$sales_id);
                $query->whereOr('tasks.task_from_user','=',$sales_id);
            }else{
                if($listUserSales){
                    array_push($listUserSales,$user->id);
                }
                if($listUserSales!="" && count($listUserSales)>0){
                    $query->whereIn('tasks.user_id',$listUserSales);
                    $query->orWhereIn('tasks.task_from_user',$listUserSales);
                }else{
                    $query->where('tasks.user_id',$user->id);
                    $query->orWhere('tasks.task_from_user',$user->id);
                }
               
            }
        })->distinct()
        ->orderBy("tasks.id", "desc");
        $totalTask=$taskQuery->count();
       // $totalTask=count($taskQuery->get());
        $taskPage=$taskQuery->paginate(20)->appends(request()->query());
            $taskList=$taskPage->map( function ( $task) use ($dateFormat){
                return [
                    'id'           => $task->id,
                    'task_deadline'   => $task->task_end,
                    'task_start'   => $task->task_start,
                    'task_description' => $task->description,
                    'task_title' => $task->task_title,
                    'task_note'  => $task->task_note,
                    'user_id'  => $task->user_id,
                    'status_title'  => $task->status_title,
                    'full_name'  => $task->first_name." ".$task->last_name,
                    'lead_name'  => $task->lead_name,
                    'lead_id'  => $task->lead_id,
                    'type_task'  => $task->type_task,
                    'report_status'  => $task->report_status,
                    'work_status'  => $task->work_status,
                    'longitude'  => $task->longitude,
                    'latitude'  => $task->latitude,
                ];
            }
        );
        return response()->json(['tasks' => $taskList, 'totalTask'=>$totalTask], 200);
    }

	/**
	 * Get single task
	 *
	 * @Get("/task")
	 * @Versions({"v1"})
	 * @Transaction({
	 *      @Request({"token": "foo","task_id":"1"}),
	 *      @Response(200, body={
				"task": {
						"id": 1,
						"task_from": "full_name",
						"finished": "0",
						"task_deadline": "2017-11-11",
						"task_description": "Lorem ipsum"
					}
				}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *       })
	 * })
	 */
	public function task(Request $request)
	{
		$this->user = JWTAuth::parseToken()->authenticate();
		$data = array(
			'task_id' => $request->input('task_id')
		);
		$rules = array(
			'task_id' => 'required|integer',
        );
        $leadDetail=[];
        $leadJson=[];
		$validator = Validator::make($data, $rules);
		if ($validator->passes() && $this->user) {
            $id=$request->input('task_id');
            $task = Task::find($id);
            if($task){
                $lead_id=$task["lead_id"];
                if($lead_id!="" && $lead_id!=0){
                    $leadDetail=Lead::select('id','opportunity as fullname','email', 'phone', 'psid' , 'URL as url_messenger', 'page_id', 'sales_person_id as user_id', 'group_id', 'status')->where('id',$lead_id)->first();
                    $chatnow=0;
                    $logData="";
                    if($leadDetail){
                        $psid=$leadDetail->psid;
                        if($psid!="" && $psid!="0"){
                            $get23ago=date("Y-m-d H:i:s", time()-(59*60*24));
                            $logData=Chatbox::select('id', 'timechat')
                            ->where('date_create','>=', $get23ago)->where('sender_id', '=', $psid)
                            ->orderBy('chat_box.id', 'desc')->first();
                        }
                        $timenow=time();
                        if(isset($logData) && $logData!=""){
                            $chatnow=1;
                        }
                        
                        $leadJson=[
                            'id' => $leadDetail->id,
                            'fullname' => $leadDetail->fullname,
                            'group_id' => $leadDetail->group_id,
                            'email' => $leadDetail->email,
                            'phone' => $leadDetail->phone,
                            'psid' => $leadDetail->psid,
                            'page_id' => $leadDetail->page_id,
                            'url_messenger' => "",
                            'thumbnail'=>"",
                            'status' => $leadDetail->status,
                            'chat' => (isset($leadDetail->psid) && $leadDetail->psid!="" && $chatnow==1)?1:0,
                        ];
                    }
    
                }
                $showApprove=1;
                if($task->user_id==$this->user->id){
                    $showApprove=0;
                }
                $approve=0;
                if($showApprove==1){
                    $approve=1;
                    $taskApprove=TaskAccept::where('task_id',$id)->where('user_id',$this->user->id)->first();
                    if(isset($taskApprove) && $taskApprove!=""){
                        $approve=0; 
                    }
                }

                $taskApproveJson=[
                    'aproved' =>$approve,
                    'show_approve' => $showApprove,
                ];


                $taskReport=TaskReport::where('task_id',$id)
                ->get();
                $taskJson=[];
                if($taskReport){
                    foreach($taskReport as $tasklist){
                        $title="Chưa hoàn thành";
                        $color="#ff0000";
                        if($tasklist->status==1){
                            $title="Hoàn thành";
                            $color="#038342";
                        }
                        $taskJson[]=[
                            'id' => $tasklist->id,
                            'user_id' => $tasklist->user_id,
                            'task_report_description' => $tasklist->task_report_description,
                            'status' => $tasklist->status,
                            'status_title' => $title,
                            'status_color' => $color,
                            'file_report' => $tasklist->file_report,
                            'date_report' => $tasklist->date_report,
                            'type_task' => $tasklist->type_task,
                            'longitude' => $tasklist->longitude,
                            'latitude' => $tasklist->latitude,
                        ];
                    }
                }
            }
			return response()->json(['success' => 1, 'task' => $task, 'customer'=>$leadJson, 'taskReport'=>$taskJson, 'taskApprove'=>$taskApproveJson], 200);
		} else {
			return response()->json(['success' => 0, 'error' => 'not_valid_data'], 500);
		} 
	}

    /**
     * Post task
     *
     * @Post("/post_task")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo","user_id":"1", "task_description":"asasas","task_deadline":"2016-10-10"}),
     *       @Response(200, body={"success":"success"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     **/

    public function postTask(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $data = array(
            'task_description' => $request->input('task_description'),
            'task_title' => $request->input('task_title'),
        );
        $rules = array(
            'task_description' => 'required',
        );
        $validator = Validator::make($data, $rules);
        $file = '/home/crmsmart/web/api.salesdy.com/public_html/smartweb/public/log/task_post_BD.txt';
        @file_put_contents($file, $request);
        if ($validator->passes()) {
            $work_status=2;
           
            $lead_id=addslashes($request->lead_id);
            $task_title=addslashes($request->task_title);
            $task_description=addslashes($request->task_description);

            $latitude=addslashes($request->latitude);
            $longitude=addslashes($request->longitude);
            $list_user_add="";
            $list_user=$request->list_user;
            if($list_user!="" && count($list_user)>0){
                $list_user_add=implode(",",$list_user);
            }

            $task_end=date("Y-m-d H:i:s", strtotime("+1 day"));
            $task_start=date("Y-m-d H:i:s");
            if($request->task_end){
                $task_end_explore=addslashes($request->task_end);
                $dateArray=explode(" ",$task_end_explore);
                $dateList=$dateArray[0];
                $dateListArray=explode("/",$dateArray[0]);
                $task_end=$dateListArray[2]."-".$dateListArray[0]."-".$dateListArray[1]." ".$dateArray[1];
            }
            if($request->task_start){
                $task_start_explore=addslashes($request->task_start);
                $dateArray=explode(" ",$task_start_explore);
                $dateList=$dateArray[0];
                $dateListArray=explode("/",$dateArray[0]);
                $task_start=$dateListArray[2]."-".$dateListArray[0]."-".$dateListArray[1]." ".$dateArray[1];
            }


            if($request->work_status){
                $work_status=addslashes($request->work_status);
            }
            $statusLead=0;
            if(isset($request->status) && $request->status){
                $statusLead=$request->status;
            }
            $fullname=$this->user["first_name"]." ".$this->user["last_name"];
            $task = new Task;
            $task->lead_id=$lead_id;
            $task->task_title=$task_title;
            $task->task_description=$task_description;
            $task->task_end=$task_end;
            $task->task_start=$task_start;
            $task->full_name=$fullname;
            $task->finished=$statusLead;
            $task->user_id=$this->user->id;
            $task->partner_id=$this->user->partner_id;
            $task->work_status=$work_status;
            $task->latitude=$latitude;
            $task->longitude=$longitude;
            $task->list_user=$list_user_add;
            $task->save();
            $task_id=$task->id;

            $notification = array(
                'partner_id'=>$this->user->partner_id,
                'user_id' => $this->user->id,
                'url'=> "",
                'title'=>"@".$fullname." tao Task mới ".$task_title,
                'desc'=>"@".$fullname." đã tạo Task mới. ".$task_description,
                'status'=>0, 
                'type'=>"task",
                'item_id'=>$task_id,
                'created_at'=> date("Y-m-d H:i:s"),
                'date_notification'=>time()
            );
            Notification::insert($notification);


            /*
            $request->merge(['task_from_user' => $this->user->id]);
            $task = new Task($request->except('token', 'full_name'));
            $task->save(); */
            return response()->json(['success' => "success", 'task_id'=>$task_id], 200);
        } else {
            return response()->json(['success' => "unsuccess", 'error' => 'not_valid_data'], 500);
        }
    }

    /**
     * Edit task
     *
     * @Post("/edit_task")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"token": "foo", "task_id":"1","user_id":"1", "task_description":"asasas","task_deadline":"2016-10-10"}),
     *       @Response(200, body={"success":"success"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */

    public function editTask(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $data = array(
            'task_id' => $request->input('task_id'),
            'task_description' => $request->input('task_description'),
        );
        $rules = array(
            'task_id' => 'required',
            'task_description' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $work_status=2;
            //$request->merge(['task_from_user' => $this->user->id]);
            $task = Task::find($request->task_id);
            if($request->work_status){
                $work_status=addslashes($request->work_status);
            }
            $statusLead=$request->status;

            $taskUpdate=array("task_title"=>$task_title, "task_description"=>$task_description, "task_end"=>$task_end, "task_start"=>$task_start, "finished"=>$statusLead, "work_status"=>$work_status);
            $task->update($taskUpdate);
            return response()->json(['success' => "success"], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }
    /* Report task*/ 
    public function addReportTask(Request $request){
        $this->user = JWTAuth::parseToken()->authenticate();
        $task_description = addslashes($request->task_report_description) ;
        $status  = addslashes($request->status);
        $task_id = addslashes($request->task_id);

        $latitude = addslashes($request->latitude);
        $longitude = addslashes($request->longitude);

        $file = $request->file('file_report');
        $filereport="";
    // $filereport = $request->file('file_report')->getRealPath();
        if($file){
            $filereport=$file->move('upload', $file->getClientOriginalName());
        } 
        $id  = addslashes($request->id);
        //file_put_contents('/home/crmsmart/web/api.salesdy.com/public_html/smartweb/public/log/task'.$id.'.txt',$request->task_description);

        if($task_description!="" && $task_id>0){
            $taskDetail=Task::where('id',$task_id)->first();
            if(isset($taskDetail) && $taskDetail!=""){
                $statusTypeUpdate=2;
                if($status!=""){
                    $titleStatus = $this->optionRepository->getAll()->where( 'id', $status)->first();
                    $statusTypeUpdate=$titleStatus["value"];
                }

                if($id!="" && $id>0){
                    $taskreport=array("task_report_description"=>$task_description, "date_report"=>date("Y-m-d H:i:s"), "user_id"=>$this->user->id, "task_id"=>$task_id, "status"=>$status, "type_task"=>$statusTypeUpdate, "latitude"=>$latitude, "longitude"=>$longitude);
                    if($filereport!=""){
                        array_push($taskreport, array("file_report"=>$filereport));
                    }
                    TaskReport::where('id', $id)->update($taskreport);
                }else{
                    $taskreport=array("task_report_description"=>$task_description, "date_report"=>date("Y-m-d H:i:s"), "user_id"=>$this->user->id, "task_id"=>$task_id, "status"=>$status, "file_report"=>$filereport, "type_task"=>$statusTypeUpdate, "latitude"=>$latitude, "longitude"=>$longitude);
                    TaskReport::insert($taskreport);
                }
                Task::where('id', $task_id)->update(["report_status"=>1, "work_status"=>$statusTypeUpdate]);
                $fullname=trim($this->user->first_name." ".$this->user->last_name);
                $notification = array(
                    'partner_id'=>$this->user->partner_id,
                    'user_id' => $this->user->id,
                    'url'=> "",
                    'title'=>"@".$fullname." báo cáo Task: ".$taskDetail->task_title,
                    'desc'=>"@".$fullname." đã báo cáo task ".$task_id.". ".$task_description,
                    'status'=>0, 
                    'type'=>"task",
                    'item_id'=>$task_id,
                    'created_at'=> date("Y-m-d H:i:s"),
                    'date_notification'=>time()
                );
                Notification::insert($notification);


                return response()->json(['success' => "success"], 200);
            }else{
                return response()->json(['success' => "unsuccess", 'error' => 'not_valid_data'], 500);
            }
        }
        return response()->json(['success' => "unsuccess", 'error' => 'not_valid_data'], 500);
    }    
    public function taskStatus(Request $request){
        $this->user = JWTAuth::parseToken()->authenticate();
        $listStatus = $this->optionRepository->getAll()->where( 'category', "status_report")->where( 'partner_id', 0)->get();
        $statusJson=[];
        if($listStatus){
            foreach($listStatus as $statuslist){
                $statusJson[]=[
                    'id' => $statuslist->id,
                    'title' => $statuslist->title,
                ];
            }
        }
        return response()->json(['success' => "success", 'listStatus'=>$statusJson], 200);
    }

    /**
     * Delete task
     *
     * @Post("/delete_task")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo", "task_id":"1"}),
     *      @Response(200, body={"success":"success"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */
    public function deleteTask(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $data = array(
            'task_id' => $request->input('task_id'),
        );
        $rules = array(
            'task_id' => 'required|integer',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $task = Task::find($request->task_id);
            $task->delete();
            return response()->json(['success' => "success"], 200);
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
				"customers": 20,
				"contracts": 12,
				"opportunities": 17,
				"products": 8,
				"opportunity_leads": {
					{
						"month": "Feb",
						"year": "2016",
						"opportunity": 0,
						"leads": 0
					},
					{
						"month": "Mar",
						"year": "2016",
						"opportunity": 0,
						"leads": 0
					},
					{
						"month": "Apr",
						"year": "2016",
						"opportunity": 0,
						"leads": 0
					},
					{
						"month": "May",
						"year": "2016",
						"opportunity": 0,
						"leads": 0
					},
					{
						"month": "Jun",
						"year": "2016",
						"opportunity": 0,
						"leads": 0
					},
					{
						"month": "Jul",
						"year": "2016",
						"opportunity": 0,
						"leads": 0
					},
					{
						"month": "Aug",
						"year": "2016",
						"opportunity": 0,
						"leads": 0
					},
					{
						"month": "Sep",
						"year": "2016",
						"opportunity": 1,
						"leads": 0
					},
					{
						"month": "Oct",
						"year": "2016",
						"opportunity": 0,
						"leads": 3
					},
					{
						"month": "Nov",
						"year": "2016",
						"opportunity": 0,
						"leads": 0
					},
					{
						"month": "Dec",
						"year": "2016",
						"opportunity": 13,
						"leads": 3
					},
					{
						"month": "Jan",
						"year": "2017",
						"opportunity": 3,
						"leads": 1
					}
				},
	            "stages_chart": {
						{
							"title": "New",
							"value": "New",
							"color": "#4fc1e9",
							"opprotunities": 0
						},
						{
							"title": "Qualification",
							"value": "Qualification",
							"color": "#a0d468",
							"opprotunities": 0
						}
	                }
		}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *       })
	 * })
	 */
	public function dashboard(Request $request)
	{
		$this->user = JWTAuth::parseToken()->authenticate();

		if ($this->user->id) {
			$customers     = $this->companyRepository->getAll()->count();
			$contracts     = $this->contractRepository->getAll()->count();
			$opportunities = $this->opportunityRepository->getAll()->count();
			$products      = $this->productRepository->getAll()->count();
			$opportunity_leads = array ();
			for ( $i = 11; $i >= 0; $i -- ) {
				$opportunity_leads[] =
					array (
						'month'       => Carbon::now()->subMonth( $i )->format( 'M' ),
						'year'        => Carbon::now()->subMonth( $i )->format( 'Y' ),
						'opportunity' => $this->opportunityRepository->getAll()->where( 'created_at', 'LIKE',
							Carbon::now()->subMonth( $i )->format( 'Y-m' ) . '%' )->count(),
						'leads'       => $this->leadRepository->getAll()->where( 'created_at', 'LIKE',
							Carbon::now()->subMonth( $i )->format( 'Y-m' ) . '%' )->count()
					);
			}
			$stages = $this->optionRepository->getAll()
			                                 ->where('category', 'stages')
			                                 ->get()
			                                 ->map(function ($title) {
				                                 return [
					                                 'title' => $title->title,
					                                 'value'   => $title->value,
				                                 ];
			                                 })->toArray();
			$colors = array('#4fc1e9','#a0d468','#37bc9b','#ffcc66','#fd9883','#c2185b','#00796b','#7b1fa2','#3f51b5','#00796b','#607d8b','#00b0ff');
			foreach($stages as $key=>$item)
			{
				$stages[$key]['color'] = isset($colors[$key])?$colors[$key]:"";
				$stages[$key]['opprotunities'] = $this->opportunityRepository->getAllForUser($this->user->id)->where('stages',$item['title'])->count();
			}

			return response()->json( [ 'customers'=>$customers,'contracts'=>$contracts,'opportunities'=>$opportunities,
			'products' => $products, 'opportunity_leads'=>$opportunity_leads, 'stages_chart' =>$stages], 200 );
		} else {
			return response()->json(['error' => 'not_valid_data'], 500);
		}

	}

	/**
	 * Get all permissions
	 *
	 * @Get("/permissions")
	 * @Versions({"v1"})
	 * @Transaction({
	 *      @Request({"token": "foo"}),
	 *      @Response(200, body={
				"permissions": {
				"id": 1,
				"task_from": "full_name",
				"finished": "0",
				"task_deadline": "2017-11-11",
				"task_description": "Lorem ipsum"
				}
				}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *       })
	 * })
	 */
	public function permissions(Request $request)
	{
		$this->user = JWTAuth::parseToken()->authenticate();
		if ($this->user) {
			$permissions = ['sales_team.read'=>'sales_team.read',
			                'sales_team.delete'=>'sales_team.delete',
			                'sales_team.write'=>'sales_team.write',
			                'leads.read'=>'leads.read',
			                'leads.delete'=>'leads.delete',
			                'leads.write'=>'leads.write',
			                'opportunities.read'=>'opportunities.read',
			                'opportunities.delete'=>'opportunities.delete',
			                'opportunities.write'=>'opportunities.write',
			                'logged_calls.read'=>'logged_calls.read',
			                'logged_calls.delete'=>'logged_calls.delete',
			                'logged_calls.write'=>'lgged_calls.write',
			                'meetings.read'=>'meetings.read',
			                'meetings.delete'=>'meetings.delete',
			                'meetings.write'=>'meetings.write',
			                'products.read'=>'products.read',
			                'products.delete'=>'products.delete',
			                'products.write'=>'products.write',
			                'quotations.read'=>'quotations.read',
			                'quotations.delete'=>'quotations.delete',
			                'quotations.write'=>'quotations.write',
			                'sales_orders.read'=>'sales_orders.read',
			                'sales_orders.delete'=>'sales_orders.delete',
			                'sales_orders.write'=>'sales_orders.write',
			                'invoices.read'=>'invoices.read',
			                'invoices.delete'=>'invoices.delete',
			                'invoices.write'=>'invoices.write',
			                'contracts.read'=>'contracts.read',
			                'contracts.delete'=>'contracts.delete',
			                'contracts.write'=>'contracts.write',
			                'staff.read'=>'staff.read',
			                'staff.delete'=>'staff.delete',
			                'staff.write'=>'staff.write',
			                'contacts.read'=>'contacts.read',
			                'contacts.delete'=>'contacts.delete',
			                'contacts.write'=>'contacts.write',
							];
			return response()->json(['permissions' => $permissions], 200);
		} else {
			return response()->json(['error' => 'not_valid_data'], 500);
		}
	}

	/**
	 * Get all email Templates
	 *
	 * @Get("/email_templates")
	 * @Versions({"v1"})
	 * @Transaction({
	 *      @Request({"token": "foo"}),
	 *      @Response(200, body={
				"email_templates": {
					{
						"id": 1,
						"title": "Title",
						"text": "Email text"
					}
				}
			}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *       })
	 * })
	 */
	public function emailTemplates(Request $request)
	{
		$this->user = JWTAuth::parseToken()->authenticate();
		$email_templates = $this->emailTemplateRepository->getAll()
		                                                 ->get()
		                                                 ->map(function ($email_template) {
			                                                 return [
				                                                 'id' => $email_template->id,
				                                                 'title' => $email_template->title,
				                                                 'text' => $email_template->text,
			                                                 ];
		                                                 })->values();
		return response()->json(['email_templates' => $email_templates], 200);
	}

	/**
	 * Get single email template
	 *
	 * @Get("/email_template")
	 * @Versions({"v1"})
	 * @Transaction({
	 *      @Request({"token": "foo","email_template_id":"1"}),
	 *      @Response(200, body={
						"email_template": {
								"id": 1,
								"title": "Title",
								"text": "Email text",
								"user_id": 1,
								"created_at": "2017-06-20 08:57:01",
								"updated_at": "2017-06-20 08:57:01",
								"deleted_at": null
						}
			}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *       })
	 * })
	 */
	public function emailTemplate(Request $request)
	{
		$this->user = JWTAuth::parseToken()->authenticate();
		$data = array(
			'email_template_id' => $request->input('email_template_id')
		);
		$rules = array(
			'email_template_id' => 'required|integer',
		);
		$validator = Validator::make($data, $rules);
		if ($validator->passes() && $this->user) {
			$email_template = EmailTemplate::find($request->input('email_template_id'));
			return response()->json(['email_template' => $email_template], 200);
		} else {
			return response()->json(['error' => 'not_valid_data'], 500);
		}
	}

	/**
	 * Post email template
	 *
	 * @Post("/post_email_template")
	 * @Versions({"v1"})
	 * @Transaction({
	 *       @Request({"token": "foo","title":"Title","text":"Email text"}),
	 *       @Response(200, body={"success":"success"}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *    })
	 * })
	 **/

	public function postEmailTemplate(Request $request)
	{
		$this->user = JWTAuth::parseToken()->authenticate();
		$data = array(
			'title' => $request->input('title'),
			'text' => $request->input('text')
		);
		$rules = array(
			'title' => 'required',
			'text' => 'required',
		);
		$validator = Validator::make($data, $rules);
		if ($validator->passes()) {
			$request->merge(['user_id' => $this->user->id]);
			$task = new EmailTemplate($request->only('user_id', 'title', 'text'));
			$task->save();
			return response()->json(['success' => "success"], 200);
		} else {
			return response()->json(['error' => 'not_valid_data'], 500);
		}
	}

	/**
	 * Edit email template
	 *
	 * @Post("/edit_email_template")
	 * @Versions({"v1"})
	 * @Transaction({
	 *       @Request({"token": "foo", "email_template_id":"1","title":"Title","text":"Email text"}),
	 *       @Response(200, body={"success":"success"}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *    })
	 * })
	 */

	public function editEmailTemplate(Request $request)
	{
		$this->user = JWTAuth::parseToken()->authenticate();
		$data = array(
			'email_template_id' => $request->input('email_template_id'),
			'title' => $request->input('title'),
			'text' => $request->input('text')
		);
		$rules = array(
			'email_template_id' => 'required|integer',
			'title' => 'required',
			'text' => 'required',
		);
		$validator = Validator::make($data, $rules);
		if ($validator->passes()) {
			$request->merge(['user_id' => $this->user->id]);
			$emailTemplate = EmailTemplate::find($request->email_template_id);
			$emailTemplate->update($request->only('user_id', 'title', 'text'));
			return response()->json(['success' => "success"], 200);
		} else {
			return response()->json(['error' => 'not_valid_data'], 500);
		}
	}

	/**
	 * Delete email template
	 *
	 * @Post("/delete_email_template")
	 * @Versions({"v1"})
	 * @Transaction({
	 *      @Request({"token": "foo", "email_template_id":"1"}),
	 *      @Response(200, body={"success":"success"}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *       })
	 * })
	 */
	public function deleteEmailTemplate(Request $request)
	{
		$this->user = JWTAuth::parseToken()->authenticate();
		$data = array(
			'email_template_id' => $request->input('email_template_id'),
		);
		$rules = array(
			'email_template_id' => 'required|integer',
		);
		$validator = Validator::make($data, $rules);
		if ($validator->passes()) {
			$emailTemplate = EmailTemplate::find($request->email_template_id);
			$emailTemplate->delete();
			return response()->json(['success' => "success"], 200);
		} else {
			return response()->json(['error' => 'not_valid_data'], 500);
		}
	}

	/**
	 * Invite staff
	 *
	 * @Post("/invite_staff")
	 * @Versions({"v1"})
	 * @Transaction({
	 *      @Request({"token": "foo", "emails":"email@mail.com,email2@mail.com"}),
	 *      @Response(200, body={"success":"success"}),
	 *      @Response(403, body={"error":"no_permissions"}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *       })
	 * })
	 */
	public function inviteStaff(Request $request)
	{
		$this->user = JWTAuth::parseToken()->authenticate();
		if($this->user->inRole('staff') && !$this->user->authorized('staffs.write')){
			return response()->json(['error' => 'no_permissions'], 403);
		}
		$data = array(
			'emails' => $request->input('emails')
		);
		$rules = array(
			'emails' => 'required',
		);
		$validator = Validator::make($data, $rules);
		if ($validator->passes()) {
			$emails = explode(";", $request->emails);
			foreach($emails as $email)
			{
				$validator = \Validator::make(
					array('individualEmail' => $email),
					array('individualEmail' => 'email')
				);

				if ($validator->passes()) {
					$invite = $this->inviteUserRepository->create( [ 'email' => trim( $email ) ] );
					Mail::to( $email )
					    ->send( new StaffInvite( $invite ) );
				}
			}
			return response()->json(['success' => 'success'], 200);
		} else {
			return response()->json(['error' => 'not_valid_data'], 500);
		}
	}

	/**
	 * Convert opportunity to quotation
	 *
	 * @Post("/convert_opportunity_to_quotation")
	 * @Versions({"v1"})
	 * @Transaction({
	 *      @Request({"token": "foo", "opportunity_id":1}),
	 *      @Response(200, body={"success":"success"}),
	 *      @Response(403, body={"error":"no_permissions"}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *       })
	 * })
	 */
	public function convertOpportunityToQuotation(Request $request)
	{
		$this->user = JWTAuth::parseToken()->authenticate();
		if($this->user->inRole('staff') && !$this->user->authorized('staffs.write')){
			return response()->json(['error' => 'no_permissions'], 403);
		}
		$data = array(
			'opportunity_id' => $request->input('opportunity_id')
		);
		$rules = array(
			'opportunity_id' => 'required',
		);
		$validator = Validator::make($data, $rules);
		if ($validator->passes()) {
				$opportunity = Opportunity::find($request->input('opportunity_id'));
			    $total_fields = Quotation::whereNull('deleted_at')->orWhereNotNull('deleted_at')->orderBy('id', 'desc')->first();
			    $quotation_no = Settings::get('quotation_prefix') . (Settings::get('quotation_start_number') + (isset($total_fields) ? $total_fields->id : 0) + 1);

			    Quotation::create([
				    'quotations_number' => $quotation_no,
				    'customer_id' => $opportunity->customer_id,
				    'date' => date(Settings::get('date_format')),
				    'exp_date' => $opportunity->expected_closing,
				    'payment_term' => Settings::get('payment_term1'),
				    'sales_person_id' => $opportunity->sales_person_id,
				    'sales_team_id' => $opportunity->sales_team_id,
				    'status' => 'Draft Quotation',
				    'user_id' => Sentinel::getUser()->id
			    ]);
			    $opportunity->update(array('stages' => 'Won'));

			    $opportunity->delete();
			return response()->json(['success' => 'success'], 200);
		} else {
			return response()->json(['error' => 'not_valid_data'], 500);
		}
	}

	/**
	 * Convert quotation to sale order
	 *
	 * @Post("/convert_quotation_to_sale_order")
	 * @Versions({"v1"})
	 * @Transaction({
	 *      @Request({"token": "foo", "quotation_id":1}),
	 *      @Response(200, body={"success":"success"}),
	 *      @Response(403, body={"error":"no_permissions"}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *       })
	 * })
	 */
	public function convertQuotationToSaleOrder(Request $request)
	{
		$this->user = JWTAuth::parseToken()->authenticate();
		if($this->user->inRole('staff') && !$this->user->authorized('staffs.write')){
			return response()->json(['error' => 'no_permissions'], 403);
		}
		$data = array(
			'quotation_id' => $request->input('quotation_id')
		);
		$rules = array(
			'quotation_id' => 'required',
		);
		$validator = Validator::make($data, $rules);
		if ($validator->passes()) {
			$quotation = Quotation::find($request->input('quotation_id'));
			$total_fields = Saleorder::whereNull('deleted_at')->orWhereNotNull('deleted_at')->orderBy('id', 'desc')->first();
			$sale_no = Settings::get('sales_prefix') . (Settings::get('sales_start_number') + (isset($total_fields) ? $total_fields->id : 0) + 1);

			$saleorder = Saleorder::create([
				'sale_number' => $sale_no,
				'customer_id' => $quotation->customer_id,
				'date' => date(Settings::get('date_format')),
				'exp_date' => $quotation->exp_date,
				'qtemplate_id' => $quotation->qtemplate_id,
				'payment_term' => isset($quotation->payment_term)?$quotation->payment_term:0,
				"sales_person_id" => $quotation->sales_person_id,
				"terms_and_conditions" => $quotation->terms_and_conditions,
				"total" => $quotation->total,
				"tax_amount" => $quotation->tax_amount,
				"grand_total" => $quotation->grand_total,
				"discount" => is_null($quotation->discount)?0:$quotation->discount,
				"final_price" => $quotation->final_price,
				'status' => 'Draft sales order',
				'user_id' => Sentinel::getUser()->id
			]);

			if (!empty($quotation->products->count() > 0)) {
				foreach ($quotation->products as $item) {
					$saleorderProduct = new SaleorderProduct();
					$saleorderProduct->order_id = $saleorder->id;
					$saleorderProduct->product_id = $item->product_id;
					$saleorderProduct->product_name = $item->product_name;
					$saleorderProduct->description = $item->description;
					$saleorderProduct->quantity = $item->quantity;
					$saleorderProduct->price = $item->price;
					$saleorderProduct->sub_total = $item->sub_total;
					$saleorderProduct->save();
				}
			}
			$quotation->delete();
			return response()->json(['success' => 'success'], 200);
		} else {
			return response()->json(['error' => 'not_valid_data'], 500);
		}
	}
	/**
	 * Convert quotation to invoice
	 *
	 * @Post("/convert_quotation_to_invoice")
	 * @Versions({"v1"})
	 * @Transaction({
	 *      @Request({"token": "foo", "quotation_id":1}),
	 *      @Response(200, body={"success":"success"}),
	 *      @Response(403, body={"error":"no_permissions"}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *       })
	 * })
	 */
	public function convertQuotationToInvoice(Request $request)
	{
		$this->user = JWTAuth::parseToken()->authenticate();
		if($this->user->inRole('staff') && !$this->user->authorized('staffs.write')){
			return response()->json(['error' => 'no_permissions'], 403);
		}
		$data = array(
			'quotation_id' => $request->input('quotation_id')
		);
		$rules = array(
			'quotation_id' => 'required',
		);
		$validator = Validator::make($data, $rules);
		if ($validator->passes()) {
			$quotation = Quotation::find($request->input('quotation_id'));
			$total_fields = Invoice::whereNull('deleted_at')->orWhereNotNull('deleted_at')->orderBy('id', 'desc')->first();
			$invoice_number = Settings::get('invoice_prefix') . (Settings::get('invoice_start_number') + (isset($total_fields) ? $total_fields->id : 0) + 1);

			$invoice_details = array(
				'order_id' => $quotation->id,
				'customer_id' => $quotation->customer_id,
				'sales_person_id' => $quotation->sales_person_id,
				'sales_team_id' => $quotation->sales_team_id,
				'invoice_number' => $invoice_number,
				'invoice_date' => date(Settings::get('date_format')),
				'due_date' => $quotation->exp_date,
				'payment_term' => isset($quotation->payment_term)?$quotation->payment_term:0,
				'status' => 'Open Invoice',
				'total' => $quotation->total,
				'tax_amount' => $quotation->tax_amount,
				'grand_total' => $quotation->grand_total,
				'unpaid_amount' => $quotation->final_price,
				'discount' => $quotation->discount,
				'final_price' => $quotation->final_price,
				'user_id' => Sentinel::getUser()->id,
			);
			$invoice = Invoice::create($invoice_details);

			$products = $quotation->products;
			if (!empty($products)) {
				foreach ($products as $item) {
					$product_add = array(
						'invoice_id' => $invoice->id,
						'product_id' => $item->id,
						'product_name' => $item->product_name,
						'description' => $item->description,
						'quantity' => $item->quantity,
						'price' => $item->price,
						'sub_total' => $item->sub_total
					);
					InvoiceProduct::create($product_add);
				}
			}
			$quotation->delete();
			return response()->json(['success' => 'success'], 200);
		} else {
			return response()->json(['error' => 'not_valid_data'], 500);
		}
	}

	/**
	 * Convert sale order to invoice
	 *
	 * @Post("/convert_sale_order_to_invoice")
	 * @Versions({"v1"})
	 * @Transaction({
	 *      @Request({"token": "foo", "sale_order_id":1}),
	 *      @Response(200, body={"success":"success"}),
	 *      @Response(403, body={"error":"no_permissions"}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *       })
	 * })
	 */
	public function convertSaleOrderToInvoice(Request $request)
	{
		$this->user = JWTAuth::parseToken()->authenticate();
		if($this->user->inRole('staff') && !$this->user->authorized('staffs.write')){
			return response()->json(['error' => 'no_permissions'], 403);
		}
		$data = array(
			'sale_order_id' => $request->input('sale_order_id')
		);
		$rules = array(
			'sale_order_id' => 'required',
		);
		$validator = Validator::make($data, $rules);
		if ($validator->passes()) {
			$saleorder = Saleorder::find($request->input('sale_order_id'));
			$total_fields = Invoice::whereNull('deleted_at')->orWhereNotNull('deleted_at')->orderBy('id', 'desc')->first();
			$invoice_number = Settings::get('invoice_prefix') . (Settings::get('invoice_start_number') + (isset($total_fields) ? $total_fields->id : 0) + 1);

			$invoice = Invoice::create([
				'order_id' => $saleorder->id,
				'customer_id' => $saleorder->customer_id,
				'sales_person_id' => $saleorder->sales_person_id,
				'sales_team_id' => $saleorder->sales_team_id,
				'invoice_number' => $invoice_number,
				'invoice_date' =>date(Settings::get('date_format')),
				'due_date' => $saleorder->exp_date,
				'payment_term' => isset($saleorder->payment_term)?$saleorder->payment_term:0,
				'status' => 'Open Invoice',
				'total' => $saleorder->total,
				'tax_amount' => $saleorder->tax_amount,
				'grand_total' => $saleorder->grand_total,
				'unpaid_amount' => $saleorder->final_price,
				'discount' => $saleorder->discount,
				'final_price' => $saleorder->final_price,
				'user_id' => Sentinel::getUser()->id,
			]);

			$products = $saleorder->products;
			if ($products->count()>0) {
				foreach ($products as $item) {
					$product_add = array(
						'invoice_id' => $invoice->id,
						'product_id' => $item->id,
						'product_name' => $item->product_name,
						'description' => $item->description,
						'quantity' => $item->quantity,
						'price' => $item->price,
						'sub_total' => $item->sub_total
					);
					InvoiceProduct::create($product_add);
				}
			}
			$saleorder->delete();
			return response()->json(['success' => 'success'], 200);
		} else {
			return response()->json(['error' => 'not_valid_data'], 500);
		}
	}

	private function ajaxCreateQuotationPdf(Quotation $quotation)
	{
		$filename = 'Quotation-' . $quotation->quotations_number;
		$pdf = App::make('dompdf.wrapper');
		$pdf->setPaper('a4','landscape');
		$pdf->loadView('quotation_template.'.Settings::get('quotation_template'), compact('quotation'));
		$pdf->save('./pdf/' . $filename . '.pdf');
		$pdf->stream();
		return  $filename . ".pdf";
	}

	/**
	 * Send quotation to email
	 *
	 * @Post("/send_quotation")
	 * @Versions({"v1"})
	 * @Transaction({
	 *      @Request({"token": "foo", "quotation_id":1,"subject":"subject","recipients":{1,2,3}, "body":"body"}),
	 *      @Response(200, body={"success":"success"}),
	 *      @Response(403, body={"error":"no_permissions"}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *       })
	 * })
	 */
	public function sendQuotation(Request $request)
	{
		$this->user = JWTAuth::parseToken()->authenticate();
		if($this->user->inRole('staff') && !$this->user->authorized('quotations.write')){
			return response()->json(['error' => 'no_permissions'], 403);
		}
		$data = array(
			'quotation_id' => $request->input('quotation_id'),
			'subject' => $request->input('subject'),
			'recipients' => $request->input('recipients'),
			'body' => $request->input('body')
		);
		$rules = array(
			'quotation_id' => 'required',
			'subject' => 'required',
			'recipients' => 'required',
			'body' => 'required'
		);
		$validator = Validator::make($data, $rules);
		if ($validator->passes()) {
			$quotation = Quotation::find($request->quotation_id)->first();
			$email_subject = $request->subject;
			$to_customers = Customer::whereIn('user_id', $request->recipients)->get();
			$email_body = $request->body;
			$message_body = Common::parse_template($email_body);
			$quotation_pdf = $this->ajaxCreateQuotationPdf($quotation);

			if (!empty($to_customers) && !filter_var(Settings::get('site_email'), FILTER_VALIDATE_EMAIL) === false) {
			    /*
				foreach ( $to_customers as $item ) {
					if ( ! filter_var( $item->email, FILTER_VALIDATE_EMAIL ) === false ) {
						Mail::send( 'emails.quotation', array ( 'message_body' => $message_body ), function ( $message )
						use ( $item, $email_subject, $quotation_pdf, $item ) {
							$message->from( Settings::get( 'site_email' ), Settings::get( 'site_name' ) );
							$message->to( $item->email )->subject( $email_subject );
							$message->attach( url( '/pdf/' . $quotation_pdf ) );
						} );
					}
					$email                     = new Email();
					$email->assign_customer_id = $item->id;
					$email->from               = Sentinel::getUser()->id;
					$email->subject            = $email_subject;
					$email->message            = $message_body;
					$email->save();
				} */
				return response()->json( [ 'success' => 'success' ], 200 );
			}
			return response()->json(['error' => 'not_valid_data'], 500);
		} else {
			return response()->json(['error' => 'not_valid_data'], 500);
		}
	}

	private function ajaxCreateSaleOrderPdf(Saleorder $saleorder)
	{
		$filename = 'SalesOrder-' . $saleorder->sale_number;
		$pdf = App::make('dompdf.wrapper');
		$pdf->setPaper('a4','landscape');
		$pdf->loadView('saleorder_template.'.Settings::get('saleorder_template'), compact('saleorder'));
		$pdf->save('./pdf/' . $filename . '.pdf');
		$pdf->stream();
		return $filename . ".pdf";
	}
	/**
	 * Send sale order to email
	 *
	 * @Post("/send_sale_order")
	 * @Versions({"v1"})
	 * @Transaction({
	 *      @Request({"token": "foo", "sale_order_id":1,"subject":"subject","recipients":{1,2,3}, "body":"body"}),
	 *      @Response(200, body={"success":"success"}),
	 *      @Response(403, body={"error":"no_permissions"}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *       })
	 * })
	 */
	public function sendSaleOrder(Request $request)
	{
		$this->user = JWTAuth::parseToken()->authenticate();
		if($this->user->inRole('staff') && !$this->user->authorized('sales_orders.write')){
			return response()->json(['error' => 'no_permissions'], 403);
		}
		$data = array(
			'sale_order_id' => $request->input('sale_order_id'),
			'subject' => $request->input('subject'),
			'recipients' => $request->input('recipients'),
			'body' => $request->input('body')
		);
		$rules = array(
			'sale_order_id' => 'required',
			'subject' => 'required',
			'recipients' => 'required',
			'body' => 'required'
		);
		$validator = Validator::make($data, $rules);
		if ($validator->passes()) {
			$saleorder = Saleorder::find($request->sale_order_id)->first();
			$email_subject = $request->subject;
			$to_customers = Customer::whereIn('user_id', $request->recipients)->get();
			$email_body = $request->body;
			$message_body = Common::parse_template($email_body);
			$saleorder_pdf = $this->ajaxCreateSaleOrderPdf($saleorder);

			if (!empty($to_customers) && !filter_var(Settings::get('site_email'), FILTER_VALIDATE_EMAIL) === false) {
			    /*
				foreach ( $to_customers as $item ) {
					if ( ! filter_var( $item->email, FILTER_VALIDATE_EMAIL ) === false ) {
						Mail::send( 'emails.quotation', array ( 'message_body' => $message_body ), function ( $message )
						use ( $item, $email_subject, $saleorder_pdf, $item ) {
							$message->from( Settings::get( 'site_email' ), Settings::get( 'site_name' ) );
							$message->to( $item->email )->subject( $email_subject );
							$message->attach( url( '/pdf/' . $saleorder_pdf ) );
						} );
					}
					$email                     = new Email();
					$email->assign_customer_id = $item->id;
					$email->from               = Sentinel::getUser()->id;
					$email->subject            = $email_subject;
					$email->message            = $message_body;
					$email->save();
				}*/
				return response()->json( [ 'success' => 'success' ], 200 );
			}
			return response()->json(['error' => 'not_valid_data'], 500);
		} else {
			return response()->json(['error' => 'not_valid_data'], 500);
		}
	}

	private function ajaxCreateInvoicePdf(Invoice $invoice)
	{
		$filename = 'Invoice-'.$invoice->invoice_number;
		$pdf = App::make('dompdf.wrapper');
		$pdf->setPaper('a4','landscape');
		$print_type = trans('invoice.invoice_no');
		$pdf->loadView('invoice_template.'.Settings::get('invoice_template'), compact('invoice', 'print_type'));
		$pdf->save('./pdf/'.$filename.'.pdf');
		$pdf->stream();
		return $filename.".pdf";
	}
	/**
	 * Send invoice to email
	 *
	 * @Post("/send_invoice")
	 * @Versions({"v1"})
	 * @Transaction({
	 *      @Request({"token": "foo", "invoice_id":1,"subject":"subject","recipients":{1,2,3}, "body":"body"}),
	 *      @Response(200, body={"success":"success"}),
	 *      @Response(403, body={"error":"no_permissions"}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *       })
	 * })
	 */
	public function sendInvoice(Request $request)
	{
		$this->user = JWTAuth::parseToken()->authenticate();
		if($this->user->inRole('staff') && !$this->user->authorized('invoices.write')){
			return response()->json(['error' => 'no_permissions'], 403);
		}
		$data = array(
			'invoice_id' => $request->input('invoice_id'),
			'subject' => $request->input('subject'),
			'recipients' => $request->input('recipients'),
			'body' => $request->input('body')
		);
		$rules = array(
			'invoice_id' => 'required',
			'subject' => 'required',
			'recipients' => 'required',
			'body' => 'required'
		);
		$validator = Validator::make($data, $rules);
		if ($validator->passes()) {
			$invoice = Invoice::find($request->invoice_id)->first();
			$email_subject = $request->subject;
			$to_customers = Customer::whereIn('user_id', $request->recipients)->get();
			$email_body = $request->body;
			$message_body = Common::parse_template($email_body);
			$invoice_pdf = $this->ajaxCreateInvoicePdf($invoice);

			if (!empty($to_customers) && !filter_var(Settings::get('site_email'), FILTER_VALIDATE_EMAIL) === false) {
			    /*
				foreach ( $to_customers as $item ) {
					if ( ! filter_var( $item->email, FILTER_VALIDATE_EMAIL ) === false ) {
						Mail::send( 'emails.quotation', array ( 'message_body' => $message_body ), function ( $message )
						use ( $item, $email_subject, $invoice_pdf, $item ) {
							$message->from( Settings::get( 'site_email' ), Settings::get( 'site_name' ) );
							$message->to( $item->email )->subject( $email_subject );
							$message->attach( url( '/pdf/' . $invoice_pdf ) );
						} );
					}
					$email                     = new Email();
					$email->assign_customer_id = $item->id;
					$email->from               = Sentinel::getUser()->id;
					$email->subject            = $email_subject;
					$email->message            = $message_body;
					$email->save();
				} */
				return response()->json( [ 'success' => 'success' ], 200 );
			}
			return response()->json(['error' => 'not_valid_data'], 500);
		} else {
			return response()->json(['error' => 'not_valid_data'], 500);
		}
	}

    /**
     * Get phone
     *
     * @Post("/phone")
     * @Versions({"v1"})
     * @Transaction({
     *      @Request({"token": "foo","staff_id":"33"}),
     *      @Response(200, body={
    "user": {
    {
    "id": 1,
    "full_name": "full name",
    "first_name": "name",
    "last_name": "last name",
    },
    }
    }
    }),
     *      @Response(500, body={"error":"not_valid_data"})
     *       })
     * })
     */

     //Update status update_status router
    public function updateStatus(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('leads.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
        }
        $log=$request->input('logs');
        $leadID=$request->input('function_id');
        $data = array(
            'function_id' =>$leadID,
            'function_type'=>$request->input('function_type'),
            'status' => $request->input('status'),
            'logs' => $log,
            'date_create'=>date('Y-m-d H:i:s')
        );
        
        $rules = array(
            'function_id' => 'required',
            'function_type' => 'required',
            'status' => 'required',
        );
        if(isset($request->sales_person_id) && $request->sales_person_id!="" && $request->sales_person_id>0){
            $persion_id=$request->sales_person_id;
        }else{
            $persion_id=$this->user->id;
        }
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $history = History::insert($data);
            $leads = Lead::find($leadID);
            $dataLogs = array(
                'user_id' =>$persion_id,
                'logs'=>$log,
                'lead_id'=>$leadID,
                'phone'=>$leads->phone,
                'created_at'=> date("Y-m-d H:i:s"),
            );
            Logs::insert($dataLogs);
            $leads->status = $request->status;
            $leads->sales_person_id = $persion_id;
            $leads->save();
            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }
     //History log
     public function history(Request $request)
     {
         $this->user = JWTAuth::parseToken()->authenticate();
         if($this->user->inRole('staff') && !$this->user->authorized('leads.read')){
             return response()->json(['error' => 'no_permissions'], 403);
         }
         $data = array(
            'lead_id' => $request->input('lead_id')
        );
        $rules = array(
            'lead_id' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
         $history = History::select('history.*')->leftJoin('leads', 'leads.id', '=', 'history.function_id')
             ->where('history.function_id', $request->lead_id)
             ->get()
             ->map(function ($history) {
                 return [
                     'id' => $history->id,
                     'logs' => $history->logs,
                     'status' => $history->status,
                     'date_create' => $history->date_create,
                 ];
             });
 
         return response()->json(['history' => $history], 200);
        }else{
            return response()->json(['error' => 'not_valid_data'], 500);
        }
     }
     // //Follow up log
     public function leadFollowUp(Request $request)
     {
         $this->user = JWTAuth::parseToken()->authenticate();
         if($this->user->inRole('staff') && !$this->user->authorized('leads.read')){
             return response()->json(['error' => 'no_permissions'], 403);
         }
         $data = array(
            'lead_id' => $request->input('lead_id')
        );
        $rules = array(
            'lead_id' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
         $leadFollowUp = LeadFollowUp::select('leads_follow_up.*')->leftJoin('leads', 'leads.id', '=', 'leads_follow_up.lead_id')
             ->where('leads_follow_up.lead_id', $request->lead_id)
             ->get()
             ->map(function ($leadFollowUp) {
                 return [
                     'id' => $leadFollowUp->id,
                     'lead_id' => $leadFollowUp->lead_id,
                     'staff_id' => $leadFollowUp->staff_id,
                     'next_time_follow' => $leadFollowUp->next_time_follow,
                     'note' => $leadFollowUp->note,
                     'status' => $leadFollowUp->status,
                     'date_create' => $leadFollowUp->date_create,
                 ];
             });
 
            return response()->json(['leadFollowUp' => $leadFollowUp], 200);
        }else{
            return response()->json(['error' => 'not_valid_data'], 500);
        }
     }
      //Update follow up
    public function addFollowUp(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
        $data = array(
            'lead_id' => $request->lead_id,
            'staff_id'=>$this->user->id,
            'next_time_follow' => $request->next_time_follow,
            'note' => $request->note,
            'date_create'=>date('Y-m-d H:i:s')
        );
        $rules = array(
            'lead_id' => 'required',
            'next_time_follow' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $history = LeadFollowUp::insert($data);
            $work_status=2;
            $task = new Task;
            $task->lead_id=$request->lead_id;
            $task->task_title="Chăm sóc khách hàng";
            $task->task_description="Cài đặt lịch chăm sóc khách hàng";
            $task->task_end=$request->next_time_follow;
            $task->task_start=date("Y-m-d H:i:s");
            $task->full_name=$this->user->first_name." ".$this->user->last_name;
            $task->finished=0;
            $task->user_id=$this->user->id;
            $task->partner_id=$this->user->partner_id;
            $task->work_status=$work_status;
            $task->save();
            $task_id=$task->id;
            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }
    //Detail lead follow up
    public function detailFollowUp(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('leads.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
        $data = array(
            'id' => $request->input('id'),
        );
      
        $rules = array(
            'id' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $leadFollowUpDetail = LeadFollowUp::select('leads_follow_up.*', 'leads.opportunity')->leftJoin('leads', 'leads_follow_up.lead_id', '=', 'leads.id')->where('leads_follow_up.id', $request->input('id'))->first();
            if($leadFollowUpDetail){
                $leadFollowUoJson=[
                    'id' => $leadFollowUpDetail->id,
                    'lead_name' => $leadFollowUpDetail->opportunity,
                    'staff_id' => $leadFollowUpDetail->staff_id,
                    'next_time_follow' => $leadFollowUpDetail->next_time_follow,
                    'note' => $leadFollowUpDetail->note,
                    'status' => $leadFollowUpDetail->status
                ];
                return response()->json(['lead_follow_up' => $leadFollowUoJson], 200);
            }else{
                return response()->json(['error' => 'not_valid_data'], 200);
            }

        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }
    //Edit lead follow up
    public function updateFollowUp(Request $request)
    {
	    $this->user = JWTAuth::parseToken()->authenticate();
	    if($this->user->inRole('staff') && !$this->user->authorized('leads.read')){
		    return response()->json(['error' => 'no_permissions'], 403);
	    }
        $data = array(
            'id' => $request->input('id'),
            'next_time_follow' => $request->input('next_time_follow'),
            'note' => $request->input('note')
        );
        $rules = array(
            'id' => 'required',
            'next_time_follow' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $leadFollowUpDetail = LeadFollowUp::where('id', $request->input('id'))->first();
            if($leadFollowUpDetail){
                $leadFollowUpUpdate = LeadFollowUp::find($request->id);
                $leadFollowUpUpdate->next_time_follow = $request->next_time_follow;
                $leadFollowUpUpdate->note = $request->note;
                $leadFollowUpUpdate->save();

                $leadUpdate = Lead::find($leadFollowUpDetail->lead_id);
                $leadUpdate->next_follow_up = $request->next_time_follow;
                $leadUpdate->save();
                //Update nexttime
                NextTimeFollow::addUpdate($leadFollowUpDetail->lead_id, $request->next_time_follow, 0);
                // End update
            
                 // Add to log
                $leadDetail = Lead::find($leadFollowUpDetail->lead_id)->first();
                $dataLogs = array(
                    'user_id' => $this->user->id,
                    'lead_id'=>$leadFollowUpDetail->lead_id,
                    'logs'=>"@".$this->user->first_name." ".$this->user->last_name." đã cập nhật thời gian chăm sóc tiếp theo cho lead @".$leadDetail->opportunity." là @".$request->next_time_follow,
                    'phone'=>$leadDetail->phone,
                    'created_at'=> date("Y-m-d H:i:s"),
                );
                Logs::insert($dataLogs);
                // end add log

                return response()->json(['success' => 'success'], 200);
            }else{
                return response()->json(['error' => 'not_valid_data'], 200);
            }

        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }

    //Show tag list
    public function tags(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $partner_id=$this->user->partner_id;
        $group_id=$request->group_id;
        $tags = Tag::where("partner_id",$partner_id)
            ->where(function ($query) use ($group_id) {
                if($group_id!=""){
                    $query->where('group_client_id',$group_id);
                }
            })
            ->orderBy("position", "DESC")
            ->get()
            ->map(function ($tags) {
                return [
                    'id' => $tags->id,
                    'title' => $tags->title
                ];
            });
        return response()->json(['tags' => $tags], 200);
    }
    //Call action status list
    public function callActionStatus(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $partner_id=$this->user->partner_id;
        $type=1;
        if(isset($request->type) && $request->type!=""){
            $type=$request->type;
        }
        $list = CallActionStatus::where("partner_id",$partner_id)
            ->where('type_client',$type)->orderBy("position", "asc")
            ->get()
            ->map(function ($list) {
                return [
                    'id' => $list->id,
                    'title' => $list->title,
                    'type' => $list->type,
                    'color_bg' => $list->color_bg,
                    'color_text' => $list->color_text,
                    'icons' => asset('uploads/icons/'.$list->icons),
                ];
            });
        return response()->json(['callList' => $list], 200);
    }
    // //Call option list
    public function optionConfig(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $data = array(
            'type' => $request->input('type'),
        );
        //function_type
        $rules = array(
            'type' => 'required',
        );
        $partner_id=$this->user->partner_id;
        if($partner_id){
            $wherepartner=$partner_id;
        }else{
            $wherepartner=0;
        }
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $list = Option::where('category',$request->input('type'))->where('partner_id',$wherepartner)->orderBy("position", "asc")
            ->get()
            ->map(function ($list) {
                return [
                    'id' => $list->id,
                    'title' => $list->title,
                ];
            });
            return response()->json(['optionList' => $list], 200);
        }else{
            return response()->json(['error' => 'not_valid_data'], 500);
        }
    }
    //Get user sales
    public function userList(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $partner_id=0;
        $partner_id=$this->user->partner_id;
        $listPartnerUser=PartnerUser::select('partner_id')->where('user_id',$this->user->id)->pluck('partner_id')->toArray();
        if(!in_array($partner_id, $listPartnerUser)){
            array_push($listPartnerUser, $partner_id);
        }
        //->where('phone_verify',1)
        $keyword=$request->keyword;

        $listData = User::select("users.*")->join('partner_user','partner_user.user_id','=','users.id')
            ->whereIn('partner_user.partner_id',$listPartnerUser)
            ->where(function ($query) use ($keyword) {
                if($keyword!=""){
                    $query->where('users.email',$keyword);
                    $query->orWhere('users.first_name','like','%'.$keyword.'%');
                }
            })
            ->get()
            ->map(function ($list) {
                return [
                    'id' => $list->id,
                    'fullname' => $list->first_name." ".$list->last_name,
                ];
            });
      //  $userShow[]=array('id' => 0, 'fullname' =>"Tất cả");
      /*
        if($listData!=""){
            foreach($listData  as $dataRow){
                $userShow[]=array('id' => $dataRow["id"], 'fullname' => $dataRow["first_name"]." ".$dataRow["last_name"]);
            }
        } */
        
        return response()->json(['userList' => $listData], 200);

    }
     //Contacts list
     public function contacts(Request $request)
     {
         $this->user = JWTAuth::parseToken()->authenticate();
         if($this->user->inRole('staff') && !$this->user->authorized('leads.read')){
             return response()->json(['error' => 'no_permissions'], 403);
         }
         $data = array('user_id' => $this->user->id);
        $rules = array(
            'user_id' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
         $contacts = Contacts::where('user_id', $this->user->id)->where('status', 1)
             ->get()
             ->map(function ($contacts) {
                 return [
                     'id' => $contacts->id,
                     'name' => $contacts->name,
                     'phone' => $contacts->phone,
                     'date_create' => $contacts->date_create,
                 ];
             });
 
         return response()->json(['contacts' => $contacts], 200);
        }else{
            return response()->json(['error' => 'not_valid_data'], 500);
        }
     }
     //Contacts list
     public function addContact(Request $request)
     {
         $this->user = JWTAuth::parseToken()->authenticate();
         $data = array(
            'phone' => $request->input('phone'),
            'name'=>$request->input('name'),
            'title'=>$request->input('title'),
            'user_id'=> $this->user->id,
            'date_create'=> date("Y-m-d H:i:s"),
        );
        $rules = array(
            'phone' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $name=$request->input('name');
            $title=$request->input('title');
            $contactDetail = Contacts::where('phone', $request->phone)->where('user_id', $this->user->id)->first();
            if(!$contactDetail){
                Contacts::insert($data);
            }else{
                Contacts::where('id',$contactDetail["id"])->update(['name'=>$name, 'title'=>$title]);
            }
            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
     }
     //Delete contact
      //Contacts list
      public function deleteContact(Request $request)
      {
          $this->user = JWTAuth::parseToken()->authenticate();
          $data = array(
             'id' => $request->input('id'),
             'status'=>2,
             'deleted_at'=>date("Y-m-d H:i:s")
         );
         $rules = array(
             'id' => 'required',
         );
         $validator = Validator::make($data, $rules);
         if ($validator->passes()) {
            $contactDelete=Contacts::find($request->id);
            $contactDelete->delete();
             return response()->json(['success' => 'success'], 200);
         } else {
             return response()->json(['error' => 'not_valid_data'], 500);
         }
      }
      //
     //Contacts list
     public function updateContact(Request $request)
     {
         $this->user = JWTAuth::parseToken()->authenticate();
         $data = array(
            'id'=>$request->input('id'),
            'phone' => $request->input('phone'),
            'name'=>$request->input('name'),
        );
        $rules = array(
            'id' => 'required',
            'phone' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $contact = Contacts::find($request->id);
            $contact->update($request->only('phone', 'name'));
            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['error' => 'not_valid_data'], 500);
        }
     }

     //Check contact exist from call
     
     public static function updatesyndataToMS($partner_id, $fullname, $title, $phone, $email, $product,$status, $payment){
         if($partner_id>0){
            $partner=Partner::where("id",$partner_id)->whereNotNull("link_ma")->where("status_ma",1)->first();
            if($partner){
                    $seagment=33;
                    $url = $partner["link_ma"];
                    //header with content_type api key
                    $titleCallStatus="Chưa cập nhật";
                    if($status!="" && $status>0){
                        $callStatus = CallActionStatus::where("id",$status)->first();
                        if( $callStatus){
                            $titleCallStatus=$callStatus["title"];
                        }
                    }
                    $segment_lead_update=$partner["segment_lead_update"];
                    $server_key="";
                    $headers = array(
                                    'Content-Type:application/json',
                                    'Authorization:key='.$server_key
                                    );
                    $data = array("first_name"=>$fullname,
                                "phone"=>$phone,
                                "email"=>$email,
                                "product"=>$product,
                                "title"=>$title,
                                "status"=>$titleCallStatus,
                                "payment"=>$payment,
                                "segment"=>$segment_lead_update
                                );
                    $datapost=json_encode($data);
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_POST, true); 
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $datapost);
                    // Execute post
                    $result = curl_exec($ch);
                    // Close connection
                    curl_close($ch);
            }
         }
    }
    //Report Home pages
	
	public function historyChat(Request $request){
        $this->user = JWTAuth::parseToken()->authenticate();
		$psid =$request->psid;
		$lastIdChat=$request->lastIdChat;
		$timeload=$request->timeload;
		$limit=30;
		if($timeload>0){
			$limit=5;
        }
        $datenow=date("Y-m-d H:i:s", strtotime("-30 day"));
		$logData=Chatbox::where(function ($query) use ($psid) {
			$query->where('sender_id', '=', $psid)
				  ->orWhere('receive_id', '=', $psid);
		})->where(function ($query) use ($lastIdChat) {
			if($lastIdChat!="" && $lastIdChat>0){
				$query->where('id', '<', $lastIdChat);
			}
        })
        ->where('date_create','>=', $datenow)
		->orderBy("id", "desc")->offset(0)->limit($limit)->get();//->paginate($limit)->appends(request()->query());
		$logshow=array();
		$listIdUpdate=array();
		if($logData){
			foreach($logData as $key=>$values){
				$messenger="";
				$pre=$logData->get($key-1);
				$next=$logData->get($key+1);
				$idpre="";
				if($pre){
					$idpre=$pre["id"];
				}
				$idnext="";
				if($next){
					$idnext=$next["id"];
				}
				$listIdUpdate[]=$values["id"];
				$phone=$this->checkphone($values["extention"]); //str_replace(array("+84", "84"),array("0","0"),$values["extention"]);
                if(isset($values["title"]) && $values["title"]!=""){
					$messenger=$values["title"];
                }else{
                    if($values["messenger"]!="Attachments"){
                        $messenger=$this->turnUrlIntoHyperlink($values["messenger"]);
                    }
                }
                
                $email="";
				if($this->validate_email($values["messenger"])!==false){
					$email=$this->validate_email($values["messenger"]);
                }
                
				
                
				$logshow[]=array('id' => $values["id"], 'idpre' => $idpre, 'idnext' => $idnext, 'sender_id' => $values["sender_id"], 'receive_id' => $values["receive_id"], 'date' => date("d/m/Y H:i:s", strtotime($values["date_create"])), "messenger" =>$messenger, 'title'=>$values["title"], 'extention'=>$phone, 'lasttime'=>$values["timechat"], 'read'=>$values["read"], "email"=>$email,  "page_id"=>$values["page_id"],  "type_attch"=>$values["type_attch"],  "file_link"=>$values["file_link"]);
			}
		}
		if(count($listIdUpdate)>0){
			Chatbox::whereIn('id',$listIdUpdate)->where('read',0)->update(['read'=>1]);
        }
        return response()->json(['data' => $logshow], 200);
	}
	public function historySms(Request $request){
		$lead_id =$request->lead_id;
		$timelast=$request->lasttime;
		$lastIdChat=$request->lastIdChat;
		$timeload=$request->timeload;
        $user = JWTAuth::parseToken()->authenticate();
		$limit=100;
		if($timeload>0){
			$limit=5;
		}
		$logData=Smsdesc::where(function ($query) use ($lead_id) {
			$query->where('lead_id', '=', $lead_id);
		})->where(function ($query) use ($lastIdChat) {
			if($lastIdChat!="" && $lastIdChat>0){
				$query->where('id', '<', $lastIdChat);
			}
		})->where('partner_id',$user["partner_id"])
		->orderBy("id", "desc")->offset(0)->limit($limit)->get();//->paginate($limit)->appends(request()->query());
		$logshow=array();
		$listIdUpdate=array();
		if($logData){
			foreach($logData as $key=>$values){
				$pre=$logData->get($key-1);
				$next=$logData->get($key+1);
				$idpre="";
				if($pre){
					$idpre=$pre["id"];
				}
				$idnext="";
				if($next){
					$idnext=$next["id"];
				}
				$listIdUpdate[]=$values["id"];
				$logshow[]=array('id' => $values["id"], 'idpre' => $idpre, 'idnext' => $idnext, 'sender_id' => $values["sender"], 'receive_id' => $values["phone"], 'date'=>date("d/m/Y H:i:s", strtotime($values["created_at"])), "messenger" => $values["description"], 'extention'=>0, 'status'=>$values["status"], 'delivery'=>$values["delivery"], 'read'=>$values["read"]);
			}
		}
		if(count($listIdUpdate)>0){
			Smsdesc::whereIn('id',$listIdUpdate)->where('read',0)->update(['read'=>1]);
        }
        return response()->json(['data' => $logshow], 200);
    }
    public static function checkphone($phone)
	{
		$phone1=explode("-",$phone);
		if(count($phone1)>0){
			$phone=trim($phone1[0]);
		}
		$phone=str_replace(array("+840","+84"," "),array("0","0",""),$phone);
		if(substr($phone,0,3)=="840"){
			$phone=substr($phone,3,strlen($phone)-3);
		}
		if(substr($phone,0,2)=="84"){
			$phone=substr($phone,2,strlen($phone)-2);
		}
		if(substr($phone,0,2)=="00"){
			$phone=trim(substr($phone,1,strlen($phone)-1));
		}
		if((int)substr($phone,0,1)>0){
			$phone="0".$phone;
		}
		// Allow +, - and . in phone number
		$filtered_phone_number = filter_var($phone, FILTER_SANITIZE_NUMBER_INT);
		// Remove "-" from number
		$phone_to_check = str_replace(array("-"," "), array("",""), $filtered_phone_number);
		// Check the lenght of number
		// This can be customized if you want phone number from a specific country
		if (strlen($phone_to_check) < 10 || strlen($phone_to_check) > 12) {
			return false;
		} else {
			return $phone_to_check;
		}
    }
    public function turnUrlIntoHyperlink($string){
		if($string!=""){
			$reg_exUrl = "/(?i)\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’]))/";
		
			// Check if there is a url in the text
			if(preg_match_all($reg_exUrl, $string, $url)) {
		
				// Loop through all matches
				foreach($url[0] as $newLinks){
					if(strstr( $newLinks, ":" ) === false){
						$link = 'http://'.$newLinks;
					}else{
						$link = $newLinks;
					}
		
					// Create Search and Replace strings
					$search  = $newLinks;
					$replace = '<a href="'.$link.'" title="'.$newLinks.'" target="_blank">'.$link.'</a>';
					$string = str_replace($search, $replace, $string);
				}
			}
		
			//Return result
			return $string;
		}else{
			return "";
		}
    }
    public function validate_email($content) {

		if($content!="" && strlen($content)>5 && (strpos($content, '@') !== false)){
			$emailList=explode(" ",$content);
			if(count($emailList)>0){
				for($i=0;$i<count($emailList);$i++){
					if (filter_var($emailList[$i], FILTER_VALIDATE_EMAIL)) {
						return $emailList[$i];
					}
				}
				return false;
			}
			return false;;
		}
		return false;
		
    }


    public function assign(Request $request)
    {
        $title = trans('task.tasks');
        $title = trans( 'task.tasks' );
		$dateFormat = config('settings.date_format');
		$date  = addslashes($request->starting_date);
		$sales_id = addslashes($request->sales_id) ;
		$status  = addslashes($request->status) ;
        $keyword = addslashes($request->keyword);
        if($date!=""){
			$dateArray=explode("-",trim($date));
			$starting_date=$this->convertDate(trim($dateArray[0]));
			$ending_date=$this->convertDate(trim($dateArray[1]));
			$starting_date=date("Y-m-d", strtotime($starting_date." -1 days"));
			$ending_date=date("Y-m-d", strtotime($ending_date." +1 days"));
			$date_select=$date;
		}else{
			$starting_date=date("Y-m-d",strtotime('today - 1 days'));
			$ending_date=date("Y-m-d",strtotime('today +1 days'));
			$date_select=date("m/d/Y",strtotime('today - 1 days'))." - ".date("m/d/Y");
		}
        $usersList = "";
        $user = JWTAuth::parseToken()->authenticate();
        //Status list
        //Source
		$statusList = $this->optionRepository->getAll()->where('partner_id','=',$user->partner_id)->where( 'category', 'status_report' )->get()
        ->map( function ( $title ) {
            return [
                'title' => $title->title,
                'value' => $title->value,
            ];
        } )->pluck( 'title', 'value')
        ->prepend(trans('lead.select_function'), '');

        $listUserSales=$this->userRepository->getAllStaffOfUser($user->id);
        $salesList="";
        if($listUserSales){
            $salesList=User::join('partner_user','partner_user.user_id','=','users.id')
            ->select('users.id', 'users.first_name', 'users.last_name', 'users.user_avatar')
            //->where('partner_user.partner_id','=',$user->partner_id)
            ->whereIn('users.id',$listUserSales)
            ->get()
            ->map( function ( $salesList ) {
                return [ 
                    'title' => $salesList->first_name." ".$salesList->last_name,
                    'value' => $salesList->id,
                ];
            } )->pluck( 'title', 'value')
            ->prepend(trans('lead.all'), '');
        }else{
            $salesList=array(''=>trans('lead.all'));
        }
        $dateFormat = config('settings.date_format');
        $date  = addslashes($request->starting_date);
		
        $assignQuery=LeadAssignStatus::select('lead_assign_status.*', 'users.first_name as first_name', 'users.last_name as last_name', 'leads.contact_name as lead_name', 'leads.id as lead_id')
        ->join('users','users.id','=','lead_assign_status.assign_from')
		->join('leads','leads.id','=','lead_assign_status.lead_id')
		//->whereIn('lead_assign_status.assign_from',$listUserSales)
        ->where(function ($query)  use ($starting_date, $ending_date, $status, $keyword){
            if($keyword!=""){
                $query->where(function ($query1)  use ($keyword){
                    $query1->where('leads.contact_name', 'LIKE', "%{$keyword}%");
                    $query1->orWhere('leads.opportunity','LIKE', "%{$keyword}%");
                });
            }
            if($starting_date!=""){
                $query->where('lead_assign_status.date_create','>=',$starting_date);
            }
            if($ending_date!=""){
                $query->where('lead_assign_status.date_create','<=',$ending_date);
			} 
			if($status!=""){
                $query->where('lead_assign_status.status',$status);
            }
        })->where(function ($query)  use ($sales_id, $listUserSales, $user){
            if($sales_id!="" && $sales_id!="0"){
                $query->where('lead_assign_status.assign_from','=',$sales_id);
                $query->whereOr('lead_assign_status.user_id','=',$sales_id);
            }else{
                if($listUserSales){
                    array_push($listUserSales,$user->id);
				}
                if($listUserSales!="" && count($listUserSales)>0){
                    $query->whereIn('lead_assign_status.assign_from',$listUserSales);
                  //  $query->orWhereIn('lead_assign_status.user_id',$listUserSales);
                }else{
                    $query->where('lead_assign_status.assign_from',$user->id);
                 //   $query->orWhere('lead_assign_status.user_id',$user->id);
                } 
               
            }
        })->distinct()
        ->orderBy("lead_assign_status.id", "desc");
        $totalAssign=count($assignQuery->get());
        $assignPage=$assignQuery->paginate(20)->appends(request()->query());
        $assignList=$assignPage->map( function ( $assign) use ($dateFormat){
                return [
                    'id'	=> $assign->id,
                    'lead_id'   => $assign->lead_id,
					'date_assign'   => $assign->date_create,
                    'lead_name' => $assign->lead_name,
					'status' => $assign->status,
					'assign_to_id' => $assign->user_id,
                    'assign_to'  => User::where('id',$assign->user_id)->first(),
                    'taskwork'  => (isset($assign->task_id) && $assign->task_id>0)?Task::where('id',$assign->task_id)->first():null,
                ];
            }
        );
        return response()->json(['statusList' => $statusList,'totalAssign' => $totalAssign,'data' => $assignList, 'date_select' => $date_select, 'status' => $status], 200);

	}
	public function assignto(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $title = trans('task.tasks');
		$dateFormat = config('settings.date_format');
		$date  = addslashes($request->starting_date);
		$sales_id = addslashes($request->sales_id) ;
		$status  = addslashes($request->status) ;
        $keyword = addslashes($request->keyword);

        if($date!=""){
			$dateArray=explode("-",trim($date));
			$starting_date=$this->convertDate(trim($dateArray[0]));
			$ending_date=$this->convertDate(trim($dateArray[1]));
			$starting_date=date("Y-m-d", strtotime($starting_date." -1 days"));
			$ending_date=date("Y-m-d", strtotime($ending_date." +1 days"));
			$date_select=$date;
		}else{
			$starting_date=date("Y-m-d",strtotime('today - 1 days'));
			$ending_date=date("Y-m-d",strtotime('today +1 days'));
			$date_select=date("m/d/Y",strtotime('today - 1 days'))." - ".date("m/d/Y");
		}
        $usersList = "";
        //Source
		$statusList = $this->optionRepository->getAll()->where('partner_id','=',$user->partner_id)->where( 'category', 'status_report' )->get()
        ->map( function ( $title ) {
            return [
                'title' => $title->title,
                'value' => $title->value,
            ];
        } )->pluck( 'title', 'value')
		->prepend(trans('lead.select_function'), '');
		
        $listUserSales=$this->userRepository->getAllStaffOfUser($user->id);
        $salesList="";
        if($listUserSales){
            $salesList=User::join('partner_user','partner_user.user_id','=','users.id')
            ->select('users.id', 'users.first_name', 'users.last_name', 'users.user_avatar')
            //->where('partner_user.partner_id','=',$user->partner_id)
            ->whereIn('users.id',$listUserSales)
            ->get()
            ->map( function ( $salesList ) {
                return [ 
                    'title' => $salesList->first_name." ".$salesList->last_name,
                    'value' => $salesList->id,
                ];
            } )->pluck( 'title', 'value')
            ->prepend(trans('lead.all'), '');
        }else{
            $salesList=array(''=>trans('lead.all'));
        }
        $dateFormat = config('settings.date_format');
        $date  = addslashes($request->starting_date);
        $assignQuery=LeadAssignStatus::select('lead_assign_status.*', 'users.first_name as first_name', 'users.last_name as last_name', 'leads.contact_name as lead_name', 'leads.id as lead_id')
        ->join('users','users.id','=','lead_assign_status.assign_from')
		->join('leads','leads.id','=','lead_assign_status.lead_id')
		->where('lead_assign_status.user_id',$user->id)
        ->where(function ($query)  use ($starting_date, $ending_date, $status, $keyword){
            if($keyword!=""){
                $query->where(function ($query1)  use ($keyword){
                    $query1->where('leads.contact_name', 'LIKE', "%{$keyword}%");
                    $query1->orWhere('leads.opportunity','LIKE', "%{$keyword}%");
                });
			}
            if($starting_date!=""){
                $query->where('lead_assign_status.date_create','>=',$starting_date);
            }
            if($ending_date!=""){
                $query->where('lead_assign_status.date_create','<=',$ending_date);
			}
			if($status!=""){
                $query->where('lead_assign_status.status',$status);
            }
        })->where(function ($query)  use ($sales_id, $listUserSales, $user){
            if($sales_id!="" && $sales_id!="0"){
                $query->where('lead_assign_status.assign_from','=',$sales_id);
            }
        })->distinct()
        ->orderBy("lead_assign_status.id", "desc");
        $totalAssign=count($assignQuery->get());
        $assignPage=$assignQuery->paginate(20)->appends(request()->query());
        $assignList=$assignPage->map( function ( $assign) use ($dateFormat){
                return [
                    'id'	=> $assign->id,
                    'lead_id'   => $assign->lead_id,
					'date_assign'   => $assign->date_create,
                    'lead_name' => $assign->lead_name,
					'status' => $assign->status,
					'link' => $assign->link,
					'assign_to_id' => $assign->user_id,
                    'assign_from'  => User::where('id',$assign->assign_from)->first(),
                    'taskwork'  => (isset($assign->task_id) && $assign->task_id>0)?Task::where('id',$assign->task_id)->first():null,
                ];
            }
        );
        return response()->json(['statusList' => $statusList,'totalAssign' => $totalAssign,'assignList' => $assignList, 'date_select' => $date_select, 'status' => $status], 200);

    }
    //Transfer task

    public function transfertask(Request $request){
		$user = JWTAuth::parseToken()->authenticate();
		$lead_id=$request->lead_id;
		$user_to=$request->user_to;
		$user_fullname=$request->user_fullname;
        $group_id=$request->group_id;
        $task_description = addslashes($request->task_description) ;
        $task_title= addslashes($request->task_title) ;
		$user_assign=$user["id"];
		$type_assign=$user["type_assign"];
		$task_deadline  =date("Y-m-d H:i:s",strtotime(addslashes(trim($request->task_deadline))));
		$task_start  =date("Y-m-d H:i:s");
		$task_from_id=$user->id;
		if($lead_id!="" && $user_to!="" && $user_assign!=""){
			$checkAssign=LeadAssignStatus::where(['user_id'=>$user_to, "lead_id"=>$lead_id, "status"=>0])->first();
			if(isset($checkAssign) && $checkAssign!=""){
				return response()->json(['success' => 'Exit'], 200);
			}
			/** */
			$leadDetail = Lead::where("id",$lead_id)->where('partner_id',$user->partner_id)->first();
			if($leadDetail){
				if(isset($task_from_id) && $task_from_id!="" && $task_from_id>0){
					Task::where('id', $task_from_id)->update(["task_end"=>date("Y-m-d H:i:s"), "work_status"=>10]);
				}
				$task = new Task;
				$task->task_title=$task_title;
				$assignName=$user["first_name"]." ".$user["last_name"];
				$task->task_description=str_replace(array('{fullname}', '{client}'),array($assignName, $leadDetail["opportunity"]),$task_description);
				$task->lead_id=$lead_id;
				$task->task_start=$task_start;
				$task->task_end=$task_deadline;
				$task->full_name=$user_fullname;
				$task->finished=$leadDetail["status"];
				$task->user_id=$user_to;
				$task->partner_id=$user->partner_id;
				$task->task_from=$task_from_id; 
				$task->save();
				$taskid=$task->id;
				
				$leadAssign = new LeadAssignStatus;
				$leadAssign->assign_from=$user->id;
				$leadAssign->assign_from_name=$user["first_name"]." ".$user["last_name"];
				$leadAssign->user_id=$user_to;
				$leadAssign->lead_id=$lead_id;
				$leadAssign->status=0;
				$leadAssign->date_create=date("Y-m-d H:i:s");
				$leadAssign->time_assign=time();
				$leadAssign->task_id=$taskid;
				$leadAssign->group_id=$group_id;
				$leadAssign->link="";
				$leadAssign->save();
				$idAssign=$leadAssign->id;
				$protocol = isset($_SERVER["HTTPS"]) ? 'https://' : 'http://';
				$domain=$protocol.$_SERVER['HTTP_HOST'];
				if(!isset($type_assign) || $type_assign==0){
					$url=$domain.'/lead/chat?approve='.$idAssign;
				}else{
					$url=$domain.'/lead?approve='.$idAssign;
				}
				LeadAssignStatus::where('id', $idAssign)->update(["link"=>$url]);

				$dataLogs = array(
					'user_id' => $user_to,
					'logs'=>$user["first_name"]." ".$user["last_name"]." đã chuyển KH ".$leadDetail["opportunity"]." cho nhân viên ".$user_fullname,
					'logs_description'=>"",
					'created_at'=> date("Y-m-d H:i:s"),
					'lead_id'=>$lead_id,
			 	);
				Logs::insert($dataLogs);
			
				$notification = array(
					'partner_id'=>$user->partner_id,
					'user_id' => $user_to,
					'url'=> $url,
					'title'=>"Nhận KH từ ".$user["first_name"]." ".$user["last_name"],
					'desc'=>"Vui lòng chấp nhật KH từ ".$user["first_name"]." ".$user["last_name"].". Nếu bạn không thực hiện KH sẽ chuyển cho nhân viên khác",
					'status'=>0, 
					'created_at'=> date("Y-m-d H:i:s"),
					'date_notification'=>time()
				);
                Notification::insert($notification);
			    return response()->json(['success' => 'success'], 200);
			}
		}else{
			return response()->json(['success' => 'NoSuccess'], 200);
		}
		return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
    }
    public function groupUser(Request $request){
        $user = JWTAuth::parseToken()->authenticate();
        $groupStaff = GroupUser::where( 'partner_id', $user->partner_id)->get();
        $groupshow=array();
        if($groupStaff){
            foreach($groupStaff as $key=>$values){
                $select="";
                $groupshow[]=array('id'=>$values["id"], 'title'=>$values["name"]);
            }
        }
        return response()->json(['success' => 'Success', 'data'=>$groupshow], 200);
    }

    // 
    public function userGroup(Request $request){
        $user = JWTAuth::parseToken()->authenticate();
        $group =$request->group_id;
        $usershow=array();
        if($group>0){
            $userData=User::where('group_id',$group)->orderBy("id", "desc")->get();
            if($userData){
                foreach($userData as $key=>$values){
                    $select="";
                    $usershow[]=array('id'=>$values["id"], 'fullname'=>$values["first_name"]." ".$values["last_name"]);
                }
            }
        }
        return response()->json(['success' => 'Success', 'data'=>$usershow], 200);
    }
    // 
    public function source(Request $request){
        $user = JWTAuth::parseToken()->authenticate();
        $sourceshow=array();
        $list = Option::where('category',"function_type")->where('partner_id',$user->partner_id)->orderBy("position", "asc")->get();
        $sourceshow[]=array('id'=>0, 'title'=>"Nguồn");
        $sourceshow[]=array('id'=>0, 'title'=>"Tất cả");
        if($list){
            foreach($list as $key=>$values){
                $sourceshow[]=array('id'=>$values["id"], 'title'=>$values["title"]);
            }
        }
        return response()->json(['success' => 'Success', 'data'=>$sourceshow], 200);
    }
    public function branches(Request $request){
        $user = JWTAuth::parseToken()->authenticate();
        $branchesshow=array();
        $list = Branch::where('partner_id',$user->partner_id)->orderBy("position", "asc")->get();
        $branchesshow[]=array('id'=>0, 'title'=>"Chi nhánh");
        $branchesshow[]=array('id'=>0, 'title'=>"Tât cả");

        if($list){
            foreach($list as $key=>$values){
                $branchesshow[]=array('id'=>$values["id"], 'title'=>$values["name"]);
            }
        }
        return response()->json(['success' => 'Success', 'data'=>$branchesshow], 200);
    }

    public function listNotificationApp(Request $request)
	{
      //  
        $user = JWTAuth::parseToken()->authenticate();
		$partner_id=$user->partner_id;
        $user_id=$user->id;
		$data = array(
			'user_id' => $user_id,
			'partner_id' => $partner_id,
		);
		$rules = array(
			'user_id' => 'required',
		);
		$validator = Validator::make($data, $rules);
		if ($validator->passes()) {
			$notification = Notification::where("user_id", $user_id)->offset(0)->limit(100)->orderBy('id', 'DESC')->get();
			return response()->json(['notification' => $notification], 200);
		}else{
			return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
		}
    }
    
    public function uploadfile(Request $request)
	{
		$this->user = JWTAuth::parseToken()->authenticate();
		$data = array(
			'file' => $request->file
		);
		$rules = array(
			'file' => 'required'
		);
		$validator = Validator::make($data, $rules);
		if ($validator->passes()) {
            /*
			if (!is_null($request->file)) {
                $output_file = uniqid() . ".jpg";
               // $ext = end(explode('.', $request->file));

				//$output_file = uniqid() . $ext;
                $ifp = fopen(public_path() . '/uploads/messenger/' . $output_file, "wb");
				fwrite($ifp, base64_decode($request->file));
                fclose($ifp);
                $linkphoto="https://api.fastercrm.com/uploads/messenger/".$output_file;
              //  $user->user_avatar = $output_file;
                return response()->json(['success' => "success", 'images' => $linkphoto], 200);

			}else{
                return response()->json(['error' => "Không upload được hình"], 500);
            }*/
            $file = $request->file('file');
            // File Details
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $tempPath = $file->getRealPath();
            $fileSize = $file->getSize();
            $mimeType = $file->getMimeType();

            // Valid File Extensions
            $valid_extension = array("jpg","jpeg","png");

            // 2MB in Bytes
            $maxFileSize = 4097152;
             // Check file extension
            if(in_array(strtolower($extension),$valid_extension)){

                // Check file size
                if($fileSize <= $maxFileSize){
                    // File upload location
                    $location = public_path() . '/uploads/messenger';
                    // Upload file
                    //$location = fopen(public_path() . '/uploads/messenger' , "wb");
                    $file->move($location,$filename);
                    $linkphoto="https://api.fastercrm.com/uploads/messenger/".$filename;
                    return response()->json(['success' => "success", 'images' => $linkphoto], 200);

                }else{
                    return response()->json(['error' => 'File too large. File must be less than 4MB.'], 500);
                }
            }

		} else {
			return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
		}
    }
    
    // show log action show_logs
	public function logsLead(Request $request)
    {
		$this->user = JWTAuth::parseToken()->authenticate();
		$data = array(
            'user_id' => $this->user->id,
            'lead_id' => $request->lead_id,
		);
		$rules = array(
            'user_id' => 'required',
            'lead_id' => 'required',
		);
		$validator = Validator::make($data, $rules);
	    if ($validator->passes()) {
        $lead_id=$request->lead_id;
		$logs = Logs::where("lead_id", $lead_id)
			->orderBy('id', 'desc')->limit(50)->get()
			->map(function ($logs) {
                return [
                    'id' => $logs->id,
                    'logs' => $logs->logs,
                    'created_at' => date("Y-m-d H:i:s",strtotime($logs->created_at))
                ];
            });
			return response()->json(['logs' => $logs], 200);
	   }else{
			return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
	   }
    }


    public function reportHome(Request $request)
    {
        
        $this->user = JWTAuth::parseToken()->authenticate();
        $starting_date  = addslashes($request->start_date);
        $ending_date  = addslashes($request->end_date);
        $store  = addslashes($request->branches);
        $source_id  = addslashes($request->source_id);
        $user_id  = addslashes($request->staffs);
        $daterange  = addslashes($request->daterange);
       
        //partner_id$this->user
         if($starting_date!=""){
             $starting_date=date("Y-m-d 00:01:00", strtotime($starting_date));
             $ending_date=date("Y-m-d 23:59:00", strtotime($ending_date));
         }else{
             $ending_date=date("Y-m-d")." 23:59:59";
             $starting_date=date("Y-m-d");
         }
         if($daterange=='thismonth'){
             $starting_date=date("Y-m-01");
             $ending_date=date("Y-m-d 23:59:00");
             $date_select=date("m/d/Y",strtotime($starting_date))." - ".date("m/d/Y",strtotime($ending_date));
         }elseif($daterange=='lastmonth'){
             $starting_date=date("Y-m-d 00:00:00", strtotime("first day of previous month"));
             $ending_date=date("Y-m-d 23:59:59", strtotime("last day of previous month"));
             $date_select=date("m/d/Y",strtotime($starting_date))." - ".date("m/d/Y",strtotime($ending_date));
         }else{
             if(isset($daterange) && $daterange==1){
                 $starting_date=date("Y-m-d 00:00:00");
                 $ending_date=date("Y-m-d 23:59:59");
                 $starting_date_search=date("Y/m/d");
                 $ending_date_search=date("Ym/d");
                 $date_select=$starting_date_search." - ".$ending_date_search;
             }elseif(isset($daterange) && $daterange==2){
                     $starting_date=date("Y-m-d 00:00:00", strtotime("-".($daterange-1)." days"));
                     $ending_date=date("Y-m-d 23:59:59", strtotime("-".($daterange-1)." days"));
                     $starting_date_search=date("Y/m/d", strtotime("-".($daterange-1)." days"));
                     $ending_date_search=date("Ym/d", strtotime("-".($daterange-1)." days"));
                     $date_select=$starting_date_search." - ".$ending_date_search;

             }elseif(isset($daterange) && $daterange!=""){
                 $starting_date=date("Y-m-d 00:00:00",strtotime("-".$daterange." days"));
                 $ending_date=date("Y-m-d 23:59:59");
                 $starting_date_search=date("Y/m/d",strtotime("-".$daterange." days"));
                 $ending_date_search=date("Y/m/d");
                 $date_select=$starting_date_search." - ".$ending_date_search;
             }
         }
         
         //$userData=$this->userRepository->getUser();
         $partner_id=$this->user->partner_id;
          /*
         
         $listUserAssignCache = cache('listUserAssignCache6'.$partner_id.$userData->id);
         $listUserCache = cache('listUserCache6'.$partner_id.$userData->id);
        
         if(isset($listUserAssignCache) && $listUserAssignCache!=""){
             $listUserAssign=$listUserAssignCache;
             $listUser=$listUserCache;
 
         }else{
             $grouppermission = GroupUser::select('group_user.permissions')->join('users','users.group_id', '=', 'group_user.id')->where('users.id',$userData->id)->groupBy('users.id')->first();
            
             $listUser=$this->userRepository->getUserListMessenger($grouppermission, $userData);
             $listUserAssign=$this->userRepository->getAllUserOfPermissionOfStaff($userData, array("messenger.view_other", "messenger.full", "messenger.view_person"));
             Cache::put('listUserAssignCache6'.$partner_id.$userData->id, $listUserAssign, now()->addMinutes(10));
             Cache::put('listUserCache6'.$partner_id.$userData->id, $listUser, now()->addMinutes(10));
         } 

         $listUserCache="1";
         $usercache=$userData->id;
         $salesListCache = cache('salesListCache_2'.$partner_id.$listUserCache.$usercache);
         if(isset($salesListCache) && $salesListCache!=""){
             $salesList=$salesListCache;
         }else{

             if(isset($listUser) && $listUser!=""){
                 $salesList=User::join('partner_user','partner_user.user_id','=','users.id')
                             ->select('users.id', 'users.first_name', 'users.last_name', 'users.user_avatar')
                             ->where('partner_user.partner_id','=',$partner_id)
                             ->whereIn('users.id',$listUser)
                             ->get()
                             ->map( function ( $salesList ) {
                                 return [ 
                                     'title' => $salesList->first_name." ".$salesList->last_name,
                                     'value' => $salesList->id,
                                 ];
                             } )->pluck( 'title', 'value')
                             ->prepend(trans('lead.all'), '');
             }else{
                 $salesList=User::join('partner_user','partner_user.user_id','=','users.id')
                 ->select('users.id', 'users.first_name', 'users.last_name', 'users.user_avatar')
                 ->where('partner_user.partner_id','=',$partner_id)
                 ->get()
                 ->map( function ( $salesList ) {
                     return [ 
                         'title' => $salesList->first_name." ".$salesList->last_name,
                         'value' => $salesList->id,
                     ];
                 } )->pluck( 'title', 'value')
                 ->prepend(trans('lead.all'), '');
             }
             Cache::put('salesListCache_2'.$partner_id.$listUserCache.$salesListCache, $salesList, now()->addMinutes(10));
         } */
         $sales_id=$this->user->id;
        
         if(isset($sales_id) && $sales_id!=""){
             $id=$sales_id;
             $sales=array($sales_id);
         }else{
             $id=$userData->id;
             if($userData->user_id==1){
                 $sales=User::select('id')->where('partner_id','=',$partner_id)->get()->pluck( 'id')->toArray();
             }else{
                 $listSales=[];
                 if(isset($salesList) && count($salesList)>0){
                     foreach($salesList as $key=>$value){
                         if($key!=""){
                             $listSales[]=$key;
                         }
                     }
                 }
                 $sales=$listSales;
             }
         }
         if(count($sales)>0){
             $salesSearch=implode(",",$sales);
         }else{
             $salesSearch=$sales;
         }
         
        //Today
        $keyword="";
        $typeSearch="";
        $totalLeadToClient=0;
        $totalTaskNotFinish=0;
        $totalTaskFinish=0;
        $totalAssign=0;
        $totalLead=0;
        $totalLeadToClient=0;
        $totalLeadHasClient=0; 
        $date_start_today=date("Y-m-d 00:00:00");
        $date_end_today=date("Y-m-d 23:59:59");
        //End totay
        $taskQuery=Task::where('partner_id', $partner_id)
        ->whereIn('user_id',$sales)
        ->where(function ($query)  use ($date_start_today, $date_end_today){
            if($date_start_today!=""){
                $query->where('task_end','>=',$date_start_today);
            }
            if($date_end_today!=""){
                $query->where('task_end','<=',$date_end_today);
            } 
            /*
            if($status!=""){
                $query->where('tasks.work_status',$status);
            } */
        });
        $totalTask=$taskQuery->count();


        //Task not finish yet on 30day
        $statusnotfinish="2";
        $starting_date_30day=date("Y-m-d",strtotime('today - 30 days'));
        $ending_date_30day=date("Y-m-d",strtotime('today +1 days'));
        $taskQueryNotfinish=Task::where('partner_id', $partner_id)
        ->whereIn('user_id',$sales)
        ->where(function ($query)  use ($starting_date_30day, $ending_date_30day, $statusnotfinish){
            if($starting_date_30day!=""){
                $query->where('task_end','>=',$starting_date_30day);
            }
            if($ending_date_30day!=""){
                $query->where('task_end','<=',$ending_date_30day);
            } 
            
            if($statusnotfinish!=""){
                $query->where('work_status',$statusnotfinish);
            }
        });
        $totalTaskNotFinish=$taskQueryNotfinish->count();
        //End task not finish
       
        //Task not finish yet on 30day
        $statusfinish='1';
        $taskQueryFinish=Task::where('partner_id', $partner_id)
        ->whereIn('user_id',$sales)
        ->where(function ($query)  use ($starting_date_30day, $ending_date_30day, $statusfinish){
            if($starting_date_30day!=""){
                $query->where('tasks.task_end','>=',$starting_date_30day);
            }
            if($ending_date_30day!=""){
                $query->where('tasks.task_end','<=',$ending_date_30day);
            } 
            if($statusfinish!=""){
                $query->where('tasks.work_status',$statusfinish);
            }
        })->distinct();
        $totalTaskFinish=$taskQueryFinish->count();
        //End task not finish
        //Lead assign to
        $status='';
        $assignQuery=LeadAssignStatus::select('lead_assign_status.*')
        ->join('users','users.id','=','lead_assign_status.user_id')
        //->join('leads','leads.id','=','lead_assign_status.lead_id')
        ->where('users.partner_id',$partner_id)
        ->whereIn('lead_assign_status.user_id',$sales)
        ->where(function ($query)  use ($starting_date, $ending_date, $status){
            if($starting_date!=""){
                $query->where('lead_assign_status.date_create','>=',$starting_date);
            }
            if($ending_date!=""){
                $query->where('lead_assign_status.date_create','<=',$ending_date);
            }
            if($status!=""){
                $query->where('lead_assign_status.status',$status);
            }
        })->distinct();
        $totalAssign=$assignQuery->count();

        ///
        //Total Lead
        $leads = Lead::select('leads.id')
        ->where('leads.partner_id', $partner_id)
        ->whereIn('leads.sales_person_id',$sales)
        ->where(function ($query) use($keyword, $starting_date, $ending_date) {
             if($starting_date!=""){
                 $query->where('leads.created_at','>=',$starting_date);
             }
             if($ending_date!=""){
                 $query->where('leads.created_at','<=',$ending_date);
             }
            if($keyword!=""){
                $query3->where('leads.title', 'like', '%'.$keyword.'%')->orWhere('leads.contact_name', 'like', '%'.$keyword.'%')->orWhere('leads.email', 'like', '%'.$keyword.'%')->orWhere('leads.opportunity', 'like', '%'.$keyword.'%')->orWhere('leads.phone', $keyword);
            }
        })
        ->groupBy("leads.id");
        $totalLead=$leads->count();
        
         /* Lead to client */
        $leadsToClient = Lead::select('leads.id')
        ->whereIn('leads.sales_person_id',$sales)
        ->where('leads.partner_id', $partner_id)
        ->whereNotNull('leads.customer_id')
        ->where(function ($query3) use($keyword, $starting_date, $ending_date) {
             if($starting_date!=""){
                 $query3->where('leads.created_at','>=',$starting_date);
             }
             if($ending_date!=""){
                 $query3->where('leads.created_at','<=',$ending_date);
             }
            if($keyword!=""){
                $query3->where('leads.title', 'like', '%'.$keyword.'%')->orWhere('leads.contact_name', 'like', '%'.$keyword.'%')->orWhere('leads.email', 'like', '%'.$keyword.'%')->orWhere('leads.opportunity', 'like', '%'.$keyword.'%')->orWhere('leads.phone', $keyword);
            }
        })
        ->groupBy("leads.id");
        $totalLeadToClient=$leadsToClient->count();
       
        /* Lead has phone */
        $leadsHasPhone = Lead::select('leads.id')
        ->whereIn('leads.sales_person_id',$sales)
        ->where('leads.phone',"!=", "")
        ->where('leads.partner_id', $partner_id)
        ->where(function ($query3) use($keyword) {
            if($keyword!=""){
                $query3->where('leads.title', 'like', '%'.$keyword.'%')->orWhere('leads.contact_name', 'like', '%'.$keyword.'%')->orWhere('leads.email', 'like', '%'.$keyword.'%')->orWhere('leads.opportunity', 'like', '%'.$keyword.'%')->orWhere('leads.phone', $keyword);
            }
        })
        ->groupBy("leads.id");
        $totalLeadHasPhone=$leadsHasPhone->count();
        
        /* Total tương tác */

        $totalLeadOld=ReportStaff::whereIn('user_id',$sales)->where('created_at','>=', $starting_date)->where('created_at','<=',$ending_date)->where('type','totalLeadOld')->sum('number');

        $totalLeadReply=ReportStaff::whereIn('user_id',$sales)->where('created_at','>=', $starting_date)->where('created_at','<=',$ending_date)->where('type','totalLeadOldHasReply')->sum('number');

        $totalCommunication=$totalLeadOld+$totalLeadReply;


        $totalRevenus=ReportStaff::whereIn('user_id',$sales)
        ->where('created_at','>=', $starting_date)
        ->where('created_at','<=',$ending_date)
        ->where('type','totalInvoice')->sum('summary_invoice');

        $totalPaid=ReportStaff::whereIn('user_id',$sales)->where('created_at','>=', $starting_date)->where('created_at','<=',$ending_date)->where('type','totalOrderPaid')->sum('summary_invoice');

        $totalStill=$totalRevenus-$totalPaid;

        $totaReturn=ReportStaff::whereIn('user_id',$sales)->where('created_at','>=', $starting_date)->where('created_at','<=',$ending_date)->where('type','totalReturn')->sum('summary_invoice');
     

         //$partnerCache= cache('partner_01'.$partner_id);
         $partner_detail=Partner::where('id',$partner_id)->first();
         /*
         if(isset($partnerCache) && $partnerCache!=""){
             $partner_detail=$partnerCache;    
         }else{
             $partner_detail=Partner::where('id',$partner_id)->first();
             Cache::put('partner_01'.$partner_id, $partner_detail, now()->addMinutes(20));
         }   */
            
         $domain=$partner_detail["link_crm"];
         if($domain=="" || $domain==null){
             $domain="https://fastercrm.com";
         }
         $url=$domain;

         $link_revenus=$url."?start_date=".$starting_date."&end_date=".$ending_date."&warehouse_id=".$store."&source_id=".$source_id;
         $link_revenus_still=$url."?start_date=".$starting_date."&end_date=".$ending_date."&warehouse_id=".$store."&source_id=".$source_id."&payment_type=2";
         $link_revenus_paid=$url."?start_date=".$starting_date."&end_date=".$ending_date."&warehouse_id=".$store."&source_id=".$source_id."&payment_type=3";
        //////
        //////
        
        echo json_encode(['taskToday' => number_format($totalTask), 'totalTaskNotFinish'=>number_format($totalTaskNotFinish), 'totalTaskFinish'=>number_format($totalTaskFinish), 'totalAssign'=>number_format($totalAssign), 'totalLead'=>number_format($totalLead), 'totalLeadToClient'=>number_format($totalLeadToClient), 'totalLeadHasPhone'=>number_format($totalLeadHasPhone), 'totalCommunication'=>number_format($totalCommunication), 'revenues'=>["value"=>number_format($totalRevenus), "link_webview"=>$link_revenus], 'paid'=>["value"=>number_format($totalPaid), "link_webview"=>$link_revenus_paid], 'still'=>["value"=>number_format($totalStill), "link_webview"=>$link_revenus_still], 'return_money'=>["value"=>number_format($totaReturn), "link_webview"=>$link_revenus]], 200); 
        die();
    }


       //Report Home pages
       public function reportHomeAdmin(Request $request)
       {
           $this->user = JWTAuth::parseToken()->authenticate();
           $starting_date  = addslashes($request->start_date);
           $ending_date  = addslashes($request->end_date);
           $store  = addslashes($request->branches);
           $source_id  = addslashes($request->source_id);
           $user_id  = addslashes($request->staffs);
           $daterange  = addslashes($request->daterange);

           //partner_id
           if($starting_date!=""){
                $starting_date=date("Y-m-d 00:01:00", strtotime($starting_date));
                $ending_date=date("Y-m-d 23:59:00", strtotime($ending_date));
            }else{
                $ending_date=date("Y-m-d")." 23:59:59";
                $starting_date=date("Y-m-d");
            }
            /*
            if($daterange=='thismonth'){
                $starting_date=date("Y-m-01");
                $ending_date=date("Y-m-d 23:59:00");
                $date_select=date("m/d/Y",strtotime($starting_date))." - ".date("m/d/Y",strtotime($ending_date));
            }elseif($daterange=='lastmonth'){
                $starting_date=date("Y-m-d 00:00:00", strtotime("first day of previous month"));
                $ending_date=date("Y-m-d 23:59:59", strtotime("last day of previous month"));
                $date_select=date("m/d/Y",strtotime($starting_date))." - ".date("m/d/Y",strtotime($ending_date));
            }else{
                if(isset($daterange) && $daterange==1){
                    $starting_date=date("Y-m-d 00:00:00");
                    $ending_date=date("Y-m-d 23:59:59");
                    $starting_date_search=date("Y/m/d");
                    $ending_date_search=date("Ym/d");
                    $date_select=$starting_date_search." - ".$ending_date_search;
                }elseif(isset($daterange) && $daterange==2){
                        $starting_date=date("Y-m-d 00:00:00", strtotime("-".($daterange-1)." days"));
                        $ending_date=date("Y-m-d 23:59:59", strtotime("-".($daterange-1)." days"));
                        $starting_date_search=date("Y/m/d", strtotime("-".($daterange-1)." days"));
                        $ending_date_search=date("Ym/d", strtotime("-".($daterange-1)." days"));
                        $date_select=$starting_date_search." - ".$ending_date_search;

                }elseif(isset($daterange) && $daterange!=""){
                    $starting_date=date("Y-m-d 00:00:00",strtotime("-".$daterange." days"));
                    $ending_date=date("Y-m-d 23:59:59");
                    $starting_date_search=date("Y/m/d",strtotime("-".$daterange." days"));
                    $ending_date_search=date("Y/m/d");
                    $date_select=$starting_date_search." - ".$ending_date_search;
                }
            }
            */
            $userData=$this->userRepository->getUser();
            $partner_id=$userData->partner_id;
            $listUserAssignCache = cache('listUserAssignCache6'.$partner_id.$userData->id);
            $listUserCache = cache('listUserCache6'.$partner_id.$userData->id);
            

            if(isset($listUserAssignCache) && $listUserAssignCache!=""){
                $listUserAssign=$listUserAssignCache;
                $listUser=$listUserCache;
    
            }else{
                $grouppermission = GroupUser::select('group_user.permissions')->join('users','users.group_id', '=', 'group_user.id')->where('users.id',$userData->id)->groupBy('users.id')->first();
               
                $listUser=$this->userRepository->getUserListMessenger($grouppermission, $userData);
                $listUserAssign=$this->userRepository->getAllUserOfPermissionOfStaff($userData, array("messenger.view_other", "messenger.full", "messenger.view_person"));
                Cache::put('listUserAssignCache6'.$partner_id.$userData->id, $listUserAssign, now()->addMinutes(10));
                Cache::put('listUserCache6'.$partner_id.$userData->id, $listUser, now()->addMinutes(10));
            } 
            
            
             //salesList
            $listUserCache="1";
            $usercache=$userData->id;
            $salesListCache = cache('salesListCache_2'.$partner_id.$listUserCache.$usercache);
            if(isset($salesListCache) && $salesListCache!=""){
                $salesList=$salesListCache;
            }else{

                if(isset($listUser) && $listUser!=""){
                    $salesList=User::join('partner_user','partner_user.user_id','=','users.id')
                                ->select('users.id', 'users.first_name', 'users.last_name', 'users.user_avatar')
                                ->where('partner_user.partner_id','=',$partner_id)
                                ->whereIn('users.id',$listUser)
                                ->get()
                                ->map( function ( $salesList ) {
                                    return [ 
                                        'title' => $salesList->first_name." ".$salesList->last_name,
                                        'value' => $salesList->id,
                                    ];
                                } )->pluck( 'title', 'value')
                                ->prepend(trans('lead.all'), '');
                }else{
                    $salesList=User::join('partner_user','partner_user.user_id','=','users.id')
                    ->select('users.id', 'users.first_name', 'users.last_name', 'users.user_avatar')
                    ->where('partner_user.partner_id','=',$partner_id)
                    ->get()
                    ->map( function ( $salesList ) {
                        return [ 
                            'title' => $salesList->first_name." ".$salesList->last_name,
                            'value' => $salesList->id,
                        ];
                    } )->pluck( 'title', 'value')
                    ->prepend(trans('lead.all'), '');
                }
                Cache::put('salesListCache_2'.$partner_id.$listUserCache.$salesListCache, $salesList, now()->addMinutes(10));
            }
            
            if(isset($user_id) && $user_id!=""){
                $id=$user_id;
                $sales=array($user_id);
            }else{
                $id=$userData->id;
                if($userData->user_id==1){
                    $sales=User::select('id')->where('partner_id','=',$partner_id)->get()->pluck( 'id')->toArray();
                }else{
                    $listSales=[];
                    if(isset($salesList) && count($salesList)>0){
                        foreach($salesList as $key=>$value){
                            if($key!=""){
                                $listSales[]=$key;
                            }
                        }
                    }
                    $sales=$listSales;
                }
            }
            if(count($sales)>0){
                $salesSearch=implode(",",$sales);
            }else{
                $salesSearch=$sales;
            }
            
           //Today
           $keyword=$request->keyword;
           $typeSearch=$request->keyword;
           $totalLeadToClient=0;
           $totalTaskNotFinish=0;
           $totalTaskFinish=0;
           $totalAssign=0;
           $totalLead=0;
           $totalLeadToClient=0;
           $totalLeadHasClient=0; 
           $date_start_today=date("Y-m-d 00:00:00");
           $date_end_today=date("Y-m-d 23:59:59");
           //End totay
           $taskQuery=Task::where('partner_id', $partner_id)
           ->whereIn('user_id',$sales)
           ->where(function ($query)  use ($date_start_today, $date_end_today){
               if($date_start_today!=""){
                   $query->where('task_end','>=',$date_start_today);
               }
               if($date_end_today!=""){
                   $query->where('task_end','<=',$date_end_today);
               } 
               /*
               if($status!=""){
                   $query->where('tasks.work_status',$status);
               } */
           });
           $totalTask=$taskQuery->count();
 
           //Task not finish yet on 30day
           $statusnotfinish="2";
           $starting_date_30day=date("Y-m-d",strtotime('today - 30 days'));
           $ending_date_30day=date("Y-m-d",strtotime('today +1 days'));
           $taskQueryNotfinish=Task::where('partner_id', $partner_id)
           ->whereIn('user_id',$sales)
           ->where(function ($query)  use ($starting_date_30day, $ending_date_30day, $statusnotfinish){
               if($starting_date_30day!=""){
                   $query->where('task_end','>=',$starting_date_30day);
               }
               if($ending_date_30day!=""){
                   $query->where('task_end','<=',$ending_date_30day);
               } 
               
               if($statusnotfinish!=""){
                   $query->where('work_status',$statusnotfinish);
               }
           });
           $totalTaskNotFinish=$taskQueryNotfinish->count();
           //End task not finish
          
           //Task not finish yet on 30day
           $statusfinish='1';
           $taskQueryFinish=Task::where('partner_id', $partner_id)
           ->whereIn('user_id',$sales)
           ->where(function ($query)  use ($starting_date_30day, $ending_date_30day, $statusfinish){
               if($starting_date_30day!=""){
                   $query->where('tasks.task_end','>=',$starting_date_30day);
               }
               if($ending_date_30day!=""){
                   $query->where('tasks.task_end','<=',$ending_date_30day);
               } 
               if($statusfinish!=""){
                   $query->where('tasks.work_status',$statusfinish);
               }
           })->distinct();
           $totalTaskFinish=$taskQueryFinish->count();
           //End task not finish
           //Lead assign to
           $status='';
           $assignQuery=LeadAssignStatus::select('lead_assign_status.*')
           ->join('users','users.id','=','lead_assign_status.user_id')
           //->join('leads','leads.id','=','lead_assign_status.lead_id')
           ->where('users.partner_id',$partner_id)
           ->whereIn('lead_assign_status.user_id',$sales)
           ->where(function ($query)  use ($starting_date, $ending_date, $status){
               if($starting_date!=""){
                   $query->where('lead_assign_status.date_create','>=',$starting_date);
               }
               if($ending_date!=""){
                   $query->where('lead_assign_status.date_create','<=',$ending_date);
               }
               if($status!=""){
                   $query->where('lead_assign_status.status',$status);
               }
           })->distinct();
           $totalAssign=$assignQuery->count();
  
           ///
           //Total Lead
           $leads = Lead::select('leads.id')
           ->where('leads.partner_id', $partner_id)
           ->whereIn('leads.sales_person_id',$sales)
           ->where(function ($query) use($keyword, $starting_date, $ending_date) {
                if($starting_date!=""){
                    $query->where('leads.created_at','>=',$starting_date);
                }
                if($ending_date!=""){
                    $query->where('leads.created_at','<=',$ending_date);
                }
               if($keyword!=""){
                   $query3->where('leads.title', 'like', '%'.$keyword.'%')->orWhere('leads.contact_name', 'like', '%'.$keyword.'%')->orWhere('leads.email', 'like', '%'.$keyword.'%')->orWhere('leads.opportunity', 'like', '%'.$keyword.'%')->orWhere('leads.phone', $keyword);
               }
           })
           ->groupBy("leads.id");
           $totalLead=$leads->count();
           
            /* Lead to client */
           $leadsToClient = Lead::select('leads.id')
           ->whereIn('leads.sales_person_id',$sales)
           ->where('leads.partner_id', $partner_id)
           ->whereNotNull('leads.customer_id')
           ->where(function ($query3) use($keyword, $starting_date, $ending_date) {
                if($starting_date!=""){
                    $query3->where('leads.created_at','>=',$starting_date);
                }
                if($ending_date!=""){
                    $query3->where('leads.created_at','<=',$ending_date);
                }
               if($keyword!=""){
                   $query3->where('leads.title', 'like', '%'.$keyword.'%')->orWhere('leads.contact_name', 'like', '%'.$keyword.'%')->orWhere('leads.email', 'like', '%'.$keyword.'%')->orWhere('leads.opportunity', 'like', '%'.$keyword.'%')->orWhere('leads.phone', $keyword);
               }
           })
           ->groupBy("leads.id");
           $totalLeadToClient=$leadsToClient->count();
          
           /* Lead has phone */
           $leadsHasPhone = Lead::select('leads.id')
           ->whereIn('leads.sales_person_id',$sales)
           ->where('leads.phone',"!=", "")
           ->where('leads.partner_id', $partner_id)
           ->where(function ($query3) use($keyword) {
               if($keyword!=""){
                   $query3->where('leads.title', 'like', '%'.$keyword.'%')->orWhere('leads.contact_name', 'like', '%'.$keyword.'%')->orWhere('leads.email', 'like', '%'.$keyword.'%')->orWhere('leads.opportunity', 'like', '%'.$keyword.'%')->orWhere('leads.phone', $keyword);
               }
           })
           ->groupBy("leads.id");
           $totalLeadHasPhone=$leadsHasPhone->count();
           
           /* Total tương tác */

           $totalLeadOld=ReportStaff::whereIn('user_id',$sales)->where('created_at','>=', $starting_date)->where('created_at','<=',$ending_date)->where('type','totalLeadOld')->sum('number');

           $totalLeadReply=ReportStaff::whereIn('user_id',$sales)->where('created_at','>=', $starting_date)->where('created_at','<=',$ending_date)->where('type','totalLeadOldHasReply')->sum('number');

           $totalCommunication=$totalLeadOld+$totalLeadReply;


           $totalRevenus=ReportStaff::whereIn('user_id',$sales)
           ->where('created_at','>=', $starting_date)
           ->where('created_at','<=',$ending_date)
           ->where('type','totalInvoice')->sum('summary_invoice');

           $totalPaid=ReportStaff::whereIn('user_id',$sales)->where('created_at','>=', $starting_date)->where('created_at','<=',$ending_date)->where('type','totalOrderPaid')->sum('summary_invoice');

           $totalStill=$totalRevenus-$totalPaid;

           $totaReturn=ReportStaff::whereIn('user_id',$sales)->where('created_at','>=', $starting_date)->where('created_at','<=',$ending_date)->where('type','totalReturn')->sum('summary_invoice');
        

            $partnerCache= cache('partner_01'.$partner_id);
            //$partner_detail=Partner::where('id',$partner_id)->first();
            
            if(isset($partnerCache) && $partnerCache!=""){
                $partner_detail=$partnerCache;    
            }else{
                $partner_detail=Partner::where('id',$partner_id)->first();
                Cache::put('partner_01'.$partner_id, $partner_detail, now()->addMinutes(20));
            }  

            $domain=$partner_detail["link_crm"];
            if($domain=="" || $domain==null){
                $domain="https://fastercrm.com";
            }
            $url=$domain;

            $link_revenus=$url."?start_date=".$starting_date."&end_date=".$ending_date."&warehouse_id=".$store."&source_id=".$source_id."&user_id=0";//.$user_id;
           //////
           //////
           echo json_encode(['taskToday' => number_format($totalTask), 'totalTaskNotFinish'=>number_format($totalTaskNotFinish), 'totalTaskFinish'=>number_format($totalTaskFinish), 'totalAssign'=>number_format($totalAssign), 'totalLead'=>number_format($totalLead), 'totalLeadToClient'=>number_format($totalLeadToClient), 'totalLeadHasPhone'=>number_format($totalLeadHasPhone), 'totalCommunication'=>number_format($totalCommunication), 'revenues'=>["value"=>number_format($totalRevenus), "link_webview"=>$link_revenus], 'paid'=>["value"=>number_format($totalPaid), "link_webview"=>$link_revenus], 'still'=>["value"=>number_format($totalStill), "link_webview"=>$link_revenus], 'return_money'=>["value"=>number_format($totaReturn), "link_webview"=>$link_revenus]], 200); 
           die();
       }
      //Task list

      public function reportHomeFlex(Request $request){
        $this->user = JWTAuth::parseToken()->authenticate();
        
        $start_date  = addslashes($request->start_date);  
        $end_date  = addslashes($request->end_date); 
        if($end_date!=$start_date){
            $timeDiff = abs(strtotime($end_date) - strtotime($start_date));
            $numberDays = $timeDiff/86400;
            $numberDays = intval($numberDays);
            if($numberDays==1){
                $end_date =$start_date; 
            }
        }
        if($start_date=="Invalid date"){
           $start_date=date('Y-m-01', strtotime("-1 months"));
           $numberDaysOfMonth = cal_days_in_month(CAL_GREGORIAN, date('m', strtotime("-1 months")), date('Y', strtotime("-1 months")));
           $end_date=date('Y-m-'.$numberDaysOfMonth, strtotime("-1 months"));
        }
        $store  = addslashes($request->branches);
        $source_id  = addslashes($request->source_id);
        
        $sales_id  = addslashes($request->staffs);
        $daterange  = addslashes($request->daterange);
        $token=addslashes($request->token); 
        @file_put_contents("/home/crmsmart/web/api.salesdy.com/public_html/smartweb/public/log/log.txt", $request);

        $keyword="";
        if($end_date!=""){
            $starting_date=date("Y-m-d", strtotime($start_date));
            $ending_date=date("Y-m-d", strtotime($end_date));
        }else{
            $ending_date=date("Y-m-d")." 23:59:59";
            $starting_date=date("Y-m-d");
        }
        //partner_id
        /*
        if($starting_date!=""){
             $starting_date=date("Y-m-d 00:01:00", strtotime($starting_date));
             $ending_date=date("Y-m-d 23:59:00", strtotime($ending_date));
         }else{
             $ending_date=date("Y-m-d")." 23:59:59";
             $starting_date=date("Y-m-d");
         }
         if($daterange=='thismonth'){
             $starting_date=date("Y-m-01");
             $ending_date=date("Y-m-d 23:59:00");
             $date_select=date("m/d/Y",strtotime($starting_date))." - ".date("m/d/Y",strtotime($ending_date));
         }elseif($daterange=='lastmonth'){
             $starting_date=date("Y-m-d 00:00:00", strtotime("first day of previous month"));
             $ending_date=date("Y-m-d 23:59:59", strtotime("last day of previous month"));
             $date_select=date("m/d/Y",strtotime($starting_date))." - ".date("m/d/Y",strtotime($ending_date));
         }else{
             if(isset($daterange) && $daterange==1){
                 $starting_date=date("Y-m-d 00:00:00");
                 $ending_date=date("Y-m-d 23:59:59");
                 $starting_date_search=date("Y/m/d");
                 $ending_date_search=date("Ym/d");
                 $date_select=$starting_date_search." - ".$ending_date_search;
             }elseif(isset($daterange) && $daterange==2){
                     $starting_date=date("Y-m-d 00:00:00", strtotime("-".($daterange-1)." days"));
                     $ending_date=date("Y-m-d 23:59:59", strtotime("-".($daterange-1)." days"));
                     $starting_date_search=date("Y/m/d", strtotime("-".($daterange-1)." days"));
                     $ending_date_search=date("Ym/d", strtotime("-".($daterange-1)." days"));
                     $date_select=$starting_date_search." - ".$ending_date_search;

             }elseif(isset($daterange) && $daterange!=""){
                 $starting_date=date("Y-m-d 00:00:00",strtotime("-".$daterange." days"));
                 $ending_date=date("Y-m-d 23:59:59");
                 $starting_date_search=date("Y/m/d",strtotime("-".$daterange." days"));
                 $ending_date_search=date("Y/m/d");
                 $date_select=$starting_date_search." - ".$ending_date_search;
             }
         }
         */
         $userData=$this->user;
         $partner_id=$userData->partner_id;

         $listUserAssignCache = cache('listUserAssignCache6'.$partner_id.$userData->id);
         $listUserCache = cache('listUserCache6'.$partner_id.$userData->id);

         if(isset($listUserAssignCache) && $listUserAssignCache!=""){
             $listUserAssign=$listUserAssignCache;
             $listUser=$listUserCache;
 
         }else{
             $grouppermission = GroupUser::select('group_user.permissions')->join('users','users.group_id', '=', 'group_user.id')->where('users.id',$userData->id)->groupBy('users.id')->first();
            
             $listUser=$this->userRepository->getUserListMessenger($grouppermission, $userData);
             $listUserAssign=$this->userRepository->getAllUserOfPermissionOfStaff($userData, array("messenger.view_other", "messenger.full", "messenger.view_person"));
             Cache::put('listUserAssignCache6'.$partner_id.$userData->id, $listUserAssign, now()->addMinutes(10));
             Cache::put('listUserCache6'.$partner_id.$userData->id, $listUser, now()->addMinutes(10));
         } 


          //salesList
         $listUserCache="1";
         $usercache=$userData->id;
         $salesListCache = cache('salesListCache_2'.$partner_id.$listUserCache.$usercache);
         if(isset($salesListCache) && $salesListCache!=""){
             $salesList=$salesListCache;
         }else{
 
             if(isset($listUser) && $listUser!=""){
                 $salesList=User::join('partner_user','partner_user.user_id','=','users.id')
                             ->select('users.id', 'users.first_name', 'users.last_name', 'users.user_avatar')
                             ->where('partner_user.partner_id','=',$partner_id)
                             ->whereIn('users.id',$listUser)
                             ->get()
                             ->map( function ( $salesList ) {
                                 return [ 
                                     'title' => $salesList->first_name." ".$salesList->last_name,
                                     'value' => $salesList->id,
                                 ];
                             } )->pluck( 'title', 'value')
                             ->prepend(trans('lead.all'), '');
             }else{
                 $salesList=User::join('partner_user','partner_user.user_id','=','users.id')
                 ->select('users.id', 'users.first_name', 'users.last_name', 'users.user_avatar')
                 ->where('partner_user.partner_id','=',$partner_id)
                 ->get()
                 ->map( function ( $salesList ) {
                     return [ 
                         'title' => $salesList->first_name." ".$salesList->last_name,
                         'value' => $salesList->id,
                     ];
                 } )->pluck( 'title', 'value')
                 ->prepend(trans('lead.all'), '');
             }
             Cache::put('salesListCache_2'.$partner_id.$listUserCache.$salesListCache, $salesList, now()->addMinutes(10));
         }
         
         if(isset($sales_id) && $sales_id!=""){
             $id=$sales_id;
             $sales=array($sales_id);
         }else{
             $id=$userData->id;
             if($userData->user_id==1){
                 $sales=User::select('id')->where('partner_id','=',$partner_id)->get()->pluck( 'id')->toArray();
             }else{
                 $listSales=[];
                 if(isset($salesList) && count($salesList)>0){
                     foreach($salesList as $key=>$value){
                         if($key!=""){
                             $listSales[]=$key;
                         }
                     }
                 }
                 $sales=$listSales;
             }
         }
         if(count($sales)>0){
             $salesSearch=implode(",",$sales);
         }else{
             $salesSearch=$sales;
         }
         

        //Today
        $totalLeadToClient=0;
        $totalTaskNotFinish=0;
        $totalTaskFinish=0;
        $totalAssign=0;
        $totalLead=0;
        $totalLeadToClient=0;
        $totalLeadHasClient=0; 
        $date_start_today=date("Y-m-d 00:00:00");
        $date_end_today=date("Y-m-d 23:59:59");
        //End totay
        /*
        $taskQuery=Task::where('partner_id', $partner_id)
        ->whereIn('user_id',$sales)
        ->where(function ($query)  use ($date_start_today, $date_end_today){
            if($date_start_today!=""){
                $query->where('task_end','>=',$date_start_today);
            }
            if($date_end_today!=""){
                $query->where('task_end','<=',$date_end_today);
            } 
        });
        $totalTask=$taskQuery->count();
*/
        //Task not finish yet on 30day
        /*
        $statusnotfinish="2";
        $starting_date_30day=date("Y-m-d",strtotime('today - 30 days'));
        $ending_date_30day=date("Y-m-d",strtotime('today +1 days'));
        $taskQueryNotfinish=Task::where('partner_id', $partner_id)
        ->whereIn('user_id',$sales)
        ->where(function ($query)  use ($starting_date_30day, $ending_date_30day, $statusnotfinish){
            if($starting_date_30day!=""){
                $query->where('task_end','>=',$starting_date_30day);
            }
            if($ending_date_30day!=""){
                $query->where('task_end','<=',$ending_date_30day);
            } 
            
            if($statusnotfinish!=""){
                $query->where('work_status',$statusnotfinish);
            }
        });
        $totalTaskNotFinish=$taskQueryNotfinish->count();
        */
        //End task not finish
       
        //Task not finish yet on 30day
        /*
        $statusfinish='1';
        $taskQueryFinish=Task::where('partner_id', $partner_id)
        ->whereIn('user_id',$sales)
        ->where(function ($query)  use ($starting_date_30day, $ending_date_30day, $statusfinish){
            if($starting_date_30day!=""){
                $query->where('tasks.task_end','>=',$starting_date_30day);
            }
            if($ending_date_30day!=""){
                $query->where('tasks.task_end','<=',$ending_date_30day);
            } 
            if($statusfinish!=""){
                $query->where('tasks.work_status',$statusfinish);
            }
        })->distinct();
        $totalTaskFinish=$taskQueryFinish->count(); */
        //End task not finish
        //Lead assign to
        /*
        $status='';
        $assignQuery=LeadAssignStatus::select('lead_assign_status.*')
        ->join('users','users.id','=','lead_assign_status.user_id')
        ->where('users.partner_id',$partner_id)
        ->whereIn('lead_assign_status.user_id',$sales)
        ->where(function ($query)  use ($starting_date, $ending_date, $status){
            if($starting_date!=""){
                $query->where('lead_assign_status.date_create','>=',$starting_date);
            }
            if($ending_date!=""){
                $query->where('lead_assign_status.date_create','<=',$ending_date);
            }
            if($status!=""){
                $query->where('lead_assign_status.status',$status);
            }
        })->distinct();
        $totalAssign=$assignQuery->count();
*/
        ///
        $cacheTotalOrder="DataCacheTotal1".md5($partner_id.implode(",",$sales).$ending_date.$starting_date.$store.$source_id);
        $cacheCode="DataCache1".md5($partner_id.implode(",",$sales).$ending_date.$starting_date.$store.$source_id);
        //Total Lead
        $cacheTotalLead=cache('totalLeadCache'.$cacheTotalOrder);
        if(isset($cacheTotalLead) && $cacheTotalLead!=""){
            $totalLead=$cacheTotalLead;
        }else{
            $totalLead = Lead::select('leads.id')
            ->where('leads.partner_id', $partner_id)
            ->whereIn('leads.sales_person_id',$sales)
            ->where(function ($query) use($keyword, $starting_date, $ending_date) {
                if($starting_date!=""){
                    $query->where('leads.created_at','>=',$starting_date);
                }
                if($ending_date!=""){
                    $query->where('leads.created_at','<=',$ending_date);
                }
            })->count('leads.id');
            Cache::put('totalLeadCache'.$cacheTotalOrder, $totalLead, now()->addMinutes(20));
        }

         /* Lead to client */
       

        $cacheTotalLeadToClient=cache('totalLeadToClientCache'.$cacheTotalOrder);
        if(isset($cacheTotalLeadToClient) && $cacheTotalLeadToClient!=""){
            $totalLeadToClient=$cacheTotalLeadToClient;
        }else{
            $totalLeadToClient = Lead::select('leads.id')
            ->whereIn('leads.sales_person_id',$sales)
            ->where('leads.partner_id', $partner_id)
            ->whereNotNull('leads.customer_id')
            ->where(function ($query3) use($starting_date, $ending_date) {
                 if($starting_date!=""){
                     $query3->where('leads.created_at','>=',$starting_date);
                 }
                 if($ending_date!=""){
                     $query3->where('leads.created_at','<=',$ending_date);
                 }
            })->count();

            Cache::put('totalLeadCache'.$cacheTotalOrder, $totalLeadToClient, now()->addMinutes(20));
        }


        $cacheTotalLeadHasPhone=cache('totalLeadHasPhoneCache'.$cacheTotalOrder);
        if(isset($cacheTotalLeadHasPhone) && $cacheTotalLeadHasPhone!=""){
            $totalLeadHasPhone=$cacheTotalLeadHasPhone;
        }else{
            $totalLeadHasPhone = Lead::select('leads.id')
                ->whereIn('leads.sales_person_id',$sales)
                ->where('leads.phone',"!=", "")
                ->where('leads.partner_id', $partner_id)
                ->where(function ($query3) use($starting_date, $ending_date) {
                    if($starting_date!=""){
                        $query3->where('leads.created_at','>=',$starting_date);
                    }
                    if($ending_date!=""){
                        $query3->where('leads.created_at','<=',$ending_date);
                    }
            })->count();

            Cache::put('totalLeadHasPhoneCache'.$cacheTotalOrder, $totalLeadHasPhone, now()->addMinutes(20));
        }

        /* Lead has phone */
    
        /* Total tương tác */
        /*
        $cacheTotalLeadOld=cache('totalLeadOldCache'.$cacheTotalOrder);
        if(isset($cacheTotalLeadOld) && $cacheTotalLeadOld!=""){
            $totalLeadOld=$cacheTotalLeadOld;
        }else{
            $totalLeadOld=ReportStaff::whereIn('user_id',$sales)->where('created_at','>=', $starting_date)->where('created_at','<=',$ending_date)->where('type','totalLeadOld')->sum('number');
            Cache::put('totalLeadOldCache'.$cacheTotalOrder, $totalLeadOld, now()->addMinutes(20));
        } */
        $cacheTotalLeadOld=cache('TotalLeadOld'.$cacheCode);
        if(isset($cacheTotalLeadOld) && $cacheTotalLeadOld!=""){
            $totalLeadOld=$cacheTotalLeadOld;
        }else{
            $totalLeadOld=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','totalLeadOld')->sum('number');
            Cache::put('TotalLeadOld'.$cacheCode, $totalLeadOld, now()->addMinutes(20));
        } 
        $cacheTotalLeadReplyOld=cache('totalLeadOldReplyCache'.$cacheTotalOrder);
        if(isset($cacheTotalLeadReplyOld) && $cacheTotalLeadReplyOld!=""){
            $totalLeadReply=$cacheTotalLeadReplyOld;
        }else{
            $totalLeadReply=ReportStaff::whereIn('user_id',$sales)->where('created_at','>=', $starting_date)->where('created_at','<=',$ending_date)->where('type','totalLeadOldHasReply')->sum('number');
            Cache::put('totalLeadOldReplyCache'.$cacheTotalOrder, $totalLeadReply, now()->addMinutes(20));
        }

        $totalCommunication=$totalLeadOld+$totalLeadReply;

        $cacheReportTags=cache('ReportTags2'.$cacheCode);
        if(isset($cacheReportTags) && $cacheReportTags!=""){
            $totalReportTags=$cacheReportTags;
        }else{
            $totalReportTags=ReportTags::whereIn('user_id',$sales)->where('created_at','>=', $starting_date)->where('created_at','<=',$ending_date)->count('id');
            Cache::put('ReportTags2'.$cacheCode, $totalReportTags, now()->addMinutes(20));
        }

       
        $cacheTotalOrderFile=cache('TotalOrder28'.$cacheTotalOrder);
        if(isset($cacheTotalOrderFile) && $cacheTotalOrderFile!=""){
            $totalOrder=$cacheTotalOrderFile;
        }else{
            $totalOrder=ReportStaff::join('users','users.id','=','report_staff.user_id')->whereIn('report_staff.user_id',$sales)
            ->where('report_staff.date_create','>=', $starting_date)
            ->where('report_staff.date_create','<=',$ending_date)
            ->where(function ($query3) use($store, $source_id) {
                if($store!="" && $store>0  && $store!=0){
                    $query3->where('users.branch_id', $store);
                }
                if($source_id!="" && $source_id>0 && $source_id!=0){
                    $query3->where('report_staff.source_id',$source_id);
                }
           })
            ->where('report_staff.type','totalInvoice')->sum('report_staff.number');
            Cache::put('TotalOrder28'.$cacheTotalOrder, $totalOrder, now()->addMinutes(10));
        } 
    
       // $cacheTotalRevenue=md5($partner_id.implode(",",$sales).$ending_date.$starting_date);
        $cacheTotalRevenueFile=cache('TotalRevenus28'.$cacheTotalOrder);
        if(isset($cacheTotalRevenueFile) && $cacheTotalRevenueFile!=""){
            $totalRevenus=$cacheTotalRevenueFile;
        }else{
            $totalRevenus=ReportStaff::join('users','users.id','=','report_staff.user_id')
            ->whereIn('report_staff.user_id',$sales)
            ->whereDate('report_staff.date_create','>=', $starting_date)
            ->whereDate('report_staff.date_create','<=',$ending_date)
            ->where(function ($query3) use($store, $source_id) {
                if($store!="" && $store>0  && $store!=0){
                    $query3->where('users.branch_id', $store);
                }
                if($source_id!="" && $source_id>0  && $source_id!=0){
                    $query3->where('report_staff.source_id',$source_id);
                }
           })
            ->where('report_staff.type','totalInvoice')->sum('report_staff.summary_invoice');
            Cache::put('TotalRevenus28'.$cacheTotalOrder, $totalRevenus, now()->addMinutes(10));
        } 

        $cacheTotalPaidFile=cache('TotalPaid4'.$cacheCode);
        if(isset($cacheTotalPaidFile) && $cacheTotalPaidFile!=""){
            $totalPaid=$cacheTotalPaidFile;
        }else{
            $totalPaid=ReportStaff::join('users','users.id','=','report_staff.user_id')
            ->whereIn('report_staff.user_id',$sales)
            ->where('report_staff.date_create','>=', $starting_date)
            ->where('report_staff.date_create','<=',$ending_date)
            ->where(function ($query3) use($store, $source_id) {
                if($store!="" && $store>0  && $store!=0){
                    $query3->where('users.branch_id', $store);
                }
                if($source_id!="" && $source_id>0  && $source_id!=0){
                    $query3->where('report_staff.source_id',$source_id);
                }
           })
            ->where('report_staff.type','totalOrderPaid')->sum('report_staff.summary_invoice');
            Cache::put('TotalPaid4'.$cacheCode, $totalPaid, now()->addMinutes(10));
        } 

        $totalStill=$totalRevenus-$totalPaid;

        $cacheTotalReturnFile=cache('TotalReturn1'.$cacheCode);
        if(isset($cacheTotalReturnFile) && $cacheTotalReturnFile!=""){
            $totaReturn=$cacheTotalReturnFile;
        }else{
            $totaReturn=ReportStaff::join('users','users.id','=','report_staff.user_id')->whereIn('report_staff.user_id',$sales)
            ->where('report_staff.date_create','>=', $starting_date)
            ->where('report_staff.date_create','<=',$ending_date)
            ->where(function ($query3) use($store, $source_id) {
                if($store!="" && $store>0  && $store!=0){
                    $query3->where('users.branch_id', $store);
                }
                if($source_id!="" && $source_id>0  && $source_id!=0){
                    $query3->where('report_staff.source_id',$source_id);
                }
           })
            ->where('report_staff.type','totalReturn')->sum('report_staff.summary_invoice');
            Cache::put('TotalReturn1'.$cacheCode, $totaReturn, now()->addMinutes(10));
        }

        $cacheTotalLeadBung=cache('TotalLeadBung'.$cacheCode);
        if(isset($cacheTotalLeadBung) && $cacheTotalLeadBung!=""){
            $totalLeadBung=$cacheTotalLeadBung;
        }else{
            $totalLeadBung=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','totalLeadBung')->sum('number');
            Cache::put('TotalLeadBung'.$cacheCode, $totalLeadBung, now()->addMinutes(120));
        } 

        $cacheTotalClientBung=cache('TotalClientBung'.$cacheCode);
        if(isset($cacheTotalClientBung) && $cacheTotalClientBung!=""){
            $totalClientBung=$cacheTotalClientBung;
        }else{
            $totalClientBung=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','totalClientBung')->sum('number');
            Cache::put('TotalClientBung'.$cacheCode, $totalClientBung, now()->addMinutes(120));
        } 

        $cacheTimeAcceptLeadB=cache('TimeAcceptLead'.$cacheCode);
        if(isset($cacheTimeAcceptLeadB) && $cacheTimeAcceptLeadB!=""){
            $timeAcceptLead=$cacheTimeAcceptLeadB;
        }else{
            $timeAcceptLead=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','timeAcceptLead')->avg('number');
            Cache::put('TimeAcceptLead'.$cacheCode, $timeAcceptLead, now()->addMinutes(120));
        }
            
        $cacheTimeProcssLead=cache('TimeProcessLead'.$cacheCode);
        if(isset($cacheTimeProcssLead) && $cacheTimeProcssLead!=""){
            $timeProcessLead=$cacheTimeProcssLead;
        }else{
            $timeProcessLead=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','timeProcessLead')->avg('number');
            Cache::put('TimeProcessLead'.$cacheCode, $timeProcessLead, now()->addMinutes(120));
        }
        
        $totalCustomerCache=cache('totalCustomerCare'.$cacheCode);
        if(isset($totalCustomerCache) && $totalCustomerCache!=""){
            $totalCustomer=$totalCustomerCache;
        }else{
            $totalCustomer=Lead::whereIn('sales_person_id',$sales)->where('created_at','>=',$starting_date)->where('created_at','<=',$ending_date)->where('group_id',44)->count();
            Cache::put('totalCustomerCare'.$cacheCode, $totalCustomer, now()->addMinutes(1));
        } 

        /* Lead to client */
        $cacheleadToClient=cache('leadToClient1'.$cacheCode);
        if(isset($cacheleadToClient) && $cacheleadToClient!=""){
            $leadToClient=$cacheleadToClient;
        }else{
            $leadToClient = Lead::select('id')
            ->whereIn('sales_person_id',$sales)
            ->where('partner_id', $totalOrder)
            ->whereNotNull('customer_id')
            ->where(function ($query3) use($starting_date, $ending_date) {
                    if($starting_date!=""){
                        $query3->where('created_at','>=',$starting_date);
                    }
                    if($ending_date!=""){
                        $query3->where('created_at','<=',$ending_date);
                    }
            })->count();
            Cache::put('leadToClient1'.$cacheCode, $leadToClient, now()->addMinutes(120));
        }

         $partnerCache= cache('partner_01'.$partner_id);
         //$partner_detail=Partner::where('id',$partner_id)->first();
         
         if(isset($partnerCache) && $partnerCache!=""){
             $partner_detail=$partnerCache;    
         }else{
             $partner_detail=Partner::where('id',$partner_id)->first();
             Cache::put('partner_01'.$partner_id, $partner_detail, now()->addMinutes(20));
         }  
         
         $domain=$partner_detail["link_crm"];
         if($domain=="" || $domain==null){
             $domain="https://fastercrm.com";
         }
         $url=$domain;

        $link_revenus=$url."?start_date=".$starting_date."&end_date=".$ending_date."&warehouse_id=".$store."&source_id=".$source_id."&user_id=0";//.$user_id;
        //////
        //////
         /* Box lavender */

         
        //$cacheTotalOrder=md5($this->partner_id.implode(",",$sales).$ending_date.$starting_date);
       
        $cacheTotalLeadOldReply=cache('TotalLeadOldReply'.$cacheCode);
        if(isset($cacheTotalLeadOldReply) && $cacheTotalLeadOldReply!=""){
            $totalLeadReply=$cacheTotalLeadOldReply;
        }else{
            $totalLeadReply=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','totalLeadOldHasReply')->sum('number');
            Cache::put('TotalLeadOldReply'.$cacheCode, $totalLeadReply, now()->addMinutes(20));
        } 

        $totalLeadNotCareCache=cache('totalLeadNotCare'.$cacheCode);
        if(isset($totalLeadNotCareCache) && $totalLeadNotCareCache!=""){
            $totalLeadNotCare=$totalLeadNotCareCache;
        }else{
            $totalLeadNotCare=Lead::where('sales_person_id',0)->where('messenger', '!=', '')->where('created_at','>=',$starting_date)->where('created_at','<=',$ending_date)->where('group_id',45)->count();
            Cache::put('totalLeadNotCare'.$cacheCode, $totalLeadNotCare, now()->addMinutes(10));
        } 
        $cacheReportTags=cache('ReportTags2'.$cacheCode);
        if(isset($cacheReportTags) && $cacheReportTags!=""){
            $totalReportTags=$cacheReportTags;
        }else{
            $totalReportTags=ReportTags::whereIn('user_id',$sales)->where('created_at','>=', $starting_date)->where('created_at','<=',$ending_date)->count('id');
            Cache::put('ReportTags2'.$cacheCode, $totalReportTags, now()->addMinutes(20));
        }
         /* end */
        //$box8="totalRevenus"
        if(!isset($totalRevenus) || $totalRevenus==""){
            $totalRevenus=0;
        }
        if(!isset($totalPaid) || $totalPaid==""){
            $totalPaid=0;
        }
        $totalStill=number_format($totalRevenus-$totalPaid);
        if(!isset($totalStill) || $totalStill==""){
            $totalStill=0;
        }
       // $box1=$box2=$box3=$box4=$box5=$box6=$box7=$box9=$box10=$box11=$box12=$box13=$box14=$box15=$box16=0;
         $starting_date  = addslashes($request->start_date);
       $ending_date  = addslashes($request->end_date);
        $box1=array('desc' => number_format($totalRevenus), 'title'=>"Doanh thu ", 'screen'=>'webview', 'link_icon'=>"https://api.fastercrm.com/icons/revenue.png", "link_webview"=>$link_revenus);

        $box2=array('desc' => number_format($totalOrder), 'title'=>"Tổng hóa đơn", 'screen'=>'webview', 'link_icon'=>"https://api.fastercrm.com/icons/invoice.png", "link_webview"=>$link_revenus);

        $box3=array('desc' => number_format($totalPaid), 'title'=>"Đã TT tại Chi nhánh", 'screen'=>'webview', 'link_icon'=>"https://api.fastercrm.com/icons/paid.png", "link_webview"=>$link_revenus);

        $box4=array('desc' => $totalStill, 'title'=>"Chưa thanh toán", 'screen'=>'webview', 'link_icon'=>"https://api.fastercrm.com/icons/unpaid.png", "link_webview"=>$link_revenus);

        $box5=array('desc' => $totalLead, 'title'=>"Tổng lead", 'link_icon'=>"https://api.fastercrm.com/icons/lead.png", 'screen'=>'task', 'paramater'=>["start_date"=>$starting_date, "end_date"=>$ending_date, "warehouse_id"=>$store, "source_id"=>$source_id, "user_id"=>$salesSearch, "lead_type"=>"lead"]);

        $box6=array('desc' => $totalLeadNotCare, 'title'=>"Chưa chăm sóc", 'link_icon'=>"https://api.fastercrm.com/icons/lead.png", 'screen'=>'task', 'paramater'=>["start_date"=>$starting_date, "end_date"=>$ending_date, "warehouse_id"=>$store, "source_id"=>$source_id, "user_id"=>$salesSearch, "lead_type"=>"lead"]);
        if(!isset($totalAssign) || $totalAssign==""){
            $totalAssign=0;
        }
        $box7=array('desc' => $totalAssign, 'title'=>"Tổng số lần xoay, chuyển Lead", 'link_icon'=>"https://api.fastercrm.com/icons/routing.png", 'screen'=>'task', 'paramater'=>["start_date"=>$starting_date, "end_date"=>$ending_date, "warehouse_id"=>$store, "source_id"=>$source_id, "user_id"=>$salesSearch, "lead_type"=>"lead"]);

        $box8=array('desc' => $totalCustomer, 'title'=>"Tổng số KH", 'link_icon'=>"https://api.fastercrm.com/icons/lead.png", 'screen'=>'task', 'paramater'=>["start_date"=>$starting_date, "end_date"=>$ending_date, "warehouse_id"=>$store, "source_id"=>$source_id, "user_id"=>$salesSearch, "lead_type"=>"lead"]);
        //$box9="totalPaid"

        $box9=array('desc' => $totalLeadOld, 'title'=>"Tổng TT Lead cũ", 'link_icon'=>"https://api.fastercrm.com/icons/lead.png", 'screen'=>'task', 'paramater'=>["start_date"=>$starting_date, "end_date"=>$ending_date, "warehouse_id"=>$store, "source_id"=>$source_id, "user_id"=>$salesSearch, "lead_type"=>"lead"]);

        $box10=array('desc' => $totalLeadReply, 'title'=>"TT Lead cũ có TL", 'link_icon'=>"https://api.fastercrm.com/icons/lead.png", 'screen'=>'task', 'paramater'=>["start_date"=>$starting_date, "end_date"=>$ending_date, "warehouse_id"=>$store, "source_id"=>$source_id, "user_id"=>$salesSearch, "lead_type"=>"lead"]);

        $box11=array('desc' => $totalLeadBung, 'title'=>"Lead bị bung", 'link_icon'=>"https://api.fastercrm.com/icons/lead.png", 'screen'=>'task', 'paramater'=>["start_date"=>$starting_date, "end_date"=>$ending_date, "warehouse_id"=>$store, "source_id"=>$source_id, "user_id"=>$salesSearch, "lead_type"=>"lead"]);

        $box12=array('desc' => $totalClientBung, 'title'=>"KH bị bung", 'link_icon'=>"https://api.fastercrm.com/icons/error.png", 'screen'=>'task', 'paramater'=>["start_date"=>$starting_date, "end_date"=>$ending_date, "warehouse_id"=>$store, "source_id"=>$source_id, "user_id"=>$salesSearch, "lead_type"=>"lead"]);

        $box13=array('desc' => round($timeAcceptLead), 'title'=>"TG tiếp nhận Lead", 'link_icon'=>"https://api.fastercrm.com/icons/time.png", 'screen'=>'task', 'paramater'=>["start_date"=>$starting_date, "end_date"=>$ending_date, "warehouse_id"=>$store, "source_id"=>$source_id, "user_id"=>$salesSearch, "lead_type"=>"lead"]);
        
        $box14=array('desc' => round($timeProcessLead), 'title'=>"TG xử lý", 'link_icon'=>"https://api.fastercrm.com/icons/time.png", 'screen'=>'task', 'paramater'=>["start_date"=>$starting_date, "end_date"=>$ending_date, "warehouse_id"=>$store, "source_id"=>$source_id, "user_id"=>$salesSearch, "lead_type"=>"lead"]);


        $box15=array('desc' => $leadToClient, 'title'=>"SL Lead chuyển sang KH", 'link_icon'=>"https://api.fastercrm.com/icons/lead.png", 'screen'=>'task', 'paramater'=>["start_date"=>$starting_date, "end_date"=>$ending_date, "warehouse_id"=>$store, "source_id"=>$source_id, "user_id"=>$salesSearch, "lead_type"=>"lead"]);
        
        $box16=array('desc' => $totalReportTags, 'title'=>"SL gắn sai thẻ", 'link_icon'=>"https://api.fastercrm.com/icons/lead.png", 'screen'=>'task', 'paramater'=>["start_date"=>$starting_date, "end_date"=>$ending_date, "warehouse_id"=>$store, "source_id"=>$source_id, "user_id"=>$salesSearch, "lead_type"=>"lead"]);
        //$box10="$totalStill"
        //Link load webbiew


        $link_map_tuongtac2chieu="https://api.fastercrm.com/api/user/communication?type=tuongtac2chieu&start_date=".$starting_date."&end_date=".$ending_date."&sales_id=".$salesSearch."&token=".$token;
        $linkReport1=array('title'=>"Chỉ số tương tác 2 chiều Lead cũ", 'link_icon'=>"",  'screen'=>'webview', "link_webview"=>$link_map_tuongtac2chieu);

        $link_map_tuongtacLeadCu="https://api.fastercrm.com/api/user/communication?type=tuongtacleadcu&start_date=".$starting_date."&end_date=".$ending_date."&sales_id=".$salesSearch."&token=".$token;
        $linkReport2=array('title'=>"Tương tác lead cũ", 'link_icon'=>"",  'screen'=>'webview', "link_webview"=>$link_map_tuongtacLeadCu);

        $link_map_tuongtacKHCu="https://api.fastercrm.com/api/user/communication?type=tuongtackhcu&start_date=".$starting_date."&end_date=".$ending_date."&sales_id=".$salesSearch."&token=".$token;

        $linkReport3=array('title'=>"Tương tác Khách hàng cũ", 'link_icon'=>"",  'screen'=>'webview', "link_webview"=>$link_map_tuongtacKHCu);
        $link_map_ty_lemuahangkhcu="https://api.fastercrm.com/api/user/communication?type=khcumuahang&start_date=".$starting_date."&end_date=".$ending_date."&sales_id=".$salesSearch."&token=".$token;

        $linkReport4=array('title'=>"Tỷ lệ mua hàng KH cũ", 'link_icon'=>"",  'screen'=>'webview', "link_webview"=>$link_map_ty_lemuahangkhcu);

        $link_map_ty_lead_moinhantuca="https://api.fastercrm.com/api/user/communication?type=leadmoinhantuca&start_date=".$starting_date."&end_date=".$ending_date."&sales_id=".$salesSearch."&token=".$token;
        $linkReport5=array('title'=>"Chỉ số nhóm lead mới nhận từ Ca", 'link_icon'=>"",  'screen'=>'webview', "link_webview"=>$link_map_ty_lead_moinhantuca);
        $box=[];
        //$box="Test test";
        
        echo json_encode(["box"=>[$box1, $box2, $box3, $box4, $box5, $box6, $box7, $box8, $box9, $box10, $box11, $box12, $box13, $box14, $box15, $box16], "view_map"=>[]], 200);
       // return json_encode($box); $linkReport1, $linkReport2, $linkReport3, $linkReport4, $linkReport5
        die();
        //return response()->json(['success' => 'Success', 'data'=>$box], 200);

       // return response()->json([["box"=>[$box8, $box9, $box10, $box11, $box1, $box2, $box3, $box4, $box5, $box6, $box7]], ["view_map"=>[$linkReport1, $linkReport2, $linkReport3]]], 200); 

      }
      public function tuongtac2chieu(Request $request){
        $this->user = JWTAuth::parseToken()->authenticate();
        $typemap=$request->type;
        $starting_date=$request->start_date;
        $ending_date=$request->end_date;
        $sales_id=$request->sales_id;
        $list_lead_group = array();
        $oldLeadOldDays=array();
        $newLeadReplyDays=array();
        $daysList=array();
        $userData=$this->user;
        $partner_id=$userData->partner_id;
        $this->partner_id=$userData->partner_id;
        $sales=array($sales_id);
        $listGroupLead=CallActionStatus::where("partner_id",$this->partner_id)->where('type',45)->get();
        foreach($listGroupLead as $listData){
            $cacheKey=md5($this->partner_id.$listData["id"].implode(",",$sales).$ending_date.$starting_date);
            $leadCache=cache('leadGroupLeadCache2'.$cacheKey);
            $leadReplyCache=cache('leadGroupLeadReplyCache2'.$cacheKey);
            $leadcountReply=0;
                // $leadcountReply=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('group_id',$listData["id"])->where('type','oldCareReply')->sum('number');

            if(isset($leadCache) && $leadCache!="" && $leadReplyCache!=""){
                $leadcount=$leadCache;
                $leadcountReply=$leadReplyCache;
            }else{

                $leadcount=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('group_id',$listData["id"])->where('type','oldCare')->sum('number');

                $leadcountReply=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('group_id',$listData["id"])->where('type','oldCareReply')->sum('number');


                //$leadcount=Lead::select("leads.id")->join('logs','logs.lead_id','=','leads.id')->where('logs.created_at','>=',$starting_date)->where('logs.created_at','<=',$ending_date)->where('leads.created_at','>',$starting_date)->where('leads.created_at','<',$ending_date)->whereIn('leads.sales_person_id',$sales)->where('leads.partner_id', $this->partner_id)->where('leads.status', $listData["id"])->groupBy('logs.lead_id')->count();
                Cache::put('leadGroupLeadCache2'.$cacheKey, $leadcount, now()->addMinutes(20));
                Cache::put('leadGroupLeadReplyCache2'.$cacheKey, $leadcountReply, now()->addMinutes(20));
            } 
            $list_lead_group[] =
                [
                    'id' =>$listData["id"],
                    'name' =>$listData["title"],
                    'color' =>$listData["color_bg"],
                    'leads'=>$leadcount,
                    'leadsReply'=>$leadcountReply
                ];
        }

        $listLeadTitle=array();
        $groupLeadTitle=implode(",", $listLeadTitle);
        $list_customer_group = array();
        $listGroupCustomer=CallActionStatus::where("partner_id",$this->partner_id)->where('type',44)->get();
        foreach($listGroupCustomer as $listData){
            $cacheKey2=md5($this->partner_id.$listData["id"].implode(",",$sales).$ending_date.$starting_date);
            $leadGroupClientCache=cache('leadGroupClient'.$cacheKey2);
            $leadGroupClientReplyCache=cache('leadGroupClientReply'.$cacheKey2);

            if($leadGroupClientCache!="" && $leadGroupClientReplyCache!=""){
                $lientCount=$leadGroupClientCache;
                $clientCountReply=$leadGroupClientReplyCache;
            }else{
               $clientCountReply=ReportStaff::whereIn('user_id',$sales)
               ->where('date_create','>=', $starting_date)
               ->where('date_create','<=',$ending_date)->where('type','totalClientOldHasReply')->sum('number');
               
               $clientcount=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','totalClientOld')->sum('number');

                Cache::put('leadGroupClient'.$cacheKey2, $clientcount, now()->addMinutes(20));
                Cache::put('leadGroupClientReply'.$cacheKey2, $clientCountReply, now()->addMinutes(20));

            } 
            $list_customer_group[] =
                [
                    'id' =>$listData["id"],
                    'name' =>$listData["title"],
                    'color' =>$listData["color_bg"],
                    'leads'=>$leadcount,
                    'leadsReply'=>$clientCountReply
                ];
        }
        $listClientTitle=array();
        foreach($listGroupLead as $listData){
            $listClientTitle[]='"'.$listData["title"].'"';
        }
        $groupClientTitle=implode(",", $listClientTitle);
        $cacheCode=md5($this->partner_id.implode(",",$sales).$ending_date.$starting_date);


        $totalLeadOldDate = cache('totalLeadReplyCache3'.$cacheCode);
        $totalLeadReplyDay = cache('totalLeadCache3'.$cacheCode);
        $start_date_list = cache('start_date2'.$cacheCode);;

        if(isset($totalLeadReply) && $totalLeadReplyDay!="" && $totalLeadOldDate!="" && $start_date_list!=""){
            $totalLeadReplyData = $totalLeadReplyDay;
            $totalLeadOldData = $totalLeadOldDate;
            $daysListData = $start_date_list;
        }else{ 
            $start = strtotime(date('Y-m-d', strtotime('-30 days', strtotime(date('Y-m-d') ))));
            $end = strtotime(date('Y-m-d', strtotime('+1 day')));
            $totalLeadReplyDataList = null;
            $totalLeadOldDataList = null;
            $daysListDataList = null;
            while($start < $end)
            {
                $start_date=date("Y-m-d",$start);
                $end_date=$start_date." 23:59:59";//date("Y-m-d",$end);

                $totalLeadReplyDate=ReportStaff::whereIn('user_id',$sales)
                ->where('date_create','>=', $start_date)
                ->where('date_create','<=',$end_date)->where('type','totalLeadOldHasReply')->sum('number');

                $totalLeadOldDate=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $start_date)->where('date_create','<=',$end_date)->where('type','totalLeadOld')->sum('number');

                $totalLeadReplyDataList[] = $totalLeadReplyDate;
                $totalLeadOldDataList[] = $totalLeadOldDate;
                $daysListDataList[] = $start_date;
                $start = strtotime("+1 day", $start);
            }
            $totalLeadReplyData = $totalLeadReplyDataList;
            $totalLeadOldData = $totalLeadOldDataList;
            $daysListData = $daysListDataList;

            Cache::put('totalLeadReplyCache3'.$cacheCode, $totalLeadReplyData, now()->addMinutes(300));
            Cache::put('totalLeadCache3'.$cacheCode, $totalLeadOldData, now()->addMinutes(300));
            Cache::put('start_date3'.$cacheCode, $daysListData, now()->addMinutes(300));
        }

        if(count($daysListData)>0){
            for($i=0;$i<count($daysListData);$i++){
                $oldLeadOldDays[] = $totalLeadOldData[$i];
                $newLeadReplyDays[] = $totalLeadReplyData[$i];
                $daysList[] =$daysListData[$i];
            }
        }

        $list_client_group_order = array();
        foreach($listGroupCustomer as $listData){
            $cacheKey=md5($this->partner_id.$listData["id"].implode(",",$sales).$ending_date.$starting_date);
            $leadCache=cache('leadOrderCount5'.$cacheKey);
            if(isset($leadCache) && $leadCache!=""){
                $orderCount=$leadCache;
            }else{ //select("leads.id")->
                $orderCount=Lead::join('customer_order','customer_order.lead_id','=','leads.id')->where('customer_order.last_order','>=',$starting_date)->where('customer_order.last_order','<=',$ending_date)->where('leads.created_at','<',$starting_date)->whereIn('leads.sales_person_id',$sales)->where('leads.partner_id', $this->partner_id)->where('leads.status', $listData["id"])->count('leads.id');
                Cache::put('leadOrderCount5'.$cacheKey, $orderCount, now()->addMinutes(20));
            } 
            $list_client_group_order[] =
                [
                    'id' =>$listData["id"],
                    'name' =>$listData["title"],
                    'order'=>$orderCount
                ];
        }



        $list_lead_group_new = array();
        foreach($listGroupLead as $listData){
            $cacheKey=md5($this->partner_id.$listData["id"].implode(",",$sales).$ending_date.$starting_date);
            $leadCache=cache('leadGroupNewLeadCache1'.$cacheKey);
            if(isset($leadCache) && $leadCache!=""){
                $totalLeadCare=$leadCache;
            }else{
                $totalLeadCare=Lead::whereIn('sales_person_id',$sales)
                ->where('created_at','>=',$starting_date)
                ->where('created_at','<=',$ending_date)
                ->where('leads.status', $listData["id"])
                ->where('group_id',45)->count();
                Cache::put('leadGroupNewLeadCache1'.$cacheKey, $totalLeadCare, now()->addMinutes(20));
            } 
            $list_lead_group_new[] =
                [
                    'id' =>$listData["id"],
                    'name' =>$listData["title"],
                    'color' =>$listData["color_bg"],
                    'leads'=>$totalLeadCare
                ];
        }
        $title="View map";

        return view( 'map.viewtuongtac', compact( 'title', 'list_client_group_order', 'daysList', 'newLeadReplyDays', 'list_lead_group_new', 'list_customer_group', 'typemap', 'list_lead_group','oldLeadOldDays')); 
      }
      

      public function camereapi(Request $request){
        $datacontent=$request;
        $keycode=$datacontent["keycode"];
        $date=$datacontent["date"];
        $action_type=$datacontent["action_type"];
        $detected_image_url=$datacontent["detected_image_url"];
        $placeID=$datacontent["placeID"];
        $deviceID=$datacontent["deviceID"];
        $deviceName=$datacontent["deviceName"];
        $personID=$datacontent["personID"];
        $id_request=$datacontent["id"];
        $time_request=$datacontent["time"];
        $personType=$datacontent["personType"];
        $placeName=$datacontent["placeName"];
        $hash_code=$datacontent["hash"];
        $personName=$datacontent["personName"];
        $aliasID=$datacontent["aliasID"]; 
        $linkPhoto="";
        $datenow=date("Ymd");
        $minute5ago=date("Y-m-d H:i:s",strtotime("-10 minutes"));
        if($date<$minute5ago){
            return response()->json(['success' =>1, "status_code"=>200], 200); 
        }
        if($detected_image_url!=""){
            $path_parts = pathinfo($detected_image_url);
            $image=$path_parts['basename'];
            
            $folderRoot=$_SERVER["DOCUMENT_ROOT"]."/images/checkin/".$datenow;
            
            if(!is_dir($folderRoot)){
                @mkdir($folderRoot);
            }
            @copy($detected_image_url, $folderRoot."/".$image);
            $linkPhoto=env('APP_URL')."/images/checkin/".$datenow."/".$image; //Domain gốc
            $folderRootWriteFile=$_SERVER["DOCUMENT_ROOT"]."/data/camera/".$datenow;
            
            if(!is_dir($folderRootWriteFile)){
                @mkdir($folderRootWriteFile);
            }
    
            $filename=$folderRootWriteFile."/report".date("YmdHis").".json";
            @file_put_contents($filename, $datacontent);
        
            $data=array("keycode"=>$keycode, "date"=>$date, "action_type"=>$action_type, "detected_image_url"=>$linkPhoto, "placeID"=>$placeID, "is_active"=>1, "deviceID"=>$deviceID, "deviceName"=>$deviceName, "personName"=>$personName, "aliasID"=>$aliasID, "deviceName"=>$deviceName, "personID"=>$personID, "id_request"=>$id_request, "time_request"=>$time_request, "personType"=>$personType, "placeName"=>$placeName, "hash_code"=>$hash_code);

            


            $logData = new CameraLogs();       
            $logData->keycode=$keycode;
            $logData->date=$date;
            $logData->action_type=$action_type;
            $logData->detected_image_url=$linkPhoto;
            $logData->placeID=$placeID;
            $logData->is_active=1;
            $logData->deviceID=$deviceID;
            $logData->deviceName=$deviceName;
            $logData->personName=$personName;
            $logData->aliasID=$aliasID; 
            $logData->deviceName=$deviceName;
            $logData->personID=$personID;
            $logData->id_request=$id_request;
            $logData->time_request=$time_request;
            $logData->personType=$personType;
            $logData->placeName=$placeName;
            $logData->hash_code=$hash_code;
            $logData->save();
            $idlog=$logData->id;
            //CameraLogs::insert($logData);
            if($personID!=""){
                $user=Employee::whereNotNull('person_id')->where('person_id',$personID)->first();
                $user_id=0;
                $type_working=1;
                $type_person=0;
                $employee_id=0;
                if($user!=""){
                    $user_id=$user["user_id"];
                    $employee_id=$user["id"];
                    $type_person=1;
                }
                $datecheck=date("Y-m-d");
                $now=date("Y-m-d H:i:s");
                $keyUpdate="check_out='".date("Y-m-d H:i:s")."', images_link='".$linkPhoto."'";
                $listInsert=[$user_id, $personID, $deviceID, $placeID, $personName, $now, $now, $datecheck, $type_person, $type_working, $linkPhoto];
                DB::insert('insert into `check_in` (`user_id`, `person_id`, `deviceID`, `placeID`, `fullname`, `check_in`, `check_out`, `date_check`, `type_person`, `type_working`, `images_link`) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) on duplicate key update '.$keyUpdate, $listInsert);
                if($user_id>0 && $employee_id){
                    $listInsert2=[$datecheck, $user_id, $employee_id, $personID, $now, $now, 1];
                    $keyUpdate2="checkout='".date("Y-m-d H:i:s")."'";
                    DB::insert('insert into `attendances` (`date`, `user_id`, `employee_id`, `person_id`, `checkin`, `checkout`, `status`) values (?, ?, ?, ?, ?, ?, ?) on duplicate key update '.$keyUpdate2, $listInsert2);
                }
            }
            return response()->json(['success' =>1, "status_code"=>200], 200); 
        }
        return response()->json(['success' =>1, "status_code"=>200], 200); 
    }
    public function camereapiupload(Request $request){
        $ThatTime1 ="08:15:00";
		$ThatTime2 ="22:00:00";
        $timenow=date("H:i");
        if (time() >= strtotime($ThatTime1) && time()<strtotime($ThatTime2)) {
            $listLog=CameraLogs::where('status_process',0)->whereNull('personID')->limit(10)->orderBy('id','desc')->get();
            //->whereNull('personID')
            if(count($listLog)>0){
                $idupdate=[];
                foreach($listLog as $listLogData){
        
                    $idupdate[]=$listLogData["id"];
                    $detected_image_url=$listLogData["detected_image_url"];
                    $placeID=$listLogData["placeID"];
                    $deviceID=$listLogData["deviceID"];
                    $idlog=$listLogData["id"];
                    $datenow=date("YmdHis");

                    $lims_customer_data_log = CameraLogs::find($idlog);
                    $lims_customer_data_log['status_process']=1;
                    $lims_customer_data_log->update();
                    
                    $id=time();
                    $khname="KVL ".$this->milliseconds();
                    $curl = curl_init();
                    $token="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJwYXJ0bmVySUQiOjI0OCwicGFydG5lck5hbWUiOiJhcGlkb25ndGhhaSIsImlkIjoiMTg2NDk4NTc3NDU2Njk0MDY3MiIsImVtYWlsIjoiZHVuZ0Bkb25ndGhhaS5jb20udm4iLCJ0cyI6MTYxNjM3NTE5Mjg5NiwiY2xpZW50X2lkIjoiZjI5MWRhMTQxYWQwNWNhMjI4YWYzOTYwMWZhZGEzYzciLCJjb2RlIjoiIiwiY29kZV9jaGFsbGVuZ2UiOiIiLCJ0eXBlIjoiYXV0aG9yaXphdGlvbl9jb2RlIiwiaWF0IjoxNjE2Mzc1MTkyLCJleHAiOjE2NDc5MTExOTJ9.W-267_INQEM2sn9eFfwVGBhFKcGFJlyFYIAkRNZ-TIA";
                    $folderRoot=$_SERVER["DOCUMENT_ROOT"]."/images/camera/".$datenow;
                    if($detected_image_url!=""){
                        $path_parts = pathinfo($detected_image_url);
                        $image=$path_parts['basename'];
                        if(!is_dir($folderRoot)){
                            @mkdir($folderRoot);
                        }
                        @copy($detected_image_url, $folderRoot."/".$image);
                    }
                // @copy("https://api.fastercrm.com/thumb.php?src=".$detected_image_url."&w=1280&h=736", $folderRoot."/".$image);
                    //$image_resize=$folderRoot."/".$image;
                    
                    $watermark_image = imagecreatefromjpeg($folderRoot."/".$image);
                    $filename=$this->milliseconds().".png";
                    @copy($_SERVER["DOCUMENT_ROOT"]."/images/thumbnail.png",$_SERVER["DOCUMENT_ROOT"]."/images/createimages/".$filename);
                    $upload_location=$_SERVER["DOCUMENT_ROOT"]."/images/createimages/".$filename;
                    $imageFile = imagecreatefrompng($upload_location);
                    $margin_right = 10; 
                    $margin_bottom = 10;
            
                    $watermark_image_width = imagesx($watermark_image); 
                    $watermark_image_height = imagesy($watermark_image);  
            
                    $image_width = imagesx($imageFile); 
                    $image_height = imagesy($imageFile);  
            
                    @imagecopy($imageFile, $watermark_image, ($image_width-$watermark_image_width)/2, ($image_height-$watermark_image_height)/2, 0, 0, $watermark_image_width, $watermark_image_height); 
                    @imagepng($imageFile, $upload_location); 
                    @imagedestroy($imageFile); 
                
                    curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://partner.hanet.ai/person/register",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS =>array('token'=>$token,'name' => $khname, 'file'=> new \CURLFILE($upload_location), 'aliasID' => $id,'placeID' => $placeID, 'title' => 'KH'),
                    ));
                    $response = curl_exec($curl);
                    curl_close($curl); 
                    $result=json_decode($response);
                    //echo "https://api.fastercrm.com/thumb.php?src=".$detected_image_url."&w=1280&h=736";
                    if(isset($result->returnCode) && $result->returnCode==1){
                        $personID=$result->data->personID;
                        $name=$result->data->name;
                        if($personID!="" && $personID){
                            $user_id=0;
                            $datecheck=date("Y-m-d");
                            $now=date("Y-m-d H:i:s");
                            $keyUpdate="check_out='".date("Y-m-d H:i:s")."', images_link='".$detected_image_url."'";
                            $type_working=0;
                            $type_person=3;
                            $listInsert=[$user_id, $personID, $deviceID, $placeID, $name, $now, $now, $datecheck, $type_person, $type_working, $detected_image_url];
                            DB::insert('insert into `check_in` (`user_id`, `person_id`, `deviceID`, `placeID`, `fullname`, `check_in`, `check_out`, `date_check`, `type_person`, `type_working`, `images_link`) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) on duplicate key update '.$keyUpdate, $listInsert);
                            $logUpdate=CameraLogs::find($idlog);
                            $logUpdate->personID=$personID;
                            $logUpdate->personName=$name;
                            $logUpdate->save();
                        }
                    }
                }
            }
            
            return response()->json(['success' =>1, "status_code"=>200], 200); 
                
        }else{
            return response()->json(['success' =>0, "status_code"=>200], 200); 
        }
    }
    public function camereapiTest(Request $request){
      
            $id=time();
            $khname="KVL ".$this->milliseconds();
            $curl = curl_init();
            $token="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJwYXJ0bmVySUQiOjI0OCwicGFydG5lck5hbWUiOiJhcGlkb25ndGhhaSIsImlkIjoiMTg2NDk4NTc3NDU2Njk0MDY3MiIsImVtYWlsIjoiZHVuZ0Bkb25ndGhhaS5jb20udm4iLCJ0cyI6MTYxNjM3NTE5Mjg5NiwiY2xpZW50X2lkIjoiZjI5MWRhMTQxYWQwNWNhMjI4YWYzOTYwMWZhZGEzYzciLCJjb2RlIjoiIiwiY29kZV9jaGFsbGVuZ2UiOiIiLCJ0eXBlIjoiYXV0aG9yaXphdGlvbl9jb2RlIiwiaWF0IjoxNjE2Mzc1MTkyLCJleHAiOjE2NDc5MTExOTJ9.W-267_INQEM2sn9eFfwVGBhFKcGFJlyFYIAkRNZ-TIA";
            $user_id=0;
            $type_working=1;
            $type_person=0;
            $employee_id=0;
            $datecheck=date("Y-m-d");
            $now=date("Y-m-d H:i:s");
            $detected_image_url=$request->images;
            $datenow=date("Ymd");
            $placeID="1859";
            $folderRoot=$_SERVER["DOCUMENT_ROOT"]."/images/checkin/".$datenow;
            if($detected_image_url!=""){
                $path_parts = pathinfo($detected_image_url);
                $image=$path_parts['basename'];
                
                if(!is_dir($folderRoot)){
                    @mkdir($folderRoot);
                }
                @copy($detected_image_url, $folderRoot."/".$image);
            }
            $watermark_image = imagecreatefromjpeg($folderRoot."/".$image);
            $filename=$this->milliseconds().".png";
            @copy($_SERVER["DOCUMENT_ROOT"]."/images/thumbnail.png",$_SERVER["DOCUMENT_ROOT"]."/images/createimages/".$filename);
            $upload_location=$_SERVER["DOCUMENT_ROOT"]."/images/createimages/".$filename;
            $imageFile = imagecreatefrompng($upload_location);
            $margin_right = 10; 
            $margin_bottom = 10;

            $watermark_image_width = imagesx($watermark_image); 
            $watermark_image_height = imagesy($watermark_image);  

            $image_width = imagesx($imageFile); 
            $image_height = imagesy($imageFile);  

            imagecopy($imageFile, $watermark_image, ($image_width-$watermark_image_width)/2, ($image_height-$watermark_image_height)/2, 0, 0, $watermark_image_width, $watermark_image_height); 
            imagepng($imageFile, $upload_location); 
            imagedestroy($imageFile);

            echo $upload_location;

            //@copy("https://api.fastercrm.com/thumb.php?src=".$detected_image_url."&w=1280&h=736", $folderRoot."/".$image);
           // $image_resize=$folderRoot."/".$image;
            curl_setopt_array($curl, array(
              CURLOPT_URL => "https://partner.hanet.ai/person/register",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS =>array('token'=>$token,'name' => $khname, 'file'=> new \CURLFILE($upload_location), 'aliasID' => $id,'placeID' => $placeID, 'title' => 'KH'),
            ));
            $response = curl_exec($curl);
            curl_close($curl); 
            $result=json_decode($response);
            if($result->returnCode==1){
               
                $personID=$result->data->personID;
                $name=$result->data->name;
                if($personID!="" && $personID){
                    $user_id=0;
                    $datecheck=date("Y-m-d");
                    $now=date("Y-m-d H:i:s");
                    $keyUpdate="check_out='".date("Y-m-d H:i:s")."'";
                    $type_working=0;
                    $type_person=3;
                    $listInsert=[$user_id, $personID, $now, $now, $datecheck, $type_person, $type_working];
                    $listInsert2=[$datecheck, $user_id, $employee_id, $personID, $now, $now, 1];
                    $keyUpdate2="checkout='".date("Y-m-d H:i:s")."'";
                    DB::insert('insert into `check_in` (`user_id`, `person_id`, `check_in`, `check_out`, `date_check`, `type_person`, `type_working`) values (?, ?, ?, ?, ?, ?, ?) on duplicate key update '.$keyUpdate, $listInsert);
                    $logUpdate=CameraLogs::find($idlog);
                    $logUpdate->personID=$personID;
                    $logUpdate->personName=$name;
                    $logUpdate->save();
                }
            }
            return response()->json(['success' =>1, "status_code"=>200, "data"=>$response], 200); 

    }
    public static function milliseconds() {
        $mt = explode(' ', microtime());
        return ((int)$mt[1]) * 1000 + ((int)round($mt[0] * 1000));
    }
    public static function resize_image($file, $w = 1280, $h = 736, $crop = false)
    {
       try {
           $ext = pathinfo(storage_path() . $file, PATHINFO_EXTENSION);
           list($width, $height) = getimagesize($file);
           // if the image is smaller we dont resize
           if ($w > $width && $h > $height) {
               return true;
           }
           $r = $width / $height;
           if ($crop) {
               if ($width > $height) {
                   $width = ceil($width - ($width * abs($r - $w / $h)));
               } else {
                   $height = ceil($height - ($height * abs($r - $w / $h)));
               }
               $newwidth = $w;
               $newheight = $h;
           } else {
               if ($w / $h > $r) {
                   $newwidth = $h * $r;
                   $newheight = $h;
               } else {
                   $newheight = $w / $r;
                   $newwidth = $w;
               }
           }
           $dst = imagecreatetruecolor($newwidth, $newheight);

           switch ($ext) {
               case 'jpg':
               case 'jpeg':
                   $src = imagecreatefromjpeg($file);
                   break;
               case 'png':
                   $src = imagecreatefrompng($file);
                   imagecolortransparent($dst, imagecolorallocatealpha($dst, 0, 0, 0, 127));
                   imagealphablending($dst, false);
                   imagesavealpha($dst, true);
                   break;
               case 'gif':
                   $src = imagecreatefromgif($file);
                   imagecolortransparent($dst, imagecolorallocatealpha($dst, 0, 0, 0, 127));
                   imagealphablending($dst, false);
                   imagesavealpha($dst, true);
                   break;
               case 'bmp':
                   $src = imagecreatefrombmp($file);
                   break;
               default:
                   throw new Exception('Unsupported image extension found: ' . $ext);
                   break;
           }
           $result = imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
           switch ($ext) {
               case 'bmp':
                   imagewbmp($dst, $file);
                   break;
               case 'gif':
                   imagegif($dst, $file);
                   break;
               case 'jpg':
               case 'jpeg':
                   imagejpeg($dst, $file);
                   break;
               case 'png':
                   imagepng($dst, $file);
                   break;
           }
           return true;
       } catch (Exception $err) {
           // LOG THE ERROR HERE 
           return false;
       }
   }

    //Show tag list
    public function partnerDetail(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $partner_id=$this->user->partner_id;
        $partners = Partner::where("id",$partner_id)
            ->get()
            ->map(function ($partner) {
                return [
                    'id' => $partner->id,
                    'gps' => $partner->gps_get,
                    'brand_type' => $partner->brand_type,
                    'logo' => $partner->logo
                ];
            });
        return response()->json(['partner' => $partners], 200);
    }

     //Accept Task
     public function acceptTask(Request $request)
     {
         $this->user = JWTAuth::parseToken()->authenticate();
         $user_id=$this->user->id;
         $partner_id=$this->user->partner_id;
         $fullname=trim($this->user->first_name." ".$this->user->last_name);
         $task_id=$request->task_id;
         $taskapprove=$request->approve; //1 approve, 0 not approve

         $data = array(
            'task_id' => $task_id,
            'user_id' => $user_id,
        );
        $rules = array(
            'task_id' => 'required',
            'user_id' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $dataTaskAccept = array(
                'task_id' =>$task_id,
                'user_id'=>$user_id,
                'approved'=>$taskapprove,
                'date_accept'=> date("Y-m-d H:i:s"),
            );
            TaskAccept::insert($dataTaskAccept);
            $titleapprove="không xác nhận task ";

            if($taskapprove==1){
                $titleapprove="đã xác nhận task ";
            }

            $notification = array(
                'partner_id'=>$partner_id,
                'user_id' => $user_id,
                'url'=> "",
                'title'=>"@".$fullname." ".$titleapprove." ".$task_title,
                'desc'=>"@".$fullname."  ".$titleapprove."",
                'status'=>0, 
                'type'=>"task",
                'item_id'=>$task_id,
                'created_at'=> date("Y-m-d H:i:s"),
                'date_notification'=>time()
            );
            Notification::insert($notification);
            return response()->json(['status' =>1], 200);
         }
         return response()->json(['status' =>0], 500);
         
     }
}
