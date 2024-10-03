<?php

// Include necessary files
require_once 'DatabaseConnection.php';
require_once 'Student.php';

// Start session
session_start();

// Add a new student
if (isset($_POST['add'])) {
    // Get form data
    $fullname = $_POST['fullname'];
    $birthdate = $_POST['birthdate'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    // Get uploaded file info
    $file = $_FILES['student-image'];
    $filename = $file['name'];
    $tmp_name = $file['tmp_name'];
    $size = $file['size'];
    $error = $file['error'];
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    $upload_dir = 'images/students/';

    // Generate a unique filename using a prefix and the current timestamp
    $unique_filename = 'pic_' . uniqid() . '.' . $extension;
    $upload_path = $upload_dir . $unique_filename;

    // Validate the uploaded file
    if (isset($tmp_name)) {
        if ($size > 200000) {
            $_SESSION['error'] = 'Error - File Size too Large';
            header('Location: students.php');
            exit();
        }
    
        if (!in_array($extension, ['jpg', 'png', 'jpeg', 'gif'])) {
            $_SESSION['error'] = 'Error - Wrong File Type';
            header('Location: students.php');
            exit();
        }
    }

    // Check if all required fields are not empty
    if (!empty(trim($fullname)) && !empty(trim($birthdate)) && !empty(trim($gender)) && !empty(trim($address)) && !empty(trim($phone)) && !empty($email)) {
        // Move the uploaded file to the designated directory
        if (move_uploaded_file($tmp_name, $upload_path)) {
            // Create a new student object
            $newStudent = new Student(0, $upload_path, $fullname, $birthdate, $gender, $address, $phone, $email);

            // Save the new student to the database
            if ($newStudent->saveToDatabase($conn)) {
                $_SESSION['success'] = 'New Student added';
                header('Location: students.php');
                exit();
            } else {
                $_SESSION['error'] = 'Student not added';
                header('Location: students.php');
                exit();
            }
        } else { 
            $_SESSION['error'] = 'Error - Failed to upload image';
            header('Location: students.php');
            exit();
        }
    } else {
        $_SESSION['error'] = 'Enter the student data';
        header('Location: students.php');
        exit();
    }
}

// Edit student
elseif (isset($_POST['edit'])) {
    // Get form data
    $id = $_POST['id'];
    $fullname = $_POST['fullname'];
    $birthdate = $_POST['birthdate'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    // Get student by ID from the database
    $student = Student::getById($conn,$id);
    
    // If the student is not found, display an error message and redirect to the dashboard page
    if (!$student) {
        $_SESSION['error'] = 'Error - Student Not Found';
        header('Location: students.php');
        exit();
    }

    // Check if a new student image file is uploaded
    if ($_FILES['student-image']['tmp_name']) {
        $file = $_FILES['student-image'];
        $filename = $file['name'];
        $tmp_name = $file['tmp_name'];
        $size = $file['size'];
        $error = $file['error'];
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $upload_dir = 'images/students/';

        // Generate a unique filename for the uploaded image
        $unique_filename = 'pic_' . uniqid() . '.' . $extension;
        $upload_path = $upload_dir . $unique_filename;

        // Validate the uploaded file
        if ($size > 200000) {
            $_SESSION['error'] = 'Error - File Size too Large';
            header('Location: students.php');
            exit();
        } elseif (!in_array($extension, ['jpg', 'png', 'jpeg', 'gif'])) {
            $_SESSION['error'] = 'Error - Wrong File Type';
            header('Location: students.php');
            exit();
        } else {
            // Move the uploaded file to the designated directory
            move_uploaded_file($tmp_name, $upload_path);
                        
            // Delete the previous student image
            unlink($student->getImage());
                        
            // Update the student image path in the database
            $student->updateImage($conn, $id, $upload_path);
        }
    }

    // Check if any field is empty
    if (!empty(trim($id)) && !empty(trim($fullname)) && !empty(trim($birthdate)) && !empty(trim($gender)) && !empty(trim($address)) && !empty(trim($phone)) && !empty($email)) {
        // Create a new Student object
        $newStudent = new Student($id, null, $fullname, $birthdate, $gender, $address, $phone, $email);

        // Save the student data to the database
        if ($newStudent->update($conn, $id)) {
            $_SESSION['success'] = "Student Data Updated";
            header('Location: students.php');
            exit();
        } else {
            $_SESSION['error'] = 'Error - Student data not updated';
            header('Location: students.php');
            exit();
        }
    }
}

// Remove student
elseif (isset($_POST['remove'])) {
    // Get student ID and image path from the form
    $id = $_POST['removeStudentId'];
    $student_image = $_POST['student-image-delete'];

    // Check if the student ID is not empty
    if (!empty(trim($id))) {
        $newStudent = new Student();

        // Remove student from the database
        if ($newStudent->deleteById($conn, $id)) {
            // Check if the student image file exists and delete it
            if (file_exists($student_image)) {
                unlink($student_image);
                $_SESSION['success'] = "Student Deleted";
                header('Location: students.php');
                exit();
            }
        }
    } else {
        $_SESSION['error'] = 'Error - Student not removed';
        header('Location: students.php');
        exit();
    }
}

?>
