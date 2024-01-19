<?php
// Include necessary files and start session
include 'sqlsrv_connect.php';

// Redirect to index.php if USER_NO is not set or empty
if (!isset($_SESSION['USER_NO']) || empty($_SESSION['USER_NO'])) {
    header("location: index.php");
    exit();
}

// Get user ID from session
$u_id = $_SESSION['p_id'];

// Query to get user profile
$queryemp = "SELECT * FROM tb_hr_profile WHERE p_id=?";
$resultm = $conn->prepare($queryemp);
$resultm->execute([$u_id]);

// Redirect if query fails
if (!$resultm) {
    die(print_r($conn->errorInfo(), true));
}

// Fetch user profile
$rowm = $resultm->fetch(PDO::FETCH_ASSOC);

// Set timezone and current date
date_default_timezone_set('Asia/Bangkok');
$datenow = date('Y-m-d');

// Query to get the latest work date, work in, and work out
$queryworkio = "SELECT MAX(workdate) as lastdate, MAX(workin) as workin, MAX(workout) as workout FROM tb_hr_work_io WHERE p_id=? AND workdate=?";
$resultio = $conn->prepare($queryworkio);
$resultio->execute([$u_id, $datenow]);

// Redirect if query fails
if (!$resultio) {
    die(print_r($conn->errorInfo(), true));
}

// Fetch the latest work date, work in, and work out
$rowio = $resultio->fetch(PDO::FETCH_ASSOC);

// Get MAC address
ob_start();
system('ipconfig/all');
$mycom = ob_get_contents();
ob_clean();
$findme = "Physical";
$pmac = strpos($mycom, $findme);
$mac = substr($mycom, ($pmac + 36), 17);

// Query to check if MAC address exists
$query_mac_check = "SELECT * FROM tb_hr_mac_ad WHERE p_id = ? AND mac_address = ?";
$result_mac_check = $conn->prepare($query_mac_check);
$result_mac_check->execute([$u_id, $mac]);

// Redirect if query fails
if (!$result_mac_check) {
    die(print_r($conn->errorInfo(), true));
}

// Fetch MAC address existence
$mac_exists = $result_mac_check->fetch(PDO::FETCH_ASSOC);

// Get start and end dates from query parameters, or use current month by default
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-01');
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-t');

// Query to retrieve work records within the specified date range
$querylist = "SELECT * FROM tb_hr_work_io WHERE p_id = ? AND workdate BETWEEN ? AND ? ORDER BY workdate DESC";
$resultlist = $conn->prepare($querylist);
$resultlist->execute([$u_id, $startDate, $endDate]);
?>

<!DOCTYPE html>
<html lang="en">

<head>
<title>ตารางลงเวลา</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="path/to/green-theme.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@1,300&display=swap" rel="stylesheet">
    <!-- Bootstrap Datepicker CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <!-- Bootstrap Datepicker JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
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

<?php include_once('header.php'); ?>



<body class="sub_page">
    <div class="wrapper">
        <div class="container mt-6">
            <h5 class="text-center mt-4">ตารางลงเวลา <?php echo $rowm['p_name']; ?></h5>
            <div class="form-row justify-content mt-3">
                <div class="col-md-2">
                    <label for="startDate">ค้นหาจากวันที่ เริ่มต้น:</label>
                    <input type="date" class="form-control" id="startDate" name="startDate" required="true">
                </div>
                <div class="col-md-2">
                    <label for="endDate">สิ้นสุด:</label>
                    <input type="date" class="form-control" id="endDate" name="endDate" required="true">
                </div>
                <div class="col-md-2 mt-4">
                    <button type="button" class="btn btn-primary" id="filterBtn">กรอง</button>
                </div>
            </div>

            <table class='table table-bordered table-striped table-success mt-2'>
                <thead>
                    <tr class='table-success'>
                        <td>วันที่</td>
                        <td>เวลาเข้างาน</td>
                        <td>เวลาออกงาน</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch work records within the specified date range
                    foreach ($resultlist as $value) {
                        echo "<tr>";
                        echo "<td>" . $value["workdate"] .  "</td> ";
                        echo "<td>" . $value["workin"] .  "</td> ";
                        echo "<td>" . $value["workout"] .  "</td> ";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function () {
        // Add an event listener to the filter button
        $('#filterBtn').on('click', function () {
            // Get the selected start and end dates
            var startDate = $('#startDate').val();
            var endDate = $('#endDate').val();

            // Check if both start and end dates are empty
            if (!startDate && !endDate) {
                // Redirect to the same page without any date parameters
                window.location.href = 'service02.php';
            } else {
                // Redirect to the same page with the selected date range as query parameters
                window.location.href = 'service02.php?startDate=' + startDate + '&endDate=' + endDate;
            }
        });
    });
</script>

</body>

<?php include_once('footer.php'); ?>

</html>
