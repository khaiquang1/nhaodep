<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    @session_start();
    require_once "connect.php";

   $result=@mysqli_query($conn,"SELECT * FROM page_partner WHERE update_syn='0' and status=1");
   if($result){
       /* fetch associative array */
       while ($row = $result->fetch_assoc()) {
           $page_id= $row["page_id"];
           $token= $row["token"];
           $update_syn= $row["update_syn"];
           mysqli_query($conn,"update salesdy.config_datas set token='".$token."' where page_id='".$page_id."'");
           mysqli_query($conn,"update page_partner set update_syn='1' where id='".$row["id"]."'");
       }
   }
   @mysqli_free_result($result);
    @mysqli_close($conn);    // The OAuth 2.0 client handler helps us manage access tokens
    ?>
