<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Models\User;
use App\Models\GroupUser;
use App\Models\Rolespartner;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Support\Facades\App;
use Sentinel;
use Stripe\Util\Set;
use App\Repositories\SentinelAuthAdapter;

class UserController extends Controller {
	protected $user;
	protected $non_read_meeages;
	protected $last_meeages;

	public function __construct() {
		$this->middleware( function ( $request, $next ) {
		    $settings = Settings::getAll();
		    view()->share('settings',$settings);
			if ( Sentinel::check() ) {
                $language = isset($settings['language'])?$settings['language']:'en';
                App::setLocale($language);
				$this->user = Sentinel::getUser();
				$user_data = User::find($this->user->id);
				view()->share( 'user_data', $user_data);
				$group_data = GroupUser::select('group_user.permissions')->join('users','users.group_id', '=', 'group_user.id')->where('users.id',$this->user->id)->groupBy('users.id')->first();
				//if(isset($_GET["test"])){
				//$listRoleLead=Rolespartner::select('slug')->where('function','lead')->get()->toArray();
				$leadpermission=0;
				//$grouparray=$group_data->toArray();
				//$recheckLead=array_udiff($group_data,$listRoleLead,$this->myfunction($group_data,$listRoleLead));
				if($user_data->user_id==1){
					view()->share( 'rolelead',1); 
					view()->share( 'roleinvoice',1); 
					view()->share( 'rolecalendar',1); 
					view()->share( 'roletask',1); 
					view()->share( 'loggedcalls',1); 
					view()->share( 'salesorderrole',1); 
					view()->share( 'productrole',1); 
					view()->share( 'roleconfig',1); 
					view()->share( 'roledashboard',1); 
					view()->share( 'rolestaff',1); 
				}

				if(isset($group_data) && $group_data!="" && $group_data!=null){
					$dashboardpermission=0;
					if($group_data->hasAccess(['dashboard.full']) || $group_data->hasAccess(['dashboard.view_other']) || $group_data->hasAccess(['dashboard.view_person'])){
						$dashboardpermission=1;
					}
					view()->share( 'roledashboard',$dashboardpermission); 

					if($group_data->hasAccess(['leads.full']) || $group_data->hasAccess(['leads.view_other']) || $group_data->hasAccess(['leads.view_person']) || $group_data->hasAccess(['leads.delete_person']) || $group_data->hasAccess(['leads.edit_owner']) || $group_data->hasAccess(['leads.delete_other']) || $group_data->hasAccess(['leads.edit_other'])){
						$leadpermission=1;
					}
					view()->share( 'rolelead',$leadpermission); 
					$configpermission=0;
					if($group_data->hasAccess(['config.full'])){
						$configpermission=1;
					}
					view()->share( 'roleconfig',$configpermission); 
		
					$invoicepermission=0;
					if($group_data->hasAccess(['invoice.full']) || $group_data->hasAccess(['invoice.view_other']) || $group_data->hasAccess(['invoice.view_person']) || $group_data->hasAccess(['invoice.edit_other']) || $group_data->hasAccess(['invoice.edit_owner']) || $group_data->hasAccess(['invoice.delete_other']) || $group_data->hasAccess(['invoice.delete_person'])){
						$invoicepermission=1;
					}
					view()->share( 'roleinvoice',$invoicepermission); 
	
					$calendarpermission=0;
					if($group_data->hasAccess(['calendar.full']) || $group_data->hasAccess(['calendar.view_other']) || $group_data->hasAccess(['calendar.view_person']) || $group_data->hasAccess(['calendar.edit_other']) || $group_data->hasAccess(['calendar.edit_owner']) || $group_data->hasAccess(['calendar.delete_other']) || $group_data->hasAccess(['calendar.delete_person'])){
						$calendarpermission=1;
					}
					view()->share( 'rolecalendar',$calendarpermission); 
		
					$taskpermission=0;
					if($group_data->hasAccess(['task.full']) || $group_data->hasAccess(['task.view_other']) || $group_data->hasAccess(['task.view_person']) || $group_data->hasAccess(['task.edit_other']) || $group_data->hasAccess(['task.edit_owner']) || $group_data->hasAccess(['task.delete_other']) || $group_data->hasAccess(['task.delete_person'])){
						$taskpermission=1;
					}
					view()->share( 'roletask',$taskpermission); 
	
					$loggedcallspermission=0;
					if($group_data->hasAccess(['logged_calls.full']) || $group_data->hasAccess(['logged_calls.view_other']) || $group_data->hasAccess(['logged_calls.view_person'])){
						$loggedcallspermission=1;
					}
					view()->share( 'loggedcalls',$loggedcallspermission); 
	
					$salesorderpermission=0;
					if($group_data->hasAccess(['sales_order.full']) || $group_data->hasAccess(['sales_order.view_other']) || $group_data->hasAccess(['sales_order.view_person']) || $group_data->hasAccess(['sales_order.edit_other']) || $group_data->hasAccess(['sales_order.edit_owner']) || $group_data->hasAccess(['sales_order.delete_other']) || $group_data->hasAccess(['sales_order.delete_person'])){
						$salesorderpermission=1;
					}
					view()->share( 'salesorderrole',$salesorderpermission); 
	
					$productspermission=0;
					if($group_data->hasAccess(['products.write']) || $group_data->hasAccess(['products.view']) || $group_data->hasAccess(['products.delete'])){
						$productspermission=1;
					}
					view()->share( 'productrole',$productspermission); 

					$staffpermission=0;
					if($group_data->hasAccess(['staff.write']) || $group_data->hasAccess(['staff.view']) || $group_data->hasAccess(['staff.delete'])){
						$staffpermission=1;
					}
					view()->share( 'rolestaff',$staffpermission); 

					
				}
				//}

				//$listPermission=null;
				$group_dataList="";
				view()->share( 'group_data',$group_data);

				$this->non_read_meeages = Email::where( 'to', $this->user->id )->where( 'read', '0' )->count();
				view()->share( 'non_read_meeages', $this->non_read_meeages );
				$this->last_meeages = Email::where( 'to', $this->user->id )->limit( 5 )->get();
				view()->share( 'last_meeages', $this->last_meeages );

				config(['settings.date_format' => Settings::get('date_format')]);
                config(['settings.time_format' => Settings::get('time_format')]);
                config(['settings.date_time_format' => Settings::get('date_format').' '.Settings::get('time_format')]);

				view()->share( 'jquery_date', Settings::get( 'jquery_date' ) );
				view()->share( 'jquery_date_time', Settings::get( 'jquery_date_time' ) );

			} else {
				Sentinel::logout( null, true );

				return redirect( 'signin' )->send();
			}

			return $next( $request );
		} );
	}
	public function convertDate($date){
		$dateList=explode("/",$date);
		if(count($dateList)>0){
			return $dateList[2]."-".$dateList[0]."-".$dateList[1];

		}else{
			return '';
		}
	}
	public function myfunction($a,$b)
	{
		if ($a===$b)
		{
		return 0;
		}
		return ($a>$b)?1:-1;
	}
}
