<?php

namespace App\Http\Controllers\Api;

use App\Models\DeviceToken;
use App\Models\Email;
use App\Models\InviteUser;
use App\Models\User;
use App\Models\Lead;
use App\Models\LeadAssignStatus;
use App\Models\UserSettingTime;
use App\Models\WebsiteConfig;
use App\Models\CallLogs;
use App\Models\Call;
use App\Models\History;
use App\Models\LogsCall;
use App\Models\Logs;
use App\Models\Product;
use App\Models\CountryPrefix;
use App\Models\NextTimeFollow;
use App\Models\Cookie;
use App\Models\PartnerUser;
use App\Models\Smsdesc;
use App\Models\SmsDescReply;
use App\Models\PartnerDevice;
use App\Models\Partner;
use App\Models\Phonefail;
use App\Models\EmailCheck;
use App\Models\EmailCheckStatus;
use App\Models\Contacts;
use App\Models\Getdata;
use App\Models\Notification;
use App\Models\MessengerPartner;
use App\Models\MessengerMarketingResult;
use App\Repositories\LeadRepository;
use App\Models\Saleorder;
use App\Models\SaleorderProduct;
use App\Models\Brand;
use App\Models\CallActionStatus;
use App\Models\Chatbox;
use App\Models\Leadmap;

use Carbon\Carbon;
use Cartalyst\Sentinel\Laravel\Facades\Reminder;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Http\Request;
use Validator;
use App\Http\Controllers\Controller;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Sentinel;
use Cartalyst\Sentinel\Checkpoints\NotActivatedException;
use Cartalyst\Sentinel\Checkpoints\ThrottlingException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;


/**
 * Auth routes
 *
 * @Resource("Auth", uri="/")
 */

class AuthController extends Controller
{
	private $leadRepository;
    /**
     * Check whether its a SALESDY installation or not
     *
     * @get("/")
     * @versions({"v1"})
     * @Request()
     * @Response(200,body={"success":"This is a SALESDY installation"}
     */
	public function __construct(LeadRepository $leadRepository)
    {
	    $this->leadRepository = $leadRepository;
    }
    public function salesdyCheck()
    {
        return response()->json(["success" => "This is a SALESDY installation"],200);
    }

    /**
     * Login to system
     *
     * @Post("/login")
     * @Versions({"v1"})
     * @Transaction({
     *  @Request({"email": "admin@crm.com","password": "bar"}),
     *  @Response(200, body={
            "token": "token",
            "user": {
            "id": 4,
            "first_name": "Admin",
            "last_name": "Doe",
            "email": "admin@crm.com",
            "phone_number": "465465415",
            "user_id": "1",
            "user_avatar": "image.jpg",
            "permissions" : "{sales_team.read:true,sales_team.write:true,sales_team.delete:true,leads.read:true,leads.write:true,leads.delete:true,opportunities.read:true,opportunities.write:true,opportunities.delete:true,logged_calls.read:true,logged_calls.write:true,logged_calls.delete:true,meetings.read:true,meetings.write:true,meetings.delete:true,products.read:true,products.write:true,products.delete:true,quotations.read:true,quotations.write:true,quotations.delete:true,sales_orders.read:true,sales_orders.write:true,sales_orders.delete:true,invoices.read:true,invoices.write:true,invoices.delete:true,pricelists.read:true,pricelists.write:true,pricelists.delete:true,contracts.read:true,contracts.write:true,contracts.delete:true,staff.read:true,staff.write:true,staff.delete:true}",
            },
            "role": "user",
            "date_format": "2017-10-10",
            "time_format": "10:15",
            "date_time_format": "2017-10-10 10:15"
     *   }),
     *   @Response(401, body={
    "error": "invalid_credentials"
     *   }),
     *   @Response(500, body={
    "error": "could_not_create_token"
     *   })
    })
     */
    public function login(Request $request)
    {
        // grab credentials from the request
        $credentials = $request->only('email', 'password');
        try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => trans('dashboard.invalid_credentials')], 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => trans('dashboard.invalid_credentials')], 500);
        }
        // all good so return the data
        Sentinel::authenticate($request->only('email', 'password'), $request['remember-me']);
        $user = Sentinel::getUser();

        if ($user->inRole('admin')) {
            $role = 'admin';
        }
        elseif ($user->inRole('user')) {
            $role = 'user';
        }
        elseif ($user->inRole('staff')) {
            $role = 'staff';
        }
        elseif ($user->inRole('customer')) {
            $role = 'customer';
        }
        else{
            $role = 'no_role';
        }
        $user = User::select('id','first_name','last_name', 'email', 'phone_number','user_id','user_avatar', 'partner_id')->find(Sentinel::getUser()->id);
        $permissions=User::find(Sentinel::getUser()->id)->getPermissions();
		User::where('id',Sentinel::getUser()->id)->update(['token_api' => $token]);
        return response()->json(['token'=> $token,
                                 'user' => $user,
                                 'role' => $role,
                                 'date_format' => Settings::get('date_format'),
                                 'time_format' => Settings::get('time_format'),
                                 'date_time_format' => Settings::get('date_format').' '.Settings::get('time_format'),
                                 'permissions'=>$permissions], 200);
	}
	
	public function logout(Request $request){
		$data = array(
			'token' => $request->input('token'),
		);
		$rules = array(
			'token' => 'required',
		);
		$validator = Validator::make($data, $rules);
	    if ($validator->passes()) {
			$this->user = JWTAuth::parseToken()->authenticate();
			User::where('id',$this->user->id)->update(['token_api' => ""]);
		}
		return response()->json(['success' => "success"], 200);
	}

	/**
	 * Edit profile
	 *
	 * @Post("/edit_profile")
	 * @Versions({"v1"})
	 * @Transaction({
	 *       @Request({"token": "foo", "first_name":"First","last_name":"Last", "phone_number":"+356421544","email":"email@email.com", "password":"password", "password_confirmation":"password","avatar":"base64_encoded_image"}),
	 *       @Response(200, body={"success":"success"}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *    })
	 * })
	 */

	public function editProfile(Request $request)
	{
		$this->user = JWTAuth::parseToken()->authenticate();
		$data = array(
			'first_name' => $request->input('first_name'),
			'last_name' => $request->input('last_name'),
			'phone_number' => $request->input('phone_number'),
			'email' => $request->input('email'),
			'password' => $request->input('password'),
		);
		$rules = array(
			'first_name' => 'required',
			'last_name' => 'required',
			'phone_number' => 'required',
			'email' => 'required'
		);
		$validator = Validator::make($data, $rules);
		if ($validator->passes()) {
			$user = User::find($this->user->id);
			if ($request->password != "") {
				$user->password = bcrypt($request->password);
			}
			if (!is_null($request->avatar)) {
				$output_file = uniqid() . ".jpg";
				$ifp = fopen(public_path() . '/uploads/avatar/' . $output_file, "wb");
				fwrite($ifp, base64_decode($request->avatar));
				fclose($ifp);
				$user->user_avatar = $output_file;
			}
			$user->phone_number = $request->phone_number;
			$user->update($request->except('token', 'password', 'avatar'));

			return response()->json(['success' => "success"], 200);
		} else {
			return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
		}
	}
/**
	 * detailProfile
	 *
	 * @Post("/edit_profile")
	 * @Versions({"v1"})
	 * @Transaction({
	 *       @Request({"token": "foo", "first_name":"First","last_name":"Last", "phone_number":"+356421544","email":"email@email.com", "password":"password", "password_confirmation":"password","avatar":"base64_encoded_image"}),
	 *       @Response(200, body={"success":"success"}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *    })
	 * })
	 */

	public function detailProfile(Request $request)
	{
		$this->user = JWTAuth::parseToken()->authenticate();
		$data = array(
			'user_id' => $this->user->id,
		);
		$rules = array(
			'user_id' => 'required',
		);
		$validator = Validator::make($data, $rules);
		if ($validator->passes()) {

			return response()->json(['user' => $this->user], 200);
		} else {
			return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
		}
	}

	/**
	 * Create profile from staff invite
	 *
	 * @Post("/create_profile_invite")
	 * @Versions({"v1"})
	 * @Transaction({
	 *       @Request({"first_name":"First","last_name":"Last", "phone_number":"+356421544","password":"password", "code":"invite_code"}),
	 *       @Response(200, body={"success":"success"}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *    })
	 * })
	 */

	public function createProfileInvite(Request $request)
	{
		$data = array(
			'first_name' => $request->input('first_name'),
			'last_name' => $request->input('last_name'),
			'phone_number' => $request->input('phone_number'),
			'password' => $request->input('password'),
			'code' => $request->input('code'),
		);
		$rules = array(
			'first_name' => 'required',
			'last_name' => 'required',
			'phone_number' => 'required',
			'password' => 'required',
			'code' => 'required',
		);
		$validator = Validator::make($data, $rules);
		if ($validator->passes()) {
			$user = User::where('email',$request->email)->first();
			$inviteUser = InviteUser::where('email',$request->email)
			                         ->where('code',$request->code)->first();
			if(!is_null($user) || !is_null($inviteUser)){
				return response()->json(['error' => "not_valid_data"], 500);
			}
			$staff = Sentinel::registerAndActivate(
				array(
					'first_name' => $request->first_name,
					'last_name' => $request->last_name,
					'email' => $inviteUser->email,
					'user_id'=>1,
					'password' => $request->password,
				)
			);
			$role = Sentinel::findRoleBySlug('staff');
			$role->users()->attach($staff);

			$user = User::find($staff->id);
			$user->phone_number = $request->phone_number;
			$user->save();

			$inviteUser->claimed_at = Carbon::now();
			$inviteUser->save();

			return response()->json(['success' => "success"], 200);
		} else {
			return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
		}
	}


	/**e
	 * Create profile from staff invite
	 *
	 * @Post("/update_password")
	 * @Versions({"v1"})
	 * @Transaction({
	 *       @Request({"code": "foo", "id":1, "password":"password"}),
	 *       @Response(200, body={"success":"success"}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *    })
	 * })
	 */

	public function updatePassword(Request $request)
	{
		$data = array(
			'code' => $request->input('code'),
			'id' => $request->input('id'),
			'password' => $request->input('password')
		);
		$rules = array(
			'code' => 'required',
			'id' => 'required',
			'password' => 'required',
		);
		$validator = Validator::make($data, $rules);
		if ($validator->passes()) {
			$user = Sentinel::findById($request->id);
			$reminder = Reminder::exists($user, $request->code);
			//incorrect info was passed.
			if ($reminder == false) {
				return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
			}
			Reminder::complete($user, $request->code, $request->password);

			return response()->json(['success' => "success"], 200);
		} else {
			return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
		}
	}

	

	/**
	 * Get all email
	 *
	 * @Get("/emails")
	 * @Versions({"v1"})
	 * @Transaction({
	 *      @Request({"token": "foo"}),
	 *      @Response(200, body={
	"emails": {
	{
	"get_emails": {
	{
	"id": 14,
	"assign_customer_id": 0,
	"to": "1",
	"from": "1",
	"subject": "dfgdfg",
	"message": "dfgfdg",
	"read": 0,
	"delete_sender": 0,
	"delete_receiver": 0,
	"created_at": "2017-06-23 11:05:46",
	"updated_at": "2017-06-23 11:05:46",
	"deleted_at": null,
	"sender": {
	"id": 1,
	"email": "admin@crm.com",
	"last_login": "2017-06-23 14:02:43",
	"first_name": "Admin",
	"last_name": "Admin",
	"phone_number": null,
	"user_avatar": null,
	"user_id": 1,
	"created_at": "2017-03-02 16:09:12",
	"updated_at": "2017-06-23 14:02:43",
	"deleted_at": null,
	"full_name": "Admin Admin",
	"avatar": "http://localhost:81/lcrm54/public/uploads/avatar/user.png"
	}
	}
	},
	"sent_emails": {
	{
	"id": 14,
	"assign_customer_id": 0,
	"to": "1",
	"from": "1",
	"subject": "dfgdfg",
	"message": "dfgfdg",
	"read": 0,
	"delete_sender": 0,
	"delete_receiver": 0,
	"created_at": "2017-06-23 11:05:46",
	"updated_at": "2017-06-23 11:05:46",
	"deleted_at": null,
	"receiver": {
	"id": 1,
	"email": "admin@crm.com",
	"last_login": "2017-06-23 14:02:43",
	"first_name": "Admin",
	"last_name": "Admin",
	"phone_number": null,
	"user_avatar": null,
	"user_id": 1,
	"created_at": "2017-03-02 16:09:12",
	"updated_at": "2017-06-23 14:02:43",
	"deleted_at": null,
	"full_name": "Admin Admin",
	"avatar": "http://localhost:81/lcrm54/public/uploads/avatar/user.png"
	}
	}
	}
	}
	}
	}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *       })
	 * })
	 */
	public function emails(Request $request)
	{
		$this->user = JWTAuth::parseToken()->authenticate();
		$get_emails = Email::with('sender')->where('to', $this->user->id)->where('delete_receiver', 0)->orderBy('id', 'desc')->get();
		$sent_emails = Email::with('receiver')->where('from', $this->user->id)->where('delete_sender', 0)->orderBy('id', 'desc')->get();

		return response()->json(['get_emails' => $get_emails, 'sent_emails'=>$sent_emails], 200);
	}

	/**
	 * Get single email
	 *
	 * @Get("/email")
	 * @Versions({"v1"})
	 * @Transaction({
	 *      @Request({"token": "foo","email_id":"1"}),
	 *      @Response(200, body={
	"email": {
	"id": 1,
	"assign_customer_id": 0,
	"to": "1",
	"from": "1",
	"subject": "dfgdfg",
	"message": "dfgfdg",
	"read": 1,
	"delete_sender": 0,
	"delete_receiver": 0,
	"created_at": "2017-06-23 11:05:46",
	"updated_at": "2017-06-23 14:34:56",
	"deleted_at": null,
	"sender": {
	"id": 1,
	"email": "admin@crm.com",
	"last_login": "2017-06-23 14:02:43",
	"first_name": "Admin",
	"last_name": "Admin",
	"phone_number": null,
	"user_avatar": null,
	"user_id": 1,
	"created_at": "2017-03-02 16:09:12",
	"updated_at": "2017-06-23 14:02:43",
	"deleted_at": null,
	"full_name": "Admin Admin",
	"avatar": "http://localhost:81/lcrm54/public/uploads/avatar/user.png"
	}
	}
	}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *       })
	 * })
	 */
	public function email(Request $request)
	{
		$this->user = JWTAuth::parseToken()->authenticate();
		$data = array(
			'email_id' => $request->input('email_id')
		);
		$rules = array(
			'email_id' => 'required|integer',
		);
		$validator = Validator::make($data, $rules);
		if ($validator->passes() && $this->user) {
			$email = Email::with('sender')->find($request->email_id);
			$email->read = 1;
			$email->save();
			return response()->json(['email' => $email], 200);
		} else {
			return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
		}
	}

	/**
	 * Post email
	 *
	 * @Post("/post_email")
	 * @Versions({"v1"})
	 * @Transaction({
	 *       @Request({"token": "foo","message":"This is message","recipients":{1,2,3},"subject":"Email subject"}),
	 *       @Response(200, body={"success":"success"}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *    })
	 * })
	 **/

	public function postEmail(Request $request)
	{
		$this->user = JWTAuth::parseToken()->authenticate();
		$data = array(
			'recipients' => $request->input('recipients'),
			'subject' => $request->input('subject'),
			'message' => $request->input('message')
		);
		$rules = array(
			'recipients' => 'required',
			'subject' => 'required',
			'message' => 'required',
		);
		$validator = Validator::make($data, $rules);
		if ($validator->passes()) {
			$request->merge(['user_id' => $this->user->id]);
			if (!empty($request->recipients)) {
				foreach ( $request->recipients as $item ) {
					if ( $item != "0" && $item != "" ) {
						$email       = new Email( $request->only( 'subject', 'message' ) );
						$email->to   = $item;
						$email->from = $this->user->id;
						$email->save();

						$user = User::find( $item );

						if ( ! filter_var( Settings::get( 'site_email' ), FILTER_VALIDATE_EMAIL ) === false ) {
							Mail::send( 'emails.contact', array (
								'user'        => $user->first_name . ' ' . $user->last_name,
								'bodyMessage' => $request->message
							),
								function ( $m )
								use ( $user, $request ) {
									$m->from( Settings::get( 'site_email' ), Settings::get( 'site_name' ) );
									$m->to( $user->email )->subject( $request->subject );
								} );
						}
					}
				}
			}
			return response()->json(['success' => "success"], 200);
		} else {
			return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
		}
	}

	/**
	 * Delete email
	 *
	 * @Post("/delete_email")
	 * @Versions({"v1"})
	 * @Transaction({
	 *      @Request({"token": "foo", "email_id":"1"}),
	 *      @Response(200, body={"success":"success"}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *       })
	 * })
	 */
	public function deleteEmail(Request $request)
	{
		$this->user = JWTAuth::parseToken()->authenticate();
		$data = array(
			'email_id' => $request->input('email_id'),
		);
		$rules = array(
			'email_id' => 'required|integer',
		);
		$validator = Validator::make($data, $rules);
		if ($validator->passes()) {
			$mail = Email::find($request->email_id);
			if ($mail->to == $this->user->id) {
				$mail->delete_receiver = 1;
			} else {
				$mail->delete_sender = 1;
			}
			$mail->save();
			return response()->json(['success' => "success"], 200);
		} else {
			return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
		}
	}

	/**
	 * Replay email
	 *
	 * @Post("/replay_email")
	 * @Versions({"v1"})
	 * @Transaction({
	 *       @Request({"token": "foo","message":"This is message", "email_id":1}),
	 *       @Response(200, body={"success":"success"}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *    })
	 * })
	 **/

	public function replayEmail(Request $request)
	{
		$this->user = JWTAuth::parseToken()->authenticate();
		$data = array(
			'email_id' => $request->input('email_id'),
			'message' => $request->input('message')
		);
		$rules = array(
			'email_id' => 'required',
			'message' => 'required',
		);
		$validator = Validator::make($data, $rules);
		if ($validator->passes()) {
			$email_orig = Email::find($request->only( 'email_id' ) )->first();
			if ( !is_null($email_orig) ) {
				$request->merge(['subject' => 'Re: '.$email_orig->subject]);
				$email       = new Email( $request->only( 'message','subject' ) );
				$email->to   = $email_orig->from;
				$email->from = $this->user->id;
				$email->save();

				$user = User::find( $email_orig->from );

				if ( ! filter_var( Settings::get( 'site_email' ), FILTER_VALIDATE_EMAIL ) === false ) {
					Mail::send( 'emails.contact', array (
						'user'        => $user->first_name . ' ' . $user->last_name,
						'bodyMessage' => $request->message
					),
						function ( $m )
						use ( $user, $request ) {
							$m->from( Settings::get( 'site_email' ), Settings::get( 'site_name' ) );
							$m->to( $user->email )->subject( $request->subject );
						} );
				}
			}
			return response()->json(['success' => "success"], 200);
		} else {
			return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
		}
	}


	/**
	 * Password recovery
	 *
	 * @Post("/password_recovery")
	 * @Versions({"v1"})
	 * @Transaction({
	 *      @Request({"email":"admin@sms.com"}),
	 *      @Response(200, body={"success":"success"}),
	 *      @Response(201, body={"error":"user_dont_exists"}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *       })
	 * })
	 */
	public function passwordRecovery(Request $request)
	{
		$data = array(
			'email' => $request->input('email'),
		);
		$rules = array(
			'email' => 'required|email',
		);
		$validator = Validator::make($data, $rules);
		if ($validator->passes()) {
			$userFind = User::where('email', $request->email)->first();
			if (isset($userFind->id)) {
				$user = Sentinel::findById($userFind->id);
				$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
				$password = '';
				for ($i = 0; $i < 5; $i++){
					$password .= $characters[mt_rand(0, 61)];
				}
				$data = [
					'email' => $user->email,
					'name' => $userFind->full_name,
					'subject' => trans('auth.your_new_password'),
					'password' => $password,
					'id' => $user->id
				];
				$user->password = bcrypt($password);
				$user->update();
				Mail::send('emails.reminderMobile', $data, function ($message) use ($data) {
					$message->to($data['email'], $data['name'])->subject($data['subject']);
				});
				return response()->json(['success' => "success"], 200);
			}
			return response()->json(['error' => "user_dont_exists"], 201);
		} else {
			return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
		}
	}
	//Phone check
    public function phoneCheck(Request $request)
    {
        $phone=$request->phone;
        if($phone!=""){
            $users = User::select('id','first_name','phone_verify', 'user_avatar')->where('phone_number', '=', $phone)->first();
            if(isset($users->id)) {
                return response()->json(['staff' => $users, 'status' => 1], 200);
            }else{
                return response()->json(['error' => 'phone_not_exist', 'status' => 2], 200);
            }
        } else {
            return response()->json(['error' => 'phone_fail', 'status' => 0], 500);
        }
    }
    //Update phone verify link /phone_update
    public function phoneUpdate(Request $request)
    {
        $phone=$request->phone;
        if($phone!=""){
            $usersCheck = User::where('phone_number', $phone)->first();
            if(isset($usersCheck->id)){
                User::where('id',$usersCheck->id)->update(['phone_verify' => 1]);
                return response()->json(['status' => 1], 200);
            }else{
                return response()->json(['error' => 'phone_not_exist', 'status' => 2], 200);
            }

        } else {
            return response()->json(['error' => 'phone_fail', 'status' => 0], 500);
        }
    }
    // Create user
    /**
     * Create profile from staff invite
     *
     * @Post("/create_profile_invite")
     * @Versions({"v1"})
     * @Transaction({
     *       @Request({"first_name":"First","last_name":"Last", "phone_number":"+356421544","password":"password", "code":"invite_code"}),
     *       @Response(200, body={"success":"success"}),
     *      @Response(500, body={"error":"not_valid_data"})
     *    })
     * })
     */

    public function createProfile(Request $request)
    {
		$phone=$request->input('phone_number');
		$prefix=CountryPrefix::get();
        $data = array(
            'first_name' => $request->input('fullname'),
            'email' => $request->input('email'),
            'phone_number' => $request->input('phone_number'),
			'password' => $request->input('password'),
        );
        $rules = array(
            'first_name' => 'required',
            'email' => 'required',
            'password' => 'required',
        );

        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $user = User::where('email',$request->email)->first();

            if(!is_null($user)){
                return response()->json(['error' => trans('dashboard.account_already_exists')], 500);
            }
            $staff = Sentinel::registerAndActivate(
                array(
                    'first_name' => $request->fullname,
                    'email' => $request->email,
                    'password' => $request->password,
                    'user_id' => 1,
                )
            );
            $role = Sentinel::findRoleBySlug('staff');
            $role->users()->attach($staff);
            $user = User::find($staff->id);
            $user->addPermission("sales_team.read");
            $user->addPermission("leads.read");
            $user->addPermission("leads.write");
			$user->addPermission("opportunities.read");
			$user->addPermission("products.read");
			$user->addPermission("quotations.read");
			$user->addPermission("quotations.write");
			$user->addPermission("opportunities.write");
			$user->user_id=1;
			$user->phone_number = $request->phone_number;
			$user->phone_verify = 1;
			$user->save();
			//Update seting
			$dataSettingTime = array(
				'user_id' => $staff->id,
				'time_start'=>"07:00:00",
				'time_end'=> "20:00:00",
				'status'=>1,
				'date_create'=> date("Y-m-d H:i:s"),
			);
			UserSettingTime::insert($dataSettingTime);
			// Add to log
			$dataLogs = array(
			 'user_id' => $staff->id,
			 'logs'=>"T???o t??i kho???n m???i",
			 'phone'=>$request->phone_number,
			 'created_at'=> date("Y-m-d H:i:s"),
			 );
			Logs::insert($dataLogs);
			// end add
            return response()->json(['success' => "success"], 200);
        } else {
            return response()->json(['error' =>  trans('dashboard.lost_infomation')], 500);
        }
    }

    //Create device token

    public function createDeviceToken(Request $request)
    {
        $data = array(
            'token' => $request->input('token'),
            'device_name' => $request->input('device_name'),
            'platform' => $request->input('platform'),
            'uuid' => $request->input('uuid'),
            'version' => $request->input('version'),
            'user_id' => $request->input('user_id'),
            'created_at'=>date('Y-m-d H:i:s')
        );
        $rules = array(
            'device_name' => 'required',
            'uuid' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $tokencheck = DeviceToken::where('token',$request->token)->first();
            if(!is_null($tokencheck)){
                return response()->json(['error' => "token_exist_update", 'result'=>1], 500);
            }else{
				DeviceToken::where('uuid',$request->uuid)->update(['status' => 0]);
			}
            $tokenData = new DeviceToken($request->except('token', 'device_name'));
            $tokenData->created_at = date('Y-m-d H:i:s');
            $tokenData->user_id = $request->input('user_id');
            $tokenData->token = $request->input('token');
            $tokenData->device_name = $request->input('device_name');
			$tokenData->platform = $request->input('platform');
			$tokenData->uuid = $request->input('uuid');
            $tokenData->version = $request->input('version');
            $tokenData->save();
            return response()->json(['success' => "success", 'result'=>1], 200);
        } else {
            return response()->json(['error' => trans('dashboard.lost_infomation'), 'result'=>0], 500);
        }
	}
	
	/**
	 * Update password mobile
	 *
	 * @Post("/update_password_mobile")
	 * @Versions({"v1"})
	 * @Transaction({
	 *       @Request({"code": "foo", "id":1, "password":"password"}),
	 *       @Response(200, body={"success":"success"}),
	 *      @Response(500, body={"error":"not_valid_data"})
	 *    })
	 * })
	 */

	public function updatePasswordMobile(Request $request)
	{
            $data = array(
                'email' => $request->input('email'),
                'password' => $request->input('password')
            );
            $rules = array(
                'email' => 'required',
                'password' => 'required',
            );
            $validator = Validator::make($data, $rules);
            if ($validator->passes()) {
                $user = User::where('email',$request->email)->first();
                if ($request->password != "") {
                    $user->password = bcrypt($request->password);
                }else{
                    return response()->json(['error' => "not_valid_data"], 500);
                }
                if(!is_null($user)){
                    $user->update($request->except('password', 'email'));
                    // Add to log
                    $dataLogs = array(
                     'user_id' => $user->id,
					 'logs'=>"C???p nh???t password m???i ",
                     'created_at'=> date("Y-m-d H:i:s"),
                     );
                    Logs::insert($dataLogs);
                    // end add
                    return response()->json(['success' => "success"], 200);
                }else{
                    return response()->json(['error' => "not_valid_data"], 500);
                }
                
            } else {
                return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
            }
        }
        // Push notification for mapp news
        public function pushNoitification(Request $request)
        {
            
            $test=0;
            $reportCalendarSMS=0;
            if(isset($request->test) && $request->test!=""){
                $test=$request->test;
            }
            if(isset($request->sms) && $request->sms!=""){
                $reportCalendarSMS=$request->sms;
            }
            if($reportCalendarSMS==1){
                //Test push
                    if(isset($request->token) && $request->token!=""){
                        $token=$request->token;
                    }
                    $url = 'https://fcm.googleapis.com/fcm/send';
                    $server_key = 'AAAAZByjYLM:APA91bFKEklcX4nzA6UM2wupVHulSHNFFkXQzh4Qz2ZqbJVZ9IvKSs8JYB0PtacwwgF878z2hIuLWJc0yCClWtbRV8aVvq4XHpf8guQOWOY3jqCjBHhqBXJDlZoMhycfsV0EGb9zSKIs';
                    //header with content_type api key
                    $headers = array(
                                     'Content-Type:application/json',
                                     'Authorization:key='.$server_key
                                     );
                    $title="Th??ng b??o l???ch g???i tin nh???n";
                    $data = array(
                                  "to"=>$token,
                                  "priority"=>"high",
                                  "data"=>array(
                                                "description"=>"B???n c?? l???ch g???i tin nh???n",
                                                "product_name"=>1,
                                                "time"=>0,
                                                "type"=>0
                                                ),
                                  //0 binh thuong, 1 New lead, 2 follow
                                  "notification"=>array(
                                                        "title"=> "FasterSendy Th??ng b??o",
                                                        "body"=> "C?? l???ch g???i tin nh???n cho kh??ch h??ng v??o l??c ".date("Y-m-d H:i:s"),
                                                        "sound"=>"default"
                                                        )
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
                    if($result === FALSE){
                        die('Curl failed: ' . curl_error($ch));
                    }
                    // Close connection
                    curl_close($ch);
                    echo $result;
                    if($token!=""){
                        return "";
                        die();
                    }
                die();
            }
            if($test==1){
                //Test push
                    if(isset($request->token) && $request->token!=""){
                        $token=$request->token;
                    }
                    $url = 'https://fcm.googleapis.com/fcm/send';
                    $server_key = 'AAAAZByjYLM:APA91bFKEklcX4nzA6UM2wupVHulSHNFFkXQzh4Qz2ZqbJVZ9IvKSs8JYB0PtacwwgF878z2hIuLWJc0yCClWtbRV8aVvq4XHpf8guQOWOY3jqCjBHhqBXJDlZoMhycfsV0EGb9zSKIs';
                    //header with content_type api key
                    $headers = array(
                                     'Content-Type:application/json',
                                     'Authorization:key='.$server_key
                                     );
                    $title="Th??ng b??o test nh???n lead";
                    $data = array(
                                  "to"=>$token,
                                  "priority"=>"high",
                                  "data"=>array(
                                                "description"=>"Test  th??ng b??o nh???n lead ",
                                                "product_name"=>1,
                                                "time"=>0,
                                                "type"=>0
                                                ),
                                  //0 binh thuong, 1 New lead, 2 follow
                                  "notification"=>array(
                                                        "title"=> "Faster CRM Th??ng b??o",
                                                        "body"=> "Th??ng b??o ????ng k?? FasterCRM",
                                                        "sound"=>"default"
                                                        )
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
                    if($result === FALSE){
                        die('Curl failed: ' . curl_error($ch));
                    }
                    // Close connection
                    curl_close($ch);
                    echo $result;
                    if($token!=""){
                        return "";
                        die();
                    }
                die();
            }
        
           // End test
			$lead_id=0;
			$token="";
			$partner_id="";
			$sales_person_id="";
		 	if(isset($request->lead_id) && $request->lead_id!=""){
			  $lead_id=$request->lead_id;
			}
			if(isset($request->token) && $request->token!=""){
				$token=$request->token;
            }
			if(isset($request->partner_id) && $request->partner_id!=""){
				$partner_id=$request->partner_id;
			}
			if(isset($request->user_id) && $request->user_id!=""){
				$sales_person_id=$request->user_id;
			}
			$timenow=date("H:i");
			$ThatTime1 ="08:00:00";
			$ThatTime2 ="22:00:00";
			if($partner_id==1){
				$ThatTime1 ="02:00:00";
				$ThatTime2 ="23:00:00";
			} 
			//if (time() >= strtotime($ThatTime1) && time()<strtotime($ThatTime2) && $partner_id!="") {
			$leadPush = Lead::select('leads.*')
			->where('leads.sales_person_id',0)
			->where(function ($query)  use ($lead_id, $partner_id){
				if($lead_id>0){
					$query->where('leads.id','=',$lead_id);
				}
				if($partner_id>0){
				$query->where('leads.partner_id','=',$partner_id);
				}
				
			})
			//->where('leads.phone','!=','')
			->offset(0)
			->limit(10)
			->get();
			if($leadPush){
				foreach($leadPush as $leadData){
					$partner_id=$leadData["partner_id"];
					$time2=date("Y-m-d H:i:s", strtotime("-2 minutes"));
					$timeint=strtotime("-2 minutes");
					$listUsersLimit ="";
					$devicePush = DeviceToken::select('device_token.*', 'users.first_name', 'users.last_name', 'users.token_api')
					->join('users','users.id','=','device_token.user_id')
					->join('user_setting_time','users.id','=','user_setting_time.user_id')
					->where(function($query) use($token,$timenow,$partner_id, $time2, $timeint, $sales_person_id){
						if($token!=""){
							
							$query->where('device_token.token','=',$token);
							/* $query->where('user_setting_time.status',1); */
							$query->where('device_token.status',1);
							$query->where('user_setting_time.status',1);
							//$query->where('user_setting_time.time_start','<=',$timenow);
							//$query->where('user_setting_time.time_end','>=',$timenow);
							//$query->where('users.token_api','<>','');
							$query->where('users.partner_id','=',$partner_id);
						}else{ 
							$query->where('device_token.status',1);
							$query->where('user_setting_time.status',1);
							$query->where('user_setting_time.time_start','<=',$timenow);
							$query->where('user_setting_time.time_end','>=',$timenow);
							//$query->where('users.token_api','<>','');
							$query->where('users.partner_id','=',$partner_id);
							//$query->where('partner_user.assign_time', '<=', $timeint);
							//$query->where('device_token.last_assign', '<=', $time2)->orWhereNull('device_token.last_assign');
						}
						if($sales_person_id!=""){
							$query->where('users.id',$sales_person_id);
						}
						$query->where('users.received_lead',1);
						
					})->orderBy('users.assign_time', 'asc')->orderBy('device_token.id', 'desc')->first();
					if($devicePush){
                        User::where(['id'=>$devicePush->user_id,'partner_id'=>$partner_id])->update(['assign_time' => time()]);
						//Check token exits
						$data = array(
							'token' => $devicePush->token_api,
						);
						$rules = array(
							'token' => 'required',
						);
						$validator = Validator::make($data, $rules);
						$tokenApi=$devicePush->token_api;
						if ($validator->passes()) {
							//if($this->user){		
								$data = array(
									'lead_id' => $leadData["id"],
									'user_id'=>$devicePush->user_id,
									'status'=> 0,
									'time_call'=>1,
									'date_create'=> date("Y-m-d H:i:s"),
								);
								LeadAssignStatus::insert($data);
								// Add to log
								$dataLogs = array(
								'user_id' => $devicePush->user_id,
								'logs'=>"@".$devicePush->first_name." ".$devicePush->last_name." Nh???n ???????c y??u c???u ch??m s??c lead @".$leadData["opportunity"],
								'phone'=>$leadData["phone"],
								'lead_id'=>$leadData["id"],
								'created_at'=> date("Y-m-d H:i:s"),
								);
								Logs::insert($dataLogs);
								DeviceToken::where('id',$devicePush->id)->update(['last_assign' => date("Y-m-d H:i:s")]);

								$lead = Lead::find($leadData["id"]);
								$lead->sales_person_id = $devicePush->user_id;
								$lead->status = 1;
								$lead->save();

								$url = 'https://fcm.googleapis.com/fcm/send';
								$server_key = 'AAAAZByjYLM:APA91bFKEklcX4nzA6UM2wupVHulSHNFFkXQzh4Qz2ZqbJVZ9IvKSs8JYB0PtacwwgF878z2hIuLWJc0yCClWtbRV8aVvq4XHpf8guQOWOY3jqCjBHhqBXJDlZoMhycfsV0EGb9zSKIs';
								//header with content_type api key
								$headers = array(
									'Content-Type:application/json',
									'Authorization:key='.$server_key
								);
								if($request->type){
									$type=$request->type;
								}else{
									$type=1;
								}
								$title="Th??ng b??o nh???n lead";
								$data = array(
									"to"=>$devicePush->token,
									"priority"=>"high",
									"data"=>array(
										"message"=>$title,
										"lead_id"=>$leadData["id"],
										"thumbnail"=>"https://api.fastercrm.com/images/167.png",
										"description"=>"H??y li??n h??? v???i kh??ch h??ng: ".$leadData["opportunity"],
										"time"=>$leadData["next_follow_up"],
										"type"=>$type
									),
									//0 binh thuong, 1 New lead, 2 follow
									"notification"=>array(
										"title"=> "Th??ng b??o",
										"body"=> "Th??ng b??o nh???n lead t??? h??? th???ng FasterCRM",
										"sound"=>"default",
										"type"=>$type,
										"lead_id"=> $leadData["id"],
										"thumbnail"=>"https://api.fastercrm.com/images/167.png",
										"product_name"=>"",
										"time"=>$leadData["next_follow_up"],
									)
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

								if($result === FALSE){
									die('Curl failed: ' . curl_error($ch));
								}
								// Close connection
								curl_close($ch);
								if($token!=""){
									return "";
									die();
								}
								return response()->json(['notification' => $devicePush["first_name"]." ".$devicePush["last_name"]], 200);


						//	}
						}
						//End check

				//	}
				}
				
			}

			}else{
				die();
			}
			return '';

	}
	// Push notification for mapp news
	public function pushNoitificationNextTimeFollow(Request $request)
	{
			$lead_id=0;
			$token="";
		 	if(isset($request->lead_id) && $request->lead_id!=""){
			  $lead_id=$request->lead_id;
			}
			if(isset($request->token) && $request->token!=""){
				$token=$request->token;
			  }
			$time_now_plus1=date("Y-m-d H:i:s", strtotime("+1 minutes"));
			$time_now_plus2=date("Y-m-d H:i:s", strtotime("+2 minutes"));
			$leadPush = Lead::select('leads.*','products.product_name','products.product_image')
						->leftJoin('products','leads.product_id','=','products.id')
						->join('next_time_follow_status','leads.id','=','next_time_follow_status.lead_id')
						->where('leads.sales_person_id','>','0')
						->where(function ($query)  use ($lead_id){
							if($lead_id>0){
								$query->where('leads.id','=',$lead_id);
							}
						})
						->where('leads.next_follow_up','<=',$time_now_plus1)
						->where('next_time_follow_status.status',0)
						->where('next_time_follow_status.count_push','<=',5)
						->where('leads.phone','!=','')
						->offset(0)
                		->limit(10)
						->get();
			$timenow=date("H:m");
			
			if($leadPush){
				foreach($leadPush as $leadData){
					$devicePush = DeviceToken::select('device_token.*', 'users.first_name', 'users.last_name')
					->join('users','users.id','=','device_token.user_id')
					->where('device_token.status',1)
					->where(function($query) use($token){
						if($token!=""){
							$query->where('device_token.token','=',$token);
						}else{
							$query->where('device_token.user_id',$leadData["sales_person_id"]);
						}
					})
					->first();
					if($devicePush){
						$data = array(
							'lead_id' => $leadData["id"],
							'user_id'=>$devicePush->user_id,
							'status'=> 0,
							'time_call'=>1,
							'date_create'=> date("Y-m-d H:i:s"),
						);
						LeadAssignStatus::insert($data);
						NextTimeFollow::where('lead_id',$leadData["id"])->increment('count_push');
						// Add to log
						$fullname=trim($devicePush->fist_name." ".$devicePush->last_name);
						$dataLogs = array(
						'user_id' => $devicePush->user_id,
						'logs'=>"@".$fullname." Nh???n ???????c y??u c???u g???i l???i cho kh??ch h??ng @".$leadData["opportunity"],
						'created_at'=> date("Y-m-d H:i:s"),
						'phone'=>$leadData["phone"],
						'lead_id'=>$leadData["id"],
						);
						Logs::insert($dataLogs);
						$url = 'https://fcm.googleapis.com/fcm/send';
						$server_key = 'AAAAZByjYLM:APA91bFKEklcX4nzA6UM2wupVHulSHNFFkXQzh4Qz2ZqbJVZ9IvKSs8JYB0PtacwwgF878z2hIuLWJc0yCClWtbRV8aVvq4XHpf8guQOWOY3jqCjBHhqBXJDlZoMhycfsV0EGb9zSKIs';
						//header with content_type api key
						$headers = array(
							'Content-Type:application/json',
							'Authorization:key='.$server_key
						);
						$type=2;
						$title="Th??ng b??o g???i l???i cho kh??ch";
						$data = array(
							"to"=>$devicePush->token,
							"priority"=>"high",
							"data"=>array(
								"message"=>$title,
								"lead_id"=>$leadData["id"],
								"thumbnail"=>env('APP_URL_PHOTO', '').'/uploads/products/'.$leadData["product_image"],
								"description"=>"G???i l???i cho kh??ch:	  ".$leadData["opportunity"].". Thu???c D??? ??n/S???n ph???m ".$leadData["product_name"],
								"product_name"=>$leadData["product_name"],
								"time"=>$leadData["next_follow_up"],
								"type"=>$type
							),
							//0 binh thuong, 1 New lead, 2 follow
							"notification"=>array(
								"title"=> "Th??ng b??o",
								"body"=> "Th??ng b??o ????ng k?? FASTERCRM",
								"sound"=>"default",
								"type"=>$type,
								"lead_id"=> $leadData["id"],
								"thumbnail"=>env('APP_URL_PHOTO', '').'/uploads/products/'.$leadData["product_image"],
								"product_name"=>$leadData["product_name"],
								"time"=>$leadData["next_follow_up"],
							)
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
						if($result === FALSE){
							die('Curl failed: ' . curl_error($ch));
						}
						// Close connection
						curl_close($ch);
						echo $result;
						if($token!=""){
							return "";
							die();
						}
					}
				}
				
			}
			return '';

	}
	public function setStatusPush(Request $request){
		$data = array(
			'lead_id' => $request->lead_id,
			'user_id'=>$request->user_id,
			'status'=>$request->status,
		);
        $rules = array(
			'lead_id' => 'required',
			'user_id' => 'required'
		);
		
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
				$LeadAssignStatusDetail = LeadAssignStatus::where('lead_id', $request->lead_id)
				->where('user_id', $request->user_id)->first();
				$leadDetail = Lead::where('id', $request->lead_id)->first();
				if($LeadAssignStatusDetail && $leadDetail){
					if($leadDetail->sales_person_id=="" || $leadDetail->sales_person_id==0){
						$statusReceptLead=0;
						if($request->status){
							$statusReceptLead=$request->status;
						}
						$leadAssignStatusUpdate = LeadAssignStatus::find($LeadAssignStatusDetail->id);
						$leadAssignStatusUpdate->status = $statusReceptLead;
						$leadAssignStatusUpdate->save();
						//Check user
						$user=User::where('id',$request->user_id)->first();
						 //$user->first_name." ".$user->last_name;
						// Add to log
						$fullname=$user->first_name." ".$user->last_name;
						if($user){
							if($statusReceptLead==1){
								$logscontent="@".trim($fullname)." ???? ch??m s??c lead @".$leadDetail->opportunity;
								NextTimeFollow::addUpdate($request->lead_id, date("Y-m-d H:i:s"), 1);
							}else{
								$logscontent="@".trim($fullname)." ???? b??? qua lead @".$leadDetail->opportunity;
							}
						}
						$dataLogs = array(
							'user_id' => $request->user_id,
							'logs'=>$logscontent,
							'created_at'=> date("Y-m-d H:i:s"),
							'phone'=>$leadDetail->phone,
							'lead_id'=>$request->lead_id,
						);
						logs::insert($dataLogs);
						// end add
						//Update lead
                        if($statusReceptLead==1){
                            $lead = Lead::find($request->lead_id);
                            $lead->sales_person_id = $request->user_id;
                            $lead->save();
                        }
                        
						//End update
						return response()->json(['success' => 'success'], 200);
					}else{	
						$leadAssignStatusUpdate = LeadAssignStatus::find($LeadAssignStatusDetail->id);
						if(isset($request->status)){
							$status=$request->status;
						}else{
							$status=0;
						}
						$leadAssignStatusUpdate->status = $status;
						$leadAssignStatusUpdate->save();
						return response()->json(['success' => 'update_seccess'], 200);
					}
				}else{
					return response()->json(['error' => 'not_looking_for_leadID'], 500);
				}
				
        } else {
            return response()->json(['error' => 'not_valid_data_null'], 500);
        }
	}
	//Update setting time
	//Contacts list setting_time
	public function userSettingTime(Request $request)
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
		$userSettingTime = UserSettingTime::where('user_id', $this->user->id)
			->get()
			->map(function ($settingTime) {
				return [
					'id' => $settingTime->id,
					'user_id' => $settingTime->user_id,
					'time_start' => $settingTime->time_start,
					'time_end' => $settingTime->time_end,
					'status' => $settingTime->status,
				];
			});

		return response()->json(['userSettingTime' => $userSettingTime], 200);
	   }else{
		   return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
	   }
	}
	//Add setting time add_setting_time
	public function addSettingTime(Request $request)
	{
		$this->user = JWTAuth::parseToken()->authenticate();
		$data = array(
		   'user_id' => $this->user->id,
		   'time_start'=>$request->input('time_start'),
		   'time_end'=> $request->input('time_end'),
		   'status'=>$request->input('status'),
		   'date_create'=> date("Y-m-d H:i:s"),
	   );
	   $rules = array(
		   'user_id' => 'required',
		   'status' => 'required',
	   );
	   $validator = Validator::make($data, $rules);
	   if ($validator->passes()) {
		   $id = UserSettingTime::insertGetId($data);
		   $dataShow = array(
			'id'=>$id,
			'user_id' => $this->user->id,
			'time_start'=>$request->time_start,
			'time_end'=> $request->time_end,
			'status'=>$request->status,
			'date_create'=> date("Y-m-d H:i:s"),
			);
		   return response()->json(['success' => 'success', 'userSettingTime' =>$dataShow], 200);
	   } else {
		   return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
	   }
	}
	//Delete contact
	//Contacts list
	public function updateTimeSetting(Request $request)
	{
		$this->user = JWTAuth::parseToken()->authenticate();
		$data = array(
		   'id'=>$request->input('id'),
		   'time_start'=>$request->input('time_start'),
		   'time_end'=> $request->input('time_end'),
		   'status'=>$request->input('status')
	   );
	   $rules = array(
		   'id' => 'required',
		   'status' => 'required',
	   );
	   $validator = Validator::make($data, $rules);
	   if ($validator->passes()) {
			$timeupdate = UserSettingTime::find($request->id);
			$timeupdate->time_start = $request->time_start;
			$timeupdate->time_end = $request->time_end;
			$timeupdate->status = $request->status;
			$timeupdate->save();
		   return response()->json(['success' => 'success'], 200);
	   } else {
		   return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
	   }
	}
	//Add Call logs
	public function addCallLog(Request $request)
	{
		$this->user = JWTAuth::parseToken()->authenticate();
		$data = array(
		   'user_id' => $this->user->id,
		   'lead_id'=>$request->input('lead_id'),
		   'date_call'=> date("Y-m-d H:i:s"),
	   );
	   $rules = array(
		   'user_id' => 'required',
		   'lead_id' => 'required',
	   );
	   $validator = Validator::make($data, $rules);
	   if ($validator->passes()) {
			$leadDetail = Lead::where("id",$request->lead_id)->where("sales_person_id",$this->user->id)->first();
			if($leadDetail ){
				$callLogAdd = CallLogs::insert($data);
				// Add to log
				$fullname=trim($this->user->first_name." ".$this->user->last_name);
				$dataLogs = array(
				 'user_id' => $this->user->id,
				 'phone' => $leadDetail->phone,
				 'logs'=>"@".$fullname." Click v??o g???i cho kh??ch h??ng @".$leadDetail->opportunity,
				 'created_at'=> date("Y-m-d H:i:s"),
				 'lead_id'=>$request->lead_id,
				 );
				Logs::insert($dataLogs);
				// end add
				return response()->json(['success' => 'success'], 200);
			}else{
				return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
			}
		  
	   } else {
		   return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
	   }
	}
	// show log action show_logs
	public function logs()
    {
		$this->user = JWTAuth::parseToken()->authenticate();
		$data = array(
			'user_id' => $this->user->id,
		);
		$rules = array(
			'user_id' => 'required',
		);
		$validator = Validator::make($data, $rules);
	    if ($validator->passes()) {
        $logs = Logs::where("user_id", $this->user->id)
			->orderBy('id', 'desc')->limit(50)->get()
			->map(function ($logs) {
                return [
                    'id' => $logs->id,
                    'logs' => $logs->logs,
                    'created_at' => $logs->created_at
                ];
            });
			return response()->json(['logs' => $logs], 200);
	   }else{
			return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
	   }
	}
	// show log action show_logs_phone_call
	public function logsPhoneCall(Request $request)
    {
		//$this->user = JWTAuth::parseToken()->authenticate();
		$device=$request->uuid;
		$user_id="";
		if($device){
			$tokencheck = DeviceToken::where('uuid',$device)->where('status',1)->first();
			if($tokencheck){
				$user_id=$tokencheck["user_id"];
			}

		}
		$data = array(
			'user_id' => $user_id,
			'phone' =>$request->phone,
		);
		$rules = array(
			'user_id' => 'required',
			'phone'=>'required'
		);
		$phone=$request->phone;
		$validator = Validator::make($data, $rules);
	    if ($validator->passes()) {
		$leads = Lead::where('user_id', $user_id)->where('phone', $phone)->orderBy('updated_at', 'desc')->first();
		$leadData=null;
		if($leads){
			$leadData=array("id"=>$leads["id"], "logs"=>$leads["product_name"], "created_at"=>date("Y-m-d H:i",strtotime($leads["updated_at"])));
		}
        $logs = Logs::where("user_id", $user_id)->where("phone", $phone)->groupBy('logs.logs')
			->orderBy('id', 'desc')->limit(4)->get()
			->map(function ($logs) {
                return [
                    'id' => $logs->id,
                    'logs' => $logs->logs,
                    'created_at' => date("Y-m-d H:i",strtotime($logs->created_at))
                ];
			});
			return response()->json(['logs' => $logs, 'leadcare' => [$leadData]], 200);
	   }else{
			return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
	   }
	}

	// show log action show_logs_phone_call
	public function logsPhoneCall_bk2(Request $request)
    {
		
		$this->user = JWTAuth::parseToken()->authenticate();
		$data = array(
			'user_id' => $this->user->id,
			'phone' =>$request->phone,
		);
		$rules = array(
			'user_id' => 'required',
			'phone'=>'required'
		);
		$phone=$request->phone;
		$validator = Validator::make($data, $rules);
	    if ($validator->passes()) {
			$logs = Logs::select('logs.*', DB::raw('COUNT(logs.id) as total_id'))->where('logs.user_id', $this->user->id)->where('logs.phone', $phone)->groupBy('logs.logs')->orderBy('logs.id', 'desc')->limit(10)->get();

			$leads = Lead::where('user_id', $this->user->id)->where('phone', $phone)
			->orderBy('id', 'desc')->first();
			$dataShow="";
			if($leads){
				$dataShow.="<p>Kh??ch h??ng:<strong> ".$leads["opportunity"]."</strong> ??ang g???i ?????n</p>";
			}
			$dataShow.="<p><strong> L???ch s??? l??m vi???c</strong> </p>";
            if($logs){
                foreach($logs as $logsData){
					$dataShow.="<p>- ".$logsData->logs."</p>";
                 }
            }
			echo $dataShow;
			die();
	   }else{
			return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
	   }
	}
	

	//Add Call logs
	public function logsPhoneCallInsert(Request $request)
	{
		$device=$request->uuid;
		$user_id="";
		if($device){
			$tokencheck = DeviceToken::where('uuid',$device)->where('status',1)->first();
			if($tokencheck){
				$user_id=$tokencheck["user_id"];
				if($user_id){
					$user = User::find( $user_id);
					$partid=$user["partner_id"];
				}
			}
		}else{
			$user = JWTAuth::parseToken()->authenticate();
			$user_id=$user->id;
			$partid=$user->partner_id;
		}
		$data = array(
		   'user_id' =>$user_id,
		   'phone' => $request->phone,
		   'token' => $request->token,
	   );
	   $rules = array(
		   'phone' => 'required',
		   'token' => 'required',
	   ); 
	   $validator = Validator::make($data, $rules);
	   if ($validator->passes()) {
		$leadCheck = Lead::where('phone', $request->phone)->where('user_id', '=', $user_id)->first();
		if(!$leadCheck){
			$lead = new Lead();   
			$lead->user_id = $user_id;
			$lead->sales_person_id = $user_id;
			$lead->phone = $request->phone;
			$lead->function =$request->function;
			$lead->partner_id=$partid;
			$lead->status =1;
			$lead->update_status=0;
			$lead->product_id=21;
			$lead->save();
			$lead_id = $lead->id;

			
		}else{
			$lead_id = $leadCheck->id;
		}
		$dataLogs = array(
			'user_id' => $user_id,
			'phone' => $request->phone,
			'token_id' => $request->token,
			'logs'=> $request->description,
			'created_at'=> date("Y-m-d H:i:s"),
			'lead_id'=>$lead_id,
			);
		   Logs::insert($dataLogs);
		   return response()->json(['success' => 'success', 'lead_id'=>$lead_id], 200);
	    } else {
		   return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
	    }
	} 

	// Login by token
	public function loginByToken(Request $request)
    {
		$data = array(
			'token' => $request->input('token'),
		);
		$rules = array(
			'token' => 'required',
		);
		$validator = Validator::make($data, $rules);
	    if ($validator->passes()) {
			$this->user = JWTAuth::parseToken()->authenticate();
			$token=$request->token;
			if($this->user){
				$users=$this->user;
				if ($users->inRole('admin')) {
					$role = 'admin';
				}
				elseif ($users->inRole('user')) {
					$role = 'user';
				}
				elseif ($users->inRole('staff')) {
					$role = 'staff';
				}
				elseif ($users->inRole('customer')) {
					$role = 'customer';
				}
				else{
					$role = 'no_role';
				}
				$permissions=User::find($this->user->id)->getPermissions();

				return response()->json(['token'=> $token,
									'user' => $this->user,
									'role' => $role,
									'date_format' => Settings::get('date_format'),
									'time_format' => Settings::get('time_format'),
									'date_time_format' => Settings::get('date_format').' '.Settings::get('time_format'),
									'permissions'=>$permissions], 200);
			}else{
				return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
			}
	   }else{
			return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
	   }
    }
	//Add logs call

	//Add Call logs
	public function addLogsCall(Request $request)
	{
		//$this->user = JWTAuth::parseToken()->authenticate(); device_token
		$partner=0;
		$device=$request->uuid;
		if($device=="" && isset($request->device_token)){
			$device=$request->device_token;
		}
		$user_id="";
		if($device){
			$tokencheck = DeviceToken::where('uuid',$device)->where('status',1)->first();
			if($tokencheck){
				$user_id=$tokencheck["user_id"];
				if($user_id){
					$this->user = User::find( $user_id);
				}
			}
		}else{
			$this->user = JWTAuth::parseToken()->authenticate();
			$user_id=$this->user->id;
			
		}
		if($this->user->partner_id){
			$partner=$this->user->partner_id;
		}
		$phone=$request->phone;
		$data = array(
			'user_id' => $user_id,
		);
		$rules = array(
			'user_id' => 'required',
		); 
	   $validator = Validator::make($data, $rules);
	   if ($validator->passes()) {
				$leadId=0;
				
				
				if(isset($request->lead_id) && $request->lead_id!=""){
					$leadId=$request->lead_id;
					$leads = Lead::where('id', $leadId)->first();
					$fullname=$leads["opportunity"];
				}elseif($phone!=""){
				// Check Lead exist and insert 
					$leads = Lead::where('user_id', $this->user->id)->where('phone', $phone)->first();
					if($request->demo==1){
						var_dump($leads);

					}
					// Add lead
					if(!$leads || $leads==""){
						$lead = new Lead;
						$fullname=$request->fullname;

						if($fullname==""){
							$contact = Contacts::where('user_id', $this->user->id)->where('phone', $phone)->first();
							if($contact){
								$fullname=$contact["name"];
							}else{
								$fullname="Ch??a c???p nh???t";
							}
						}
						$lead->opportunity =$fullname;
						$lead->contact_name =$fullname;
						$lead->phone =$phone;
						$lead->function="Call Logs";
						$lead->status=1;
						$lead->product_id=0;
						$lead->partner_id=$partner;
						$lead->user_id =$this->user->id;
						$lead->sales_person_id =$this->user->id;
						$lead->token =$device;
						$lead->save();

						$leadId=$lead->id;
					}else{

						$leadId=$leads["id"];
						$fullname=$leads["opportunity"];
					}

				}
				// Add to log device_token
				$phone_type="Android";
				if(isset($request->phone_type) && $request->phone_type!=""){
					$phone_type=$request->phone_type;
				}
				$statuscall=$request->status;
				$filerecord ="";
				if (!is_null($request->file_record) && in_array($statuscall, array(1,2))) {
					//$phone=$request->phone;
					$extention=".m4a";
					if(isset($request->file_record_extention) && $request->file_record_extention!=""){
						$extention=".".$request->file_record_extention;
					}
					$output_file = "RecordAudio-".$phone."-".time().$extention;
					$date=date("Y-m-d");
					$folder="";
					$link_file="";
					if(!is_dir(public_path() . '/uploads/media/'.$partner)){
						$folder=public_path() . '/uploads/media/'.$partner;
						@mkdir($folder, 0777);
					}
					if(!is_dir(public_path() . '/uploads/media/'.$partner.'/'.$date)){
						$folder=public_path() . '/uploads/media/'.$partner.'/'.$date;
						@mkdir($folder, 0777);
					}
					$folder=public_path() . '/uploads/media/'.$partner.'/'.$date;
					$link_file= 'uploads/media/'.$partner.'/'.$date;
					if(is_dir($folder)){
						$ifp = fopen($folder . '/' . $output_file, "wb");
						//$ifp = fopen(public_path() . '/uploads/media/' . $output_file, "wb");
						//$ifp = fopen(public_path() . '/uploads/media/' . $output_file, "wb");
						@fwrite($ifp, base64_decode($request->file_record));
						@fclose($ifp);
						$filerecord=url($link_file.'/' . $output_file);
					}
					
					//return response()->json(['success' => 1, 'link'=>$filerecord], 200);
				}
				$dataLogs = array(
				 'user_id' => $this->user->id,
				 'device_id' => $device,
				 'phone'=>$phone,
				 'lead_id'=>$leadId,
				 'end_time'=>$request->end_time,
				 'start_time'=>$request->start_time,
				 'phone_type'=>$phone_type,
				 'status'=>$statuscall, //1 in, 2 out, 3 missing
				 'date_create'=> date("Y-m-d H:i:s"),
				 'file_record'=>$filerecord
				 );
				 LogsCall::insert($dataLogs);
				 // Add to log
				 switch ($statuscall) {
					case 1:
						$call_summary="Kh??ch g???i l???i";
						break;
					case 2:
						$call_summary="G???i cho kh??ch";
						break;
					case 3:
						$call_summary="Kh??ng b???t m??y";
						break;
					default:
						$call_summary="Kh??ch h??ng g???i";
				}
				$to_time = strtotime($request->end_time);
				$from_time = strtotime($request->start_time);
				$duration=round(abs($to_time - $from_time),2);
				// end add
				// Khach hang goi
				$dataLogs2 = array(
					'user_id' => $this->user->id,
					'phone' => $phone,
					'token_id' => $request->device_token,
					'logs'=> $call_summary,
					'created_at'=> date("Y-m-d H:i:s"),
					'lead_id'=>$leadId,
				);
				Logs::insert($dataLogs2);
				$dataCall = array(
					'user_id' => $request->user_id,
					'resp_staff_id'=> $request->user_id,
					'company_id'=>0,
					'duration'=>$duration,
					'call_summary'=>$call_summary, //1 in, 2 out, 3 missing
					'date'=> date("Y-m-d"),
					'created_at'=>date("Y-m-d H:i:s")
				);
				Call::insert($dataCall);
				//$lead->calls()->create($dataCall, ['user_id' => $request->user_id]);
				if($leadId>0){
					$dataCallHistory = array(
						'function_id'=> $leadId,
						'function_type'=>'leads',
						'status'=>$statuscall,
						'logs'=>$call_summary, //1 in, 2 out, 3 missing
						'date_create'=>date("Y-m-d H:i:s")
					);
					History::insert($dataCallHistory);
				}

				if($request->demo==1){
					var_dump($leadId);
					die();
					}
				return response()->json(['success' => 'success'], 200);
	   } else {
		   return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
	   }
	} 
	//
	//Add Call logs
	public function addLogsCallCenter(Request $request)
		{
			//$this->user = JWTAuth::parseToken()->authenticate(); device_token
			$partner=0;
			$partner_id=$request->partner_id;
			$phone=$request->phone;
			$statuscall=$request->status;
			$file_record=$request->file_record;
			$start_time=$request->start_time;
			$end_time=$request->end_time;
			$extention=$request->extention;

			$data = array(
				'partner_id' => $partner_id,
				'phone' => $phone,
			);
			$rules = array(
				'partner_id' => 'required',
				'phone' => 'required',
				
			); 
		   $validator = Validator::make($data, $rules);
		   if ($validator->passes()) {
					$leadId=0;
					if($phone!=""){
					// Check Lead exist and insert 
						$leads = Lead::where('partner_id', $partner_id)->where('phone', $phone)->first();
						if($request->demo==1){
							var_dump($leads);
	
						}
						$user_id=0;
						// Add lead
						if(!$leads || $leads==""){
							$lead = new Lead;
							$fullname=$phone;
							$lead->opportunity =$fullname;
							$lead->contact_name =$fullname;
							$lead->phone =$phone;
							$lead->function="Call Logs";
							$lead->status=1;
							$lead->product_id=0;
							$lead->partner_id=$partner;
							$lead->user_id =0;
							$lead->sales_person_id =0;
							$lead->token =$device;
							$lead->extention_code=$extention;
							$lead->save();
							$leadId=$lead->id;

							// end add
							$listUser=User::select('id')->where('partner_id',$partner_id)->orderByRaw('RAND()')->first();
							$url="https://fastercrm.com/lead/".$leadId."/edit";
							$notification = array(
								'partner_id'=>$partner_id,
								'user_id' => $listUser["id"],
								'url'=> $url,
								'title'=>"Kh??ch h??ng m???i t??? hotline",
								'desc'=>"Vui l??ng c???p nh???t cho KH s??? ??i???n tho???i ".$phone."",
								'status'=>0, //1 in, 2 out, 3 missing
								'created_at'=> date("Y-m-d H:i:s")
							);
							Notification::insert($notification);


						}else{
							$leadId=$leads["id"];
							if($leads["sales_person_id"]>0){
								$user_id=$leads["sales_person_id"];
							}else{
								$user_id=$leads["user_id"];
							}
							$fullname=$leads["opportunity"];
						}
						
					}
					// Add to log device_token
					$phone_type="CallCenter";	
					$filerecord =$file_record;		
					$dataLogs = array(
					 'user_id' => $user_id,
					 'device_id' => "CallCenter",
					 'phone'=>$phone,
					 'lead_id'=>$leadId,
					 'end_time'=>$end_time,
					 'start_time'=>$start_time,
					 'phone_type'=>$phone_type, 
					 'status'=>$statuscall, //1 in, 2 out, 3 missing
					 'date_create'=> date("Y-m-d H:i:s"),
					 'file_record'=>$filerecord
					 );
					 LogsCall::insert($dataLogs);
					 // Add to log
					 switch ($statuscall) {
						case 1:
							$call_summary="Kh??ch g???i l???i";
							break;
						case 2:
							$call_summary="G???i cho kh??ch";
							break;
						case 3:
							$call_summary="Kh??ng b???t m??y";
							break;
						default:
							$call_summary="Kh??ch h??ng g???i";
					}
					$to_time = strtotime($end_time);
					$from_time = strtotime($start_time);
					$duration=round(abs($to_time - $from_time),2);
					// end add
					$dataCall = array(
						'user_id' => $user_id,
						'resp_staff_id'=> $user_id,
						'company_id'=>0,
						'duration'=>$duration,
						'call_summary'=>$call_summary, //1 in, 2 out, 3 missing
						'date'=> date("Y-m-d"),
						'created_at'=>date("Y-m-d H:i:s")
					);
					Call::insert($dataCall);
					//$lead->calls()->create($dataCall, ['user_id' => $request->user_id]);
					if($leadId>0){
						$dataCallHistory = array(
							'function_id'=> $leadId,
							'function_type'=>'leads',
							'status'=>$statuscall,
							'logs'=>$call_summary, //1 in, 2 out, 3 missing
							'date_create'=>date("Y-m-d H:i:s")
						);
						History::insert($dataCallHistory);
					}


					

					return response()->json(['success' => 'success'], 200);
		   } else {
			   return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
		   }
		} 
	/* post lead from website */
	public function postLeadNoLogin(Request $request)
    {
        $salesTeamID=0;

        $data = array(
            'data' => $request->input('data')
        );
        $rules = array(
            'data' => 'required',
		);
		$validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $status=0;
            $tags=$request->tags;
            if(!isset($request->tags) || $request->tags==""){
                $tags="";
            }
			$lead = new Lead;
			$websiteConfig=null;
			$dataExport=(array)json_decode($request->data);
			$fullname="";
			$email="";
			$phone="";
			foreach($dataExport as $key => $value){
				if($this->checkphone($value)){
					$phone=$this->checkphone($value);
				}
				if(@$this->validateEmail($value)){
					$email=$value;
				}
				$namekey=array("regis-name", "regis", "fullname_gui", "username", "user", "name", "ten", "fullname", "full-name", "full_name", "ho_ten", "hoten", "your-name", "your_name", "fullname_ft", "hovaten", "fullname_0", "fullname_1", "fullname_2", "fullname_3", "fullname_5", "fullname_4", "fullname_6");
				if(!@$this->validateEmail($value) && !$this->checkphone($value) && in_array(strtolower($key),$namekey)){
					$fullname=$value;
				}

			}
			$product_id=$request->product_id;
			$partner=Product::where('id','=',$product_id)->first();
			$partner_id=0;
			if($partner){
				$partner_id=$partner->partner_id;
			}
			if($phone){
				$countLead=Lead::where('phone',$phone)->where('product_id',$request->product_id)->count();
				if($countLead<=0){
					$lead->opportunity = $fullname;
					$lead->partner_id = $partner_id;
					$lead->email = $email;
					$lead->phone =  $phone;
					$lead->function=$request->utm_source;
					$lead->cookie_id=$request->cookie_id;
					$lead->status=0;
					$lead->user_id =0;
					$lead->sales_person_id =0;
					$lead->contact_name =$fullname;
					$lead->tags =$request->tags;
					$lead->sales_team_id =0;
					$lead->product_id =$request->product_id;
					$lead->UTM_Source =$request->utm_source;
					$lead->UTM_Campaign=$request->utm_campaign;
					$lead->UTM_Medium=$request->utm_medium;
					$lead->UTM_Term=$request->utm_term;
					$lead->UTM_Content=$request->utm_content;
					$lead->URL=$request->url;
					$lead->PID=$request->PID;
					$lead->GCLID=$request->GCLID;
					$lead->FBCLID=$request->FBCLID;
					$lead->token=$request->Token;
					$lead->save();
					return response()->json(['success' => 'success'], 200);
				}else{
					return response()->json(['success' => 'Exit'], 200);
				}
				
			}else{
				return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
			}

        } else {
            return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
        }
	}

	/* post lead from api by javascript */
	public function postLeadApi(Request $request)
    {
        $salesTeamID=0;
		$title=$phone=$email=$internal_notes=$cookie_id=$client_name=$utm_campaign=$utm_medium=$utm_term=$utm_content=$url=$PID=$GCLID=$FBCLID=$token=$page_id=$fullname=$UTM_Source=$function=$tags=$statusorder=$psid=$page_id="";
		$product_id =0;
		$partner_id=19;
		$page_id=0;
		$status=0;
		$group=0;
		if(isset($_POST) && $_POST!=""){
			$data=$_POST;

			if(isset($data) && $data!=""){
				if(isset($data) && $data!=""){
					if(isset($data["fields"]["partner_id"]) && $data["fields"]["partner_id"]["value"]!=""){
						$partner_id=$data["fields"]["partner_id"]["value"];
					}
					if(isset($data["fields"]["name"]) && $data["fields"]["name"]["value"]!=""){
						$fullname=$data["fields"]["name"]["value"];
					}
					if(isset($data["fields"]["email"]) && $data["fields"]["email"]["value"]!=""){
						$email=$data["fields"]["email"]["value"];
					}
					if(isset($data["fields"]["phone"]) && $data["fields"]["phone"]["value"]!=""){
						$phone=$data["fields"]["phone"]["value"];
					}
					if(isset($data["fields"]["fields"]["url"]) && $data["fields"]["url"]["title"]!=""){
						$utm_content=$data["fields"]["url"]["title"];
					}
				}
				//@file_put_contents("/home/crmsmart/web/fasterich.com/public_html/smartweb/public/lead_log/".time().".json", json_encode($data));
			}
			$utm_source="webhook";
			
		}
		if(isset($request) && $request!="" && $phone=="" && $email==""){
			$phone=$request->input('phone');
			$email=$request->input('email');
			$psid=$request->input('psid');
			$tags=$request->tags;
			if(isset($request->psid)){
                $psid=$request->psid;
			}
			if(isset($request->title)){
                $title=$request->title;
			}
			if(isset($request->information)){
                $internal_notes=$request->information;
			}
			if(isset($request->company)){
                $client_name=$request->company;
			}
			$fullname=$request->fullname;
			$email=$request->email;

			if($request->utm_source!=""){
				$function=$request->utm_source;
			}
			if($request->cookie_id!=""){
				$cookie_id=$request->cookie_id;
			}
			if(isset($request->callId) && $request->callId!=""){
				$utm_source ="Hotline";
				$function="Hotline";
			}else{
				$utm_source =$request->utm_source;
			}
			$utm_campaign=$request->utm_campaign;
			$utm_medium=$request->utm_medium;
			$utm_term=$request->utm_term;
			$utm_content=$request->utm_content;
			$url=$request->url;
			$PID=$request->PID;
			$GCLID=$request->GCLID;
			$FBCLID=$request->FBCLID;
			$token=$request->Token;
			$page_id=$request->page_id;
			$product_id=$request->product_id;
			$partner_id=$request->partner_id;
			//$group=$request->group;
			if(isset($request->group) && $request->group!=""){
				$group=$request->group;
			}
			if(isset($request->status) && $request->status!=""){
				$status=$request->status;
			}
		}
		//$validator = Validator::make($data, $rules);
        if ($phone!="" or $email!="") {
				if(!isset($tags) || $tags==""){
					$tags="";
				}
				$websiteConfig=null;
				
				if($phone!=""){
					$phone=trim($phone);
					$phone=$this->checkphone($phone);
				}else{
					$phone="";
				}

				if(($partner_id=="" || $partner_id==0) && $product_id!=""){
					$partner=Product::where('id','=',$product_id)->first();
					if($partner){
						$partner_id=$partner->partner_id;
					}
				}
				if($page_id!="" && $page_id!=0 && ($partner_id=="" || $partner_id==0)){
					$configpage=Getdata::where('page_id','=',$page_id)->first();
					if($configpage){
						$partner_id=$configpage->partner_id;
					}
				} 
				if($psid!=""){
					$leadps=Lead::where('psid',$psid)->where('partner_id',$partner_id)->first();
				}
				if(($phone!="" || $email!="") && $partner_id!=""){

					$lead = new Lead;
					$countLead=Lead::where(function ($query) use($phone, $email){
						if($phone!=""){
							$query->where('phone', $phone);
						}
						if($email!="" && $phone!=""){
							$query->orWhere('email', $email);
						}elseif($email!=""){
							$query->where('email', $email);
						}
					})->where('partner_id',$partner_id)->first();	
					
					if(!isset($countLead) || $countLead==""){
						if(isset($leadps) && $leadps!="" && $leadps!=null){
							$leadUpdate=array();
							$lead = $this->leadRepository->find($leadps->id);
							$leadUpdate["lead_action"]=date("Y-m-d H:i:s");
							if($fullname!=""){
								$leadUpdate["opportunity"]=$fullname;
							}
							if($title!=""){
								$leadUpdate["title"]=$title;
							}
							$leadUpdate["process_action"]=0;
							$leadUpdate["page_id"]=$page_id;
							$leadUpdate["status"]=$status;
							$leadUpdate["group_id"]=$group;
							$leadUpdate["email"]=$email;
							$leadUpdate["phone"]=$phone;
							if($utm_source!=""){
								$leadUpdate["UTM_Source"]=$utm_source;
							}
							$leadUpdate["lead_action"]=date("Y-m-d H:i:s");
							$result=$lead->update( $leadUpdate);
							$dataLogs = array(
								'user_id' => $leadps->user_id,
								'phone' => $phone,
								'token_id' => "",
								'logs'=> "C???p nh???t ?????ng b??? s??? ??i???n thoai ".$phone." v??o ID kh??ch h??ng ".$leadps->id,
								'created_at'=> date("Y-m-d H:i:s"),
								'lead_id'=>$leadps->id,
							);
							Logs::insert($dataLogs);
						}else{
							$lead->opportunity = $fullname;
							$lead->partner_id = $partner_id;
							$lead->title = $title;
							$lead->email = $email;
							$lead->phone =  $phone;
							$lead->psid =  $psid;
							$lead->page_id =  $page_id;
							$lead->function=$function;
							$lead->cookie_id=$cookie_id;
							$lead->UTM_Source =$utm_source;
							$lead->status=$status;
							$lead->group_id=$group;
							$lead->internal_notes=$internal_notes;
							$lead->client_name=$client_name;
							$lead->user_id =0;
							$lead->sales_person_id =0;
							$lead->contact_name =$fullname;
							$lead->tags =$tags;
							$lead->sales_team_id =0;
							$lead->product_id =$product_id;
							$lead->UTM_Campaign=$utm_campaign;
							$lead->UTM_Medium=$utm_medium;
							$lead->UTM_Term=$utm_term;
							$lead->UTM_Content=$utm_content;
							$lead->URL=$url;
							$lead->PID=$PID;
							$lead->GCLID=$GCLID;
							$lead->FBCLID=$FBCLID;
							$lead->token=$token;
							$lead->lead_action=date("Y-m-d H:i:s");
							$lead->save();
							$leadId=$lead->id;

							// end add
							$listUser=User::select('id')->where('partner_id',$partner_id)->orderByRaw('RAND()')->first();
							$url="https://fastercrm.com/lead/".$leadId."/edit";
							$notification = array(
								'partner_id'=>$partner_id,
								'user_id' => $listUser["id"],
								'url'=> $url,
								'title'=>"Kh??ch h??ng m???i",
								'desc'=>"Vui l??ng c???p nh???t cho KH s??? ??i???n tho???i ".$phone."",
								'status'=>0, 
								'created_at'=> date("Y-m-d H:i:s"),
								'date_notification'=>time()
							);
							Notification::insert($notification);
						}
						
						return response()->json(['success' => 'success'], 200);
					}else{
						$leadUpdate=array();
						$lead = $this->leadRepository->find($countLead->id);
						$leadUpdate["lead_action"]=date("Y-m-d H:i:s");
						$leadUpdate["process_action"]=0;
						$leadUpdate["psid"]=$psid;
						$leadUpdate["page_id"]=$page_id;
						$leadUpdate["status"]=$status;
						$leadUpdate["group_id"]=$group;
						if($utm_source!=""){
							$leadUpdate["UTM_Source"]=$utm_source;
						}
						$lead->update( $leadUpdate);
						$dataLogs = array(
							'user_id' => $countLead->user_id,
							'phone' => $phone,
							'token_id' => "",
							'logs'=> "C???p nh???t lead t??? h??? th???ng t??? ngu???n ".$utm_source,
							'created_at'=> date("Y-m-d H:i:s"),
							'lead_id'=>$countLead->id,
						);
						Logs::insert($dataLogs);
						return response()->json(['success' => 'Exit'], 200);
					}
				}
				if(isset($leadps) && $leadps!="" && $phone!=""){
					$mapleadAdd=array("lead_id"=>$leadps["id"], "phone"=>$phone, "psid"=>$psid);
					Leadmap::insert($mapleadAdd);
					$dataLogs = array(
						'user_id' => $countLead->user_id,
						'phone' => $phone,
						'token_id' => "",
						'logs'=> "H??? th???ng ph??t hi???n tr??ng kh??ch h??ng ID:".$utm_source,
						'created_at'=> date("Y-m-d H:i:s"),
						'lead_id'=>$leadps["id"],
					);
					Logs::insert($dataLogs);
				}

        } else {
            return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
        }
	}
	/* post lead from api by javascript */
	public function postOrder(Request $request)
    {
        $salesTeamID=0;
		$title=$phone=$email=$internal_notes=$cookie_id=$client_name=$utm_campaign=$utm_medium=$utm_term=$utm_content=$url=$PID=$GCLID=$FBCLID=$token=$page_id=$fullname=$UTM_Source=$function=$tags=$statusorder="";
		$product_id =0;
		$partner_id=19;
		$status=0;
		$group=0;
		if(isset($request) && $request!="" && $phone=="" && $email==""){
			$phone=$request->input('phone');
			$email=$request->input('email');
			$tags=$request->tags;
			if(isset($request->title)){
                $title=$request->title;
			}
			if(isset($request->information)){
                $internal_notes=$request->information;
			}
			if(isset($request->company)){
                $client_name=$request->company;
			}
			$fullname=$request->fullname;
			$email=$request->email;

			if($request->utm_source!=""){
				$function=$request->utm_source;
				$utm_source =$request->utm_source;
			}
			if($request->cookie_id!=""){
				$cookie_id=$request->cookie_id;
			}
			$utm_campaign=$request->utm_campaign;
			$utm_medium=$request->utm_medium;
			$utm_term=$request->utm_term;
			$utm_content=$request->utm_content;
			$url=$request->url;
			$PID=$request->PID;
			$GCLID=$request->GCLID;
			$FBCLID=$request->FBCLID;
			$token=$request->Token;
			$page_id=$request->page_id;
			$product_id=$request->product_id;
			$partner_id=$request->partner_id;
			$group=$request->group;
			$status=$request->status;
			$statusorder=$request->statusorder;
			$product=$request->product;
			$detailInvoice=$request->orderdetail;

			if($product!=""){
				$productlist=json_decode($product);
				if(count($productlist)>0){
					for($i=0;$i<count($productlist);$i++){
						$orderdetailData=$productlist[$i];
						$productCode=$orderdetailData->productCode;
						$productname=$orderdetailData->productName;
						$price=$orderdetailData->price;
						$productCheck=Product::where('sku',$productCode)->where('partner_id',$partner_id)->first();
						if(!isset($productCheck) || $productCheck!=""){
							$productadd=array('sku'=>$productCode, 'partner_id'=>$partner_id, 'product_name'=>$productname, 'product_type'=>'Product','status'=>'C?? s???n','quantity_on_hand'=>10, 'quantity_available'=>10,'sale_price'=>$price);
							Product::insert($productadd);
						}
					}
				}
			}
			
		}
		//$validator = Validator::make($data, $rules);
        if ($phone!="" or $email!="") {
            
            if(!isset($tags) || $tags==""){
                $tags="";
			}
			$websiteConfig=null;
			
			if($phone!=""){
				$phone=trim($phone);
				$phone=$this->checkphone($phone);
			}else{
				$phone="";
			}
			if(($phone!="" || $email!="") && $partner_id!=""){

				$lead = new Lead;
				$countLead=Lead::where(function ($query) use($phone, $email){
					if($phone!=""){
						$query->where('phone', $phone);
					}
					if($email!="" && $phone!=""){
						$query->orWhere('email', $email);
					}elseif($email!=""){
						$query->where('email', $email);
					}
				})->where('partner_id',$partner_id)->first();
				
				if(!isset($countLead) || $countLead==""){

					$lead->opportunity = $fullname;
					$lead->partner_id = $partner_id;
					$lead->title = $title;
					$lead->email = $email;
					$lead->phone =  $phone;
					$lead->function=$function;
					$lead->cookie_id=$cookie_id;
					$lead->UTM_Source =$utm_source;
					$lead->status=$status;
					$lead->group_id=$group;
					$lead->internal_notes=$internal_notes;
					$lead->client_name=$client_name;
					$lead->user_id =0;
					$lead->sales_person_id =0;
					$lead->contact_name =$fullname;
					$lead->tags =$tags;
					$lead->sales_team_id =0;
					$lead->product_id =$product_id;
					$lead->UTM_Campaign=$utm_campaign;
					$lead->UTM_Medium=$utm_medium;
					$lead->UTM_Term=$utm_term;
					$lead->UTM_Content=$utm_content;
					$lead->URL=$url;
					$lead->PID=$PID;
					$lead->GCLID=$GCLID;
					$lead->FBCLID=$FBCLID;
					$lead->token=$token;
					$lead->lead_action=date("Y-m-d H:i:s");
					$lead->save();
					$leadId=$lead->id;
					//return response()->json(['success' => 'success'], 200);
				}else{
					$leadId=$countLead->id;
				}
				// end add

				$status=0;
				$checkStatusOrder=CallActionStatus::where('title',$statusorder)->where('partner_id',$partner_id)->where('type',2)->first();
				if(isset($checkStatusOrder) && $checkStatusOrder!=""){
					$status=$checkStatusOrder["id"];
				}else{
					$callActionStatus = new CallActionStatus;
					$callActionStatus->title = $statusorder;
					$callActionStatus->partner_id = $partner_id;
					$callActionStatus->type = 2;
					$callActionStatus->status = 1;
					$callActionStatus->save();
					$status=$callActionStatus->id;
				}

				$per_id=0;
				$listUser=User::select('id')->where('partner_id',$partner_id)->orderByRaw('RAND()')->first();
				$per_id=$listUser["id"];
				$detailInvoice=json_decode($detailInvoice);
				$Saleorder = new Saleorder();   
				$Saleorder->partner_id = $partner_id;
				$Saleorder->sale_number = $detailInvoice->code;
				$Saleorder->customer_id =0;
				$Saleorder->status_client =$status;
				
				$Saleorder->lead_id=$leadId;
				$Saleorder->date_ship=date("Y-m-d H:i:s",strtotime($detailInvoice->purchaseDate));
				$Saleorder->date_exp=date("Y-m-d H:i:s",strtotime("+1 day"));
				$Saleorder->shipping_term="";
			//	$Saleorder->shipping_term_id=25;
				$chi_nhanh=$detailInvoice->branchName;
				$brand_id=0;
				if($chi_nhanh!=""){
					$brand=Brand::where('partner_id',$partner_id)->where('name',$chi_nhanh)->first();
					$brand_id=$brand["id"];
				}
				$Saleorder->branch_id=$brand_id;
				$Saleorder->terms_and_conditions="";
				$Saleorder->status=1;
				$Saleorder->total=$detailInvoice->total;
				$Saleorder->tax_amount=0; 
				$Saleorder->grand_total=$detailInvoice->total;
				$Saleorder->discount=$detailInvoice->discount;
				$Saleorder->final_price=$detailInvoice->total;
				$Saleorder->status_order=$detailInvoice->statusValue; 
				if(isset($detailInvoice->note) && $detailInvoice->note!=""){
				$Saleorder->terms_and_conditions=$detailInvoice->note; 
				}
				$Saleorder->user_id=$per_id;
				$Saleorder->sales_person_id=$per_id;
				$Saleorder->save();
				$saleorder_id = $Saleorder->id;
				$productSalesOrder=null;
				$product_user="";
				if($product!=""){
					$productlist=json_decode($product);
					if(count($productlist)>0){
						for($i=0;$i<count($productlist);$i++){
							$orderdetailData=$productlist[$i];
							$productCode=$orderdetailData->productCode;
							$productname=$orderdetailData->productName;
							$quantity=$orderdetailData->quantity;
							$price=$orderdetailData->price;
							$sub_total=$price*$quantity;
							$productCheck=Product::where('sku',$productCode)->where('partner_id',$partner_id)->first();
							$productSalesOrder[]=array('saleorder_id'=>$saleorder_id, 'product_id'=>$productCheck["id"], 'product_name'=>$productname,'quantity'=>$quantity,'price'=>$price, 'taxes'=>0, 'sub_total'=>$sub_total);
							$product_user=$productCheck["id"];
						}
					}
				}
				if($productSalesOrder!="" && count($productSalesOrder)>0){
					SaleorderProduct::insert($productSalesOrder);
				}
			
				$leadUpdate=array();
				$lead = $this->leadRepository->find($leadId);
				$leadUpdate["lead_action"]=date("Y-m-d H:i:s");
				$leadUpdate["process_action"]=0;
				$leadUpdate["status"]=$status;
				$leadUpdate["group_id"]=$group;
				$leadUpdate["product_id"]=$product_user;
				$leadUpdate["product_name"]=$productname;
				if($utm_source!=""){
					$leadUpdate["UTM_Source"]=$utm_source;
				}
				$lead->update( $leadUpdate);

				$url="https://fastercrm.com/sales_order/".$saleorder_id."/edit";
				$notification = array(
					'partner_id'=>$partner_id,
					'user_id' => $listUser["id"],
					'url'=> $url,
					'title'=>"Kh??ch ?????t h??ng m???i",
					'desc'=>"Vui l??ng c???p nh???t t??nh tr???ng ????n h??ng ",
					'status'=>0, //1 in, 2 out, 3 missing
					'created_at'=> date("Y-m-d H:i:s"),
					'date_notification'=>time()
				);
				Notification::insert($notification);
				/*
				$SaleorderProduct->saleorder_id=$saleorder_id;
				$SaleorderProduct->product_id=$product_id;
				$SaleorderProduct->product_name=$product_name;
				$SaleorderProduct->description=0;
				$SaleorderProduct->quantity=1;
				$SaleorderProduct->price=$price;
				$SaleorderProduct->taxes=0;
				$SaleorderProduct->sub_total=$price;
				$SaleorderProduct->save();
				*/
				return response()->json(['success' => 'Success'], 200);

			}else{
				

				return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
			}

        } else {
            return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
        }
	}
	/* post lead from api by javascript */
	public function postLeadApiList(Request $request)
    {
        $salesTeamID=0;
        $data = array(
			'data' => $request->input('data'),
			'product_id' => $request->input('product_id')		
        );
        $rules = array(
            'data' => 'required', 'product_id' => 'required',
		);
		$validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $status=0;
			$tags="";
			
			$websiteConfig=null;
			$data=explode("|<>|",$request->data);
			$product_id=$request->product_id;
			$partner=Product::where('id','=',$product_id)->first();
			$partner_id=0;
			if($partner){
				$partner_id=$partner->partner_id;
			}
		
			if($data){
				for($i=0;$i<count($data);$i++){
					$listData=$data[$i];
					$dataItem=explode("-|-",$listData);
					$fullname=$email=$phone=$additionl_info="";
					for($j=0;$j<count($dataItem);$j++){
						$dataItemSmall=$dataItem[$j];
						$dataItemSmallArr=explode("->",$dataItemSmall);
						if(in_array($dataItemSmallArr[0],array("email"))){
							$email=trim($dataItemSmallArr[1]);
						}
						if(in_array($dataItemSmallArr[0],array("s???_??i???n_tho???i","phone", "phone_number"))){
							$phone=trim($dataItemSmallArr[1]);
						}
						if(in_array($dataItemSmallArr[0],array("full_name","t??n_?????y_?????"))){
							$fullname=trim($dataItemSmallArr[1]);
						}
                        if(in_array($dataItemSmallArr[0],array("nhu_c???u:_","l???ch_tham_quan_d???_??n"))){
							$additionl_info=trim($dataItemSmallArr[1]);
						}
					}
					//Add data
					$phone=$this->checkphone($phone);

					if($phone && !in_array($phone,array("0961133113", "0987654321"))){
                    	$countLead=Lead::where('phone',$phone)->where('product_id',$product_id)->count();
						if($countLead<=0){
							$lead = new Lead;
							$lead->opportunity = $fullname;
							$lead->partner_id = $partner_id;
							$lead->email = $email;
							$lead->phone =  $phone;
							$lead->function=$request->utm_source;
							$lead->cookie_id="";
							$lead->status=0;
							$lead->user_id =0;
							$lead->sales_person_id =0;
							$lead->contact_name =$fullname;
							$lead->tags =$request->tags;
							$lead->sales_team_id =0;
							$lead->product_id =$request->product_id;
							$lead->UTM_Source =$request->utm_source;
							$lead->UTM_Campaign=$request->utm_campaign;
							$lead->token=$request->Token;
							$lead->additionl_info=$additionl_info;
							$lead->save();
							$leadId=$lead->id;
							$listUser=User::select('id')->where('partner_id',$partner_id)->orderByRaw('RAND()')->first();
							$url="https://fastercrm.com/lead/".$leadId."/edit";
							$notification = array(
								'partner_id'=>$partner_id,
								'user_id' => $listUser["id"],
								'url'=> $url,
								'title'=>"Kh??ch h??ng m???i t??? ".$request->utm_source,
								'desc'=>"Vui l??ng c???p nh???t cho KH s??? ??i???n tho???i ".$phone."",
								'status'=>0, //1 in, 2 out, 3 missing
								'created_at'=> date("Y-m-d H:i:s"),
								'date_notification'=>time()
							);
							Notification::insert($notification);
						}
					}

				}
			}

        } else {
            return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
        }
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
	public static function validateEmail($email)
	{

			$emailIsValid = FALSE;

			if (!empty($email))
			{
				// GET EMAIL PARTS

					$domain = ltrim(@stristr($email, '@'), '@') . '.';
					$user   = @stristr($email, '@', TRUE);

					if
					(
						!empty($user) &&
						!empty($domain) &&
						checkdnsrr($domain)
					)
					{$emailIsValid = TRUE;}
			}

			return $emailIsValid;
	}
	public function addCookie(Request $request){
		$data = array(
			'cookie_id' => $request->cookie_id,
			'cookie_site' => $request->cookie_site,
		);
		$rules = array(
			'cookie_id' => 'required',
			'cookie_site' => 'required',
		);
		$validator = Validator::make($data, $rules);
	   	if ($validator->passes()) {
			$cookie=$request->cookie_id;
			$url=urldecode($request->url);
			$url_refer=urldecode($request->url_refer);
			$browser=$request->browser;
			$system_type=$request->system_type;
			$utm_source=$request->utm_source;
			$utm_medium=$request->utm_medium;
			$utm_campaign=$request->utm_campaign;
			$utm_content=$request->utm_content;
			$meta_keyword=$request->meta_keyword;
			$meta_description=$request->meta_description;
			$user_agent=$request->user_agent;
			$meta_title=$request->meta_title;
			$cookie_site=$request->cookie_site;
			$ip_add=$request->ip_add;
			$product_id=$request->product_id;
			/*
			$dataCookie = array(
			'cookie_id' => $cookie,
			'url'=>urldecode($request->url),
			'url_refer'=>urldecode($request->url_refer),
			'browser'=>$request->browser,
			'system_type'=>$request->system_type,
			'create_date'=>date("Y-m-d H:i:s"),
			'utm_source'=>$request->utm_source,
			'utm_medium'=>$request->utm_medium, 
			'utm_campaign'=>$request->utm_campaign,
			'utm_content'=>$request->utm_content, 
			'meta_keyword'=>$request->meta_keyword,
			'meta_description'=>$request->meta_description, 
			'user_agent'=>$request->user_agent, 
			'meta_title'=>$request->meta_title, 
			'cookie_site'=>$request->cookie_site,
			'ip_add'=>$request->ip_add,
			'product_id'=>$request->product_id,
			); */
			$listInsert2=[$cookie, $url, $url_refer, $browser, $system_type, $utm_source, $utm_medium, $utm_campaign, $utm_content, $meta_keyword, $meta_description, $user_agent, $meta_title, $cookie_site, $ip_add, $product_id, date("Y-m-d H:i:s")];
			//$keyUpdate[]="`cookie_id`='".$phone."', `url`='".$url."'";
			DB::insert('insert into cookie(cookie_id, url, url_refer, browser, system_type, utm_source, utm_medium, utm_campaign, utm_content, meta_keyword, meta_description, user_agent, meta_title, cookie_site, ip_add, product_id, create_date, url_md5) values ("'.$cookie.'", "'.$url.'", "'.$url_refer.'", "'.$browser.'", "'.$system_type.'", "'.$utm_source.'", "'.$utm_medium.'", "'.$utm_campaign.'", "'.$utm_content.'", "'.$meta_keyword.'", "'.$meta_description.'", "'.$user_agent.'", "'.$meta_title.'", "'.$cookie_site.'", "'.$ip_add.'", "'.$product_id.'", "'.date("Y-m-d H:i:s").'", "'.md5($url).'") ON DUPLICATE KEY UPDATE number_view = number_view + 1;');

			//Cookie::insert($dataCookie);
		}
	}

	/* post lead from api by javascript */
	public function postLeadFacebook(Request $request)
    {
		$salesTeamID=0;
		$token = "EAAK481QSfNIBAGxELWjXcm6krDRHq3KaeFrOzO2T092T6F574TntiDdrSZCLhpdKscVIzwa0VUwfdv2O15m5tMEz36rMPuGn5vdZBbOBLwHEGtWSu97CT8uMhxbejZAdzEo6teZBrT5Cffi01RqNy2OugeiqKzy6q1OWlW7yb3yFclJjfhRXKIG7W8DTjMDQSShrtoXmmmyhpIRvo1JO";
		$challenge = $_REQUEST['hub.challenge'];
		$verify_token = $_REQUEST['hub.verify_token'];
		if ($verify_token === $token) {
		  echo $challenge;
		}
		$phone="";
		$request = file_get_contents('php://input');
        $data = array(
			'phone' => $phone,
			
        );
        $rules = array(
            'phone' => 'required',
		);
		$validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $status=0;
            $tags="";
			$lead = new Lead;
			$websiteConfig=null;
			$fullname=$request->fullname;
			$email=$request->email;
			$phone=$request->phone;
			$cookie = $request->cookie_id;
			$utm_source="leadFormFacebook";
			$product_id=13;
			//$partner=Product::where('id','=',$product_id)->first();
			$partner_id=8;
			if($phone!=""){
				$countLead=Lead::where('phone',$phone)->where('product_id',$product_id)->first();
				if($countLead=="" || !isset($countLead)){
					$lead->opportunity = $fullname;
					$lead->partner_id = $partner_id;
					$lead->email = $email;
					$lead->phone =  $phone;
					$lead->function=$utm_source;
					$lead->cookie_id=$cookie;
					$lead->status=0;
					$lead->user_id =0;
					$lead->sales_person_id =0;
					$lead->contact_name =$fullname;
					$lead->tags =$request->tags;
					$lead->sales_team_id =0;
					$lead->product_id =$product_id;
					$lead->UTM_Source =$utm_source;
					$lead->UTM_Campaign="";
					$lead->UTM_Medium="";
					$lead->UTM_Term="";
					$lead->UTM_Content="";
					$lead->URL="";
					$lead->PID="";
					$lead->GCLID="";
					$lead->FBCLID="";
					$lead->token=$token;
					$lead->save();
					return response()->json(['success' => 'success'], 200);
				}else{
					if($cookie!=""){
						Lead::where('id',$countLead["id"])->update(['cookie_id'=>$cookie]);
					}
					return response()->json(['success' => 'Exit'], 200);
				}
				
			}else{
				return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
			}

        } else {
            return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
        }
	}
	/* post lead from api by javascript */
	public function postLeadFacebookMessenger(Request $request)
	{
		$salesTeamID=0;
		$token = "";
		$psid = $request->psid;
		$page_id = $request->page_id;
		$cookie = $request->cookie_id;
		$source = $request->source;
		$url = $request->url;
		$data = array(
			'psid' => $psid,
			'page_id'=>$page_id
		);
		$rules = array(
			'psid' => 'required',
			'page_id' => 'required',
		);
		$validator = Validator::make($data, $rules);
		if ($validator->passes()) {
			$status=0;
			$tags="";
			$partner_id="";
			$status=0;
			$partner=Getdata::where('page_id','=',$page_id)->first();
			if($partner!=""){
				$partner_id=$partner["partner_id"];
				$token=$partner["token"];
				$status=$partner["client_status_id"];
			}
			if($token==""){
				exit();
			}
					$lead = new Lead;
					$websiteConfig=null;
					$utm_source="facebookMessenger";
					

					$countLead=Lead::where('psid',$psid)->where('partner_id',$partner_id)->first();
					if($countLead=="" || !isset($countLead)){
						$fullname="";
						$gender="";
						//$partner=Product::where('id','=',$product_id)->first();
						$contentFetch=@file_get_contents("https://graph.facebook.com/".$psid."?fields=first_name,last_name,profile_pic,gender&access_token=".$token);
						$profile=json_decode($contentFetch);
						if(isset($profile) && $profile!=""){
						$fullname=$profile->first_name." ".$profile->last_name;
						$gender=$profile->gender;
						}

						$needle = "HTTP request failed"; 
						$haystack =$contentFetch;
						if (strpos($haystack, $needle) !== true){
							$lead->opportunity = $fullname;
							$lead->partner_id = $partner_id;
							$lead->psid = $psid;
							$lead->gender=$gender;
							$lead->phone =  "";
							$lead->function=$utm_source;
							$lead->cookie_id=$cookie;
							$lead->status=$status;
							$lead->user_id =0;
							$lead->sales_person_id =0; 
							$lead->contact_name =$fullname;
							$lead->tags =$request->tags;
							$lead->sales_team_id =0;
							$lead->product_id =0;
							$lead->UTM_Source =$utm_source;
							$lead->UTM_Campaign=$page_id;
							$lead->UTM_Medium="";
							$lead->UTM_Term="";
							$lead->UTM_Content="";
							$lead->URL=$url;
							$lead->GCLID="";
							$lead->FBCLID="";
							$lead->token=$token;
							$lead->save();
							$leadId=$lead->id;
							$listUser=User::select('id')->where('partner_id',$partner_id)->orderByRaw('RAND()')->first();
							$url="https://fastercrm.com/lead/".$leadId."/edit";
							$notification = array(
								'partner_id'=>$partner_id,
								'user_id' => $listUser["id"],
								'url'=> $url,
								'title'=>"Kh??ch h??ng m???i t??? facebookMessenger",
								'desc'=>"Vui l??ng c???p nh???t cho KH c?? PSID",
								'status'=>0, //1 in, 2 out, 3 missing
								'created_at'=> date("Y-m-d H:i:s"),
								'date_notification'=>time()
							);
							Notification::insert($notification);
							return response()->json(['success' => 'success'], 200);
						}else{
							return response()->json(['success' => 'Data Fail'], 200);
						}
					}else{
						if($cookie!=""){
							Lead::where('id',$countLead["id"])->update(['cookie_id'=>$cookie]);
						}
						return response()->json(['success' => 'Exit'], 200);
					}
				
		} else {
			return response()->json(['error' => trans('dashboard.datafail')], 500);
		}
	}
	/* post SMS from api by javascript */
	public function getSMS(Request $request)
	{
		$partner = $request->partner;
		if($partner=="353793102602003"){
			$data = array(
                'partner' => $partner,
            );
            $rules = array(
                'partner' => 'required',
			);
			
            $validator = Validator::make($data, $rules);
            if ($validator->passes()) {
                $status=0;
                $tags="";
                $smsdata = new Smsdesc;
                if($partner!=""){
					
                    $stillNumber=0;
					$limit=10;
					$limitSend=10;
					$getNumberStill=PartnerDevice::where('device',$partner)->where('status',1)->where('date_sent_last',date("Y-m-d"))->first();
                    if(isset($getNumberStill) && $getNumberStill!=""){
                        $stillNumber=$getNumberStill["limit_sms"]-$getNumberStill["total_sms_last_sent"];
                        $limitStill=(int)$stillNumber;
					}else{
						exit();
					}
					if($limitStill>0){
						if($limitStill<$limit){
							$limitSend=$limitStill;
						}
					}else{
						exit();
					}
					$type_sms=$getNumberStill["sms_type"];
					$time_last_sent=$getNumberStill["time_last_sent"];
					$partner_id=$getNumberStill["partner_id"];

					if($type_sms==1){
						$time10minuteago=strtotime('-10 minutes');
						if($time_last_sent>=$time10minuteago){
							exit();
						}
					}
                    $smsJson=[];
                    if($limit>0){ 
					$smsDesc=Smsdesc::select('sms_desc.*')->join('partner_device','partner_device.partner_id','=','sms_desc.partner_id')->where('partner_device.device',$partner)
                    ->where('sms_desc.status',0)->where('sms_desc.delivery',0)->orderBy('sms_desc.id', 'asc')->limit($limitSend)->get();
					
					if($smsDesc){
                            if($smsDesc!="" && count($smsDesc)>0){
								$count=0;
								$listUpdate=array();
                                foreach($smsDesc as $smsDescList){
                                    $smsJson[]=[
                                        'id' => $smsDescList->id,
                                        'partner_id' => $smsDescList->partner_id,
                                        'phone' => $smsDescList->phone,
                                        'description' => $smsDescList->description,
                                        'status' => $smsDescList->status,
									];
									$listUpdate[]=$smsDescList->id;
                                    $count++;
								}
								if(count($listUpdate)>0){
									Smsdesc::whereIn('id',$listUpdate)->update(['delivery' => 1]);
								}
                                PartnerDevice::where('partner_id',$partner_id)->where('device',$partner)->update(['date_sent_last' => date("Y-m-d"), 'time_last_sent'=>time(), 'total_sms_last_sent'=> DB::raw('total_sms_last_sent+'.$count)]);
                                return response()->json(['smsdata' => $smsJson], 200);
                            }else{
                                return response()->json(['error' => 'Data tr???ng', 'smsdata' => []], 200);
                            }
                        }
                    }else{
                        return response()->json(['error' => trans('Data null'), 'smsdata' => []], 200);
                    }
                }else{
                    return response()->json(['error' => trans('Partner not register yet'), 'smsdata' =>[]], 500);
                }

            } else {
                return response()->json(['error' => trans('Partner not register yet'), 'smsdata' =>[]], 500);
            }
		}
        if(date("H")>=7 && date("H")<=22){
            
            $data = array(
                'partner' => $partner,
            );
            $rules = array(
                'partner' => 'required',
			);
			
            $validator = Validator::make($data, $rules);
            if ($validator->passes()) {
                $status=0;
                $tags="";
                $smsdata = new Smsdesc;
                if($partner!=""){
					
                    $stillNumber=0;
					$limit=10;
					$limitSend=10;
					$getNumberStill=PartnerDevice::where('device',$partner)->where('status',1)->where('limit_sms','>','total_sms_last_sent')->first();
					if(isset($getNumberStill) && $getNumberStill!=""){
                        $stillNumber=$getNumberStill["limit_sms"]-$getNumberStill["total_sms_last_sent"];
                        $limitStill=(int)$stillNumber;
					}else{
						exit();
					}
					if($limitStill>0){
						if($limitStill<$limit){
							$limitSend=$limitStill;
						}
					}else{
						exit();
					}
					$type_sms=$getNumberStill["sms_type"];
					$time_last_sent=$getNumberStill["time_last_sent"];
					$partner_id=$getNumberStill["partner_id"];

					if($type_sms==1){
						$time10minuteago=strtotime('-10 minutes');
						if($time_last_sent>=$time10minuteago){
							exit();
						}
					}
                    $smsJson=[];
                    if($limit>0){ 
					$smsDesc=Smsdesc::select('sms_desc.*')->join('partner_device','partner_device.partner_id','=','sms_desc.partner_id')->where('partner_device.device',$partner)
                    ->where('sms_desc.status',0)->where('sms_desc.delivery',0)->orderBy('sms_desc.id', 'asc')->limit($limitSend)->get();
					
					if($smsDesc){
                            if($smsDesc!="" && count($smsDesc)>0){
								$count=0;
								$listUpdate=array();
                                foreach($smsDesc as $smsDescList){
                                    $smsJson[]=[
                                        'id' => $smsDescList->id,
                                        'partner_id' => $smsDescList->partner_id,
                                        'phone' => $smsDescList->phone,
                                        'description' => $smsDescList->description,
                                        'status' => $smsDescList->status,
									];
									$listUpdate[]=$smsDescList->id;
                                    $count++;
								}
								if(count($listUpdate)>0){
									Smsdesc::whereIn('id',$listUpdate)->update(['delivery' => 1]);
								}
                                PartnerDevice::where('partner_id',$partner_id)->where('device',$partner)->update(['date_sent_last' => date("Y-m-d"), 'time_last_sent'=>time(), 'total_sms_last_sent'=> DB::raw('total_sms_last_sent+'.$count)]);
                                return response()->json(['smsdata' => $smsJson], 200);
                            }else{
                                return response()->json(['error' => 'Data tr???ng', 'smsdata' => []], 200);
                            }
                        }
                    }else{
                        return response()->json(['error' => trans('Data null'), 'smsdata' => []], 200);
                    }
                }else{
                    return response()->json(['error' => trans('Partner not register yet'), 'smsdata' =>[]], 500);
                }

            } else {
                return response()->json(['error' => trans('Partner not register yet'), 'smsdata' =>[]], 500);
            }
        }else{
            return response()->json(['error' => trans('No time send'), 'smsdata' =>[]], 500);
            exit();
        }
	}
    public function updateLimitSMS()
    {
		if(date("H")>=0 && date("H")<=1){
			PartnerDevice::where('status',1)->update(['date_sent_last' => date("Y-m-d"), 'total_sms_last_sent'=> 0]);
		}
        exit();
    }
	public function postUpdateSms(Request $request){
		$data = array(
			'id'=>$request->id
		);
		$rules = array(
			'id' => 'required',
		);
		$status=1;
		$delivery=0;
		$device="";
        if(isset($request->status) && $request->status!=""){
            $status=$request->status;
		}
		if(isset($request->delivery) && $request->delivery!=""){
            $delivery=$request->delivery;
		}
		if(isset($request->device) && $request->device!=""){
            $device=$request->device;
        }
		$validator = Validator::make($data, $rules);
		$sms_data ="";
		if ($validator->passes()) {
			$sms = Smsdesc::find($request->id);
			if($delivery>0 ){
				$sms_data = array(
					'status_send' => $delivery,
					'device_id' => $device
				);
			}else{
				if( $status>0){
					$sms_data = array(
						'status' => $status,
						'device_id' => $device,
						'num_sent'=> DB::raw('num_sent+1')
					);
				}
				
			}
			if($sms_data!=""){
				$result=$sms->update($sms_data);
			}
			return response()->json(['success' => 'success', 'result'=>$result], 200);
		} else {
			return response()->json(['error' => 'not_valid_data'], 500);
		}
	}
	public function postUpdateReplySms(Request $request){

		$data = array(
			'phone'=>$request->phone,
			//'description'=>$request->description
		);
		$rules = array(
			'phone' => 'required',
			//'description'=>'required',
		);
		$status=0;
		$type="";
		$validator = Validator::make($data, $rules);
		if ($validator->passes()) {
			if(isset($request->status) && $request->status!=""){
				$status=$request->status;	
			}
			if(isset($request->type) && $request->type!=""){
				$type=$request->type;	
			}
			$phone=$request->phone;
			$sms_id="";
			if(isset($request->sms_id) && $request->sms_id!=""){
				$sms_id=$request->sms_id;	
			}
			$descEndCode=md5($request->description);
			$smsDate=$request->smsDate;
			$smsData="";
			if($smsDate!=""){
				$smsData=SmsDescReply::where('date_resent',$smsDate)->where('phone',$phone)->first();
			}else{
				$smsDate=time();
			}
			if($smsData && $smsData!=""){
				//$dateSend=$smsData["created_at"];
				//$get5HouseAgo=date('Y-m-d H:i:s', strtotime('-5 hours'));
				$dateSend=strtotime($smsData["created_at"]);
				$get5HouseAgo=strtotime('-5 hours');
				//$get5HouseAgo=strtotime('-30 minutes');
				//echo date("Y-m-d H:i:s",$get5HouseAgo)."<br/>";
				$timeResent=$smsData["time_resent"];
				$dateResent=$smsData["date_resent"];
				
				if($dateResent<=$get5HouseAgo && $timeResent<=3)
				{
					SmsDescReply::where('id',$smsData["id"])->update(['date_resent' => time(), 'time_resent'=> DB::raw('time_resent+1')]);
					Smsdesc::where('device_id',$request->device_id)->where('phone',$phone)->where('description_encode',$descEndCode)->where('num_sent','<',3)->update(['status' => 0]);
					return response()->json(['success' => 'success'], 200);
				}
			}else{
		
				$sms = new SmsDescReply;
				if($phone){
					$sms->phone = $phone;
					$sms->device_id = $request->device_id;
					$sms->description = $request->description;
					$sms->description_encode = md5($request->description);
					$sms->sender = $request->sender;
					$sms->senderNum = $request->senderNum;
					$sms->created_at=date("Y-m-d H:i:s");
					$sms->status=$status;
					$sms->type=$type;
					$sms->sms_id=$sms_id;
					$sms->date_resent=$smsDate;
					$sms->save();
					return response()->json(['success' => 'success'], 200);
				}
			}
			return response()->json(['success' => 'unsuccess'], 200);
		} else {
			return response()->json(['error' => 'not_valid_data'], 500);
		}
	}
	public function addDecsSms(Request $request){
		$data = array(
			'phone'=>$request->phone,
			'description'=>$request->description
		);
		$rules = array(
			'phone' => 'required',
			'description'=>'required',
		);
		$validator = Validator::make($data, $rules);
		if ($validator->passes()) {
			//$sms = new Smsdesc;
			$extention="+84";
			if(isset($request->extention) && $request->extention!=""){
				$extention=$request->extention;
			}
			$phone=$this->checkPhoneNumber($request->phone, $extention);

			if($phone){
                /*
				if( $request->partner_id==10){
					$content=$request->description;
				}else{
					$content=$this->convert_vi_to_en($request->description);
				} */

				$content=$this->convert_vi_to_en($request->description);
				
				$partner=$request->partner_id;
				$sender=$request->sender;
				if(strlen($content)>160){
					$numbersms=(int)(strlen(trim($content))/160)+1;
					$start=0;
					$sms0="";
					$sms1="";
					$sms2="";
					$sms3="";
					$data=array();
					for($i=0;$i<$numbersms;$i++){
						if($i==0){
							$sms0=trim($this->substrwords($content,0, 160, ''));
							$data[]=array("phone"=>$phone, "partner_id"=>$partner, "description"=>$sms0, "description_encode"=>md5($sms0), "sender"=>$sender, "test"=>0, "status"=>0, "created_at"=>date("Y-m-d H:i:s"));
						}
						if($i==1){
							$contentNew1=trim(str_replace($sms0, "",$content));
							$sms1 =trim($this->substrwords($contentNew1,0, 160, ''));
							$data[]=array("phone"=>$phone, "partner_id"=>$partner, "description"=>$sms1, "description_encode"=>md5($sms1), "sender"=>$sender, "test"=>0, "status"=>0, "created_at"=>date("Y-m-d H:i:s"));
						}
						
						if($i==2){
							$contentNew2 = trim(str_replace(array($sms0,$sms1), array("",""),$content));
							$sms2=trim($this->substrwords($contentNew2, 0, 160, ''));
							$data[]=array("phone"=>$phone, "partner_id"=>$partner, "description"=>$sms2, "description_encode"=>md5($sms2), "sender"=>$sender, "test"=>0, "status"=>0, "created_at"=>date("Y-m-d H:i:s"));
						}
						if($i==3){
							$contentNew3 = trim(str_replace(array($sms0,$sms1,$sms2), array("","",""),$content));
							$sms3=trim($this->substrwords($contentNew3, 0, 160, ''));
							$data[]=array("phone"=>$phone, "partner_id"=>$partner, "description"=>$sms3, "description_encode"=>md5($sms3), "sender"=>$sender, "test"=>0, "status"=>0, "created_at"=>date("Y-m-d H:i:s"));
						}
					}
				}else{
					$data=array("phone"=>$phone, "partner_id"=>$partner, "description"=>$content, "description_encode"=>md5($content), "sender"=>$sender, "test"=>0, "status"=>0, "created_at"=>date("Y-m-d H:i:s"));
				}
				
				if(isset($data) && count($data)>0){
					Smsdesc::insert($data); // Eloquent approach
				}
				return response()->json(['success' => 'success'], 200);
			}
			return response()->json(['success' => 'unsuccess'], 200);
		} else {
			return response()->json(['error' => 'not_valid_data'], 500);
		}
	}
	public static function checkPhoneNumber($phone, $extention="+84"){
		$phone=str_replace(array(" ", "-"), array("",""), $phone);
		if(strlen($phone)<10 || strlen($phone)>=15){
			return;
		}
		if(substr($extention, 0, 1)!="+"){
			$extention="+".$extention;
		}
		
		if((int)substr($phone, 0, 1)==0){
			$length=strlen($phone);
			$phone=$extention.substr($phone, 1, $length-1);
		}elseif(substr($phone, 0, 3)!=$extention){
			$phone=$extention.$phone;
		}
		if(substr($phone, 0, 4)==$extention."0"){
			$phone=str_replace($extention."0", $extention, $phone);
		}
		if(strlen($phone)>=11 && strlen($phone)<=13){
			return $phone;
		}
		return;
	}

	public static function convert_vi_to_en($str) { 
		$str =strip_tags($str);
		$str = preg_replace("/(??|??|???|???|??|??|???|???|???|???|???|??|???|???|???|???|???)/", "a", $str);
		$str = preg_replace("/(??|??|???|???|???|??|???|???|???|???|???)/", "e", $str);
		$str = preg_replace("/(??|??|???|???|??)/", "i", $str);
		$str = preg_replace("/(??|??|???|???|??|??|???|???|???|???|???|??|???|???|???|???|???)/", "o", $str);
		$str = preg_replace("/(??|??|???|???|??|??|???|???|???|???|???)/", "u", $str);
		$str = preg_replace("/(???|??|???|???|???)/", "y", $str);
		$str = preg_replace("/(??)/", "d", $str);
		$str = preg_replace("/(??|??|???|???|??|??|???|???|???|???|???|??|???|???|???|???|???)/", "A", $str);
		$str = preg_replace("/(??|??|???|???|???|??|???|???|???|???|???)/", "E", $str);
		$str = preg_replace("/(??|??|???|???|??)/", "I", $str);
		$str = preg_replace("/(??|??|???|???|??|??|???|???|???|???|???|??|???|???|???|???|???)/", "O", $str);
		$str = preg_replace("/(??|??|???|???|??|??|???|???|???|???|???)/", "U", $str);
		$str = preg_replace("/(???|??|???|???|???)/", "Y", $str);
		$str = preg_replace("/(??)/", "D", $str);
		$str = str_replace(array("  ", "   "), array(" ", " "),$str);
		//$str = str_replace(" ", "-", str_replace("&*#39;","",$str));
		/*if(strlen($str)>158){
			$str=substr($str,0,158);
		} */
		return $str;
	}
	public static function convert_vi_to_en2($str) { 
		$str =strip_tags($str);
		$str = preg_replace("/(??|??|???|???|??|??|???|???|???|???|???|??|???|???|???|???|???)/", "a", $str);
		$str = preg_replace("/(??|??|???|???|???|??|???|???|???|???|???)/", "e", $str);
		$str = preg_replace("/(??|??|???|???|??)/", "i", $str);
		$str = preg_replace("/(??|??|???|???|??|??|???|???|???|???|???|??|???|???|???|???|???)/", "o", $str);
		$str = preg_replace("/(??|??|???|???|??|??|???|???|???|???|???)/", "u", $str);
		$str = preg_replace("/(???|??|???|???|???)/", "y", $str);
		$str = preg_replace("/(??)/", "d", $str);
		$str = preg_replace("/(??|??|???|???|??|??|???|???|???|???|???|??|???|???|???|???|???)/", "a", $str);
		$str = preg_replace("/(??|??|???|???|???|??|???|???|???|???|???)/", "E", $str);
		$str = preg_replace("/(??|??|???|???|??)/", "i", $str);
		$str = preg_replace("/(??|??|???|???|??|??|???|???|???|???|???|??|???|???|???|???|???)/", "o", $str);
		$str = preg_replace("/(??|??|???|???|??|??|???|???|???|???|???)/", "u", $str);
		$str = preg_replace("/(???|??|???|???|???)/", "y", $str);
		$str = preg_replace("/(K)/", "k", $str);
		$str = preg_replace("/(??)/", "d", $str);
		$str = str_replace(array(" ", "  ", "   "), array("-","-","-"),$str);
		$str = str_replace(array("!","#","$","&","=",";",",","?"), array("","","","","","","",""),$str);
		/*if(strlen($str)>158){
			$str=substr($str,0,158);
		} */
		return $str;
	}

	public static function substrwords($text, $start=0, $maxchar, $end='...') {
		$text=trim($text);
		if (strlen($text) > $maxchar || $text == '') {
			$words = preg_split('/\s/', $text);      
			$output = '';
			$i      = $start;
			while (1) {
				$length = strlen($output)+strlen($words[$i]);
				if ($length > $maxchar) {
					break;
				} 
				else {
					$output .= " " . $words[$i];
					++$i;
				}
			}
			$output .= $end;
		} 
		else {
			$output = $text;
		}
		return $output;
	}

	public function pushNoitificationAlertSms(){
		$time2minuteago=date("Y-m-d H:i:s", strtotime('-2 minutes'));
		$tokenReport=Smsdesc::select('device_token.token', DB::raw('count(device_token.id) as total'))
			->join('partner_device','partner_device.partner_id','=','sms_desc.partner_id')
			->join('device_token','partner_device.device','=','device_token.uuid')
			->where('device_token.status',1)
			->where('sms_desc.status',0)
			->where('sms_desc.created_at','<=',$time2minuteago)
			->groupBy('device_token.token')
			->orderBy('device_token.id', 'desc')->get();
			if($tokenReport){
				foreach($tokenReport as $tokenReportList){
                    $url = 'https://fcm.googleapis.com/fcm/send';
                    $server_key = 'AAAAZByjYLM:APA91bFKEklcX4nzA6UM2wupVHulSHNFFkXQzh4Qz2ZqbJVZ9IvKSs8JYB0PtacwwgF878z2hIuLWJc0yCClWtbRV8aVvq4XHpf8guQOWOY3jqCjBHhqBXJDlZoMhycfsV0EGb9zSKIs';
                    //header with content_type api key
                    $headers = array(
                                     'Content-Type:application/json',
                                     'Authorization:key='.$server_key
                                     );
                    $title="Th??ng b??o l???ch g???i tin nh???n";
                    $data = array(
                                  "to"=>$tokenReportList["token"],
                                  "priority"=>"high",
                                  "data"=>array(
                                                "description"=>"B???n c?? l???ch g???i tin nh???n",
                                                "product_name"=>1,
                                                "time"=>0,
                                                "type"=>0
                                                ),
                                  //0 binh thuong, 1 New lead, 2 follow
                                  "notification"=>array(
                                                        "title"=> "FasterSendy Th??ng b??o",
                                                        "body"=> "C?? l???ch g???i tin nh???n cho kh??ch h??ng v??o l??c ".date("Y-m-d H:i:s"),
                                                        "sound"=>"default"
                                                        )
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
                    if($result === FALSE){
                        die('Curl failed: ' . curl_error($ch));
                    }
                    // Close connection
                    curl_close($ch);
				
				}
				exit();
			}else{
				exit();
			}
	}
	// Add contact
	//Contacts list
	public function addContactFromCall(Request $request)
	{
		$user_id="";
		$device=$request->uuid;
		$tokens = $request->header('token');
		$phone=$request->phone;
		$this->user=null;
		if($tokens!=null && $tokens==""){
			$token  = JWTAuth::parseToken($tokens);		
			$this->user = $token->authenticate();
		}elseif($device){
			$tokencheck = DeviceToken::where('uuid',$device)->where('status',1)->first();
			if($tokencheck){
				$user_id=$tokencheck["user_id"];
				if($user_id){
					$this->user = User::find( $user_id);
				}
			}
		}
		$data = array(
		   'phone' => $request->input('phone'),
		   'device_id' => $device,
		   'name'=>$request->input('name'),
		   'user_id'=> $user_id,
		   'date_create'=> date("Y-m-d H:i:s"),
	   );
	   $rules = array(
		   'phone' => 'required',
		   'device_id' =>'required',
	   );
	   $validator = Validator::make($data, $rules);
	   if ($validator->passes()) {

		$listContactPhone=$request->phone;
		$listContactName=$request->input('name');

		   $data = array(
				'phone' => $request->input('phone'),
				'device_id' => $device,
				'name'=>$request->input('name'),
				'user_id'=> $user_id,
				'date_create'=> date("Y-m-d H:i:s"),
			);
			$phoneData=$request->input('phone');
			$nameData=$request->input('name');
			$phoneList=explode("<:>",$phoneData);
			$nameList=explode("<:>",$nameData);
			$listInsert=null;
			$listInsert2=array();
			$keyUpdate=array();
			for($i=0;$i<count($phoneList);$i++){
				if($phoneList[$i]){
					$phone=$phoneList[$i];
					if((int)substr($phoneList[$i],0,1)>0){
						$phone="0".$phoneList[$i];
					}
					if(strlen($phone)>=10){
						$listInsert2[]=[$phone, $device, $nameList[$i], $user_id, date("Y-m-d H:i:s")];
						$keyUpdate[]="`phone`='".$phone."', `user_id`='".$user_id."'";
					}
					
				} 
			}
			//'phone'=>$attributes, 
			$attributes=array('phone','user_id');
			if(count($listInsert2)>0){
				//DB::table('contacts')->updateOrInsert($listInsert);
				try {
					$DataList=$listInsert2;
					for($i=0;$i<count($DataList);$i++){
						DB::insert('insert into `contacts` (`phone`, `device_id`, `name`, `user_id`, `date_create`) values (?, ?, ?, ?, ?) on duplicate key update '.$keyUpdate[$i],$listInsert2[$i]);

					}  
					//DB::table('contacts')->updateOrInsert($listInsert, $attributes);
				} catch (ModelNotFoundException $exception) {
					return "";
				}
			}
		   return response()->json(['success' => 'success'], 200);
	   } else {
		   return response()->json(['error' => 'not_valid_data'], 500);
	   }
	}
	public function checkContactFromCall(Request $request){
		$user_id="";
		$tokens = $request->header('token');
		$device=$request->uuid;
		$phone=$request->phone;
		$this->user=null;
		if($tokens!=null && $tokens==""){
			$token  = JWTAuth::parseToken($tokens);		
			$this->user = $token->authenticate();
		}
		if ($this->user) {
			$user_id=$this->user->id;
		}elseif($device){
			$tokencheck = DeviceToken::where('uuid',$device)->where('status',1)->first();
			if($tokencheck){
				$user_id=$tokencheck["user_id"];
				if($user_id){
					$this->user = User::find( $user_id);
				}
			}
		}
		$data = array(
			'user_id' => $user_id,
			'device_id' => $device,
		);
		$rules = array(
			'user_id' => 'required',
			'device_id' => 'required',
		);
		$validator = Validator::make($data, $rules);
	   	if ($validator->passes()) {
			$contactDesc=Contacts::where('user_id',$user_id)->where('phone',$phone)->first();
			if($contactDesc){
				return response()->json(['result' => 1, 'name' => $contactDesc->name], 200);
			}else{
				return response()->json(['result' => 0, 'phone' => null, 'name' => null], 200);

			}
		}
	}

	public function uploadFileAudio(Request $request){
		$filerecord ="";
		//if (!is_null($request->file_record)) {
			$ifp = fopen(public_path() . '/logs/file.txt', "wb");
			fwrite($ifp, base64_decode($request));
			fclose($ifp);
			/*
			$phone=$request->phone;
			$output_file = "RecordAudio-".$request->phone."-".time(). ".m4a";
			$ifp = fopen(public_path() . '/uploads/media/' . $output_file, "wb");
			fwrite($ifp, base64_decode($request->file_record));
			fclose($ifp);
			$filerecord = $output_file;
			return response()->json(['success' => 1, 'link'=>$filerecord], 200); */
		//}
		return response()->json(['success' => 0], 200);
	}
	//Add contact
	public static function addDataToMa($link, $fullname, $email, $sdt, $seagment=0, $product, $title, $status, $payment){
		$url = $link;
		//header with content_type api key
		$server_key="";
		$headers = array(
						 'Content-Type:application/json',
						 'Authorization:key='.$server_key
						 );
		$data = array("first_name"=>$fullname,
					  "phone"=>$sdt,
					  "email"=>$email,
					  "segment"=>$seagment,
					  "product"=>$product,
					  "title"=>$title,
					  "status"=>$status,
					  "payment"=>$payment
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
		if($result === FALSE){
		}
		// Close connection
		curl_close($ch);
	}
	public function syndataToMS(){
		$leadID=array();
		$leadData=Lead::select('leads.*', 'call_action_status.title as title_status', 'partner.link_ma as link_ma', 'partner.segment_lead as segment_lead', 'partner.segment_lead_update as segment_lead_update')->leftJoin('partner','partner.id','=','leads.partner_id')->leftJoin('call_action_status','call_action_status.id','=','leads.status')->whereNotNull('partner.link_ma')->where('leads.partner_id','>',1)->where('leads.syn_data',0)
		->orderBy('leads.id', 'desc')
		->limit(20)
		->get();
		if($leadData){
			foreach($leadData as $dataShow){
				$fullname=$dataShow["opportunity"];
				$title=$dataShow["title"];
				$phone=$dataShow["phone"];
				$email=$dataShow["email"];
				$product=$dataShow["product_name"];
				$status=$dataShow["title_status"];
				$linkma=$dataShow["link_ma"];
				$payment=$dataShow["tags"];
				$segment_lead=$dataShow["segment_lead"];
				$leadID[]=$dataShow["id"];
				$this->addDataToMa($linkma, $fullname, $email, $phone, $segment_lead, $product, $title, $status, $payment);
			}
			if(count($leadID)>0){
				Lead::whereIn('id',$leadID)->update(['syn_data' => 1]);
			}
		}
		exit();
	}

	public function updatePhoneFail(){
			//DB::table('contacts')->updateOrInsert($listInsert);
			$smsDataFail=SmsDescReply::select("sms_desc_reply.*", "users.partner_id")->join("device_token","device_token.uuid","=","sms_desc_reply.device_id")->join("users", "users.id","=","device_token.user_id")->whereIn('sms_desc_reply.status',array(32,68,72))->groupBy("sms_desc_reply.phone")->get();
			
			if($smsDataFail){
				$listInsert2=array();
				$keyUpdate=array();
				foreach($smsDataFail as $listData){
					$phone=$listData["phone"];
					$partner_id=$listData["partner_id"];
					$listInsert2[]=[$phone, $partner_id, $listData["status"]];
					$keyUpdate[]="phone='".$phone."', partner_id=".$partner_id;
				}
				try {
					$DataList=$listInsert2;
					for($i=0;$i<count($DataList);$i++){
						DB::insert('insert into `phone_fail` (`phone`, `partner_id`, `code_error`) values (?, ?, ?) on duplicate key update '.$keyUpdate[$i],$listInsert2[$i]);

					}  
					//DB::table('contacts')->updateOrInsert($listInsert, $attributes);
				} catch (ModelNotFoundException $exception) {
					return "";
				}
			}
			
	}	

	//Check Email update to SM
	public function getEmailUpdate(){
		//DB::table('contacts')->updateOrInsert($listInsert);
		$emailCheckList=EmailCheck::select('email_check.*', 'email_check_status.link_ma', 'email_check_status.link_ma', 'email_check_status.partner_id', 'email_check_status.segment')->join('email_check_status','email_check_status.partner_id','=','email_check.partner_id')->where('email_check.status_check',0)->where('email_check_status.status',1)->limit(10)->get();
		$listDelete=array(); 
		$listIdCheckSuceess=array();
		$listIdCheckNoSuccess=array();
		
		if($emailCheckList){
			$numberreduce=0;

			foreach($emailCheckList as $listData){
				$numberreduce++;
				$linkMa=$listData["link_ma"];
				$partner_id=$listData["partner_id"];
				$email=$listData["email"];
				$phone=$listData["phone"];
				$seagment=$listData["segment"];
				$fullname=$listData["full_name"];
				EmailCheck::where('id', $listData['id'])->update(['status_check'=>1]);
				if($listData['type_data']=='email'){
					if (filter_var($listData['email'], FILTER_VALIDATE_EMAIL)) {
						try {
							
							if($linkMa){
	
								$bIsEmailValid = @$this->jValidateEmailUsingSMTP("$email", "gmail.com", "email@gmail.com");
								//$bIsEmailValid = $this->jValidateEmailUsingSMTP("$email", "bounce@fastersendy.com", "fastersendy.com");
								$bufferList="";
								if($bIsEmailValid){
									if(!$phone){
										$phone="";
									}
									if(!$seagment){
										$seagment=0;
									}
									$this->addDataToMaEmailVerify($linkMa, $fullname, $email, $phone, $seagment, "", "", "");
									//EmailCheck::where('id', $listData['id'])->update(['status_check'=>1, 'status_email'=>1]);
									$listIdCheckSuceess[]=$listData['id'];
								}else{
									$listIdCheckNoSuccess[]=$listData['id'];
									//EmailCheck::where('id', $listData['id'])->update(['status_check'=>1, 'status_email'=>0]);
								//	EmailCheck::whereIn('id', $listIdCheckNoSuccess)->update(['status_check'=>1, 'status_email'=>0]);
								}
							}
							//DB::table('contacts')->updateOrInsert($listInsert, $attributes);
						} catch (ModelNotFoundException $exception) {
							return "";
						}
					}else{
						$listDelete[]=$listData["id"];
					}
				}else{
					//$configpage=Getdata::where('partner_id','=',$partner_id)->where("check_page",1)->get();
					$configpage=Getdata::where('partner_id','=',$partner_id)->where("check_page",1)->first();
					if($configpage){
						$token=$configpage["token"];
						$pageid=$configpage["page_id"];
						$sender=$email;
						$contentFetch=@file_get_contents("https://graph.facebook.com/".$sender."?fields=first_name,last_name,profile_pic&access_token=".$token);
						$needle = "HTTP request failed"; 
						$haystack =$contentFetch;
						if (strpos($haystack, $needle) !== true){
							$profile=json_decode($contentFetch);
							if(isset($profile) && $profile!=""){
								EmailCheck::where('id', $listData['id'])->update(['status_check'=>1, 'status_email'=>1]);
								//Update seting
								$fullname=$profile->first_name." ".$profile->last_name;
								$this->addDataToMaChatbotVerify($linkMa, $fullname, "", $phone, $seagment, "", "", "", $email);
								$insertMessenger = array(
									'psid' => $sender,
									'type'=>"facebook",
									'fullname'=> $fullname,
									'page_id'=>$pageid,
									'page_token'=>$token,
									'created_at'=> date("Y-m-d H:i:s")
								);
								MessengerPartner::insert($insertMessenger);
								$listIdCheckSuceess[]=$listData['id'];
								
							}
						}
						
					}
				}
				
				
			}
			
			if(count($listDelete)>0){
				 EmailCheck::whereIn('id', $listDelete)->delete();
			}
			if(count($listIdCheckSuceess)>0){
				EmailCheck::whereIn('id', $listIdCheckSuceess)->update(['status_check'=>1, 'status_email'=>1]);
			}
			
			if(count($listIdCheckNoSuccess)>0){
				EmailCheck::whereIn('id', $listIdCheckNoSuccess)->update(['status_check'=>1, 'status_email'=>0]);
			}
			if($numberreduce>0){
			//	EmailCheckStatus::where('partner_id',$partner_id)->update(['number_check_email' => DB::raw('number_check_email-'.$numberreduce), 'total_email_checked']);
			EmailCheckStatus::where('partner_id',$partner_id)->update(['total_email_checked'=>DB::raw('total_email_checked+'.$numberreduce)]);
			}
		}
		
	}	
	public static function jValidateEmailUsingSMTP1($sToEmail, $sFromDomain = "gmail.com", $sFromEmail = "email@gmail.com", $bIsDebug = false) {
		$bIsValid = true; // assume the address is valid by default..
		$aEmailParts = explode("@", $sToEmail); // extract the user/domain..
		getmxrr($aEmailParts[1], $aMatches); // get the mx records..
	
		if (sizeof($aMatches) == 0) {
			return false; // no mx records..
		}

		foreach ($aMatches as $oValue) {
	
			if ($bIsValid && !isset($sResponseCode)) {
	
				// open the connection..
				$oConnection = @fsockopen($oValue, 25, $errno, $errstr, 30);
				$oResponse = @fgets($oConnection);
	
				if (!$oConnection) {
	
					$aConnectionLog['Connection'] = "ERROR";
					$aConnectionLog['ConnectionResponse'] = $errstr;
					$bIsValid = false; // unable to connect..
	
				} else {
	
					$aConnectionLog['Connection'] = "SUCCESS";
					$aConnectionLog['ConnectionResponse'] = $errstr;
					$bIsValid = true; // so far so good..
	
				}
	
				if (!$bIsValid) {
					if ($bIsDebug) print_r($aConnectionLog);
					return false;
	
				}
	
				// say hello to the server..
				fputs($oConnection, "HELO $sFromDomain\r\n");
				$oResponse = fgets($oConnection);
				$aConnectionLog['HELO'] = $oResponse;
	
				// send the email from..
				fputs($oConnection, "MAIL FROM: <$sFromEmail>\r\n");
				$oResponse = fgets($oConnection);
				$aConnectionLog['MailFromResponse'] = $oResponse;
	
				// send the email to..
				fputs($oConnection, "RCPT TO: <$sToEmail>\r\n");
				$oResponse = fgets($oConnection);
				$aConnectionLog['MailToResponse'] = $oResponse;
	
				// get the response code..
				$sResponseCode = substr($aConnectionLog['MailToResponse'], 0, 3);
				$sBaseResponseCode = substr($sResponseCode, 0, 1);
	
				// say goodbye..
				fputs($oConnection,"QUIT\r\n");
				$oResponse = fgets($oConnection);
	
				// get the quit code and response..
				$aConnectionLog['QuitResponse'] = $oResponse;
				$aConnectionLog['QuitCode'] = substr($oResponse, 0, 3);
	
				if ($sBaseResponseCode == "5") {
					$bIsValid = false; // the address is not valid..
				}
	
				// close the connection..
				@fclose($oConnection);
	
			}
	
		}
	
		if ($bIsDebug) {
			print_r($aConnectionLog); // output debug info..
		}
	
		return $bIsValid;
	
	}

	public static function jValidateEmailUsingSMTP($sToEmail, $sFromDomain = "fastersendy.com", $sFromEmail = "bounce@fastersendy.com", $bIsDebug = false) {
		$bIsValid = true; // assume the address is valid by default..
		$aEmailParts = explode("@", $sToEmail); // extract the user/domain..
		getmxrr($aEmailParts[1], $aMatches); // get the mx records..
	
		if (sizeof($aMatches) == 0) {
			return false; // no mx records..
		}

		foreach ($aMatches as $oValue) {
	
			if ($bIsValid && !isset($sResponseCode)) {
	
				// open the connection..
				$oConnection = @fsockopen($oValue, 25, $errno, $errstr, 30);
				$oResponse = @fgets($oConnection);

				if (!$oConnection) {
	
					$aConnectionLog['Connection'] = "ERROR";
					$aConnectionLog['ConnectionResponse'] = $errstr;
					$bIsValid = false; // unable to connect..
	
				} else {
	
					$aConnectionLog['Connection'] = "SUCCESS";
					$aConnectionLog['ConnectionResponse'] = $errstr;
					$bIsValid = true; // so far so good..
	
				}
	
				if (!$bIsValid) {
					if ($bIsDebug) print_r($aConnectionLog);
					return false;
	
				}
				
				// say hello to the server..
				fputs($oConnection, "HELO $sFromDomain\r\n");
				$oResponse = fgets($oConnection);
				$aConnectionLog['HELO'] = $oResponse;
	
				// send the email from..
				fputs($oConnection, "MAIL FROM: <$sFromEmail>\r\n");
				$oResponse = fgets($oConnection);
				$aConnectionLog['MailFromResponse'] = $oResponse;
	
				// send the email to..
				fputs($oConnection, "RCPT TO: <$sToEmail>\r\n");
				$oResponse = fgets($oConnection);
				$aConnectionLog['MailToResponse'] = $oResponse;
	
				// get the response code..
				$sResponseCode = substr($aConnectionLog['MailToResponse'], 0, 3);
				$sBaseResponseCode = substr($sResponseCode, 0, 1);
	
				// say goodbye..
				fputs($oConnection,"QUIT\r\n");
				$oResponse = fgets($oConnection);
	
				// get the quit code and response..
				$aConnectionLog['QuitResponse'] = $oResponse;
				$aConnectionLog['QuitCode'] = substr($oResponse, 0, 3);
	
				if ($sBaseResponseCode == "5") {
					$bIsValid = false; // the address is not valid..
				}
				
				// close the connection..
				@fclose($oConnection);
	
			}
	
		}
		if ($bIsDebug) {
			print_r($aConnectionLog); // output debug info..
		}
	
		return $bIsValid;
	
	}

	public static function addDataToMaEmailVerify($link, $fullname, $email, $sdt, $seagment=0, $product, $title, $status){
		$url = $link."/api_process/addma.php";	
		//header with content_type api key
		$server_key="";
		$headers = array(
						 'Content-Type:application/json',
						 'Authorization:key='.$server_key
						 );
		$data = array("first_name"=>$fullname,
					  "phone"=>$sdt,
					  "email"=>$email,
					  "segment"=>$seagment,
					  "product"=>$product,
					  "title"=>$title,
					  "status"=>$status
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
		if($result === FALSE){
		}
		// Close connection
		curl_close($ch);
	}
	public static function addDataToMaChatbotVerify($link, $fullname, $email, $sdt, $seagment=0, $product, $title, $status, $psid){
		$url = $link."/api_process/addma.php";	
		//header with content_type api key
		$server_key="";
		$headers = array(
						 'Content-Type:application/json',
						 'Authorization:key='.$server_key
						 );
		$data = array("first_name"=>$fullname,
					  "phone"=>$sdt,
					  "email"=>"",
					  "segment"=>$seagment,
					  "product"=>$product,
					  "title"=>$title,
					  "status"=>$status,
					  "psid"=>$psid
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
		echo $datapost;
		echo $url;
		$result = curl_exec($ch);
		if($result === FALSE){
		}
		// Close connection
		curl_close($ch);
	}

		// show log action show_logs
	public function notification(Request $request)
	{
		$user_id=$request->user_id;
		$partner_id=$request->partner_id;

		$data = array(
			'user_id' => $user_id,
			'partner_id' => $partner_id,
		);
		$rules = array(
			'user_id' => 'required',
		);
		$validator = Validator::make($data, $rules);
		if ($validator->passes()) {
			$date = strtotime('-1 hour');
			$notification = Notification::where("user_id", $user_id)->where("date_notification",'>=', $date)->where('status',0)->first();
			if($notification){
					$notification1 = Notification::find($notification["id"]);
					$notification1->view = $notification["view"]+1;
					$notification1->save();
			} 
			return response()->json(['notification' => $notification], 200);
		}else{
			return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
		}
	}

	public function updatenotification(Request $request)
	{
		$id=$request->id;
		$data = array(
			'id' => $id,
		);
		$rules = array(
			'id' => 'required',
		);
		$validator = Validator::make($data, $rules);
		if ($validator->passes()) {
			$notification1 = Notification::find($id);
			$notification1->view = 1;
			$notification1->status = 1;
			$notification1->save();
			return response()->json(['notification' => $notification], 200);
		}else{
			return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
		}
	}
	public function sendMessenger(Request $request){
		$token="";
		if(isset($request->facebook_messenger_id) && $request->facebook_messenger_id!=""){
            $sender=$request->facebook_messenger_id;
            $content=$request->content;
            $result=MessengerPartner::select('messenger_partner.page_id', 'config_datas.token as page_token')->join('config_datas','config_datas.page_id','=','messenger_partner.page_id')->where('messenger_partner.psid',$sender)->first();
            if($result){
                $token=$result["page_token"];
                $page=$result["page_id"];
            }
        }
		if($token!="" && $sender!=""){
			$fullname="";
			$title="A/C";
			$images="";
			$link="";
			$type="normal";
			$message_to_reply="";
			$button="T?? V???N NGAY";
			$buttongapnhanvien="G??p NV T?? V???n";

			$tag="tu_van_ngay";
			$buttonstop="H???n Ch??? Nh???n Tin";
			$taggapnhanvien="gap_nhan_vien_tu_van";
			$tagstop="ngung_nhan_tin";

			$profile=json_decode(file_get_contents("https://graph.facebook.com/".$sender."?fields=first_name,last_name,profile_pic,gender&access_token=".$token));
			
			$fullname=$profile->first_name." ".$profile->last_name;
			if($profile->gender=="female"){
			$title="Ch???";
			}elseif($profile->gender=="male"){
				$title="Anh";
			}else{
				$title="Anh/Ch???";
			} 
			//$title="Anh/Ch???";
			if(isset($request->name) && $request->name!=""){
				$fullname="Ch??o b???n ".$title." ".$request->name;
			}
			if(isset($request->content_reply) && $request->content_reply!=""){
				$message_to_reply=$fullname." ".$request->content_reply;
			}
			$message=htmlspecialchars_decode($request->content);
			$message=str_replace('{title}',$title,$message);
			if(isset($request->images) && $request->images!=""){
				$images=$request->images;
			}
			if(isset($request->link) && $request->link!=""){
				$link=$request->link;
			}
			if(isset($request->type) && $request->type!=""){
				$type=$request->type;
			}
			if(isset($request->button) && $request->button!=""){
				$button=$request->button;
				$tag=$this->convert_vi_to_en2($button);
			}
			if(isset($request->keyword) && $request->keyword!=""){
				$tag=$request->keyword;	
			}
			
			$url = 'https://graph.facebook.com/v4.0/me/messages?access_token='.$token;
			$ch = curl_init($url);
			if($type=="normal"){
				$jsonData = '{
					"recipient":{
						"id":"'.$sender.'"
					},
					"message":{
						"text":"'.$message.'",
					},
					"messaging_type": "MESSAGE_TAG",
					"tag": "ACCOUNT_UPDATE"
				}';
				echo $jsonData;
			}else{
				if($link==""){
					$jsonData = '{
						"recipient":{
							"id":"'.$sender.'"
						},
						"message":{
							"attachment":{
								"type":"'.$type.'",
								"payload":{
									"template_type":"button",
									"text":"'.$message.'",
									"buttons":[
										{
											"type":"postback",
											"title":"'.$button.'",
											"payload":"'.$tag.'"
										},
										{
											"type":"postback",
											"title":"'.$buttonstop.'",
											"payload":"'.$tagstop.'"
										},
										{
											"type":"postback",
											"title":"'.$buttongapnhanvien.'",
											"payload":"'.$taggapnhanvien.'"
										} 
									]
								}
							}
						},
						"messaging_type": "MESSAGE_TAG",
						"tag": "ACCOUNT_UPDATE"
					}';
				}else{
					$jsonData = '{
						"recipient":{
							"id":"'.$sender.'"
						},
						"message":{
							"attachment":{
								"type":"'.$type.'",
								"payload":{
									"template_type":"generic",
									"elements":[
										{
										 "title":"'.$fullname.'",
										 "image_url":"'.$images.'",
										 "subtitle":"'.$message.'",
										 "default_action": {
										   "type": "web_url",
										   "url": "'.$link.'",
										   "webview_height_ratio": "tall",
										 },
										 "buttons":[
										   {
											 "type":"postback",
											 "title":"'.$button.'",
											 "payload":"'.$tag.'"
										   },
										   {
												"type":"postback",
												"title":"'.$buttongapnhanvien.'",
												"payload":"'.$taggapnhanvien.'"
										   },
										   {
											"type":"postback",
											"title":"'.$buttonstop.'",
											"payload":"'.$tagstop.'"
											}
										 ]      
									   }
									 ]
								}
							}
						},
						"messaging_type": "MESSAGE_TAG",
						"tag": "ACCOUNT_UPDATE"
					}';
				}
				
			}
			//Encode the array into JSON.
			$jsonDataEncoded = $jsonData;
			//Tell cURL that we want to send a POST request.
			curl_setopt($ch, CURLOPT_POST, 1);
			//Attach our encoded JSON string to the POST fields.
			curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
			//Set the content type to application/json
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			//Execute the request
			$reponsive = array();
			//if( !empty($input['entry'][0]['messaging'][0]['message']) ){
			$reponsive = curl_exec($ch);
			//}

			$res = array(
				'message' => $message,
				'reponsive' => $reponsive,
			);
			//Update seting
			$datamessenger = array(
				'sender_id' =>$page,
				'receive_id' =>$sender,
				'messenger'=>$message,
				'type_send'=>"Marketing",
				'page_id'=>$page,
				'status'=>$reponsive,
				'date_create'=>date("Y-m-d H:i:s"),
			);
			MessengerMarketingResult::insert($datamessenger);
			return $res;
		}else{
			return "";
		}
		
	}

	//Chat with client
	public function chatWithUser(Request $request){
		$token=""; 
		if(isset($request->facebook_messenger_id) && $request->facebook_messenger_id!="" && $request->page_id!=""){
			$sender=$request->facebook_messenger_id;
			$page_id=$request->page_id;
			$partner_id=$request->partner_id;
            $content=$request->content;
		   // $result=MessengerPartner::select('messenger_partner.page_id', 'config_datas.token as page_token')->join('config_datas','config_datas.page_id','=','messenger_partner.page_id')->where('messenger_partner.psid',$sender)->first();
		   $result=Getdata::where('page_id',$page_id)->where('partner_id',$partner_id)->where('status',1)->first();
            if($result){
                $token=$result["token"];
                $page=$result["page_id"];
            }
        }
		
		if($token!="" && $sender!="" && $content!=""){
			$fullname="";
			$title="A/C";
			$images="";
			$link="";
			$type="normal";
			$message_to_reply="";
			$button="T?? V???N NGAY";
			$buttongapnhanvien="G??p NV T?? V???n";

			$tag="tu_van_ngay";
			$buttonstop="H???n Ch??? Nh???n Tin";
			$taggapnhanvien="gap_nhan_vien_tu_van";
			$tagstop="ngung_nhan_tin";

			$message=htmlspecialchars_decode($content);
			/*
			$message=str_replace('{title}',$title,$message);
			if(isset($request->images) && $request->images!=""){
				$images=$request->images;
			}
			if(isset($request->link) && $request->link!=""){
				$link=$request->link;
			}
			if(isset($request->type) && $request->type!=""){
				$type=$request->type;
			}
			if(isset($request->button) && $request->button!=""){
				$button=$request->button;
				$tag=$this->convert_vi_to_en2($button);
			}
			if(isset($request->keyword) && $request->keyword!=""){
				$tag=$request->keyword;	
			} */
			$url = 'https://graph.facebook.com/v4.0/me/messages?access_token='.$token;
			$ch = curl_init($url);
			if($type=="normal"){
				$jsonData = '{
					"recipient":{
						"id":"'.$sender.'"
					},
					"message":{
						"text":"'.$message.'",
					},
					"messaging_type": "MESSAGE_TAG",
					"tag": "ACCOUNT_UPDATE"
				}';
			}else{
				if($link==""){
					$jsonData = '{
						"recipient":{
							"id":"'.$sender.'"
						},
						"message":{
							"attachment":{
								"type":"'.$type.'",
								"payload":{
									"template_type":"button",
									"text":"'.$message.'",
									"buttons":[
										{
											"type":"postback",
											"title":"'.$button.'",
											"payload":"'.$tag.'"
										},
										{
											"type":"postback",
											"title":"'.$buttonstop.'",
											"payload":"'.$tagstop.'"
										},
										{
											"type":"postback",
											"title":"'.$buttongapnhanvien.'",
											"payload":"'.$taggapnhanvien.'"
										} 
									]
								}
							}
						},
						"messaging_type": "MESSAGE_TAG",
						"tag": "ACCOUNT_UPDATE"
					}';
				}else{
					$jsonData = '{
						"recipient":{
							"id":"'.$sender.'"
						},
						"message":{
							"attachment":{
								"type":"'.$type.'",
								"payload":{
									"template_type":"generic",
									"elements":[
										{
										 "title":"'.$fullname.'",
										 "image_url":"'.$images.'",
										 "subtitle":"'.$message.'",
										 "default_action": {
										   "type": "web_url",
										   "url": "'.$link.'",
										   "webview_height_ratio": "tall",
										 },
										 "buttons":[
										   {
											 "type":"postback",
											 "title":"'.$button.'",
											 "payload":"'.$tag.'"
										   },
										   {
												"type":"postback",
												"title":"'.$buttongapnhanvien.'",
												"payload":"'.$taggapnhanvien.'"
										   },
										   {
											"type":"postback",
											"title":"'.$buttonstop.'",
											"payload":"'.$tagstop.'"
											}
										 ]      
									   }
									 ]
								}
							}
						},
						"messaging_type": "MESSAGE_TAG",
						"tag": "ACCOUNT_UPDATE"
					}';
				}
				
			}
			//Encode the array into JSON.
			$jsonDataEncoded = $jsonData;
			//Tell cURL that we want to send a POST request.
			curl_setopt($ch, CURLOPT_POST, 1);
			//Attach our encoded JSON string to the POST fields.
			curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
			//Set the content type to application/json
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			//Execute the request
			$reponsive = array();
			//if( !empty($input['entry'][0]['messaging'][0]['message']) ){
			$reponsive = curl_exec($ch);
            if($reponsive === FALSE) {
                die(curl_error($ch));
            }
            curl_close($ch);
			//}
    
			
			//Update seting
			/*
			$datamessenger = array(
				'sender_id' =>$page,
				'receive_id' =>$sender,
				'messenger'=>$message,
				'type_send'=>"FacebookMarketing",
				'page_id'=>$page,
				'status'=>$reponsive,
				'date_create'=>date("Y-m-d H:i:s"),
				'timechat'=>time()
			); */
            $res = array(
                'message' => $message,
                'reponsive' => $reponsive,
            );
			$Chatbox = new Chatbox;
			if($message!=""){
					$Chatbox->sender_id = $page;
					$Chatbox->receive_id = $sender;
					$Chatbox->messenger = $message;
					$Chatbox->type_send = "FacebookMarketing";
					$Chatbox->page_id = $page;
					$Chatbox->status ="True";
					$Chatbox->date_create=date("Y-m-d H:i:s");
					$Chatbox->timechat=time();
					$Chatbox->save();
					$idchat=$Chatbox->id;
					return response()->json(['success' => 'success', 'id' => $idchat], 200);
                    exit();
			}
			return response()->json(['success' => 'unsuccess', 'id' => 0], 200);
            exit();
				/*
			$result=null;
			if($message!=""){
				$result=Chatbox::insert($datamessenger);
			}
			return response()->json(['notification' => $result], 200); */
		}else{
			return "";
		}
		
	}
}

