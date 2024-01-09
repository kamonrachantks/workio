<?php
@session_start();

include 'class/class.scdb.php';

$query = new SCDB();

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the work ID from the POST data
    $workId = $_POST['w_id'];

    try {
        if (!$query->connect()) {
            throw new Exception("Database connection error: " . $query->getError());
        }

        // Remove the annotation column value in the database
        $sqlRemoveAnnotation = "UPDATE tb_hr_work_io SET annotation = NULL WHERE w_id = :work_id";
        $stmtRemoveAnnotation = $query->prepare($sqlRemoveAnnotation);
        $stmtRemoveAnnotation->bindParam(':work_id', $workId, PDO::PARAM_INT);
        $stmtRemoveAnnotation->execute();

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
