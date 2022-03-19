<?php
require("PDO/src/PDO.class.php");
error_reporting(0);
include 'connection.php';
include_once("PHPMailer-master/PHPMailerAutoload.php");


$fullname = trim($_POST['fullname']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);

$utm_source = isset($_COOKIE["utm_source"]) ? trim($_COOKIE["utm_source"]) :'';
$utm_medium = isset($_COOKIE["utm_medium"]) ? trim($_COOKIE["utm_medium"]) : '';
$utm_campaign = isset($_COOKIE["utm_campaign"]) ? trim($_COOKIE["utm_campaign"]) :'';
$utm_term = isset($_COOKIE["utm_term"]) ? trim($_COOKIE["utm_term"]) :'';
$utm_content = isset($_COOKIE["utm_content"]) ? trim($_COOKIE["utm_content"]) :'';

if ($fullname != "" && $email != "" && $phone != "") {
    date_default_timezone_set("Asia/Ho_Chi_Minh");
    $date_create = date('Y-m-d H:i:s');
    if (checkphone($phone) == 1) {
        echo json_encode(array(
            'result' => '0'
        ));
        die();
    } 
    else {

        insertregister($fullname,$email,$phone,$service,$utm_source,$utm_medium,$utm_campaign,$utm_term,$utm_content,$date_create);

        /*

        $mail = new PHPMailer;
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'tin8-noreply@urekamedia.vn';                 // SMTP username
        $mail->Password = '0123476954Tb';                           // SMTP password
        $mail->SMTPSecure = 'TLS';                           // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587;
        $mail->setFrom('tin8-noreply@urekamedia.vn', 'Sunshine City');

        $mail->addAddress('huyendt@sunshinemail.vn');
        $mail->addAddress('phongmarketing03@sunshinemail.vn');


        $mail->WordWrap = 50;
        $mail->isHTML(true);
        $mail->Subject = 'Sunshine City';
        $mail->Body = "
				
				Thông tin khách hàng: <br/>
				Tên: $fullname <br/>
				Số điện thoại: $phone <br/>
				Email: $email <br/><br/>
					
				#UTM Tracking# <br/>
				utm_source = $utm_source <br/>
				utm_medium = $utm_medium <br/>
				utm_campaign = $utm_campaign
				
			";
        $mail->AltBody = 'Sunshine City';

        if ($mail->send()) {
            echo "1";
        } else {
            echo "2";
        }
        */
        echo json_encode(array(
            'result' => '1'                    
        ));
        die();


    }
} else {
    die("404");
}

?>