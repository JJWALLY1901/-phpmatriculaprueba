<?php

// Include necessary files
require_once 'DatabaseConnection.php';
require_once 'Mark.php';

// Start session
session_start();

// Add a new Mark
if (isset($_POST['addMark'])) {
    // Extract data from the form
    $enrollmentID = $_POST['markEnrollmentID'];
    $mark = $_POST['mark'];
    $status = $_POST['status'];
    $remark = $_POST['remark'];

    // Check if required fields are not empty
    if (!empty(trim($enrollmentID)) && !empty(trim($mark)) && !empty(trim($status)) && !empty(trim($remark))) {

        // Create a new Mark object
        $newMark = new Mark(0, $enrollmentID, null, null, $mark, $status, $remark);

        // Try saving the Mark to the database
        try {
            $val = $newMark->saveToDatabase($conn);
            if ($val) {
                $_SESSION['success'] = "New Mark added";
            } else {
                $_SESSION['error'] = "Mark not added";
            }
        } catch (PDOException $ex) {
            $_SESSION['error'] = "Mark not added - " . $ex->getMessage();
        }

    } else {
        // If any required field is empty, set error message
        $_SESSION['error'] = "Mark data cannot be empty";
    }

    // Redirect back to the dashboard
    header('Location: marks.php');
    exit(); 
}

// Edit an existing Mark
if (isset($_POST['edit'])) {
    // Extract data from the form
    $markID = $_POST['markID'];
    $enrollmentID = $_POST['markEnrollmentID'];
    $mark = $_POST['mark'];
    $status = $_POST['status'];
    $remark = $_POST['remark'];

    // Check if required fields are not empty
    if (!empty(trim($markID)) && !empty(trim($enrollmentID)) && !empty(trim($mark)) && !empty(trim($status)) && !empty(trim($remark))) {

        // Create a new Mark object with updated details
        $newMark = new Mark(0, $enrollmentID, null, null, $mark, $status, $remark);

        // Try updating the Mark in the database
        try {
            $val = $newMark->update($conn, $markID);
            if ($val) {
                $_SESSION['success'] = "Mark Edited";
            } else {
                $_SESSION['error'] = "Mark not Edited";
            } 
        } catch (PDOException $ex) {
            $_SESSION['error'] = "Mark not Edited - " . $ex->getMessage();
        }
        
    } else {
        // If any required field is empty, set error message
        $_SESSION['error'] = "Mark not Edited";
    }

    // Redirect back to the dashboard
    header('Location: marks.php');
    exit();
}

// Remove a Mark
elseif (isset($_POST['remove'])) {
    // Extract Mark ID from the form
    $mark_id = $_POST['removeMarkID'];

    // Check if Mark ID is not empty
    if (!empty(trim($mark_id))) {

        // Create a new Mark object
        $newMark = new Mark();

        // Try deleting the Mark from the database
        try {
            $val = $newMark->deleteById($conn, $mark_id);
            if ($val) {
                $_SESSION['success'] = "Mark deleted";
            } else {
                $_SESSION['error'] = "Mark not deleted";
            }
        } catch (PDOException $ex) {
            $_SESSION['error'] = "Mark not deleted - " . $ex->getMessage();
        }

    } else {
        // If Mark ID is empty, set error message
        $_SESSION['error'] = "One or more fields are empty";
    }

    // Redirect back to the dashboard
    header('Location: marks.php');
    exit();
}
?>
