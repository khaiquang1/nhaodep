<?php
/*
 $servername = "localhost";
 $username = "landing_vietcapital";
 $password = "654321";
 $database = "landing_vietcapital";
 */
function DBConfig()
{
    $servername = "203.113.174.8";
    $username = "landing_hoanmy";
    $password = "ureka@123";
    $database = "landing_hoanmy";
    return array(
        'servername' => $servername,
        'username' => $username,
        'password' => $password,
        'database' => $database,
    );
}
function dbConnectPDO()
{
    $config = DBConfig();
    // Create connection
    $DB = new Db($config['servername'], $config['database'], $config['username'], $config['password']);
    if (!$DB) {
        die("Connection failed: " . mysqli_connect_error());
    } else {
        return $DB;
    }
}
function dbConnect()
{
    $config = DBConfig();
    // Create connection
    $conn = mysqli_connect($config['servername'], $config['username'], $config['password'], $config['database']);
    mysqli_set_charset($conn, "utf8");
    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    } else {
        return $conn;
    }
}
function getdata()
{
    $data = array();
    $sql = 'SELECT * FROM registers';
    $result = mysqli_query(dbConnect(), $sql);

    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    return $data;
}

function checkphone($phone)
{
    $DB = dbConnectPDO();
    $result = $DB->single("SELECT * FROM registers WHERE phone=? ", array($phone));
    //var_dump($result);die;
    if ($result === false) {
        return false;
    } else {
        return true;
    }
}

function checklogin($username, $password)
{

    $sql = 'SELECT * FROM admin where username="' . $username . '" and password="' . md5($password) . '"';
    $result = mysqli_query(dbConnect(), $sql);

    if (mysqli_num_rows($result) > 0) {
        return 1;
    } else {
        return 0;
    }
}

/*
function insertregister($fullname, $city, $phone, $utm_source, $utm_medium, $utm_campaign, $date_create,$email)
{

$sql = "INSERT INTO registers (name,phone,email,city,utm_medium,utm_source,utm_campaign,date_create) VALUES ('" . $fullname . "','" . $phone . "','".$email."','" . $city . "','" . $utm_medium . "','" . $utm_source . "','" . $utm_campaign . "','" . $date_create . "')";
$conn = dbConnect();
$conn->query($sql);

}
 */

function insertregister($fullname,$email,$phone,$utm_source,$utm_medium,$utm_campaign,$utm_term,$utm_content,$date_create)
{
    try {
        $DB = dbConnectPDO();
        $result = $DB->query("INSERT INTO registers(name,email,phone,utm_medium,utm_source,utm_campaign,utm_term,utm_content,date_create) VALUES(?,?,?,?,?,?,?,?,?)",
            array($fullname,$email,$phone,$utm_source,$utm_medium,$utm_campaign,$utm_term,$utm_content,$date_create));
        $DB->CloseConnection;
        if ($result) {
            return true;
        } else {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }
}

// function select_tinhthanh()
// {

//     $data = array();
//     $sql = 'SELECT * FROM `tinhthanh` ORDER BY `TenTinhThanh`';
//         $result = mysqli_query(dbConnect(), $sql);

//         while($row = mysqli_fetch_assoc($result)) {
//         $data[] = $row;
//     }

//        return $data;

// }

// function select_quanhuyen($id_tinhthanh)
// {

//     $data = array();
//     $sql = "SELECT * FROM `quanhuyen` where MaTinhThanh = $id_tinhthanh";
//         $result = mysqli_query(dbConnect(), $sql);

//         while($row = mysqli_fetch_assoc($result)) {
//         $data[] = $row;
//     }

//        return $data;

// }
