<?php
// Include necessary files and start the session
@session_start();
include 'class/class.scdb.php';
$query = new SCDB();
$mode = $_GET['Action'] ?? '';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get user input from the form
    $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Validate input (add more validation as needed)
    if (empty($newPassword) || empty($confirmPassword)) {
        $response = array('success' => false, 'message' => 'กรุณากรอกข้อมูลทุกช่อง');
    } elseif ($newPassword !== $confirmPassword) {
        $response = array('success' => false, 'message' => 'รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน');
    } else {
        // Retrieve p_cid and p_tel from the session
        $p_cid = $_SESSION['p_cid'] ?? '';
        $p_tel = $_SESSION['p_tel'] ?? '';

        // Fetch user data based on p_cid and p_tel
        $userParams = array($p_cid, $p_tel);
        $userResult = $query->execute("SELECT u_id, u_user FROM tb_hr_user_io WHERE p_id IN (SELECT p_id FROM tb_hr_profile WHERE p_cid = ? AND p_tel = ?)", $userParams);

        // Fetch the result as an associative array
        $userData = $userResult->fetch(PDO::FETCH_ASSOC);

        // Check if a matching user is found
        if ($userData) {
            $uId = $userData['u_id'];
            $uUser = $userData['u_user'];

            // Check if the new password is different from the current password
            $hashedNewPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $updateParams = array($hashedNewPassword, $uId);

            // Update the password in the database
            $query->execute("UPDATE tb_hr_user_io SET u_pass = ? WHERE u_id = ?", $updateParams);

            // Destroy the session variables
            session_unset();
            session_destroy();

            $response = array('success' => true, 'redirect' => 'index.php');
        } else {
            $response = array('success' => false, 'message' => 'ไม่พบข้อมูลผู้ใช้');
        }
    }

    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>สร้างรหัสผ่านใหม่</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="images/icons/favicon.ico" />
    <link rel="stylesheet" type="text/css" href="vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/animate/animate.css">
    <link rel="stylesheet" type="text/css" href="vendor/css-hamburgers/hamburgers.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/select2/select2.min.css">
    <link rel="stylesheet" type="text/css" href="css/util.css">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <div class="limiter">
        <div class="container-login100">
            <div class="wrap-login100">
                <div class="login100-pic js-tilt" data-tilt>
                    <img src="images/koratcoop.png" alt="IMG">
                </div>

                <form class="login100-form validate-form" method="post" action="?Action=createNewPassword" id="createNewPasswordForm">
                    <span class="login100-form-title">
                        สร้างรหัสผ่านใหม่
                    </span>

                    <?php if (isset($error)) : ?>
                        <p class="error-message"><?php echo $error; ?></p>
                    <?php endif; ?>

                    <div class="wrap-input100 validate-input" data-validate="รหัสผ่านใหม่ is required">
                        <input class="input100" type="password" name="new_password" placeholder="รหัสผ่านใหม่" aria-describedby="basic-addon1" id="new_password" required="true">
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-lock" aria-hidden="true"></i>
                        </span>
                    </div>

                    <div class="wrap-input100 validate-input" data-validate="ยืนยันรหัสผ่าน is required">
                        <input class="input100" type="password" name="confirm_password" placeholder="ยืนยันรหัสผ่าน" aria-describedby="basic-addon1" id="confirm_password" required="true">
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-lock" aria-hidden="true"></i>
                        </span>
                    </div>

                    <div class="container-login100-form-btn">
                        <button class="login100-form-btn">
                            บันทึก
                        </button>
                    </div>

                    <div class="text-center p-t-20">
                        <a href="index.php" class="nav-item nav-link ">หน้าเข้าสู่ระบบ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="vendor/jquery/jquery-3.2.1.min.js"></script>
    <script src="vendor/bootstrap/js/popper.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="vendor/select2/select2.min.js"></script>
    <script src="vendor/tilt/tilt.jquery.min.js"></script>
    <script>
        $('.js-tilt').tilt({
            scale: 1.1
        });
    </script>

    <script src="js/main.js"></script>

    <script>
        $(document).ready(function () {
            $('#createNewPasswordForm').submit(function (e) {
                e.preventDefault();

                $.ajax({
                    type: $(this).attr('method'),
                    url: $(this).attr('action'),
                    data: $(this).serialize(),
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'เปลี่ยนรหัสผ่านสำเร็จ',
                                text: 'กำลังเปลี่ยนเส้นทาง...',
                                icon: 'success',
                                timer: 1000,
                                timerProgressBar: true,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = response.redirect;
                            });
                        } else {
                            Swal.fire({
                                title: 'เปลี่ยนรหัสผ่านไม่สำเร็จ',
                                text: response.message,
                                icon: 'error',
                                showConfirmButton: true
                            });
                        }
                    }
                });
            });
        });
    </script>
</body>

</html>
