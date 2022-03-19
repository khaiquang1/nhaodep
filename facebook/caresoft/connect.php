<?php
$servername = "localhost";
$username = "faster_crm";
$password = "Fastercrm@123";
$dbname = "faster_crm";
// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
mysqli_set_charset($conn, "utf8");
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
    
    
         function getAccessToken( )
        {   /*if( !empty($this->getLongAccessToken()) ){
                return $this->getLongAccessToken();
            }
            return $this->getDefaultAccessToken();*/
            if( !empty($_SESSION['FB_ACCESS_TOKEN']) ){
                return $_SESSION['FB_ACCESS_TOKEN'];
            }
            return '';
        }
    
         function getPageAccessToken( )
        {
            if( !empty($_SESSION['FB_PAGE_ACCESS_TOKEN']) ){
                return $_SESSION['FB_PAGE_ACCESS_TOKEN'];
            }
            return '';
        }
    
         function setAccessToken( $access_token )
        {
            $_SESSION['FB_ACCESS_TOKEN'] = $access_token;
            return "";
        }
    
         function setPageAccessToken( $access_token )
        {
            $_SESSION['FB_PAGE_ACCESS_TOKEN'] = $access_token;
            return "";
        }
    
        function getFacebook()
        {
            $config = array(
                /*
                'app_id' => getAppId(),
                'app_secret' => getAppSecret(),
                'default_graph_version' => getDefaultGraphVersion(), */
                'app_id' => '766305180482770', // Replace {app-id} with your app id
                'app_secret' => 'b5091bcba3128b45d944f651362c2b91',
                'default_graph_version' => 'v5.0',
                //'default_access_token' => $this->default_access_token, // optional
            );
            $fb = new Facebook($config);
            if( !empty(getAccessToken() ) ){
                $fb->setDefaultAccessToken(getAccessToken() );
            }
            return $fb;
        }
    
         function getLongLivedAccessToken( $fb , $access_token )
        {
            if( !empty($fb) && !empty($access_token) ){
                $oAuth2Client = $fb->getOAuth2Client();
                $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($access_token);
                return $longLivedAccessToken;
            }
            return '';
        }
?>
