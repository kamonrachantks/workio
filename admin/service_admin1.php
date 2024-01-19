<?php
// Include necessary files
include 'class/class.scdb.php';
include 'sqlsrv_connect.php';

// Check if user is logged in
if ((!isset($_SESSION['USER_NO'])) || ($_SESSION['USER_NO'] == '')) {
    header("location: login.php");
    exit();
}

// Create SCDB instance
$query = new SCDB();

// Initialize search variables
$searchCondition = "";
$searchTerm = "";
$startDate = "";
$endDate = "";

// Check if the search term is set
if (isset($_POST['search']) && !empty($_POST['search'])) {
    $searchTerm = $_POST['search'];
    $searchCondition .= " AND (p.p_id = :searchTerm OR p.p_name LIKE :searchTerm)";
}

// Initialize date search variables
$dateSearchCondition = "";

// Check if start and end dates are set
if (isset($_POST['startDate']) && isset($_POST['endDate'])) {
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $dateSearchCondition .= " AND w.workdate BETWEEN :startDate AND :endDate";
}

// Set the number of records per page and current page offset
$recordsPerPage = 20;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $recordsPerPage;

// SQL query to retrieve data with search conditions
$querylist = "SELECT w.*, p.p_name FROM tb_hr_profile p
              JOIN tb_hr_work_io w ON w.p_id = p.p_id
              WHERE 1=1 $searchCondition $dateSearchCondition
              ORDER BY w.workdate DESC
              OFFSET :offset ROWS
              FETCH NEXT :recordsPerPage ROWS ONLY";
$stmtList = $conn->prepare($querylist);
$stmtList->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmtList->bindParam(':recordsPerPage', $recordsPerPage, PDO::PARAM_INT);

// Bind search term parameter if it exists
if (!empty($searchTerm)) {
    $stmtList->bindValue(':searchTerm', $searchTerm, PDO::PARAM_STR);
}

// Bind date search term parameters if they exist
if (!empty($startDate) && !empty($endDate)) {
    $stmtList->bindParam(':startDate', $startDate, PDO::PARAM_STR);
    $stmtList->bindParam(':endDate', $endDate, PDO::PARAM_STR);
}

$stmtList->execute();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once('header_admin.php'); ?>
    <title>ประวัติบันทึกเวลา</title>

    <style>
        body {
            height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
        }

        .wrapper {
            flex: 1;
        }

        .mt-30 {
            margin-top: 30px;
        }

        .pagination {
            display: flex;
            list-style: none;
            padding: 0;
        }

        .pagination li {
            margin-right: 10px;
        }

        .gray-bg {
            background-color: #f8f9fa;
        }

        .container {
            padding: 20px;
        }

        .form-row {
            margin-top: 20px;
        }

        .form-row label {
            margin-bottom: 0;
        }

        .form-row .col {
            padding-right: 10px;
        }

        .form-row .btn {
            margin-top: 7px;
        }

        table {
            margin-top: 20px;
        }
    </style>
</head>

<body class="sub_page">
    <div class="wrapper">
        <div class="container mt-6">
            <h5 class="text-center mt-4">ประวัติบันทึกเวลา</h5>
            <form method="POST" action="service_admin1.php">
                <div class="form-row mt-3 d-flex align-items-center">
                    <div class="col col-sm-2">
                        <label for="startDate">ค้นหาจากวันที่ เริ่มต้น:</label>
                        <input type="date" class="form-control" id="startDate" name="startDate" required="true">
                    </div>
                    <div class="col col-sm-2">
                        <label for="endDate">สิ้นสุด:</label>
                        <input type="date" class="form-control" id="endDate" name="endDate" required="true">
                    </div>
                    <div class="col col-sm-3">
                        <button type="submit" class="btn btn-primary" id="filterBtn">ค้นหา</button>
                    </div>
                </div>
            </form>

            <?php
            echo '<table border="2" class="table">';
            echo '<thead class="gray-bg">';
            echo '<tr>';
            echo '<th>ลำดับ</th>';
            echo '<th>ชื่อ</th>';
            echo '<th>เวลาเข้างาน</th>';
            echo '<th>เวลาออกงาน</th>';
            echo '<th>วันที่</th>';
            echo '<th>สถานะ</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            $cnt = 1;
            while ($row = $stmtList->fetch(PDO::FETCH_ASSOC)) {
                echo '<tr>';
                echo '<td>' . $cnt . '</td>';
                echo '<td>' . $row['p_name'] . '</td>';
                echo '<td><p>' . $row['workin'] . '</p></td>';
                echo '<td>' . $row['workout'] . '</td>';
                echo '<td>' . $row['workdate'] . '</td>';
                echo '<td>';
                echo $row['w_status'] == '2' ? 'ปรับเปลี่ยนเวลาแล้ว' : '';
                echo $row['w_status'] == '1' ? 'สาย' : '';
                echo $row['w_status'] == '0' ? 'ปกติ' : '';
                echo '</td>';
                echo '<td>';
                echo '</td>';
                echo '</tr>';
                $cnt = $cnt + 1;
            }

            echo '</tbody>';
            echo '</table>';
            ?>

            <!-- Display pagination links -->
            <div style="padding-top: 10px;">
                <ul class="pagination">
                    <?php
                    $nextPage = $page + 1;
                    $prevPage = $page - 1;

                    if ($page > 1) {
                        echo '<li class="page-item"><a class="page-link" href="?page=' . $prevPage . '">Previous</a></li>';
                    }
                    echo '<li class="page-item"><a class="page-link" href="?page=' . $nextPage . '">Next</a></li>';
                    ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- JavaScript Dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script type="text/javascript" src="js/jquery-3.4.1.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.js"></script>
    <script type="text/javascript" src="js/custom.js"></script>
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
                    window.location.href = 'service_admin1.php';
                } else {
                    // Redirect to the same page with the selected date range as query parameters
                    window.location.href = 'service_admin1.php?startDate=' + startDate + '&endDate=' + endDate;
                }
            });
        });
    </script>
</body>

<!-- Footer Section -->
<?php include_once('footer.php'); ?>
</html>
