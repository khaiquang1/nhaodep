<?php 
define('AUTHOR', 'vietnamtotravel.com');
define('SITE_NAME', $_SERVER['HTTP_HOST']);
define('DS', DIRECTORY_SEPARATOR);
define('DOMAIN','www.vietnamtotravel.com/');
define('SERVER','http://www.vietnamtotravel.com');
if (!defined('FOLDERWEB')) {
    define('FOLDERWEB', SERVER);
}
define("DOMAINLIST","vietnam-food.net,cheaphotel.vn,entertain-vn.com,buysales.net,lifeviet.com,violajunk.com");
define('ACTICLE_LINK_CONFIG','diem-den');
define('ACTICLE_LINK_CONFIG1','to-travel-vietnam');
define('FOLDERWEBSITE','');
define('DOMAINROOT','http://www.vietnamtotravel.com/');
define('PHOTODOMAIN','//www.vietnamtotravel.com/custom/');
define('FOLDERRLL',$_SERVER["DOCUMENT_ROOT"].FOLDERWEBSITE);
define("PATH_UPLOAD_IMAGE_MEMBER", "custom/profile");
define("PATH_UPLOAD_IMAGE_ADVERTIES", FOLDERWEB."/quangcao/");
define("hotelmax",5);
define("ADMIN","http://".DOMAIN);
define("WIDTH", 400);
define("HEIGHT", 250);
define("WEBINFO","http://www.vietnamtotravel.com/style"); //static.asttravelgroup.com
define("WEBINFO1","//static.asttravelgroup.com"); //
define("ID_CAT_MAP_DETAIL_LISTING", 396);
define("FACEBOOKID","1506276259390919");
define("FACEBOOKSECRET", "702ce2aef5e93832793c9764f29825e0");
define("FACEBOOKFANPAGE", "784805794918634");
define("GOOGLEKEY", "AIzaSyAjtULyU1rb9ZRiuwV3xAb-AhSoVrNgZfY");

$ip = getIp();//$_SERVER['REMOTE_ADDR'];
define("USD", 21000);
define('ISDEMO', FALSE);
$GLOBALS['AdminEmailInfo'] = 'bookinglifeviet@gmail.com';
define('VERSION_STYLE', '1.2.8036');
/*
$GLOBALS["usersendmail"]="no-reply@asttravelgroup.com";
$GLOBALS["passsendmail"]="fKPkbbxs4I";
$GLOBALS["serversendmail"]="mail.asttravelgroup.com";
$GLOBALS['MailUseSMTP'] = 1; 
$GLOBALS['MailSMTPPort'] = '25';
$GLOBALS['tls'] = 'tls'; */
$GLOBALS["usersendmail"]="booktourvietnam@gmail.com";
$GLOBALS["passsendmail"]="cuocsongthinhvuong1978";
$GLOBALS["serversendmail"]="smtp.gmail.com";
$GLOBALS['MailUseSMTP'] = 1; 
$GLOBALS['MailSMTPPort'] = '587';
$GLOBALS['tls'] = 'tls';

define("TOURCAT","38,39,40,41");
define("SYSMONEY","đ");
define('CLIENTIDGG', '657698915536-0t6tgc9pvlv3rdu90pk2ilg9aukiq3bv.apps.googleusercontent.com');
define('CLIENTSECRETGG', 'ELMFRsI_r34_KitFdfj7XN-C');
define('MAXRESULTS', 50);
$GLOBALS['CharacterSet'] = 'UTF-8';
$GLOBALS["AdminEmail"]="noresendmail@cheaphotel.vn";
/// config payment
define("LinkPayment", "https://onepay.vn/vpcpay/vpcpay.op");
define("Merchant_ID", "OP_ASTTGVNV");
define("Access_Code", "33F03459");
// end config
if(!isset($_SESSION['location']) || $_SESSION['location']==""){
	if(isset($_COOKIE["locationcookie111"]) && $_COOKIE["locationcookie111"]!=""){
		$location=$_COOKIE["locationcookie111"];
	}else{
		$location="hcm-city";
	}
}else{
	$location=$_SESSION['location'];
}
define("tpdefault",$location);
if(isset($_COOKIE["languageselect"]) && $_COOKIE["languageselect"]!=""){
	$_SESSION['language']=$_COOKIE["languageselect"];
}

?>
