<?php
    @session_start();
    require_once __DIR__ . '/vendor/autoload.php';
    $fb = new Facebook\Facebook([
      'app_id' => '766305180482770', // Replace {app-id} with your app id
      'app_secret' => 'b5091bcba3128b45d944f651362c2b91',
      'default_graph_version' => 'v6.0',
      ]);
    require_once "connect.php";

    $helper = $fb->getRedirectLoginHelper();
    if (isset($_GET['state'])) {
      $helper->getPersistentDataHandler()->set('state', $_GET['state']);
    }

    try {
      $accessToken = $helper->getAccessToken();
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
      // When Graph returns an error
      @header('Location: https://fastercrm.com/getdata/#');
      echo 'Graph returned an error: ' . $e->getMessage();
      exit;
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
      // When validation fails or other local issues
      @header('Location: https://fastercrm.com/getdata/#');
      echo 'Facebook SDK returned an error: ' . $e->getMessage();
      exit;
    }
    $default_access_token = $accessToken->getValue();

    if (! isset($accessToken)) {
      if ($helper->getError()) {
        header('HTTP/1.0 401 Unauthorized');
        echo "Error: " . $helper->getError() . "\n";
        echo "Error Code: " . $helper->getErrorCode() . "\n";
        echo "Error Reason: " . $helper->getErrorReason() . "\n";
        echo "Error Description: " . $helper->getErrorDescription() . "\n";
      } else {
        header('HTTP/1.0 400 Bad Request');
        echo 'Bad request';
      }
      exit;
    }
    $long_access_token = '';
    if ( !$accessToken->isLongLived() ) {
        $accessToken = getLongLivedAccessToken($fb, $accessToken);
        $long_access_token = $accessToken->getValue();
    }else{
        $long_access_token = $default_access_token;
    }
    setAccessToken( (string)$accessToken );

    $page_access_token = '';
   // $fb=getFacebook();

    //$fb = new Facebook($config);
    if( !empty($accessToken) ){
        $fb->setDefaultAccessToken($accessToken);
    }

    try {
        $response = $fb->get('/me/accounts');
    } catch( FacebookResponseException $e ) {
        $error[] = $e->getMessage();
    } catch( FacebookSDKException $e ) {
        $error[] = $e->getMessage();
    }
    $accountsEdge = $response->getGraphEdge();
    $metaData = $accountsEdge->getMetaData();
    $totalCount = $accountsEdge->getTotalCount();
    $urlNext = $accountsEdge->getPaginationUrl('next');
    $urlPrevious = $accountsEdge->getPaginationUrl('previous');
    $listinsert=array();
    $partner_id=0;
    $total=0;
    $page=array();
    do {
      foreach ($accountsEdge as $account) {
          $ac = $account->asArray();
          if(!empty($ac['access_token']) ){
              $page_access_token = $ac['access_token'];
              $name = $ac['name'];
              $page_id=$ac["id"];
              $page[]=$ac["id"].":".$ac['name'];
              $listinsert[]="('".$name."','".$partner_id."','".$page_id."','".$page_access_token."')";
              $curl = curl_init();
              curl_setopt_array($curl, array(
                  CURLOPT_RETURNTRANSFER => 1,
                  CURLOPT_URL => 'https://graph.facebook.com/v6.0/'.$page_id.'/subscribed_apps?access_token='.$page_access_token.'&subscribed_fields=messages,messaging_postbacks,messaging_optins,message_reads,messaging_referrals,inbox_labels,message_deliveries,message_reactions,message_echoes',
                  CURLOPT_USERAGENT => 'POST',
                  CURLOPT_POST => 1,
                  CURLOPT_SSL_VERIFYPEER => false, //Bỏ kiểm SSL
                  CURLOPT_POSTFIELDS => null
              ));
              $resp = curl_exec($curl); 
              $total++;
              curl_close($curl);
          }
      }
  } while ( $accountsEdge = $fb->next($accountsEdge) );
  if($listinsert!="" && count($listinsert)>0){
    @mysqli_query($conn,"insert into page_partner(title, partner_id, page_id, token) values".implode(",",$listinsert)." on DUPLICATE KEY UPDATE token=VALUES(token), update_syn=0");
    @mysqli_close($conn);
  }
  echo "Đồng bộ được ".$total." Pages";
    if($page!="" && count($page)>0){
        @header('Location: https://fastercrm.com/getdata/pageupdate?page='.implode(",",$page));
    }else{
        @header('Location: https://fastercrm.com/getdata');
    }
  exit();
    // The OAuth 2.0 client handler helps us manage access tokens
//
    ?>
