<?php
include 'sqlsrv_connect.php';

if (!isset($_SESSION['USER_NO']) || empty($_SESSION['USER_NO'])) {
    header("location: index.php");
    exit();
}

$u_id = $_SESSION['p_id'];

$queryemp = "SELECT * FROM tb_hr_profile WHERE p_id=?";
$resultm = $conn->prepare($queryemp);
$resultm->execute([$u_id]);

$querylist = "SELECT * FROM tb_hr_work_io WHERE p_id = ? ORDER BY workdate DESC";
$resultlist = $conn->prepare($querylist);
$resultlist->execute([$u_id]);

if (!$resultm) {
    die(print_r($conn->errorInfo(), true));
}

$rowm = $resultm->fetch(PDO::FETCH_ASSOC);

date_default_timezone_set('Asia/Bangkok');
$timenow = date('H:i:s');
$datenow = date('Y-m-d');

$queryworkio = "SELECT MAX(workdate) as lastdate, MAX(workin) as workin, MAX(workout) as workout FROM tb_hr_work_io WHERE p_id=? AND workdate=?";
$resultio = $conn->prepare($queryworkio);
$resultio->execute([$u_id, $datenow]);

if (!$resultio) {
    die(print_r($conn->errorInfo(), true));
}

$rowio = $resultio->fetch(PDO::FETCH_ASSOC);

ob_start();
system('ipconfig/all');
$mycom = ob_get_contents();
ob_clean();
$findme = "Physical";
$pmac = strpos($mycom, $findme);
$mac = substr($mycom, ($pmac + 36), 17);

$query_mac_check = "SELECT * FROM tb_hr_mac_ad WHERE p_id = ? AND mac_address = ?";
$result_mac_check = $conn->prepare($query_mac_check);
$result_mac_check->execute([$u_id, $mac]);

if (!$result_mac_check) {
    die(print_r($conn->errorInfo(), true));
}

$mac_exists = $result_mac_check->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ระบบบันทึกเวลาการทำงาน</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
        integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@1,300&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
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

        .container {
            margin-top: 6rem;
        }

        .card {
            border: 3px solid #28a745;
            border-radius: 10px;
            max-width: 500px;
            margin: auto;
            padding: 20px;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .btn-primary {
            background-color: #28a745;
            border: none;
        }

        @media (max-width: 576px) {
            .container {
                margin-top: 3rem;
            }

            .card {
                max-width: 100%;
            }
        }
    </style>
</head>

<?php include_once('header.php'); ?>

<body class="sub_page">
    <div class="wrapper">
        <div class="container">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">ลงเวลาเข้า-ออกงาน <?php echo date('d-m-Y');?></h3>
                    <h5 class="card-title"><?php echo $rowm['p_name']; ?></h5>
                    <form id="workForm" method="post" class="form-horizontal">
                        <input type="hidden" name="p_id" id="p_id">
                        <input type="hidden" name="latitude" id="latitude">
                        <input type="hidden" name="longitude" id="longitude">
                        <input type="hidden" name="ip_address" id="ip_address" value="<?php echo $_SERVER['REMOTE_ADDR'];?>">
                        <input type="hidden" name="mac_address" id="mac_address"
                            value="<?php echo $mac=substr($mycom,($pmac+36),17);?>">
                        <input type="hidden" name="annotation" id="annotation" value="">
                        <input type="hidden" name="mac_matches" id="mac_matches" value="">
                        <input type="hidden" name="w_status" id="w_status" value="">

                        <div class="form-group row">
                            <input type="hidden" class="form-control " name="p_id" placeholder="รหัสพนักงาน"
                                value="<?php echo $rowm['p_id'];?>" readonly>

                            <div class="col col-sm-4">
                                <label for="p_id">เวลาเข้างาน</label>
                                <?php if(isset($rowio['workin'])){ ?>
                                <input type="text" class="form-control" name="workin" value="<?php echo $rowio['workin'];?>"
                                    disabled>
                                <?php } else { ?>
                                <input type="text" class="form-control" name="workin" value="<?php echo date('H:i:s');?>"
                                    readonly>
                                    <?php
if ($timenow < '08:30:00') {

} else {
    echo '<input type="hidden" class="form-control" name="w_status" value="1">';
    echo '<label for="p_id" style="font-size: 12px;">เข้างานเกินเวลา</label>';
    echo '<input type="text" class="form-control" name="annotation" value="" placeholder="กรอกเหตุผล (หากมี)" style="font-size: 12px;" required>';
}

if ($mac_exists) {
    echo '<input type="hidden" class="form-control" name="mac_matches" value="0">';
    
} else {
    echo '<label for="p_id" style="font-size: 12px;">ใช่เครื่องเดียวกันคนอื่น</label>';
    echo '<input type="text" class="form-control" name="mac_matches" id="mac_matches" value="" placeholder="กรอกเหตุผล (บังคับ)" style="font-size: 12px;" required>';
}

?>
                                <?php } ?>
                            </div>

                            <div class="col col-sm-4">
                                <label for="p_id">เวลาออกงาน</label>
                                <?php
                                    if($timenow > '09:30:00'){
                                        if(isset($rowio['workout'])){ ?>
                                <input type="text" class="form-control" name="workout" value="<?php echo $rowio['workout'];?>"
                                    disabled>
                                <?php }else{ ?>
                                <input type="text" class="form-control" name="workout" value="<?php echo date('H:i:s');?>"
                                    readonly>
                                <?php
                                    }
                                }else{ ?>
                                <br><font color="red"> หลัง 16.30 น. </font>
                                <?php } ?>
                            </div>
                            <div class="col col-sm-1">
                                <label>-</label>
                                <button type="button" class="btn btn-primary"
                                    onclick="getLocation(); submitForm();">บันทึก</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            const x = document.getElementById("demo");

            function getLocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(showPosition);
                } else {
                    x.innerHTML = "Geolocation is not supported by this browser.";
                }
            }

            function showPosition(position) {
                // Set latitude and longitude values in the hidden input fields
                document.querySelector('#latitude').value = position.coords.latitude;
                document.querySelector('#longitude').value = position.coords.longitude;
            }

            getLocation();

            document.getElementById("saveButton").addEventListener("click", function () {
                submitForm();
            });

            function submitForm() {
    // Check if required fields are filled
    if (!validateForm()) {
        return;
    }

    // Gather form data
    var formData = $("#workForm").serialize();

    // AJAX request
    $.ajax({
        type: "POST",
        url: "save.php",
        data: formData,
        dataType: "json",
        success: function (response) {
            if (response.status === 'success') {
                // Show SweetAlert2 success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message,
                }).then((result) => {
                    // Redirect if needed
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    }
                });
            } else {
                // Show SweetAlert2 error message
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message,
                });
            }
        },
    });
}

function validateForm() {
    var isValid = true;

    // Check if required fields are filled
    if ($('#mac_matches:required').val() === '') {
        isValid = false;
        Swal.fire({
            icon: 'error',
            title: 'ลงเวลาเข้าไม่สำเร็จ',
            text: 'กรุณากรอกเหตุผลในช่อง (บังคับ)',
        });
    }

    // Add similar checks for other required fields

    return isValid;
}

        </script>
        
    </div>
</body>

</html>
