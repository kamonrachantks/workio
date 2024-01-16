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
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    
    <link rel="stylesheet" href="path/to/green-theme.css">


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@1,300&display=swap" rel="stylesheet">

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
        font-family: 'Roboto', sans-serif;
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
                    
                    <h5 class="text-center mt-4">ตารางลงเวลา <?php echo $rowm['p_name']; ?></h5>
                    <?php
                    $querylist = "SELECT * FROM tb_hr_work_io WHERE p_id = $u_id ORDER BY workdate DESC";
                    $resultlist = $conn->query($querylist);
                    
                    echo "
                    <table class='table table-bordered table-striped table-success mt-2'>
                    <thead>
                    <tr class='table-success'>
                    <td>วันที่</td>
                    <td>เวลาเข้างาน</td>
                    <td>เวลาออกงาน</td>
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

    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="js/jquery-3.4.1.min.js"></script>
    <script type="text/javascript" src="js/custom.js"></script>
    </div>
</body>


    <?php include_once('footer.php'); ?>

</html>
