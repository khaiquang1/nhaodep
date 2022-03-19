<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    @session_start();
    include_once 'connect.php';
    include 'config.php';
    $result2=@mysqli_query($conn,"SELECT *  FROM list_contact WHERE process=0 limit 0,50");
    if($result2){
        $idlist=null;
        while ($row2 = $result2->fetch_assoc()) {
            $idcontact=$row2["contact_id"];
            $idlist[]=$row2["id"];
           $curl = curl_init();
           curl_setopt_array($curl, array(CURLOPT_URL => "https://api.caresoft.vn/Lavender/api/v1/contacts/".$idcontact,
             CURLOPT_RETURNTRANSFER => true,
             CURLOPT_ENCODING => "",
             CURLOPT_MAXREDIRS => 10,
             CURLOPT_TIMEOUT => 0,
             CURLOPT_FOLLOWLOCATION => true,
             CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
             CURLOPT_CUSTOMREQUEST => "GET",
             CURLOPT_HTTPHEADER => array(
               "Content-Type: application/json",
               "Authorization: Bearer 4O8VeUnUVOMlNL4",
               "Cookie: laravel_session=eyJpdiI6Inl1eFBKb2ducjFnMnFhRytVWXpDT1E9PSIsInZhbHVlIjoiK25zQnJFZ0hPeEZwbzl6eHNEWnV3a1Ntc28xczR1M1owcFdZeDJwbWNrRE5wVUxKQkIxZVhKNVZyZldDQ3hVTEtWUHZySzJaZXlxczA5a0pIclZlNVE9PSIsIm1hYyI6Ijc0YmE1ZmQwM2M2MGQ0ZjRhYTc5YjRlOGZiNzU2NjQ4NmRlZDI2YjNhZGQ4YzFmNTYzMzc0ZGUwMDUzOGU3NmUifQ%3D%3D"
             ),
           ));
           $response = curl_exec($curl);
           curl_close($curl);
           $contactlist=json_decode($response);
            
           if($contactlist->code=="ok"){
               $contactdetail=$contactlist->contact;
               $account_id=$contactdetail->account_id;
             
               $contact_id=$contactdetail->id;
               $username=$contactdetail->username;
               $email=$contactdetail->email;
               $email2=$contactdetail->email2;
               $phone_no=$contactdetail->phone_no;
               $phone_no2=$contactdetail->phone_no2;
               $phone_no3=$contactdetail->phone_no3;
               $facebook=$contactdetail->facebook;
               $gender=$contactdetail->gender;
               $organization_id=$contactdetail->organization_id;
               $created_at=$contactdetail->created_at;
               $updated_at=$contactdetail->updated_at;
               $role_id=$contactdetail->role_id;
               $custom_fields=$contactdetail->custom_fields;
               $organization=$contactdetail->organization;
               $result=mysqli_query($conn,"insert into contacts(account_id, id_contact, username, email, email2, phone_no, phone_no2, phone_no3, facebook, gender, organization_id, created_at, updated_at, role_id, custom_fields, organization) values('".$account_id."', '".$contact_id."', '".$username."', '".$email."', '".$email2."', '".$phone_no."', '".$phone_no2."', '".$phone_no3."', '".$facebook."', '".$gender."', '".$organization_id."', '".$created_at."', '".$updated_at."', '".$role_id."', '".$custom_fields."', '".$organization."') ON DUPLICATE KEY UPDATE email ='".$email."';");
           }
        }
        //echo "update list_contact set process=1 where id in(".implode(",",$idlist).")");
        if(count($idlist)>0){
        $result=@mysqli_query($conn,"update list_contact set process=1 where id in(".implode(",",$idlist).")");
        @mysqli_free_result($result);
        }
       @mysqli_close($conn);
    }
    die();
   
    ?>
