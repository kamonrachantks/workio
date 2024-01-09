<?php
@session_start();

include 'class/class.scdb.php';

$query = new SCDB();

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the work ID and new workin from the POST data
    $workId = $_POST['w_id'];
    $newWorkin = $_POST['new_workin'];

    try {
        if (!$query->connect()) {
            throw new Exception("Database connection error: " . $query->getError());
        }

        // Update the workin in the database to the new value
        $sqlUpdateWorkin = "UPDATE tb_hr_work_io SET workin = :new_workin WHERE w_id = :work_id";
        $stmtUpdateWorkin = $query->prepare($sqlUpdateWorkin);
        $stmtUpdateWorkin->bindParam(':new_workin', $newWorkin, PDO::PARAM_STR);
        $stmtUpdateWorkin->bindParam(':work_id', $workId, PDO::PARAM_INT);
        $stmtUpdateWorkin->execute();

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
