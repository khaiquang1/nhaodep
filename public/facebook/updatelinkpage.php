<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    @session_start();
    require_once "connect.php";

   $result=@mysqli_query($conn,"SELECT id,psid FROM salesdy.leads WHERE UTM_Source='facebookMessenger' and psid!=''");
   if($result){
       /* fetch associative array */
       while ($row = $result->fetch_assoc()) {
           $leadid= $row["id"];
           $psid= $row["psid"];
           
           $result2=@mysqli_query($conn,"SELECT receive_id FROM messenger WHERE sender_id=".$psid);
           if($result2){
               while ($row2 = $result2->fetch_assoc()) {
                   echo $row2["receive_id"]."<br/>";
                    echo $leadid."<br/>";
                   mysqli_query($conn,"update salesdy.leads set UTM_Campaign='".$row2["receive_id"]."' where id='".$leadid."'");
               }

           }
       }
   }
   @mysqli_free_result($result);
    @mysqli_close($conn);    // The OAuth 2.0 client handler helps us manage access tokens
    ?>
