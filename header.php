<!DOCTYPE html>
<html lang="en">
<head>

<!-- Bootstrap CSS -->
<link href="path/to/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<!-- jQuery -->
<script src="path/to/jquery.min.js"></script>

<!-- Bootstrap JS -->
<script src="path/to/bootstrap/js/bootstrap.min.js"></script>

<nav class="navbar navbar-expand-lg bg-white navbar-light py-0 px-4">
    <!-- Navbar content -->
        <a  class="navbar-brand d-flex align-items-center text-center">
        <div class="icon p-2 me-2">
                <img class="img-fluid" src="img/clockwise.png" alt="Icon" style="width: 30px; height: 30px;">
            </div>
            <h4 class="m-0 text-primary">ระบบบันทึกเวลาการทำงาน</h4>
        </a>
        <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav ms-auto">
            
                <?php if (!isset($_SESSION['p_id']) || $_SESSION['p_id'] == 0) { ?>
                    <a href="login.php" class="nav-item nav-link active">เข้าสู่ระบบ</a>
                <?php } ?>

                <?php if (isset($_SESSION['p_id']) && $_SESSION['p_id'] > 0) { ?>
                    <a href="service.php" class="nav-item nav-link ">ลงเวลา</a>
                    <a href="service02.php" class="nav-item nav-link ">ประวัติลงเวลา</a>
                    <a href="logout.php" class="nav-item nav-link active">ออกจากระบบ</a>
                <?php } ?>
            </div>
        </div>
    </nav>

    
</html>
