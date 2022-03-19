<?php
    require __DIR__ . '/vendor/autoload.php';
    use FacebookAds\Object\Ad;
    use FacebookAds\Object\Lead;
    use FacebookAds\Api;
    use FacebookAds\Logger\CurlLogger;
    
    
    $access_token = 'EAAK481QSfNIBACc7cYepnnt6gT4pZCIQmYwZCZC41knXQZAAxt3ijAZAdGcJkOwfo2zOFf0evfYZC5Hias7nN1qDndcnZAXDWZC92y6ZCGqFhbWipEqWxaeLZBNqsTM6EHjK1L7PZCZBNwUaeWrdacZCDSFVrpmCwR2VloxmK9IPZAYdmbB6b2CMNcyj5XqnndiT8n8RJayJN0xEnIHtZAhe5FluSDq';
    $app_secret = 'b5091bcba3128b45d944f651362c2b91';
    $app_id = '766305180482770';
    $id = '23843655284770377';
    $page='901537370189587';
    $product_id=13;
    $user_token="EAARMgIC3SRoBAPNhmn0pfWqAGZCfFvQBYLvqLBQ6SiQW1ZA9Or4p1ecAHwaehpSqcDjlZCpRoYqLj2cJgNesixYTr68vo2WgnESyCrMrmE8FNvv9VPP14kL7hfTfZBVCXzso3qbrDoEFpJWytMJDGh7z2DrXxR5NrVkH6GNiMQu9oe6G3VOW";
    
    function getLead($leadgen_id,$access_token) {
        //fetch lead info from FB API
        $graph_url = 'https://graph.facebook.com/v4.0/2700430493311292/leads?pretty=0&limit=25&after=QVFIUnpfMGJVcHpPLXlnNVRtLW1BMks4ZAWswamlkWnEwQ291REZA2akRWdzlLQnEyNjNTTXgxVmRqZAlRycWMwekR4Rlp2eDBDYUhkQ3poYmt0NjYyUk01RlF3';
        //$graph_url = 'https://graph.facebook.com/v4.0/' . $leadgen_id. '/leads?access_token=' . $access_token;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $graph_url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        $leaddata = json_decode($output);
        
        $getDataFetch=array();
        if($leaddata){
            $getDataFetch=$leaddata->data;
        }
        if(count($getDataFetch)>0){
            for($i=0;$i<count($getDataFetch);$i++){
                $created_time=$getDataFetch[$i]->created_time;
                $id=$getDataFetch[$i]->id;
                $field=$getDataFetch[$i]->field_data;
                if($field[0]->name=="email"){
                    $email=$field[0]->values[0];
                }
                $key1=$field[0]->name."->".$field[0]->values[0];
                $key2=$field[1]->name."->".$field[1]->values[0];
                $key3=$field[2]->name."->".$field[2]->values[0];
                $key4=$field[3]->name."->".$field[3]->values[0];
                $listData[]=$key1."-|-".$key2."-|-".$key3."-|-".$key4;
            }
        }
        return $listData;
    }
    $listData=array();
    $data=array();
    //fetch lead info from FB API
    $token=md5("Tealive");
    $graph_url1 = 'https://graph.facebook.com/v4.0/' . $page. "/leadgen_forms?access_token=" . $access_token;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $graph_url1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $output = curl_exec($ch);
    curl_close($ch);
    $listForm = json_decode($output);
    if($listForm){
        $dataList=$listForm->data;
        if($dataList){
            for($i=0;$i<count($dataList);$i++){
                if($dataList[$i]->id){
                    // getLead($dataList[$i]->id, $access_token);
                    $leadgen_id=$dataList[$i]->id;
                    $graph_url = 'https://graph.facebook.com/v4.0/' . $leadgen_id. '/leads?access_token=' . $access_token;
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $graph_url);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    $output = curl_exec($ch);
                    curl_close($ch);
                    $leaddata = json_decode($output);
                    $getDataFetch=array();
                    if($leaddata){
                        $getDataFetch=$leaddata->data;
                    }
                    
                    if(count($getDataFetch)>0){
                        $listData=array();
                        for($j=0;$j<count($getDataFetch);$j++){
                            $created_time=$getDataFetch[$j]->created_time;
                            $id=$getDataFetch[$j]->id;
                            $field=$getDataFetch[$j]->field_data;
                            $key1=$field[0]->name."->".$field[0]->values[0];
                            $key2=$field[1]->name."->".$field[1]->values[0];
                            $key3=$field[2]->name."->".$field[2]->values[0];
                            $key4=$field[3]->name."->".$field[3]->values[0];
                            $listData[]=$key1."-|-".$key2."-|-".$key3."-|-".$key4;
                        }
                        //echo count($getDataFetch);
                        //echo json_encode($listData);
                        if(isset($listData)){
                            $listData=implode("|<>|",$listData);
                            $curl = curl_init();
                            curl_setopt_array($curl, array(
                                                           CURLOPT_URL => "https://api.crmsmart.io/api/add_lead_api_list",
                                                           CURLOPT_RETURNTRANSFER => true,
                                                           CURLOPT_MAXREDIRS => 10,
                                                           CURLOPT_POST=>1,
                                                           CURLOPT_TIMEOUT => 0,
                                                           CURLOPT_FOLLOWLOCATION => false,
                                                           CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                                           CURLOPT_CUSTOMREQUEST => "POST",
                                                           CURLOPT_POSTFIELDS =>"data=".$listData."&product_id=".$product_id."&utm_source=Perform&utm_campaign=Facebook Lead&Token=".$token,
                                                           CURLOPT_HTTPHEADER => array("Content-Type: application/x-www-form-urlencoded"),
                                                           )
                                              );
                            $response = curl_exec($curl);
                            // $err = curl_error($curl);
                            curl_close($curl);
                        }
                    }
                }
            }
        }
    }
    /*
     if(isset($listData)){
     $curl = curl_init();
     curl_setopt_array($curl, array(
     CURLOPT_URL => "https://api.crmsmart.io/api/add_lead_api_list",
     CURLOPT_RETURNTRANSFER => true,
     CURLOPT_ENCODING => "",
     CURLOPT_MAXREDIRS => 10,
     CURLOPT_TIMEOUT => 0,
     CURLOPT_FOLLOWLOCATION => false,
     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
     CURLOPT_CUSTOMREQUEST => "POST",
     CURLOPT_POSTFIELDS => "data=".json_encode($listData)."&product_id=".$product_id."&utm_source=Perform&utm_campaign=Facebook Lead&Token=".$token,
     CURLOPT_HTTPHEADER => array("Content-Type: application/x-www-form-urlencoded"),
     )
     );
     
     $response = curl_exec($curl);
     $err = curl_error($curl);
     curl_close($curl);
     
     if ($err) {
     exit("cURL Error #:" . $err);
     } else {
     exit($response);
     }
     }*/
    ?>
