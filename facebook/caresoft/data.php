<?php
    $sendid="4155408647834353";
    $page_id="112905952502420";
    $cookie="";
    if(is_file('cookie/log'.$sendid.$page_id.'.txt')){
        echo 'cookie/log'.$sendid.$page_id.'.txt';
        $cookie=file_get_contents('cookie/log'.$sendid.$page_id.'.txt');
        echo $cookie;
    }
    echo $cookie;
    
    $json=json_decode(file_get_contents("chatlog/112905952502420-4155408647834353-112905952502420-1597069452.txt"));
    echo "<pre>";
   //     var_dump($json);
     echo "</pre>";
   //var_dump($json->entry[0]->messaging[0]->referral->ref);
    die()
    ?>
