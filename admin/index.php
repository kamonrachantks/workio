<?php

include 'sqlsrv_connect.php';

if (!isset($_SESSION['USER_NO']) || empty($_SESSION['USER_NO'])) {
    header("location: index.php");
    exit();
}

$u_id = $_SESSION['p_id'];

$queryemp = "SELECT * FROM tb_hr_profile WHERE p_id=?";
$resultm = $conn->prepare($queryemp);
$resultm->execute([$u_id]);

$querylist = "SELECT * FROM tb_hr_work_io WHERE p_id = ? ORDER BY workdate DESC";
$resultlist = $conn->prepare($querylist);
$resultlist->execute([$u_id]);

if (!$resultm) {
    die(print_r($conn->errorInfo(), true));
}

$rowm = $resultm->fetch(PDO::FETCH_ASSOC);

date_default_timezone_set('Asia/Bangkok');
$timenow = date('H:i:s');
$datenow = date('Y-m-d');

$queryworkio = "SELECT MAX(workdate) as lastdate, MAX(workin) as workin, MAX(workout) as workout FROM tb_hr_work_io WHERE p_id=? AND workdate=?";
$resultio = $conn->prepare($queryworkio);
$resultio->execute([$u_id, $datenow]);

if (!$resultio) {
    die(print_r($conn->errorInfo(), true));
}

$rowio = $resultio->fetch(PDO::FETCH_ASSOC);

ob_start();
system('ipconfig/all');
$mycom = ob_get_contents();
ob_clean();
$findme = "Physical";
$pmac = strpos($mycom, $findme);
$mac = substr($mycom, ($pmac + 36), 17);

$query_mac_check = "SELECT * FROM tb_hr_mac_ad WHERE p_id = ? AND mac_address = ?";
$result_mac_check = $conn->prepare($query_mac_check);
$result_mac_check->execute([$u_id, $mac]);

if (!$result_mac_check) {
    die(print_r($conn->errorInfo(), true));
}

$mac_exists = $result_mac_check->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
<title>ระบบบันทึกเวลาการทำงาน</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    
    <link rel="stylesheet" href="path/to/green-theme.css">
    <title>ระบบบันทึกเวลาการทำงาน</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .dashboard-container {
            display: flex;
            justify-content: center; 
            flex-wrap: wrap; 
        }

        .dashboard-section {
            padding: 20px;
            border: 3px solid #28a745;
            border-radius: 10px;
            margin: 20px;
            max-width: 500px; 
            flex: 1; 
        }

        .total-requests {
            font-size: 19px;
            font-weight: bold;
            color: #fff;
            cursor: pointer;
            text-decoration: none;
        }

        .green-button {
            background-color: #28a745;
            padding: 10px 20px;
            border-radius: 5px;
            display: inline-block;
            text-decoration: none;
            margin-top: 10px; 
            display: block; 
        }

        body {
        height: 100vh;
        margin: 0;
        display: flex;
        flex-direction: column;
    }

    .wrapper {
        flex: 1;
    }


    </style>
</head>

<body class="sub_page">
<div class="wrapper">

    <div class="hero_area">
        <!-- Header Section -->
        <?php include_once('header_admin.php'); ?>
        <!-- End Header Section -->
    </div>
    
        <div class="container mt-6">

        <div class="row">
            <div class="col-sm-8 mx-auto mt-4">
                <div class="form-group row">
                <h3> ลงเวลาเข้า-ออกงาน <?php echo date('d-m-Y');?></h3>
                    <form id="workForm" action="save.php" method="post" class="form-horizontal">

                        <input type="hidden" name="p_id" id="p_id">
                        <input type="hidden" name="latitude" id="latitude">
                        <input type="hidden" name="longitude" id="longitude">
                        <input type="hidden" name="ip_address" id="ip_address" value="<?php echo $_SERVER['REMOTE_ADDR'];?>">
                        <input type="hidden" name="mac_address" id="mac_address" value="<?php echo $mac=substr($mycom,($pmac+36),17);?>">
                        <input type="hidden" name="annotation" id="annotation" value="">
                        <input type="hidden" name="mac_matches" id="mac_matches" value="">
                        <input type="hidden" name="w_status" id="w_status" value="">

                        <div class="form-group row">

                                <input type="hidden" class="form-control " name="p_id" placeholder="รหัสพนักงาน" value="<?php echo $rowm['p_id'];?>" readonly>

                            <div class="col col-sm-3">
                                <label for="p_id">เวลาเข้างาน</label>
                                <?php if(isset($rowio['workin'])){ ?>
                                    <input type="text" class="form-control" name="workin" value="<?php echo $rowio['workin'];?>" disabled>
                                <?php } else { ?>
                                    <input type="text" class="form-control" name="workin" value="<?php echo date('H:i:s');?>" readonly>

                                    <?php
                                    if ($timenow > '08:30:00') {
                                        echo '<label for="p_id">กรอกเหตุผลที่เข้างานเกินเวลา</label>';
                                        echo '<input type="text" class="form-control" name="annotation" value="" required="true">';
                                        echo '<input type="hidden" class="form-control" name="w_status" value="1">';
                                    } else {

                                        echo '<input type="hidden" class="form-control" name="w_status" value="0">';
                                    }

                                    if ($mac_exists) {
                                        echo '<input type="hidden" class="form-control" name="mac_matches" value="NULL">';
                                        
                                    } else {
                                        echo '<label for="p_id">กรอกเหตุผลที่ใช่เครื่องเดียวกันคนอื่น</label>';
                                        echo '<input type="text" class="form-control" name="mac_matches" value="" required="true">';
                                    }
                                    ?>
                                <?php } ?>
                            </div>

                            <div class="col col-sm-3">
                                <label for="p_id">เวลาออกงาน</label>
                                <?php
                                if($timenow > '09:30:00'){
                                    if(isset($rowio['workout'])){ ?>
                                        <input type="text" class="form-control" name="workout" value="<?php echo $rowio['workout'];?>" disabled>
                                    <?php }else{ ?>
                                        <input type="text" class="form-control" name="workout" value="<?php echo date('H:i:s');?>" readonly>
                                    <?php
                                    }
                                }else{ ?>
                                    <br><font color="red"> หลัง 16.30 น. </font>
                                <?php } ?>
                            </div>
                            <div class="col col-sm-1">
                                <label>-</label>
                                <button class="btn btn-primary" onclick="getLocation() ,submitForm()">บันทึก</button>
                            </div>
                        </div>
                    </form>

                
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>

    <script>
        const x = document.getElementById("demo");

        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition);
            } else { 
                x.innerHTML = "Geolocation is not supported by this browser.";
            }
        }

        function showPosition(position) {
            // Set latitude and longitude values in the hidden input fields
            document.querySelector('#latitude').value = position.coords.latitude;
            document.querySelector('#longitude').value = position.coords.longitude;
        }

        getLocation();
    </script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="js/jquery-3.4.1.min.js"></script>
    <script type="text/javascript" src="js/custom.js"></script>
    </div>
</body>


    <?php include_once('footer.php'); ?>

</html>
