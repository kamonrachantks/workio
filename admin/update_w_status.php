<?php
@session_start();

include 'class/class.scdb.php';

$query = new SCDB();

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the work ID and new w_status from the POST data
    $workId = $_POST['w_id'];
    $newWStatus = $_POST['new_w_status'];

    try {
        if (!$query->connect()) {
            throw new Exception("Database connection error: " . $query->getError());
        }

        // Update the w_status in the database to the new value
        $sqlUpdateWStatus = "UPDATE tb_hr_work_io SET w_status = :new_w_status WHERE w_id = :work_id";
        $stmtUpdateWStatus = $query->prepare($sqlUpdateWStatus);
        $stmtUpdateWStatus->bindParam(':new_w_status', $newWStatus, PDO::PARAM_INT);
        $stmtUpdateWStatus->bindParam(':work_id', $workId, PDO::PARAM_INT);
        $stmtUpdateWStatus->execute();

        // Respond to the client
        echo "Success";
    } catch (Exception $e) {
        // Log or handle the exception appropriately
        header("HTTP/1.1 500 Internal Server Error");
        echo "An error occurred: " . $e->getMessage();
    }
} else {
    // Respond with a 400 Bad Request if the request method is not POST
    header("HTTP/1.1 400 Bad Request");
    echo "Bad Request";
}
?>
