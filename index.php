<?php 
@session_start();
include 'class/class.scdb.php';
$query = new SCDB();
$mode = $_GET['Action'] ?? '';

if ($mode == "chklogin") {
    $user_no = $_POST['txtuser'] ?? '';
    $user_pw = $_POST['txtpass'] ?? '';

    $params = array($user_no, $user_pw);
    $result = $query->fetch("SELECT p_id, u_user, u_status FROM tb_hr_user WHERE u_user = ? AND u_pass = ?", $params);

    if (is_array($result) && (count($result) > 0)) {
        $user = $result['u_user'];
        $p_id = $result['p_id'];
        $u_status = $result['u_status'];
        $_SESSION['USER_NO'] = $user;
        $_SESSION['p_id'] = $p_id;

        if ($u_status == 1) {
            // Redirect to service_admin.php if u_status is 1
            header('Location: admin/index.php');
        } elseif ($u_status == 0) {
            // Redirect to index.php if u_status is 0
            header('Location: service.php');
        }
    } else {
        echo "<script language='JavaScript' type='text/javascript'> alert('ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง'); </script>";
        print('<meta http-equiv="refresh" content="2;url=index.php">');
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
	<title>ระบบบันทึกเวลาการทำงาน</title>
	<meta charset="UTF-8">
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
<body>
	
	<div class="limiter">
		<div class="container-login100">
			<div class="wrap-login100">
				<div class="login100-pic js-tilt" data-tilt>
					<img src="images/img-01.png" alt="IMG">
				</div>

				<form class="login100-form validate-form" method="post" action="?Action=chklogin" onSubmit="return checkform(this);">
					<span class="login100-form-title">
					 ระบบบันทึกเวลาการทำงาน
					</span>

					<div class="wrap-input100 validate-input" data-validate = "Valid email is required: ex@abc.xyz">
						<input class="input100" type="text" name="txtuser" placeholder="เลขทะเบียนเจ้าหน้าที่" id="txtuser" oninput=" this.value = this.value.replace(/(\..*)\./g, '$1');" onKeyDown="if(this.value.length==10 && event.keyCode!=11 && event.keyCode!=12) return false;" required="true">
						<span class="focus-input100"></span>
						<span class="symbol-input100">
							<i class="fa fa-envelope" aria-hidden="true"></i>
						</span>
					</div>

					<div class="wrap-input100 validate-input" data-validate = "Password is required">
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

					<div class="text-center p-t-12">
						<span class="txt1">
							Forgot
						</span>
						<a class="txt2" href="#">
							Username / Password?
						</a>
					</div>

					<div class="text-center p-t-136">
					</div>
				</form>
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