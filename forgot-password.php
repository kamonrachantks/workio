<?php
// Include necessary files and start the session
@session_start();
include 'class/class.scdb.php';
$query = new SCDB();
$mode = $_GET['Action'] ?? '';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get user input from the form
    $p_cid = isset($_POST['p_cid']) ? $_POST['p_cid'] : '';
    $p_tel = isset($_POST['p_tel']) ? $_POST['p_tel'] : '';

    // Validate input (add more validation as needed)
    if (empty($p_cid) || empty($p_tel)) {
        $response = array('success' => false, 'message' => 'กรุณากรอกข้อมูลทุกช่อง');
    } else {
        // Check if the provided p_cid and p_tel match an entry in tb_hr_profile
        $profileParams = array($p_cid, $p_tel);
        $profileResult = $query->execute("SELECT p_id FROM tb_hr_profile WHERE p_cid = ? AND p_tel = ?", $profileParams);

        // Fetch the result as an associative array
        $profileData = $profileResult->fetch(PDO::FETCH_ASSOC);

        // Check if a matching profile is found
        if ($profileData) {
            // If the profile is found, redirect to the page for creating a new password
            $_SESSION['p_cid'] = $p_cid;
            $_SESSION['p_tel'] = $p_tel;

            $response = array('success' => true, 'redirect' => 'create_new_password.php');
        } else {
            $response = array('success' => false, 'message' => 'ข้อมูลไม่ถูกต้อง');
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
    <title>ลืมรหัสผ่าน</title>
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

                <form class="login100-form validate-form" method="post" action="?Action=forgotPassword" id="forgotPasswordForm">
                    <span class="login100-form-title">
                        ลืมรหัสผ่าน
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

                    <div class="wrap-input100 validate-input" data-validate="เบอร์โทรศัพท์ is required">
                        <input class="input100" type="text" name="p_tel" placeholder="เบอร์โทรศัพท์" aria-describedby="basic-addon1" id="p_tel" required="true">
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-phone" aria-hidden="true"></i>
                        </span>
                    </div>

                    <div class="container-login100-form-btn">
                        <button class="login100-form-btn">
                            ตรวจสอบข้อมูล
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
            $('#forgotPasswordForm').submit(function (e) {
                e.preventDefault();

                $.ajax({
                    type: $(this).attr('method'),
                    url: $(this).attr('action'),
                    data: $(this).serialize(),
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'ข้อมูลถูกต้อง',
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
                                title: 'ข้อมูลไม่ถูกต้อง',
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
