<?php
@session_start();
include 'class/class.scdb.php';

$query = new SCDB();
$mode = $_GET['Action'] ?? '';

if ($mode == "chklogin") {
    $user_no = $_POST['txtuser'] ?? '';
    $user_pw = $_POST['txtpass'] ?? '';

    $params = array($user_no);
    $result = $query->fetch("SELECT p_id, u_user, u_pass, u_status FROM tb_hr_user_io WHERE u_user = ?", $params);

    if (is_array($result) && count($result) > 0) {
        $hashed_password = $result['u_pass'];

        if (password_verify($user_pw, $hashed_password)) {
            $user = $result['u_user'];
            $p_id = $result['p_id'];
            $u_status = $result['u_status'];
            $_SESSION['USER_NO'] = $user;
            $_SESSION['p_id'] = $p_id;

            if ($u_status == 1) {
                $response = array('success' => true, 'redirect' => 'admin/index.php');
            } elseif ($u_status == 0) {
                $response = array('success' => true, 'redirect' => 'service.php');
            }
        } else {
            $response = array('success' => false, 'message' => 'ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง');
        }
    } else {
        $response = array('success' => false, 'message' => 'ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง');
    }

    // Send the JSON response to the AJAX request
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

                <form class="login100-form validate-form" method="post" action="?Action=chklogin" id="loginForm">
                    <span class="login100-form-title">
                        ระบบบันทึกเวลาการทำงาน
                    </span>

                    <div class="wrap-input100 validate-input" data-validate="Valid email is required: ex@abc.xyz">
                        <input class="input100" type="text" name="txtuser" placeholder="ชื่อผู้ใช้" id="txtuser" oninput="this.value = this.value.replace(/(\..*)\./g, '$1');" onKeyDown="if(this.value.length==10 && event.keyCode!=11 && event.keyCode!=12) return false;" required="true">
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                          <i class="fa fa-user" aria-hidden="true"></i>
                        </span>
                    </div>

                    <div class="wrap-input100 validate-input" data-validate="Password is required">
                        <input class="input100" type="password" name="txtpass" placeholder="รหัสผ่าน" aria-describedby="basic-addon1" id="txtpass" required="true">
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-lock" aria-hidden="true"></i>
                        </span>
                    </div>

                    <div class="container-login100-form-btn">
                        <button class="login100-form-btn">
                            เข้าสู่ระบบ
                        </button>
                    </div>

                    <div class="text-center p-t-20">
                    <a href="register.php" class="nav-item nav-link ">ลงทะเบียน</a>
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
        })
    </script>

    <script>
        $(document).ready(function() {
            $('#loginForm').submit(function(e) {
                e.preventDefault();

                $.ajax({
                    type: $(this).attr('method'),
                    url: $(this).attr('action'),
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'เข้าสู่ระบบสำเร็จ',
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
                                title: 'เข้าสู่ระบบไม่สำเร็จ',
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

    <script src="js/main.js"></script>
</body>

</html>
