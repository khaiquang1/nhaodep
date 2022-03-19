<?php namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
	protected $guarded = ['id'];
	protected $dates = ['deleted_at'];
    protected $table = 'notifications';

	/*
    private $title;
	private $message;
	private $image_url;
	private $action;
	private $action_destination;
	private $data;
	private $lead_id;
	private $product_name;
	private $type;
	private $time;

	function __construct(){
         
	}

	public function setProductName($title){
		$this->product_name = $title;
	}
	public function setType($setType){
		$this->type = $setType;
	}
	public function setTime($time){
		$this->time = $time;
	}
	public function setTitle($title){
		$this->title = $title;
	}
	public function setLead($id){
		$this->lead_id = $lead_id;
	}
 
	public function setMessage($message){
		$this->message = $message;
	}
 
	public function setImage($imageUrl){
		$this->image_url = $imageUrl;
	}

	public function setAction($action){
		$this->action = $action;
	}
 
	public function setActionDestination($actionDestination){
		$this->action_destination = $actionDestination;
	}
 
	public function setPayload($data){
		$this->data = $data;
	}
	
	public function getNotificatin(){
		$notification = array();
		$notification['title'] = $this->title;
		$notification['lead_id'] = $this->lead_id;
		$notification['product_name'] = $this->product_name;
		$notification['type'] = $this->type;
		$notification['time'] = $this->time;
		$notification['message'] = $this->message;
		$notification['image'] = $this->image_url;
		$notification['action'] = $this->action;
		$notification['action_destination'] = $this->action_destination;
		return $notification;
	} */
}