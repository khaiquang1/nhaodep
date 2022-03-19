<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/**
 * Webhook for Time Bot- Facebook Messenger Bot
 */
include_once 'connect.php';
include 'config.php';
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
function validate_phone_number($phone)
{
    // Allow +, - and . in phone number
    $filtered_phone_number = filter_var($phone, FILTER_SANITIZE_NUMBER_INT);
    // Remove "-" from number
    $phone_to_check = str_replace("-", "", $filtered_phone_number);
    // Check the lenght of number
    // This can be customized if you want phone number from a specific country
    if (strlen($phone_to_check) < 10 || strlen($phone_to_check) > 14) {
        return false;
    } else {
        return true;
    }
}
function addLead($phone, $sendid){
    $curl = curl_init();
    $Token="namthanhapp";
    curl_setopt_array($curl, array(
                                   CURLOPT_URL => "https://crm.etrip4u.com/api/add_lead_api",
                                   CURLOPT_RETURNTRANSFER => true,
                                   CURLOPT_ENCODING => "",
                                   CURLOPT_MAXREDIRS => 10,
                                   CURLOPT_TIMEOUT => 0,
                                   CURLOPT_FOLLOWLOCATION => false,
                                   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                   CURLOPT_CUSTOMREQUEST => "POST",
                                   CURLOPT_POSTFIELDS => "phone=".$phone."&fullname=".$sendid."&sender=".$sendid."&product_id=1&utm_source=FacebookMessenger&utm_medium=Page&utm_campagin=FacebookPage&timestamp=".time()."&url=&utm_content=&utm_term=&GCLID=&FBCLID=&PID=&PSID=&Token=".$Token."&cookie_id=",
                                   CURLOPT_HTTPHEADER => array("Content-Type: application/x-www-form-urlencoded"),
                                   )
                      );
    //

    
    
}
$hub_verify_token = null;
if( !empty($_REQUEST['hub_challenge']) ) {
    $challenge = $_REQUEST['hub_challenge'];
    $hub_verify_token = $_REQUEST['hub_verify_token'];
    if ( $hub_verify_token === $verify_token ) {
	    echo $challenge;
	}
} else {
	$parameter = file_get_contents("php://input");
    $input = json_decode($parameter, true);
	file_put_contents('log.log', json_encode(array('parameter' => $parameter, 'input' => $input)), FILE_APPEND);
	$sender = $input['entry'][0]['messaging'][0]['sender']['id'];
	$receive_id = $input['entry'][0]['messaging'][0]['recipient']['id'];
	$message = $input['entry'][0]['messaging'][0]['message']['text'];
	$postback = $input['entry'][0]['messaging'][0]['postback']['payload'];
	$tagPostback = $input['entry'][0]['messaging'][0]['message']['metadata'];
	if (!empty($postback)) {
		$message =$postback;
	}
	if(!empty($message)){
			//API Url
			//Initiate cURL.
			$tagname='';
			$contactroom="";
			$tag="";
			//Check file get content
			$filecontent="";
			//if(!empty($tagPostback) ){
				file_put_contents('chatlog/log'.$sender."-".$receive_id.'.txt', $tagPostback);
				$taglist=explode(":",$tagPostback);
				$contactroom=trim($taglist[0]);
				$tagname=strtolower(trim($taglist[1]));
		//	}
			if(is_file('chatlog/log'.$sender.$receive_id.'.txt') && filesize('chatlog/log'.$sender.$receive_id.'.txt')>=1){
				$filecontent='chatlog/log'.$sender.$receive_id.'.txt';
			}elseif(is_file('chatlog/log'.$receive_id.$sender.'.txt') && filesize('chatlog/log'.$receive_id.$sender.'.txt')>=1){
				$filecontent='chatlog/log'.$receive_id.$sender.'.txt';
			}
			if( !empty($filecontent) ){
				$tagtext=file_get_contents($filecontent);
				if($tagtext!=""){
					$taglist=explode(":",$tagtext);
					$contactroom=trim($taglist[0]);
					$tagname=strtolower(trim($taglist[1]));
				}
			}
			$type="normal";
            $message_to_reply="";
        /*
			if( strtolower($message)=="publisher" or strtolower($contactroom)=="publisher" ){
				switch ($tagname) {
					case "name":
						$message_to_reply="Bạn vui lòng cho biết Email của bạn";
						$tag="publisher:email";
						break;
					case "email":
						$message_to_reply="Bạn vui lòng cho biết Số Điện Thoại của bạn";
						$tag="publisher:sodienthoai";
						break;
					case "sodienthoai":
						$message_to_reply="Dạ, rất cảm ơn bạn quan tâm. Để mình chuyển cho bộ phận phụ trách phát triển network liên hệ tư vấn cho bạn được tiện nhất ạ!";
						$tag="publisher:chat";
						break;
					case "chat":
						$message_to_reply = 'Xin lỗi, Tôi không hiểu bạn nói gì. Bạn có thể đặt lại câu hỏi cho tôi được không? ';
						$tag="publisher:name";
						if(strlen($message)<4){
							$result=@mysqli_query($conn,"SELECT description_reply FROM facebook_description WHERE title like '".$message."' or keyword like '".$message."' or title_en like '".$message."' or keyword_en like '".$message."' limit 1");
						}else{
							$result=@mysqli_query($conn,"SELECT description_reply, MATCH (title, keyword, title_en, keyword_en) AGAINST ('+".$message."+') AS relevance FROM facebook_description WHERE MATCH (title, keyword, title_en, keyword_en) AGAINST ('+".$message."+') order by relevance desc limit 1");
						}
						if(mysqli_num_rows($result)>0){
							while($row = mysqli_fetch_assoc($result)){
								$message_to_reply = $row["description_reply"];
								$tag="publisher:chat";
								$type="normal";
							}
							@mysqli_free_result($result);
						}else{
							$type="template";
							$tag="publisher:name";
						}
						break;
					default:
						$message_to_reply="Bạn vui lòng cho biết tên của bạn";
						$tag="publisher:name";
				}
			}else{
				$type="template";
				$message_to_reply = 'Xin lỗi, Tôi không hiểu bạn nói gì. Bạn có thể đặt lại câu hỏi cho tôi được không?';
				if(strlen($message)<4){
					$result=@mysqli_query($conn,"SELECT description_reply FROM facebook_description WHERE title like '".$message."' or keyword like '".$message."' or title_en like '".$message."' or keyword_en like '".$message."' limit 1");
				}else{
					$result=@mysqli_query($conn,"SELECT description_reply, MATCH (title, keyword, title_en, keyword_en) AGAINST ('".$message."' IN BOOLEAN MODE) AS relevance FROM facebook_description WHERE MATCH (title, keyword, title_en, keyword_en) AGAINST ('".$message."' IN NATURAL LANGUAGE MODE) order by relevance desc limit 1");
				}
				if(mysqli_num_rows($result)>0){
					while($row = mysqli_fetch_assoc($result)){
						$message_to_reply = $row["description_reply"];
					}
					@mysqli_free_result($result);
				}
			} */
            $mess=explode(" ",$message);
            if(count($mess)>0){
                for($i=0;$i<count($mess);$i++)
                {
                    $phone=$mess[$i];
                    if(validate_phone_number($phone)){
                        addLead($phone,$sender);
                    }
                }
            }
			@mysqli_query($conn,"insert into messenger(sender_id, receive_id, messenger, messenger_reply, type_send) values('".$sender."', '".$receive_id."', '".$message."', '".$message_to_reply."', '".$type."')");
			@mysqli_close($conn); 
			//The JSON data.
            /*
			$url = 'https://graph.facebook.com/v2.11/me/messages?access_token='.$access_token;
			$ch = curl_init($url);
			if($type=="normal"){
				$jsonData = '{
					"recipient":{
						"id":"'.$sender.'"
					},
					"message":{
						"text":"'.$message_to_reply.'",
						"metadata":"'.$tag.'"
					}
				}';
			}else{
				$jsonData = '{
					"recipient":{
						"id":"'.$sender.'"
					},
					"message":{
						"attachment":{
							  "type":"template",
							  "payload":{
								"template_type":"button",
								"text":"'.$message_to_reply.'",
								"buttons":[
								  {
									"type":"postback",
									"payload":"publisher",
									"title":"Publisher"
								  },
								  {
									"type":"postback",
									"payload":"advertiser",
									"title":"Advertiser / Marketer"
								  },
								  {
									"type":"web_url",
									"url":"https://urekamedia.com/contact",
									"title":"Jobs"
								  },
								]
							  }
						}
					},
					
				}';
			}
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
			//if( !empty($input['entry'][0]['messaging'][0]['message']) ){
				$reponsive = curl_exec($ch);
			//}
			$res = array(
				'message' => $message,
				'reponsive' => $reponsive,
			);	*/
	}else{
		echo "Not access here";
	}
}
