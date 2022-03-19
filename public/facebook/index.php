<!DOCTYPE html>
<html>
<head>
  <title>
    My Name
  </title>
</head>

<body>
  <strong style="color:red">Hệ thống FasterLead cần lấy dữ liệu từ page Facebook. Anh/Chi cần cho phép Ứng dụng FasteRich lấy data từ các page</strong>
<?php
require_once __DIR__ . '/vendor/autoload.php';
    $partner="";
    if(isset($_GET["partner_id"]) && ["partner_id"]!=""){
        $partner=$_GET["partner_id"];
    }
    session_start();
    $fb = new Facebook\Facebook([
      'app_id' => '766305180482770', // Replace {app-id} with your app id
      'app_secret' => 'b5091bcba3128b45d944f651362c2b91',
      'default_graph_version' => 'v4.0',
      ]);

    $helper = $fb->getRedirectLoginHelper();

    $permissions = ['pages_show_list', 'pages_messaging', 'pages_manage_metadata', 'leads_retrieval', 'pages_read_engagement', 'pages_read_user_content']; // Optional permissions
    $callbackUrl = htmlspecialchars('https://api2.fastercrm.com/facebook/callback.php');
    $loginUrl = $helper->getLoginUrl($callbackUrl, $permissions);
    //echo '<a href="' . $loginUrl . '">Kết nối liên kết dữ liệu</a>';
    echo '<a href="'.$loginUrl.'" target="_blank">Kết nối liên kết dữ liệu</a>';


?>
</body>
</html>
