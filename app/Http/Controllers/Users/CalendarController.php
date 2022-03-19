<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Repositories\ContractRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\MeetingRepository;
use App\Repositories\OpportunityRepository;
use App\Repositories\QuotationRepository;
use App\Repositories\UserRepository;


use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use App\Models\TaskReport;
use Carbon\Carbon;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Support\Facades\DB;

use Yajra\Datatables\Datatables;

class CalendarController extends UserController
{
     /**
     * @var QuotationRepository
     */
    private $quotationRepository;
    /**
     * @var MeetingRepository
     */
    private $meetingRepository;
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
    private $userRepository;

    public function __construct(QuotationRepository $quotationRepository,
                                MeetingRepository $meetingRepository,
                                InvoiceRepository $invoiceRepository,
                                ContractRepository $contractRepository,
                                OpportunityRepository $opportunityRepository,
                                UserRepository $userRepository
                                )
    {
        parent::__construct();
        $this->quotationRepository = $quotationRepository;
        $this->meetingRepository = $meetingRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->contractRepository = $contractRepository;
        $this->opportunityRepository = $opportunityRepository;
        $this->userRepository = $userRepository;
    }
    public $events = [];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('calendar.calendar');
        $user=$this->userRepository->getUser();
        $partner_id=$user->partner_id;
         //salesList
		$staff_care=User::select("first_name","last_name","id")->join('partner_user','partner_user.user_id','=','users.id')
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

        return view('user.calendar.index', compact('title', 'staff_care'));
    }
    public function show($calendar)
    {
        $action = "show";
        $title = trans('calendar.view');
        return view('user.calendar.show', compact('title'));
    }
    public function events(Request $request)
    {
        $date = strtotime(date("Y-m-d"));
        $start = addslashes($request->start) ;
        $end = addslashes($request->end) ;
        $status = addslashes($request->status) ;
        $keyword = addslashes($request->keyword) ;
        $user_id = addslashes($request->user_id) ;
        
        $user = $this->userRepository->getUser();
        $events = array();
        $usersList = "";
        $listUserSales=$this->userRepository->getAllStaffOfUser($user->id);
        $salesList="";
        if($listUserSales){
            $salesList=User::join('partner_user','partner_user.user_id','=','users.id')
            ->select('users.id', 'users.first_name', 'users.last_name', 'users.user_avatar')
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
        /*
        $quotations = $this->quotationRepository->getAll()->where('exp_date', '>', $start)
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
        $this->add_events_to_list($quotations); */
        $taskQuery=Task::select('tasks.*', 'users.first_name as first_name', 'users.last_name as last_name', 'leads.contact_name as lead_name', 'leads.id as lead_id')
        ->leftJoin('users','users.id','=','tasks.user_id')
        ->leftJoin('leads','leads.id','=','tasks.lead_id')
        ->where('tasks.partner_id', $user->partner_id)
        ->where(function ($query)  use ($start, $end, $status, $keyword){
            if($keyword!=""){
                $query->where(function ($query1)  use ($keyword){
                    $query1->where('tasks.task_title', 'LIKE', "%{$keyword}%");
                    $query1->orWhere('tasks.task_description','LIKE', "%{$keyword}%");
                });
            }
            if($start!=""){
                $query->where('tasks.task_end','>=',$start);
            }
            if($end!=""){
                $query->where('tasks.task_end','<=',$end);
            } 
            if($status!=""){
                $query->where('tasks.work_status',$status);
            }
        })->where(function ($query)  use ($user_id, $listUserSales, $user, $keyword){
            if($user_id!="" && $user_id!="0"){
                $query->where('tasks.user_id','=',$user_id);
                $query->whereOr('tasks.task_from_user','=',$user_id);
            }else{
                if($listUserSales!="" && count($listUserSales)>0){
                    $query->whereIn('tasks.user_id',$listUserSales);
                    $query->orWhereIn('tasks.task_from_user',$listUserSales);
                }else{
                    $query->where('tasks.user_id',$user->id);
                    $query->orWhere('tasks.task_from_user',$user->id);
                }
               
            }
        })
        ->distinct()
        ->orderBy("tasks.id", "desc")->get()
        ->map(function ($meeting){
            return [
                'id' => $meeting->id,
                'title' => $meeting->task_title,
                'description' => $meeting->task_description,
                'note' => $meeting->task_note,
                'fullname'=>$meeting->first_name." ".$meeting->last_name,
                'contact_name'=>$meeting->lead_name,
                'item_id' => $meeting->lead_id,
                'start_date' => $meeting->task_start,
                'end_date' => $meeting->task_end,
                'report_status'=>$meeting->report_status,
                'type' => 'lead'
            ];
        });
        $this->add_events_to_list($taskQuery);

        $invoices = $this->invoiceRepository->getAll()->where('partner_id', $user->partner_id)->where('invoice_deadline_date', '>', $start)
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'title' => $invoice->invoice_number,
                    'start_date' => $invoice->invoice_deadline_date,
                    'end_date' => $invoice->invoice_date,
                    'note' => "",
                    'item_id'=>$invoice->id,
                    'description' => "Hóa đơn ".$invoice->id." đến hạn thanh toán.",
                    'type' => 'invoice'
                ];
            });
        $this->add_events_to_list($invoices);
        /*
        $meetings = $this->meetingRepository->getAll()->where('starting_date', '>', $start)
            ->with('responsible')
            ->latest()->get()
            ->filter(function ($meeting) {
                return ($meeting->privacy=='Everyone' || ($meeting->privacy=='Main Staff' && $meeting->responsible_id==$this->user->id));
            })
            ->map(function ($meeting) {
                return [
                    'id' => $meeting->id,
                    'title' => $meeting->meeting_subject,
                    'start_date' => $meeting->starting_date,
                    'end_date' => $meeting->ending_date,
                    'type' => 'meeting'
                ];
            });
        $this->add_events_to_list($meetings);

        

        $contracts = $this->contractRepository->getAll()->where('end_date', '>', $start)
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

        $opportunities = $this->opportunityRepository->getAll()->where('next_action', '>', $start)
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
            */
        return json_encode($this->events);

    }

    /**
     * @param $events_data
     */
    public function add_events_to_list($events_data)
    {
        foreach ($events_data as $d) {
            $event = [];
            $start_date = date('Y-m-d H:i',(is_numeric($d['start_date'])?$d['start_date']:strtotime($d['start_date'])));
            $end_date = date('Y-m-d H:i',(is_numeric($d['end_date'])?$d['end_date']:strtotime($d['end_date'])));
            $event['title'] = $d['title'];
            $event['id'] = $d['id'];
            $event['start'] = $start_date;
            $event['end'] = $end_date;
            $event['allDay'] = false;
            $color="#1641f0";
            if($d['report_status']==0 && strtotime(date("Y-m-d H:i:s"))>=strtotime($start_date)){
                $color="#ff0000";
            }else{
                if(strtotime(date("Y-m-d H:i:s"))>=strtotime($end_date) && $d['report_status']==1){
                    $color="#039c48";
                }
            } 
            $event['color'] = $color;
            $link="";
            $note="";
            $thoigian="";
            $fullname="";
            if(isset($d['fullname']) && $d['fullname']!=""){
                $fullname='<div class="linenote">Thực hiện: <strong>'.$d['fullname'].'</strong></div>';
            }
            if(isset($d['item_id']) && $d['item_id']!=""){
                $link='<div class="linenote"><a href="/'.$d['type'].'/'.$d['item_id'].'/edit" target="_blank">Xem chi tiết</a></div>';
            }
            if(isset($d['note']) && $d['note']!=""){
                $note='<div class="linenote">Ghi chú: '.$d['note']."</div>";
            }
            if(isset($start_date) && $start_date!="" && isset($end_date) && $end_date!=""){
                $thoigian='<div class="linenote">Bắt đầu: '.date("d/m/Y H:i",strtotime($start_date)).' - Kết thúc: '.date("d/m/Y H:i",strtotime($end_date)).'</div>';
            }
            $event['description'] = $fullname.$thoigian.$d['description'].$note.$link;// . '&nbsp;<a href="' . url($d['type'] . '/' . $d['id'] . '/edit') . '" class="btn btn-sm btn-success"><i class="fa fa-pencil-square-o">&nbsp;</i></a>';
            array_push($this->events, $event);
        }
    }
}
