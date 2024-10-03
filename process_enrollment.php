<?php

// Include necessary files
require_once 'DatabaseConnection.php';
require_once 'Enrollment.php';

// Start session
session_start();

// Add a new Enrollment
if (isset($_POST['add'])) {
    // Retrieve data from form fields
    $studentID = $_POST['studentID'];
    $courseID = $_POST['courseID'];
    $enrollmentDate = $_POST['enrollmentDate'];
    $enrollmentStatus = $_POST['enrollmentStatus'];

    // Check if form fields are not empty
    if (!empty(trim($studentID)) && !empty(trim($courseID)) && !empty(trim($enrollmentDate)) && !empty(trim($enrollmentStatus))) {

        // Create a new Enrollment object
        $newEnrollment = new Enrollment(0, $studentID, null, $courseID, null, $enrollmentDate, $enrollmentStatus);

        try {
            // Save the new enrollment to the database
            $val = $newEnrollment->saveToDatabase($conn);
            if ($val) {
                $_SESSION['success'] = "New Enrollment added";
            } else {
                $_SESSION['error'] = "Enrollment not added";
            }

        } catch (PDOException $ex) {
            $_SESSION['error'] = "Enrollment not added - " . $ex->getMessage();
        }

    } else {
        // Error message if form fields are empty
        $_SESSION['error'] = "Enrollment data cannot be empty";
    }

    // Redirect back to the dashboard
    header('Location: enrollments.php');
    exit();
}

// Edit Enrollment
if (isset($_POST['edit'])) {
    // Retrieve data from form fields
    $enrollmentID = $_POST['enrollmentID'];
    $studentID = $_POST['studentID'];
    $courseID = $_POST['courseID'];
    $enrollmentDate = $_POST['enrollmentDate'];
    $enrollmentStatus = $_POST['enrollmentStatus'];

    // Check if form fields are not empty
    if (!empty(trim($studentID)) && !empty(trim($courseID)) && !empty(trim($enrollmentDate)) && !empty(trim($enrollmentStatus))) {

        // Create a new Enrollment object
        $newEnrollment = new Enrollment(0, $studentID, null, $courseID, null, $enrollmentDate, $enrollmentStatus);

        try {
            // Update the enrollment in the database
            $val = $newEnrollment->update($conn, $enrollmentID);
            if ($val) {
                $_SESSION['success'] = "Enrollment Edited";
            } else {
                $_SESSION['error'] = "Enrollment not Edited";
            }

        } catch (PDOException $ex) {
            $_SESSION['error'] = "Enrollment not Edited - " . $ex->getMessage();
        }

    } else {
        // Error message if form fields are empty
        $_SESSION['error'] = "Enrollment not Edited";
    }

    // Redirect back to the dashboard
    header('Location: enrollments.php');
    exit();
}

// Remove Enrollment
elseif (isset($_POST['remove'])) {
    // Retrieve enrollment ID from form field
    $enrollment_id = $_POST['removeEnrollmentID'];

    // Check if enrollment ID is not empty
    if (!empty(trim($enrollment_id))) {

        // Create a new Enrollment object
        $newEnrollment = new Enrollment();

        try {
            // Delete the enrollment from the database
            $val = $newEnrollment->deleteById($conn, $enrollment_id);
            if ($val) {
                $_SESSION['success'] = "Enrollment deleted";
            } else {
                $_SESSION['error'] = "Enrollment not deleted";
            }

        } catch (PDOException $ex) {
            $_SESSION['error'] = "Enrollment not deleted - " . $ex->getMessage();
        }

    } else {
        // Error message if enrollment ID is empty
        $_SESSION['error'] = "One or more fields are empty";
    }

    // Redirect back to the dashboard
    header('Location: enrollments.php');
    exit();
}
?>
