
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
<link href="img/favicon.ico" rel="icon">

<!-- Google Web Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Inter:wght@700;800&display=swap" rel="stylesheet">

<!-- Icon Font Stylesheet -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

<!-- Libraries Stylesheet -->
<link href="lib/animate/animate.min.css" rel="stylesheet">
<link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

<!-- Customized Bootstrap Stylesheet -->
<link href="css/bootstrap.min.css" rel="stylesheet">

<!-- Template Stylesheet -->
<link href="css/style.css" rel="stylesheet">


    <nav class="navbar navbar-expand-lg bg-white navbar-light py-0 px-4">
        <a  class="navbar-brand d-flex align-items-center text-center">
        <div class="icon p-2 me-2">
                <img class="img-fluid" src="img/clockwise.png" alt="Icon" style="width: 30px; height: 30px;">
            </div>
            <h4 class="m-0 text-primary">บันทึกเวลาการทำงาน</h4>
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
                    <a href="index.php" class="nav-item nav-link ">ลงเวลา</a>
                    <a href="service02.php" class="nav-item nav-link ">ประวัติลงเวลา</a>
                    <a href="service.php" class="nav-item nav-link ">รายการอนุมัติ </a>
                    <a href="logout.php" class="nav-item nav-link active">ออกจากระบบ</a>
                <?php } ?>
            </div>
        </div>
    </nav>

    </html>
