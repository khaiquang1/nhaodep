<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    @session_start();
    include_once 'connect.php';
    include 'config.php';
   $result=@mysqli_query($conn,"SELECT messenger.*,page_partner.token as tokenpage  FROM messenger join page_partner on (page_partner.page_id=messenger.receive_id or page_partner.page_id=messenger.sender_id) WHERE messenger.update_profile=0 and messenger.receive_id in(select page_id from page_partner) group by sender_id order by messenger.id desc limit 0,500");
   if($result){
       /* fetch associative array */
       while ($row = $result->fetch_assoc()) {
           $page_id= $row["receive_id"];
           $psid= $row["sender_id"];
           $token=$row["tokenpage"];
           $profile=json_decode(file_get_contents("https://graph.facebook.com/".$psid."?fields=first_name,last_name,profile_pic,gender&access_token=".$token));
           $fullname=$profile->first_name." ".$profile->last_name;
           if($profile->gender=="female"){
               $title="Chị";
           }elseif($profile->gender=="male"){
               $title="Anh";
           }else{
               $title="Anh/Chị";
           }
           @mysqli_query($conn,"insert into salesdy.messenger_partner(psid, title, fullname, page_id) values('".$psid."', '".$title."', '".$fullname."', '".$page_id."') ON DUPLICATE KEY UPDATE title='".$title."';");
           @mysqli_query($conn,"update messenger set update_profile='1' where sender_id='".$psid."'");
           echo $psid."<br/>";
       }
   }
   @mysqli_free_result($result);
    @mysqli_close($conn);    // The OAuth 2.0 client handler helps us manage access tokens
    ?>
