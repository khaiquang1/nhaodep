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
    $str = preg_replace("/(K)/", 'k', $str);
    $str = preg_replace("/(T)/", 't', $str);
    $str = preg_replace("/(Q)/", 'q', $str);
    $str = preg_replace("/(V)/", 'v', $str);
    $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
    $str = preg_replace("/(Đ)/", 'D', $str);
    return $str;
}
    
function validate_phone_number($char)
{
    $phone="";
    if(strlen($char)>5){
        $mess=explode(" ",$char);
        for($i=0;$i<count($mess);$i++)
        {
            $phone=checkPhoneNumber($mess[$i]);
        }
    }
    return $phone;
}
    
function checkPhoneNumber($phone, $extention="+84"){
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
    
function validate_email($char)
{
    if(strlen($char)>5){
        $stringProcess=explode(" ",$char);
        if(count($stringProcess)>0){
            for($i=0;$i<count($stringProcess);$i++){
                if (filter_var($stringProcess[$i], FILTER_VALIDATE_EMAIL)) {
                    return $stringProcess[$i];
                }
            }
            return "";
        }
    }
    return "";
    
}
function addLead($phone, $email, $sendid="", $receive_id="", $parner_id, $fullname,  $source="", $pageid=""){
    $curl = curl_init();
    $cookie="";
    curl_setopt_array($curl, array(
                                   CURLOPT_URL => "https://api.fastercrm.com/api/add_lead_api",
                                   CURLOPT_RETURNTRANSFER => true,
                                   CURLOPT_ENCODING => "",
                                   CURLOPT_MAXREDIRS => 10,
                                   CURLOPT_TIMEOUT => 0,
                                   CURLOPT_FOLLOWLOCATION => false,
                                   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                   CURLOPT_CUSTOMREQUEST => "POST",
                                   CURLOPT_POSTFIELDS => "phone=".$phone."&email=".$email."&psid=".$sendid."&page_id=".$pageid."&fullname=".$fullname."&sender=".$receive_id."&partner_id=".$parner_id."&utm_source=FacebookMessenger&utm_medium=".$sendid."&utm_campagin=FacebookPage&&timestamp=".time()."&url=&utm_content=&utm_term=&GCLID=&FBCLID=&PID=&PSID=&Token=".$Token."&cookie_id=".$cookie."&source=FacebookMessenger",
                                   CURLOPT_HTTPHEADER => array("Content-Type: application/x-www-form-urlencoded"),
                                   )
                      );
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    
}
    function addLeadMessenger($sendid="", $pageid="", $urlconversion="", $source=""){
        $cookie="";
        if(is_file('cookie/log'.$sendid.$pageid.'.txt')){
            $cookie=@file_get_contents('cookie/log'.$sendid.$pageid.'.txt');
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
                                       CURLOPT_URL => "https://api.fastercrm.com/api/add_lead_facebook_messenger",
                                       CURLOPT_RETURNTRANSFER => true,
                                       CURLOPT_ENCODING => "",
                                       CURLOPT_MAXREDIRS => 10,
                                       CURLOPT_TIMEOUT => 0,
                                       CURLOPT_FOLLOWLOCATION => false,
                                       CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                       CURLOPT_CUSTOMREQUEST => "POST",
                                       CURLOPT_POSTFIELDS => "psid=".$sendid."&page_id=".$pageid."&utm_campagin=".$pageid."&url=".$urlconversion."&cookie_id=".$cookie."&source=".$source,
                                       CURLOPT_HTTPHEADER => array("Content-Type: application/x-www-form-urlencoded"),
                                       )
                          );
        $response = curl_exec($curl);

        $err = curl_error($curl);
        curl_close($curl);
        
    }
$hub_verify_token = null;
if( !empty($_REQUEST['hub_challenge']) ) {
    $challenge = $_REQUEST['hub_challenge'];
    $hub_verify_token = $_REQUEST['hub_verify_token'];
    if ( $hub_verify_token === $verify_token ) {
        echo $challenge;
    }
} else {
    $partner_id=0;
    $parameter = file_get_contents("php://input");

    $input = json_decode($parameter, true);
    $page_id = $input['entry'][0]['id'];
    $token="";
    if($page_id){
        $filecontent=file_get_contents('partner/'.$page_id.'.txt');
        if(is_file('partner/'.$page_id.'.txt')){
            $filecontent=json_decode(file_get_contents('partner/'.$page_id.'.txt'));
            $partner_id=$filecontent->partner_id;
            $token=$filecontent->token;
        }else{
            $result=@mysqli_query($conn,"SELECT * FROM page_partner WHERE page_id='".$page_id."' and status=1 limit 1");
            if($result){
                    $row = $result->fetch_assoc();
                    $token=$row["token"];
            }
            @mysqli_free_result($result);
            if($partner_id>0){
                @file_put_contents('partner/'.$page_id.'.txt', json_encode(array("page_id"=>$page_id, "token"=>$token)));
            }
        }
    }
    
    $sender = $input['entry'][0]['messaging'][0]['sender']['id'];
    $receive_id = $input['entry'][0]['messaging'][0]['recipient']['id'];
    $message = $input['entry'][0]['messaging'][0]['message']['text'];
    $postback = strtolower($input['entry'][0]['messaging'][0]['postback']['payload']);
    $title = $input['entry'][0]['messaging'][0]['postback']['title'];
    $tagPostback = $input['entry'][0]['messaging'][0]['message']['metadata'];
    $delivery = $input['entry'][0]['messaging'][0]['delivery']['watermark'];
    $recipient = $input['entry'][0]['messaging'][0]['recipient']['id'];
    $readtime = $input['entry'][0]['messaging'][0]['read']['watermark'];
    $timestamp = $input['entry'][0]['messaging'][0]['timestamp'];
    $timesend = $input['entry'][0]['time'];
    $referral = $input['entry'][0]['messaging'][0]['referral']['ref'];
    $source = $input['entry'][0]['messaging'][0]['referral']['source'];
    $type = $input['entry'][0]['messaging'][0]['referral']['type'];
    @file_put_contents('chatlog/'.$page_id."-".$sender."-".$receive_id."-".time().'.txt', $parameter);

    if(isset($readtime) && $readtime!=""){
      @mysqli_query($conn,"insert into messenger_read(sender_id, receive_id, page_id, time_send, timestamp, time_read) values('".$sender."', '".$receive_id."', '".$page_id."', '".$timesend."', '".$timestamp."', '".$readtime."') ON DUPLICATE KEY UPDATE number_view = number_view + 1;");
    }
    if (!empty($postback)) {
          $message=$postback;
    }
    if($referral!=""){
        //$filecontent=file_get_contents('cookie/log'.$sender.$page_id.'.txt');
       // if(!is_file('cookie/log'.$sender.'.txt')){
            @file_put_contents('cookie/log'.$sender.$page_id.'.txt', $referral);
            @mysqli_query($conn,"insert into messenger_cookie(psid, page_id, refer, source, type, date_time) values('".$sender."', '".$page_id."', '".$referral."', '".$source."', '".$type."', '".date("Y-m-d H:i:s")."') ON DUPLICATE KEY UPDATE number_view = number_view + 1;");
            @mysqli_query($conn,"insert into salesdy.cookie_map(psid, cookie, date_create) values('".$sender."', '".$referral."', '".date("Y-m-d H:i:s")."') ON DUPLICATE KEY UPDATE number_view = number_view + 1;");
       // }
    }
    
    if(!empty($message)){
            //API Url
            //Initiate cURL.
            $tagname='';
            $contactroom="";
            $tag="";
            //Check file get content
            $filecontent="";
            if(!empty($tagPostback) ){
                file_put_contents('chatlog/log'.$sender.$receive_id.'.txt', $message);
                $taglist=explode(":",$tagPostback);
                $contactroom=trim($taglist[0]);
                $tagname=strtolower(trim($taglist[1]));
            }
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
                
        //API Url
            //Initiate cURL.
            $tagname='';
            $contactroom="";
            $tag="";
            //Check file get content
            $filecontent="";
        
            if(!empty($tagPostback) ){
                file_put_contents('chatlog/log'.$sender.$receive_id.'.txt', $message);
                $taglist=explode(":",$tagPostback);
                $contactroom=trim($taglist[0]);
                $tagname=strtolower(trim($taglist[1]));
            }
        
            $type="normal";
            $mess=explode(" ",$message);
            $phone=validate_phone_number($message);
            $email=validate_email($message);
            $fullname="";
           //
            if(!is_file('profile/'.$sender.'.txt')){ $profile=json_decode(file_get_contents("https://graph.facebook.com/".$sender."?fields=first_name,last_name,profile_pic,gender&access_token=".$token));
                @file_put_contents('profile/'.$sender.'_.txt', "https://graph.facebook.com/".$sender."?fields=first_name,last_name,profile_pic&access_token=".$token);
                $fullname=$profile->first_name." ".$profile->last_name;
               @file_put_contents('profile/'.$sender.'.txt', $fullname);
            }else{
                $fullname=file_get_contents('profile/'.$sender.'.txt');;
            }
            if($email!="" || $phone!=""){
                addLead($phone, $email, $sender, $receive_id, $partner_id, $fullname, $source, $page_id);
            }
            $psid=$sender;
            if($page_id==$sender){
                $psid=$receive_id;
            }
            if($psid!="" && $page_id!="" ){
                if(!is_file('lead/'.$page_id."_".$psid.'.txt')){
                    $conversion =json_decode(file_get_contents("https://graph.facebook.com/v6.0/".$page_id."/conversations?user_id=".$psid."&access_token=".$token));
                      $urlconversion=$conversion->data[0]->link;
                     @file_put_contents('lead/'.$page_id."_".$psid.'.txt', $urlconversion);
                }else{
                     $urlconversion=file_get_contents('lead/'.$page_id.'_'.$psid.'.txt');
                }
                addLeadMessenger($psid, $page_id, $urlconversion, $source);

            }
            @mysqli_query($conn,"insert into messenger(sender_id, receive_id, messenger, messenger_reply, type_send, extention, partner_id, mess_read, mess_recipient, title, refer, source, type_messenger) values('".$sender."', '".$receive_id."', '".$message."', '".$message_to_reply."', '".$type."', '".$phone."', '".$partner_id."', '".$readtime."','".$recipient."','".$title."','".$referral."','".$source."','".$type."')");
        
        @mysqli_query($conn,"insert into salesdy.chat_box(sender_id, receive_id, messenger, messenger_reply, type_send, extention, partner_id, mess_read, mess_recipient, title, refer, source, type_messenger, date_create) values('".$sender."', '".$receive_id."', '".$message."', '".$message_to_reply."', '".$type."', '".$phone."', '".$partner_id."', '".$readtime."','".$recipient."','".$title."','".$referral."','".$source."','".$type."','".date("Y-m-d H:i:s")."')");

            $result=@mysqli_query($conn,"SELECT * FROM messeger_content WHERE page_id='".$page_id."' and keyword='".$message."' order by rand() limit 1");
            if($result){
                $row = $result->fetch_assoc();
                $content_reply=$row["content"];
                $button_text_next=$row["button_text_next"];
                $keyword_text_next=$row["keyword_text_next"];
                $type_button_next=$row["type_button_next"];
                $url="https://api.fastercrm.com/api/send_messenger";
                $data = array("facebook_messenger_id"=>$sender,
                              "content"=>$content_reply,
                              "name"=>$fullname,
                              "button"=>$button_text_next,
                              "type"=>$type_button_next,
                              "keyword"=>$keyword_text_next);
                $datapost=json_encode($data);
                $server_key="1291";
                /*
                $headers = array(
                                 'Content-Type:application/json',
                                 'Authorization:key='.$server_key
                ); */
                $ch = curl_init();
                /*
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $datapost); */
                
                curl_setopt_array($ch, array(
                  CURLOPT_URL => "https://api.fastercrm.com/api/send_messenger",
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => "",
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 0,
                  CURLOPT_FOLLOWLOCATION => true,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => "POST",
                  CURLOPT_POSTFIELDS =>$data,
                ));
                // Execute post
                $result = curl_exec($ch);
                // Close connection
                curl_close($ch);
                @mysqli_query($conn,"insert into messenger_reply(psid, page_id, content_reply, date_create) values('".$sender."', '".$page_id."', '".$message."', '".date("Y-m-d H:i:s")."')");
            }
            @mysqli_close($conn);
            @mysqli_free_result($result);
    }else{
        echo "Not access here";
    }
}
?>
