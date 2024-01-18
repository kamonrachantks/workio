<?php
include 'class/class.scdb.php';
include 'sqlsrv_connect.php';

$u_id = $_SESSION['p_id'];
$query = new SCDB();

// Redirect to the login page if session variables are not set
if (!isset($_SESSION['USER_NO']) || $_SESSION['USER_NO'] == '') {
    header("location: login.php");
    exit();
}

// Add this code before preparing the SQL statement
$searchCondition = "";
$searchTerm = "";

if (isset($_POST['search']) && !empty($_POST['search'])) {
    $searchTerm = $_POST['search'];
    $searchCondition .= " AND p.p_id = :searchTerm";
}

// Add date search condition
$dateSearchCondition = "";
$dateSearchTerm = "";
if (isset($_POST['date_search']) && !empty($_POST['date_search'])) {
    $dateSearchTerm = date('Y-m-d', strtotime($_POST['date_search']));
    $dateSearchCondition .= " AND w.workdate = :dateSearchTerm";
}

$recordsPerPage = 20;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $recordsPerPage;

// Modify the SQL query to include the search conditions and join with tb_hr_profile
$querylist = "SELECT w.*, p.p_name FROM tb_hr_work_io w
              JOIN tb_hr_profile p ON w.p_id = p.p_id
              WHERE 1=1 $searchCondition $dateSearchCondition and  w.w_status = 1
              ORDER BY w.workdate DESC
              OFFSET :offset ROWS
              FETCH NEXT :recordsPerPage ROWS ONLY";
$stmtList = $conn->prepare($querylist);
$stmtList->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmtList->bindParam(':recordsPerPage', $recordsPerPage, PDO::PARAM_INT);

// Bind the search term parameters if they exist
if (!empty($searchTerm)) {
    $stmtList->bindParam(':searchTerm', $searchTerm, PDO::PARAM_INT);
}

// Bind the date search term parameter if it exists
if (!empty($dateSearchTerm)) {
    $stmtList->bindParam(':dateSearchTerm', $dateSearchTerm, PDO::PARAM_STR);
}

$stmtList->execute();

$offset = ($page - 1) * $recordsPerPage;

// Initialize $nextPage and $prevPage
$nextPage = $page + 1;
$prevPage = $page - 1;



?>

<!DOCTYPE html>
<html lang="en">

<head>
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
    </style>
</head>

<body class="sub_page">
    <div class="wrapper">
        <div class="hero_area">
            <!-- Header Section -->
            <?php include_once('header_admin.php'); ?>
            <!-- End Header Section -->
        </div>

        <!-- About Section -->
        <section class="about_section">
            <div class="container">
                <div class="row">
                    <section class="w3l-contact-info-main col-md-12" id="contact">
                        <div class="contact-sec">
                            <div class="container">
                                <div>
                                    <div class="cont-details">
                                        <div class="table-content table-responsive cart-table-content m-t-30">
                                            <div style="padding-top: 30px;">
                                                <h4 style="padding-bottom: 20px;text-align: center;color: #5c6bc0 ;">รายการมาสาย </h4>
                                                <div style="padding-top: 10px;">
                                                    <form method="post">
                                                        <label for="search">ค้นหา :</label>
                                                        <input type="text" name="search" id="search" placeholder="กรอกรหัสพนักงาน">
                                                        <label for="date_search">ค้นหาจากวันที่ :</label>
                                                        <input type="date" name="date_search" id="date_search">
                                                        <button type="submit" class="btn btn-primary">ค้นหา</button>
                                                    </form>
                                                </div>
                                                <div style="padding-top: 30px;">
                                                    <table border="2" class="table">
                                                        <thead class="gray-bg">
                                                            <tr>
                                                                <th>ลำดับ</th>
                                                                <th>Id</th>
                                                                <th>ชื่อ</th>
                                                                <th>เวลาเข้างาน</th>
                                                                <th>เวลาออกงาน</th>
                                                                <th>วันที่</th>
                                                                <th>สถานะ</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $cnt = 1;
                                                            while ($row = $stmtList->fetch(PDO::FETCH_ASSOC)) {
                                                                echo '<tr>';
                                                                echo '<td>' . $cnt . '</td>';
                                                                echo '<td>' . $row['p_id'] . '</td>';
                                                                echo '<td>' . $row['p_name'] . '</td>';
                                                                echo '<td><p>' . $row['workin'] . '</p></td>';
                                                                echo '<td>' . $row['workout'] . '</td>';
                                                                echo '<td>' . $row['workdate'] . '</td>';
                                                                echo '<td>' . ($row['w_status'] == '1' ? 'มาสาย' : '') . '</td>';
                                                                echo '<td>';
                                                                echo '<button class="btn btn-primary" onclick="confirmAction(' . $row['w_id'] . ')">ปรับเปลี่ยนเวลา</button>';
                                                                // echo '<button class="btn btn-secondary" onclick="cancelAction(' . $row['w_id'] . ')">ยกเลิก</button>';
                                                                echo '</td>';
                                                                echo '</tr>';
                                                                $cnt = $cnt + 1;
                                                            }
                                                            ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div style="padding-top: 10px;">
                                                    <ul class="pagination">
                                                        <?php
                                                        if ($page > 1) {
                                                            echo '<li><a href="?page=' . $prevPage . '">Previous</a></li>';
                                                        }
                                                        echo '<li><a href="?page=' . $nextPage . '">Next</a></li>';
                                                        ?>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </section>
        <!-- End About Section -->

        <!-- JavaScript Dependencies -->
        <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
        <script type="text/javascript" src="js/bootstrap.js"></script>
        <script type="text/javascript" src="js/custom.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
        <script>
            function confirmAction(workId) {
                $.ajax({
                    type: "POST",
                    url: "update_status.php",
                    data: {
                        w_id: workId,
                        new_status: 2
                    },
                    success: function (response) {
                        updateWStatus(workId);
                    },
                    error: function (xhr, status, error) {
                        handleError("Error updating status: " + error);
                    }
                });
            }

            function updateWStatus(workId) {
                $.ajax({
                    type: "POST",
                    url: "update_w_status.php",
                    data: {
                        w_id: workId,
                        new_w_status: 2
                    },
                    success: function (response) {
                        removeAnnotation(workId);
                    },
                    error: function (xhr, status, error) {
                        handleError("Error updating w_status: " + error);
                    }
                });
            }

            function removeAnnotation(workId) {
                $.ajax({
                    type: "POST",
                    url: "remove_annotation.php",
                    data: {
                        w_id: workId
                    },
                    success: function (response) {
                        handleSuccess("Status, w_status, and annotation updated successfully!");
                    },
                    error: function (xhr, status, error) {
                        handleError("Error removing annotation: " + error);
                    }
                });
            }

            function handleError(errorMessage) {
                console.error(errorMessage);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred. Please try again.',
                });
            }

            function handleSuccess(successMessage) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: successMessage,
                }).then((result) => {
                    if (result.isConfirmed || result.isDismissed) {
                        location.reload();
                    }
                });
            }
        </script>

    </div>
</body>


<!-- Footer Section -->
<?php include_once('footer.php'); ?>
</html>
