<?php
/**
 * Webhook for Time Bot- Facebook Messenger Bot
 */
include 'config.php';
$hub_verify_token = null;

if(isset($_REQUEST['hub_challenge'])) {
    $challenge = $_REQUEST['hub_challenge'];
    $hub_verify_token = $_REQUEST['hub_verify_token'];
}

if ($hub_verify_token === $verify_token) {
    echo $challenge;
}
$data=$_POST;
$sender="2087067324642410";
$message="";
if(isset($data) && $data['send']!="" && $data['send']!=null){
	//$sender=$data['send'];
	$message=urldecode($data['messenger']);
}
//$sender = ;//$input['entry'][0]['messaging'][0]['sender']['id'];
//$input['entry'][0]['messaging'][0]['message']['text'];
$message_to_reply = 'Xin lỗi, Tôi không hiểu bạn nói gì. Bạn có thể đặt lại câu hỏi cho tôi được không?';
$key1=0;
$listtextcauhoimodau=strtolower('[Chào ad|Chào bạn|hi|chào Ureka|Hello|Xin chào]');
$listconvertmodau=convert_vi_to_en($listtextcauhoimodau);
if(preg_match($listtextcauhoimodau, strtolower($message)) || preg_match($listconvertmodau, strtolower($message))) {
	$message_to_reply = "Chào bạn, Ureka Media có thể giúp gì cho bạn?";
	$key1=1;
} 
if($key1==0){
	$listtextcauhoisaucung=strtolower('[Vâng, cảm ơn bạn|Dạ, cảm ơn ad|Vâng, cảm ơn ad|Dạ, cảm ơn bạn|Ok, cảm ơn nhé|Ok, tks bạn|Cảm ơn bạn nhiều|Cảm ơn ad nhé]');
	$listconvertsaucung=convert_vi_to_en($listtextcauhoisaucung);
	if(preg_match($listtextcauhoisaucung, strtolower($message)) || preg_match($listconvertsaucung, strtolower($message))) {
		$message_to_reply = "Dạ, chào bạn. Nếu có gì còn chưa rõ bạn có thể pm cho Ureka Media hoặc gọi hotline: 1900 588 888 để được tư vấn kịp thời nhé. Một lần nữa cảm ơn bạn đã quan tâm!";
		$key1=1;
	} 
}

//Content Nhóm tư vấn dịch vụ				
if($key1==0){
	$listtextcauhoicontent=strtolower('[Bên mình cần tư vấn dịch vụ facebook|Bên mình cần thực hiện chiến dịch quảng cáo trên|Mình muốn hỏi báo giá|cần chạy quảng cáo trên Skype|cần chạy quảng cáo trên LinkedIn|Cần chạy quảng cáo trên GDN|cần chạy quảng cáo trên Facebook|thực hiện chiến dịch digital marketing|thực hiện chiến dịch online marketing|muốn làm quảng cáo video|muốn làm quảng cáo trueview|Facebook]');
	$listconvertcontent=convert_vi_to_en($listtextcauhoicontent);
	if(preg_match($listtextcauhoicontent, strtolower($message)) || preg_match($listconvertcontent, strtolower($message))) {
		$message_to_reply = "Dạ, rất cảm ơn bạn quan tâm. Bạn có thể cho mình thông tin liên lạc (tên, số điện thoại, email, khu vực hoạt động) để mình chuyển cho bộ phận account phụ trách từng khu vực liên hệ tư vấn cho bạn được tiện nhất ạ!";
		$key1=1;
	} 
}
//Content Nhóm Nhóm từ chối dịch vụ			
if($key1==0){
	$listtextcauhoicontent=strtolower('[muốn đóng dấu xanh Facebook|muốn gắn tick xanh Facebook|muốn verify fanpage|muốn làm video clip|muốn thiết kế web|muốn thiết kế	]');
	$listconvertcontent=convert_vi_to_en($listtextcauhoicontent);
	if(preg_match($listtextcauhoicontent, strtolower($message)) || preg_match($listconvertcontent, strtolower($message))) {
		$message_to_reply = "Dạ, rất cảm ơn bạn đã quan tâm. Tuy nhiên, Ureka Media bên mình không có dịch vụ này bạn nhé. Cảm ơn bạn!";
		$key1=1;
	} 
}

//API Url
$url = 'https://graph.facebook.com/v2.11/me/messages?access_token='.$access_token;

//Initiate cURL.
$ch = curl_init($url);
//The JSON data.
$jsonData = '{
    "recipient":{
        "id":"'.$sender.'"
    },
    "message":{
        "text":"'.$message_to_reply.'"
    }
}';

//Encode the array into JSON.
$jsonDataEncoded = $jsonData;
//Tell cURL that we want to send a POST request.
curl_setopt($ch, CURLOPT_POST, 1);
//Attach our encoded JSON string to the POST fields.
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
//Set the content type to application/json
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
//Execute the request
$reponsive = array();
if( !empty($message) ){
    $reponsive = curl_exec($ch);
}
$res = array(
        'message' => $message,
        'reponsive' => $reponsive,
);
function convert_vi_to_en($str) {
  $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
  $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
  $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
  $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
  $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
  $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
  $str = preg_replace("/(đ)/", 'd', $str);
  $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
  $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
  $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
  $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
  $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
  $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
  $str = preg_replace("/(Đ)/", 'D', $str);
  return $str;
}
echo die(json_encode(array('success'=>true, 'messenger'=>$res)));
