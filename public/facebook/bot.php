`<?php
include 'FbBot.php';
$tokken = $_REQUEST['hub_verify_token'];
$hubVerifyToken = '123456789';
$challange = $_REQUEST['hub_challenge'];
$accessToken = 'EAAbJu7sd260BAP72KD1knwBTCPZBdGEuE61iRC3rNVBZCZCrwa5T7M4nryshJZBPZBsX5ZCAcFjJvMSZAv08LNuIZBiHQX2Hvq00oTujSazuoIQMKJrIG3BdyTxPVpcsUV0BxJ3uOzf830UxgUkkjitdT8g7CNbK6B6ASgLW8bjJQQZDZD';
$bot = new FbBot();
$bot->setHubVerifyToken($hubVerifyToken);
$bot->setaccessToken($accessToken);
echo $bot->verifyTokken($tokken,$challange);
$input = json_decode(file_get_contents('php://input'), true);
$message = $bot->readMessage($input);
$textmessage = $bot->sendMessage($message);
