<?php
require __DIR__ . '/vendor/autoload.php';
require_once 'php-graph-sdk/src/Facebook/autoload.php';
    
use FacebookAds\Api;
use FacebookAds\Object\Lead;
use FacebookAds\Logger\CurlLogger;
use FacebookAds\Object\LeadgenForm;
use FacebookAds\Object\Ad;
use FacebookAds\Object\Page;
    
$access_token = 'EAAK481QSfNIBACBQUWnRJRZChTuh6eaMBj1aZAJq19c71ePHfPeQZBi2xb55TrU7SfLlIRHWWiD566ZB6YQmsMKrynkn58as1N6Sq3ZBnHgzRxuxO3IlwbfViZC4azM2u4RV9Ojr9ef91AgJdmLNjHZAdpbi4V64VOZCxd9Ns1BXn1htvNcIWzJU8WMceN3iSKquMYtuZAYtI5VmRxBZBpOGIA';
    /* $app_secret = 'b5091bcba3128b45d944f651362c2b91';
    
    $id = '350485549001639'; */
    //$form = new LeadgenForm('396239777719371');
    //$form->read();
    
    $app_secret = 'b5091bcba3128b45d944f651362c2b91';
    $app_id = '766305180482770';
    $id = '396239777719371'; // AD_GROUP_ID
    $pageid = '901537370189587';
    $fb = new Facebook\Facebook([
                                'app_id' => $app_id,
                                'app_secret' => $app_secret,
                                'default_graph_version' => 'v4.0',
                                ]);
    /* PHP SDK v5.0.0 */
    /* make the API call */
    try {
        // Returns a `Facebook\FacebookResponse` object
        $response = $fb->get(
                             '/'. $pageid.'/leadgen_forms',
                             $access_token
                             );
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
        echo 'Graph returned an error: ' . $e->getMessage();
        exit;
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;
    }
    $total_posts = array();
    $posts_response = $response->getGraphEdge();
    if($fb->next($posts_response)) {
        $response_array = $posts_response->asArray();
        $total_posts = array_merge($total_posts, $response_array);
        while ($posts_response = $fb->next($posts_response)) {
            $response_array = $posts_response->asArray();
            $total_posts = array_merge($total_posts, $response_array);
        }
       // print_r($total_posts);
    } else {
        $posts_response = $response->getGraphEdge()->asArray();
       // print_r($posts_response);
    }

   // $file = fopen(dirname(__FILE__) . '/download/396239777719371.csv', 'w+');
   
    /* handle the result */
    
    $access_token = $access_token;
    $app_secret = $app_secret;
    $app_id = $app_id;
    $id = $pageid;
    
    $api = Api::init($app_id, $app_secret, $access_token);
    $api->setLogger(new CurlLogger());
    
    $fields = array(
    );
    $params = array(
                    'subscribed_fields' => 'leadgen',
                    );
    echo json_encode((new Page($id))->createSubscribedApp(
                                                          $fields,
                                                          $params
                                                          )->exportAllData(), JSON_PRETTY_PRINT);
