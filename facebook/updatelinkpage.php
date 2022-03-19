<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    @session_start();
    require_once "connect.php";
   $result=@mysqli_query($conn,"SELECT salesdy.leads.id, salesdy.leads.psid, config_datas.token as token FROM salesdy.leads join salesdy.config_datas on salesdy.leads.page_id=salesdy.config_datas.page_id WHERE (salesdy.leads.UTM_Source='facebookMessenger' or salesdy.leads.UTM_Source='FacebookMessenger') and salesdy.leads.psid!='' and (salesdy.leads.photos='' or salesdy.leads.photos IS NULL) and (salesdy.leads.page_id='1926370057686628' or salesdy.leads.page_id='2055072568039460') order by salesdy.leads.id desc limit 0,100");
   if($result){
      
       /* fetch associative array */
       while ($row = $result->fetch_assoc()) {
           $leadid= $row["id"];
           $psid= $row["psid"];
           $token= $row["token"];
           if($token!="" && $leadid!="" && $psid){ $profile=json_decode(file_get_contents("https://graph.facebook.com/".$psid."?fields=first_name,last_name,profile_pic,gender&access_token=".$token));
               $needle = "HTTP request failed";
               $haystack =$profile;
               if (strpos($haystack, $needle) !== true){
                   $photo=$profile->profile_pic;
                   $fullname=$profile->first_name." ".$profile->last_name;
                   if($photo!=""){
                       @mysqli_query($conn,"update salesdy.leads set photos='".$photo."', opportunity='".$fullname."' where id='".$leadid."'");
                   }
               }
           }
       }
   }
   @mysqli_free_result($result);
    @mysqli_close($conn);    // The OAuth 2.0 client handler helps us manage access tokens
    ?>
