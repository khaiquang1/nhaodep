<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    @session_start();
    include_once 'connect.php';
    include 'config.php';
    $curl = curl_init();
    if(is_file('cron/linkcron.txt')){
        $page=@file_get_contents('cron/linkcron.txt');
        $pageexport=json_decode($page);
        $pagerun=$pageexport->page;
    $link="https://api.caresoft.vn/Lavender/api/v1/contacts?created_since=2020-01-01T00:00:00Z&page=".$pagerun."&count=100&order_by=created_at&order_type=desc";
    }else{
       $pagerun=1; $link="https://api.caresoft.vn/Lavender/api/v1/contacts?created_since=2020-01-01T00:00:00Z&page=1&count=100&order_by=created_at&order_type=desc";
    }
    if($pagerun>=1800){
        exit();
    }
    curl_setopt_array($curl, array(
      CURLOPT_URL =>$link,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer 4O8VeUnUVOMlNL4",
        "Cookie: laravel_session=eyJpdiI6ImFVaXJId2I1ZzlPT1paUWJSSXBrTUE9PSIsInZhbHVlIjoid0VGRXJmSm5OUDFDUTNTMzk1aFNSTk9FbndTVkZ2Yll6NDFydkRjSUJpWXdUUkkwKzdMeHpkUmFGb2taZUMrbU9ybTZuK3NkSWVidlB5dmc2SnBuaGc9PSIsIm1hYyI6Ijc0YzUyMGM3NWE3OTU5YWYwMWNiMTc4OTBlN2NlYzI2MzI2NDU4MGQ0ODFiMjhlNGNkN2Y1ZGY4NDJjMGMwNDEifQ%3D%3D"
      ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    $data=json_decode($response);
    if($data->code=="ok"){
        for($i=0;$i<count($data->contacts);$i++){
            $contactPhone=0;
            $contactId=$data->contacts[$i]->id;
           $contactName=$data->contacts[$i]->username;
           $contactCreate=$data->contacts[$i]->created_at;
           $contactUpdate=$data->contacts[$i]->updated_at;
            if(isset($data->contacts[$i]->phone_no)){
                $contactPhone=$data->contacts[$i]->phone_no;

            }
            @mysqli_query($conn,"insert into list_contact(contact_id, username, updated_at, created_at, phone_no) values('".$contactId."', '".$contactName."', '".$contactUpdate."', '".$contactCreate."', '".$contactPhone."') ON DUPLICATE KEY UPDATE username ='".$contactName."';");
        }
       @mysqli_free_result($result);
       @mysqli_close($conn);
       $jsonwrite=json_encode(array("page"=>$pagerun+1));
       var_dump($jsonwrite);
       @file_put_contents('cron/linkcron.txt', $jsonwrite);
    }
    
    echo $response;
    
    ?>
