<?php

// Include necessary files
require_once 'DatabaseConnection.php';
require_once 'Course.php';

// Start session
session_start();

/*
if(Course::isCourseNameExists($conn,$course_name)){
            $_SESSION['error'] = "This Course name already exists";
        }
*/

// Add a new course
if (isset($_POST['add'])) {
    // Retrieve form data
    $course_name = $_POST['course_name'];
    $course_description = $_POST['course_description'];
    $course_duration = $_POST['course_duration'];
    $course_instructor = $_POST['course_instructor'];
    $course_level = $_POST['course_level'];
    $course_fee = $_POST['course_fee'];

    // Check if form fields are not empty
    if (!empty(trim($course_name)) && !empty(trim($course_description)) && !empty(trim($course_duration)) && !empty(trim($course_instructor)) && !empty(trim($course_level)) && !empty(trim($course_fee))) {
        
        // Check if a course name already exists in the database
        if(Course::isCourseNameExists($conn,$course_name)){
            $_SESSION['error'] = "This Course name already exists";
        }

        else{
            // Create a new course object
            $newCourse = new Course(0, $course_name, $course_description, $course_duration, $course_instructor, $course_level, $course_fee);

            try {
                // Save the course to the database
                $val = $newCourse->saveToDatabase($conn);
                if ($val) {
                    $_SESSION['success'] = "New course added";
                } else {
                    $_SESSION['error'] = "Course not added";
                }
            } catch (PDOException $ex) {
                $_SESSION['error'] = "Course not added - " . $ex->getMessage();
            }
        }

    }
    
    else {
        $_SESSION['error'] = "Course name cannot be empty";
    }

    // Redirect back to the courses dashboard
    header('Location: courses.php');
    exit();
}

// Edit course
if (isset($_POST['edit'])) {
    // Retrieve form data
    $course_id = $_POST['course_id'];
    $course_name = $_POST['course_name'];
    $course_description = $_POST['course_description'];
    $course_duration = $_POST['course_duration'];
    $course_instructor = $_POST['course_instructor'];
    $course_level = $_POST['course_level'];
    $course_fee = $_POST['course_fee'];

    // Check if form fields are not empty
    if (!empty(trim($course_name)) && !empty(trim($course_description)) && !empty(trim($course_duration)) && !empty(trim($course_instructor)) && !empty(trim($course_level)) && !empty(trim($course_fee))) {
        // Update the course's details
        $newCourse = new Course(0, $course_name, $course_description, $course_duration, $course_instructor, $course_level, $course_fee);

        try {
            $val = $newCourse->update($conn, $course_id);
            if ($val) {
                $_SESSION['success'] = "Course Edited";
            } else {
                $_SESSION['error'] = "Course not Edited";
            }
        } catch (PDOException $ex) {
            $_SESSION['error'] = "Course not Edited - " . $ex->getMessage();
        }
    } else {
        $_SESSION['error'] = "Course not Edited";
    }

    // Redirect back to the courses dashboard
    header('Location: courses.php');
    exit();
}

// Remove course
elseif (isset($_POST['remove'])) {
    // Retrieve form data
    $course_id = $_POST['removeCourseId'];

    // Check if form field is not empty
    if (!empty(trim($course_id))) {
        // Create a new course object
        $newCourse = new Course();

        try {
            // Delete the course from the database
            $val = $newCourse->deleteById($conn, $course_id);
            if ($val) {
                $_SESSION['success'] = "Course deleted";
            } else {
                $_SESSION['error'] = "Course not deleted";
            }
        } catch (PDOException $ex) {
            $_SESSION['error'] = "Course not deleted - " . $ex->getMessage();
        }
    } else {
        $_SESSION['error'] = "One or more fields are empty";
    }

    // Redirect back to the courses dashboard
    header('Location: courses.php');
    exit();
}

?>
