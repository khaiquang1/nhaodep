<?php

namespace App\Http\Controllers\Users;

use App\Events\Call\CallCreated;
use App\Http\Controllers\UserController;
use App\Http\Requests\CallRequest;
use App\Models\Smsdesc;
use App\Models\SmsDescReply;
use App\Models\User;
use App\Models\Phonefail;
use App\Repositories\OptionRepository;


use Illuminate\Pagination\Paginator;
use App\Repositories\CallRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\LeadRepository;
use App\Repositories\UserRepository;


use Yajra\Datatables\Datatables;
use Illuminate\Http\Request;


class SmsController extends UserController
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var CallRepository
     */
    private $callRepository;
    /**
     * @var CompanyRepository
     */
    private $companyRepository;
    /**
	 * @var OptionRepository
	 */
	private $optionRepository;

    private $leadRepository;

    public function __construct(UserRepository $userRepository,
                                CallRepository $callRepository,
                                CompanyRepository $companyRepository,
                                LeadRepository $leadRepository, 
                                OptionRepository $optionRepository
    )
    {
        parent::__construct();

        $this->middleware('authorized:logged_calls.read', ['only' => ['index', 'data']]);
        $this->middleware('authorized:logged_calls.write', ['only' => ['create', 'store', 'update', 'edit']]);
        $this->middleware('authorized:logged_calls.delete', ['only' => ['delete']]);

        $this->userRepository = $userRepository;
        $this->callRepository = $callRepository;
        $this->companyRepository = $companyRepository;
        $this->leadRepository = $leadRepository;
        $this->optionRepository = $optionRepository;
        view()->share('type', 'sms');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = trans('sms.sms_title');
        $dateFormat = config('settings.date_format');
        $user=$this->userRepository->getUser();
        $user_id=$user->id;
        $this->partner_id=$user->partner_id;
        $date  = addslashes($request->starting_date);
        $keyword = addslashes($request->keyword);
        $status  = addslashes($request->status);
        if($date!=""){
			$dateArray=explode("-",trim($date));
			$starting_date=$this->convertDate(trim($dateArray[0]));
			$ending_date=$this->convertDate(trim($dateArray[1]));
			$date_select=$date;
		}else{
			$starting_date=date("Y-m-d",strtotime('today - 30 days'));
			$ending_date=date("Y-m-d",strtotime('today +1 days'));
			$date_select=date("m/d/Y",strtotime('today - 30 days'))." - ".date("m/d/Y");
        }

        $smsQuery = Smsdesc::select('sms_desc.*')
			->where('sms_desc.partner_id',$this->partner_id)
			->where(function ($query)  use ($starting_date, $ending_date, $keyword, $status){
				if($starting_date!=""){
					$query->where('sms_desc.created_at','>=',$starting_date);
				}
				if($ending_date!=""){
					$query->where('sms_desc.created_at','<=',$ending_date);
				}
				
				if($status!="" && $status!="0"){
                    if($status==3){
                        $listPhoneFail=Phonefail::select("phone")->where('partner_id',$this->partner_id)->get();
                        if($listPhoneFail){
                            $query->whereIn('sms_desc.phone',$listPhoneFail);
                        }
                    }else{
                        $query->where('sms_desc.status','=',$status);
                    }
                }
                
                if($keyword!=""){
					$query->where(function ($query1)  use ($keyword){
						$query1->where('sms_desc.description', 'LIKE', "%{$keyword}%");
						$query1->orWhere('sms_desc.phone', 'LIKE', "%{$keyword}%");

					});
				}

			})
			->orderBy('sms_desc.id', 'DESC');
			$totalSMS=$smsQuery->count();
            $smsData=$smsQuery->paginate(50)->appends(request()->query());
            
            $statusList = $this->optionRepository->getAll()->where( 'category', 'sms_status' )->get()
            ->map( function ( $title ) {
                return [
                    'title' => $title->title,
                    'value' => $title->value,
                ];
            } )->pluck( 'title', 'value')
			->prepend(trans('lead.all'), '');

            $smslist=$smsData->map( function ( $sms) use ($dateFormat){
                return [
                    'id'           => $sms->id,
					'created_at'   => date("d/m/Y H:i:s",strtotime($sms->created_at)),
					'device' => $sms->device_id,
                    'phone' => $sms->phone,
					'description'  => $sms->description,
					'status'  => $sms->status
                ];
			});

			
        return view('user.sms.index', compact('title', 'smslist', 'smsData', 'totalSMS', 'statusList'));
    }

    public function reply(Request $request)
    {
        $title = trans('sms.sms_title');
        $dateFormat = config('settings.date_format');
        $user=$this->userRepository->getUser();
        $user_id=$user->id;
        $this->partner_id=$user->partner_id;
        $date  = addslashes($request->starting_date);
        $keyword = addslashes($request->keyword);
        $status  = addslashes($request->status);
        if($date!=""){
			$dateArray=explode("-",trim($date));
			$starting_date=$this->convertDate(trim($dateArray[0]));
			$ending_date=$this->convertDate(trim($dateArray[1]));
			$date_select=$date;
		}else{
			$starting_date=date("Y-m-d",strtotime('today - 30 days'));
			$ending_date=date("Y-m-d",strtotime('today +1 days'));
			$date_select=date("m/d/Y",strtotime('today - 30 days'))." - ".date("m/d/Y");
        }

        $smsQuery = SmsDescReply::select('sms_desc_reply.*')->join("device_token","device_token.uuid","=","sms_desc_reply.device_id")
            ->join("users","users.id","=","device_token.user_id")
            ->where("device_token.status",1)
			->where('users.partner_id',$this->partner_id)->where('sms_desc_reply.type',"inbox")
			->where(function ($query)  use ($starting_date, $ending_date, $keyword, $status){
				if($starting_date!=""){
					$query->where('sms_desc_reply.created_at','>=',$starting_date);
				}
				if($ending_date!=""){
					$query->where('sms_desc_reply.created_at','<=',$ending_date);
				}
				
				if($status!="" && $status!="0"){
                    if($status==3){
                        $listPhoneFail=Phonefail::select("phone")->where('partner_id',$this->partner_id)->get();
                        if($listPhoneFail){
                            $query->whereIn('sms_desc_reply.phone',$listPhoneFail);
                        }
                    }else{
                        $query->where('sms_desc_reply.status','=',$status);
                    }
                }
                
                if($keyword!=""){
					$query->where(function ($query1)  use ($keyword){
						$query1->where('sms_desc_reply.description', 'LIKE', "%{$keyword}%");
						$query1->orWhere('sms_desc_reply.phone', 'LIKE', "%{$keyword}%");

					});
				}

            })
            ->groupBy('sms_desc_reply.sms_id','sms_desc_reply.phone')
			->orderBy('sms_desc_reply.id', 'DESC');
			$totalSMS=$smsQuery->count();
            $smsData=$smsQuery->paginate(50)->appends(request()->query());
            
            $statusList = $this->optionRepository->getAll()->where( 'category', 'sms_status' )->get()
            ->map( function ( $title ) {
                return [
                    'title' => $title->title,
                    'value' => $title->value,
                ];
            } )->pluck( 'title', 'value')
			->prepend(trans('lead.all'), '');

            $smslist=$smsData->map( function ( $sms) use ($dateFormat){
                return [
                    'id'           => $sms->id,
					'created_at'   => date("d/m/Y H:i:s",strtotime($sms->created_at)),
					'device' => $sms->device_id,
                    'phone' => $sms->phone,
                    'description'  => $sms->description,
                    'datesend'  => $sms->date_resent,
					'status'  => $sms->status
                ];
			});

			
        return view('user.sms.reply', compact('title', 'smslist', 'smsData', 'totalSMS', 'statusList'));
    }

    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('call.new');

        $this->generateParams();

        return view('user.call.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(CallRequest $request)
    {
        $call = $this->callRepository->create($request->all());

        event(new CallCreated($call));

        return redirect("call");
    }

    public function edit($call)
    {
        $call = $this->callRepository->find($call);
        $title = trans('call.edit');

        $this->generateParams();

        return view('user.call.create', compact('title', 'call'));
    }

    public function update(CallRequest $request, $call)
    {
        $call = $this->callRepository->find($call);
        $call->update($request->all());

        return redirect("call");
    }


    public function show($call)
    {
        $call = $this->callRepository->find($call);
        $title = trans('call.show');
        $this->generateParams();
        $action = "show";
        return view('user.call.show', compact('title', 'call','action'));
    }

    public function delete($call)
    {
        $call = $this->callRepository->find($call);
        $title = trans('call.delete');
        $this->generateParams();
        return view('user.call.delete', compact('title', 'call'));
    }

    public function destroy($call)
    {
        $call = $this->callRepository->find($call);
        $call->delete();
        return redirect('call');
    }

    public function replySMS(Request $request){
        $phone=$request->phone;
        $date=$request->date;
        if($phone!="" && $date){
            $smsQuery = SmsDescReply::select('sms_desc_reply.*')->where('sms_desc_reply.phone','like','%'.$phone)->where('sms_desc_reply.date_resent','>',$date)
            ->where('sms_desc_reply.type',"sent")->orderBy("sms_desc_reply.id", "desc")->paginate(30)->appends(request()->query());
            $smsQuery=$smsQuery->map( function ( $smsdata){

                    return [
                        'id' => $smsdata->id,
                        'phone' => $smsdata->phone,
                        "description" => $smsdata->description,
                    ];
                }
            );
            return $smsQuery;
        }else{
            return "";
        }
      
    }


}
