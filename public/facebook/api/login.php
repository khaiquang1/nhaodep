<?php
if(isset($_POST)){
	$data =$_POST;
	if($data['id'])
	{
		setcookie('email', $data['email'], time() + 3600);
		setcookie('user_id', $data['id'], time() + 3600);
	}
	die(json_encode(array('success'=>true, 'id'=>$data['id']))) ;
}
?>