<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    @session_start();
    require_once "connect.php";
   $result=@mysqli_query($conn,"select * from salesdy.cookie_map where update_cookie=0");
   if($result){

       /* fetch associative array */
       while ($row = $result->fetch_assoc()) {
           $cookie_id=$row["cookie"];
           $psid=$row["psid"];
           $id=$row["id"];
           echo "update salesdy.leads set cookie_id='".$cookie_id."' where psid='".$psid."' and cookie_id=''";
           @mysqli_query($conn,"update salesdy.leads set cookie_id='".$cookie_id."', function='facebookMessengerWebsite' where psid='".$psid."' and (cookie_id='' or ISNULL(cookie_id))");
           @mysqli_query($conn,"update salesdy.cookie_map set update_cookie='1' where id='".$id."'");
       }
   }
   @mysqli_free_result($result);
    @mysqli_close($conn);    // The OAuth 2.0 client handler helps us manage access tokens
    ?>
