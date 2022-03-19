<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
    
$input=json_encode(file_get_contents('./log.log'));
   
$sender = $input['entry'][0]['messaging'][0]['sender']['id'];
    echo $sender;
$receive_id = $input['entry'][0]['messaging'][0]['recipient']['id'];
$message = $input['entry'][0]['messaging'][0]['message']['text'];
$postback = $input['entry'][0]['messaging'][0]['postback']['payload'];
$tagPostback = $input['entry'][0]['messaging'][0]['message']['metadata'];
if (!empty($postback)) {
    $message =$postback;
}
     var_dump($input);
