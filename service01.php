<?php
include 'sqlsrv_connect.php';

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

date_default_timezone_set('Asia/Bangkok'); // Fixed the syntax error here
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

// ตรวจสอบ p_id และ mac_address ในฐานข้อมูล
$query_mac_check = "SELECT * FROM tb_hr_mac_ad WHERE p_id = ? AND mac_address = ?";
$result_mac_check = $conn->prepare($query_mac_check);
$result_mac_check->execute([$u_id, $mac]);

if (!$result_mac_check) {
    die(print_r($conn->errorInfo(), true));
}

$mac_exists = $result_mac_check->fetch(PDO::FETCH_ASSOC);



?>
<head>

   
    
</head>



<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="path/to/green-theme.css">
    <title>ระบบบันทึกเวลาการทำงาน</title>
    
	<meta name="viewport" content="width=device-width, initial-scale=1">
<!--===============================================================================================-->	
	<link rel="icon" type="image/png" href="images/icons/favicon.ico"/>
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/bootstrap/css/bootstrap.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/animate/animate.css">
<!--===============================================================================================-->	
	<link rel="stylesheet" type="text/css" href="vendor/css-hamburgers/hamburgers.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/select2/select2.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="css/util.css">
	<link rel="stylesheet" type="text/css" href="css/main.css">
<!--===============================================================================================-->
</head>

<body class="bg-light">
	
	<div class="limiter">
		<div class="container-login100">
			<div class="wrap-login100">

            <div class="container mt-5">
        <div class="row">
            <div class="col-sm-12">
                <h3 class="text-center">ระบบบันทึกเวลาการทำงาน</h3>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3 text-center mt-4">
                <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-9 mx-auto mt-4">
            <div class="form-group row">
            <form id="workForm" action="save.php" method="post" class="form-horizontal">
                
            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">
            <input type="hidden" name="ip_address" id="ip_address" value="<?php echo $_SERVER['REMOTE_ADDR'];?>">
            <input type="hidden" name="mac_address" id="mac_address" value="<?php echo $mac=substr($mycom,($pmac+36),17);?>">
            <input type="hidden" name="annotation" id="annotation" value="">
            <input type="hidden" name="mac_matches" id="mac_matches" value="">
            
                <div class="form-group row">
                    <div class="col col-sm-3">
                        <label for="p_id">รหัสพนักงาน</label>
                        <input type="text" class="form-control" name="p_id" placeholder="รหัสพนักงาน" value="<?php echo $rowm['p_id'];?>" readonly>
                    </div>
                    <div class="col col-sm-3">
    <label for="p_id">เวลาเข้างาน</label>
    <?php if(isset($rowio['workin'])){ ?>
        <input type="text" class="form-control" name="workin" value="<?php echo $rowio['workin'];?>" disabled>
    <?php } else { ?>
        <input type="text" class="form-control" name="workin" value="<?php echo date('H:i:s');?>" readonly>
        
        <?php
        // ตรวจสอบเงื่อนไข
        if ($timenow > '02:30:00') {
            // ขอให้ผู้ใช้กรอกหมายเหตุ
            echo '<label for="p_id">กรุณากรอกหมายเหตุ</label>' ;
            echo '<input type="text" class="form-control" name="annotation" value="" required="true"  >';
        }

        if ($mac_exists) {
            
        } else {
            // แสดงเมื่อไม่ตรง
            echo '<label for="p_id">กรุณากรอกหมายเหตุที่ใช่เครื่องเดียวกันคนอื่น</label>';
            echo '<input type="text" class="form-control" name="mac_matches" value="" required="true">';
        }
        
        ?>
        
    <?php } ?>
</div>
                    
                    <div class="col col-sm-3">
                        <label for="p_id">เวลาออกงาน</label>
                        <?php
                        if($timenow > '02:30:00'){
                            if(isset($rowio['workout'])){ ?>
                                <input type="text" class="form-control" name="workout" value="<?php echo $rowio['workout'];?>" disabled>
                            <?php }else{ ?>
                                <input type="text" class="form-control" name="workout" value="<?php echo date('H:i:s');?>" readonly>
                            <?php
                            }
                        }else{ ?>
                            <br><font color="red"> หลัง 17.00 น. </font>
                        <?php } ?>
                    </div>
                    <div class="col col-sm-1">
                        <label>-</label>
                        <button  class="btn btn-primary" onclick="getLocation() ,submitForm()" >บันทึก</button>
                    </div>
                </div>
                </div>
            </form>


            <h5 class="text-center mt-4">ตารางลงเวลา <?php echo $rowm['p_name']; ?></h5>
            <?php
            $querylist = "SELECT * FROM tb_hr_work_io WHERE p_id = $u_id ORDER BY workdate DESC";
            $resultlist = $conn->query($querylist);
            
            echo "
            <table class='table table-bordered table-striped table-success mt-2'>
            <thead>
            <tr class='table-danger'>
            <td>date</td>
            <td>work-in</td>
            <td>work-out</td>
            </tr>
            </thead>
            ";

            foreach ($resultlist as $value) {
                echo "<tr>";
                echo "<td>" . $value["workdate"] .  "</td> ";
                echo "<td>" . $value["workin"] .  "</td> ";
                echo "<td>" . $value["workout"] .  "</td> ";
                echo "</tr>";
            }
            echo '</table>';
            ?>
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
    
			</div>
		</div>
	</div>
	
	

	
<!--===============================================================================================-->	
	<script src="vendor/jquery/jquery-3.2.1.min.js"></script>
<!--===============================================================================================-->
	<script src="vendor/bootstrap/js/popper.js"></script>
	<script src="vendor/bootstrap/js/bootstrap.min.js"></script>
<!--===============================================================================================-->
	<script src="vendor/select2/select2.min.js"></script>
<!--===============================================================================================-->
	<script src="vendor/tilt/tilt.jquery.min.js"></script>
	<script >
		$('.js-tilt').tilt({
			scale: 1.1
		})
	</script>
<!--===============================================================================================-->
	<script src="js/main.js"></script>

</body>

</html>