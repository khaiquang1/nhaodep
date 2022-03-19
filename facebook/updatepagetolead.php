<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    @session_start();
    include_once 'connect.php';
    include 'config.php';
    $result=@mysqli_query($conn,"update salesdy.leads set page_id=(select receive_id from messenger where salesdy.leads.psid=sender_id limit 1) where psid!='' and psid!=0 and page_id!=0");
   @mysqli_free_result($result);
    @mysqli_close($conn);    // The OAuth 2.0 client handler helps us manage access tokens
    ?>
