<?php
session_start();

$serverName = "LAPTOP-FIIPHBAB";
$uid = "";  // แทนที่ด้วยชื่อผู้ใช้ SQL Server ของคุณ
$pwd = "";  // แทนที่ด้วยรหัสผ่าน SQL Server ของคุณ
$DB = "Domc_TEST";

define('LINE_API', "https://notify-api.line.me/api/notify");

$token = "Y3zH1oQp4rVu0Wx4wINmhNzy5wCwpVwCv5Dp8kfVkkI"; // แทนที่ด้วย Token ของ Line Notify ของคุณ

function notify_message($message, $token) {
    $queryData = array('message' => $message);
    $queryData = http_build_query($queryData, '', '&');
    $headerOptions = array(
        'http' => array(
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n"
                . "Authorization: Bearer " . $token . "\r\n"
                . "Content-Length: " . strlen($queryData) . "\r\n",
            'content' => $queryData
        ),
    );
    $context = stream_context_create($headerOptions);
    $result = file_get_contents(LINE_API, FALSE, $context);
    $res = json_decode($result);
    return $res;
}

try {
    $conn = new PDO("sqlsrv:server=$serverName;Database=$DB", $uid, $pwd);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage());
}

$response = [
    'status' => '',
    'message' => '',
    'redirect' => '',
];

if (isset($_POST["workin"])) {
    try {
        $workdate = date('Y-m-d');
        $p_id = $_POST["p_id"];
        $workin = $_POST["workin"];
        $latitude = $_POST["latitude"];
        $longitude = $_POST["longitude"];
        $ip_address = $_POST["ip_address"];
        $mac_address = $_POST["mac_address"];
        $annotation = $_POST["annotation"];
        $mac_matches = $_POST["mac_matches"];
        $w_status = $_POST["w_status"];

        $centerLatitude = 14.9749;
        $centerLongitude = 102.1125;

        $distance = calculateHaversineDistance($latitude, $longitude, $centerLatitude, $centerLongitude);

        if ($distance > 50) {
            $response['message'] = 'คุณไม่ได้อยู่ในพื้นที่';
            echo json_encode($response);
            exit();
        }

        // Check if mac_address exists in tb_hr_mac_ad and is not duplicated
        $checkQuery = "SELECT COUNT(*) FROM tb_hr_mac_ad WHERE mac_address = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->execute([$mac_address]);
        $count = $checkStmt->fetchColumn();

        if ($count == 0) {
            // Insert into tb_hr_mac_ad if mac_address is not duplicated
            $query = "INSERT INTO tb_hr_mac_ad (p_id, mac_address) VALUES (?, ?)";
            $stmt = $conn->prepare($query);
            $result = $stmt->execute([$p_id, $mac_address]);

        }


        // Insert into tb_hr_work_io
        $query = "INSERT INTO tb_hr_work_io (workdate, p_id, workin, latitude, longitude, ip_address, mac_address, annotation, mac_matches, w_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $result = $stmt->execute([$workdate, $p_id, $workin, $latitude, $longitude, $ip_address, $mac_address, $annotation, $mac_matches, $w_status]);
        
        if ($result) {      
            
            $selectQuery = "SELECT p_name FROM tb_hr_profile WHERE p_id = ?";
            $selectStmt = $conn->prepare($selectQuery);
            $selectStmt->execute([$p_id]);
            $profileData = $selectStmt->fetch(PDO::FETCH_ASSOC);
    
            $str = "ลงเวลาเข้า\nชื่อ: " . $profileData['p_name'] . "\nเวลาเข้า: " . $workin . "\nวันที่: " . $workdate;
            $res = notify_message($str, $token);

            
                $response['status'] = 'success';
                $response['message'] = 'บันทึกข้อมูลสำเร็จ' ;
                $response['redirect'] = 'service.php';
                echo json_encode($response);
     
        } else {
            throw new Exception("การดำเนินการคิวรี SQL ล้มเหลว.");
        }
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
        echo json_encode($response);
    }
} else if (isset($_POST["workout"])) {
    // จัดการตรวจสอบเวลาออกที่นี่
    try {
        $workdate = date('Y-m-d');
        $p_id = $_POST["p_id"];
        $workout = $_POST["workout"];

        // ดึงข้อมูลเวลาเข้า (workin) จากฐานข้อมูล
        $selectCheckinQuery = "SELECT workin FROM tb_hr_work_io WHERE p_id = ? AND workdate = ?";
        $selectCheckinStmt = $conn->prepare($selectCheckinQuery);
        $selectCheckinStmt->execute([$p_id, $workdate]);
        $checkinData = $selectCheckinStmt->fetch(PDO::FETCH_ASSOC);

        if ($checkinData) {
            $workin = $checkinData['workin'];
        } else {
            // จัดการกรณีที่ไม่พบเวลาเข้า
            throw new Exception("ไม่พบเวลาเข้า.");
        }

        // อัปเดต tb_hr_work_io สำหรับเวลาออก
        $query = "UPDATE tb_hr_work_io SET workout = ? WHERE p_id = ? AND workdate = ?";
        $stmt = $conn->prepare($query);
        $result = $stmt->execute([$workout, $p_id, $workdate]);

        // ดึงข้อมูลชื่อผู้ใช้จาก tb_hr_profile
        $selectQuery = "SELECT p_name FROM tb_hr_profile WHERE p_id = ?";
        $selectStmt = $conn->prepare($selectQuery);
        $selectStmt->execute([$p_id]);
        $profileData = $selectStmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $response['status'] = 'success';
            $response['message'] = 'บันทึกข้อมูลสำเร็จ';
            $response['redirect'] = 'service.php';
            echo json_encode($response);
        } else {
            throw new Exception("การดำเนินการคิวรี SQL ล้มเหลว.");
        }
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
        echo json_encode($response);
    }
} else {
    $response['message'] = 'คุณได้บันทึกเวลาเข้า-ออกงานวันนี้เรียบร้อยแล้ว';
    echo json_encode($response);
}

function calculateHaversineDistance($lat1, $lon1, $lat2, $lon2)
{
    // สูตร Haversine ในการคำนวณระยะทาง
    $R = 6371000;
    $lat1Rad = deg2rad($lat1);
    $lon1Rad = deg2rad($lon1);
    $lat2Rad = deg2rad($lat2);
    $lon2Rad = deg2rad($lon2);
    $dlat = $lat2Rad - $lat1Rad;
    $dlon = $lon2Rad - $lon1Rad;
    $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1Rad) * cos($lat2Rad) * sin($dlon / 2) * sin($dlon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distance = $R * $c;
    return $distance;
}
?>
