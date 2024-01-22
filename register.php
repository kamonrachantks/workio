<?php
// Include necessary files and start the session
@session_start();
include 'class/class.scdb.php';
$query = new SCDB();
$mode = $_GET['Action'] ?? '';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get user input from the form
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $p_cid = isset($_POST['p_cid']) ? $_POST['p_cid'] : '';

    // Validate input (add more validation as needed)
    if (empty($username) || empty($password) || empty($confirmPassword) || empty($p_cid)) {
        $response = array('success' => false, 'message' => 'กรุณากรอกข้อมูลทุกช่อง');
    } elseif ($password !== $confirmPassword) {
        $response = array('success' => false, 'message' => 'รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน');
    } else {
        // Hash the password using password_hash
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Retrieve p_id from tb_hr_profile based on p_cid
        $profileParams = array($p_cid);
        $profileResult = $query->execute("SELECT p_id FROM tb_hr_profile WHERE p_cid = ?", $profileParams);

        // Fetch the result as an associative array
        $profileData = $profileResult->fetch(PDO::FETCH_ASSOC);

        // Check if a matching profile is found
        if ($profileData) {
            $pId = $profileData['p_id'];

            // Perform the registration and store the hashed password, p_id, and u_status in the database
            $userParams = array($username, $hashedPassword, $pId, 0);
            $query->execute("INSERT INTO tb_hr_user_io (u_user, u_pass, p_id, u_status) VALUES (?, ?, ?, ?)", $userParams);

            $response = array('success' => true, 'redirect' => 'index.php');
        } else {
            $response = array('success' => false, 'message' => 'ไม่พบข้อมูลที่เชื่อมโยงกับเลขบัตรประชาชนนี้');
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
    <title>ระบบบันทึกเวลาการทำงาน</title>
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

                <form class="login100-form validate-form" method="post" action="?" id="registerForm">
                    <span class="login100-form-title">
                        ลงทะเบียน
                    </span>

                    <?php if (isset($error)) : ?>
                        <p class="error-message"><?php echo $error; ?></p>
                    <?php endif; ?>

                    <div class="wrap-input100 validate-input" data-validate="เลขบัตรประชาชน is required">
                        <input class="input100" type="text" name="p_cid" placeholder="เลขบัตรประชาชน" aria-describedby="basic-addon1" id="p_cid" required="true">
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-id-card" aria-hidden="true"></i>
                        </span>
                    </div>

                    <div class="wrap-input100 validate-input" data-validate="ชื่อผู้ใช้ is required">
                        <input class="input100" type="text" name="username" placeholder="ชื่อผู้ใช้" id="username" oninput="this.value = this.value.replace(/(\..*)\./g, '$1');" onKeyDown="if(this.value.length==10 && event.keyCode!=11 && event.keyCode!=12) return false;" required="true">
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-user" aria-hidden="true"></i>
                        </span>
                    </div>

                    <div class="wrap-input100 validate-input" data-validate="Password is required">
                        <input class="input100" type="password" name="password" placeholder="รหัสผ่าน" aria-describedby="basic-addon1" id="password" required="true">
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
                            ลงทะเบียน
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
            $('#registerForm').submit(function (e) {
                e.preventDefault();

                $.ajax({
                    type: $(this).attr('method'),
                    url: $(this).attr('action'),
                    data: $(this).serialize(),
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'ลงทะเบียนสำเร็จ',
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
                                title: 'ลงทะเบียนไม่สำเร็จ',
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
