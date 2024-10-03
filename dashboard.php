<?php 
    // Incluir archivos necesarios

    include 'redirection.php'; // Redirect if not logged in
    require_once 'DatabaseConnection.php'; // Include DatabaseConnection class
    require_once 'Student.php'; // Include Student class
    require_once 'Course.php'; // Include Course class
    require_once 'Enrollment.php'; // Include Enrollment class

    $students = Student::getAll($conn); // Retrieve all students from the database
    $courses = Course::getAll($conn); // Retrieve all courses from the database
    $enrollments = Enrollment::getAll($conn); // Retrieve all enrollments from the database

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard estudianntes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style/common-style.css">
    <style>
        .cards-container {
            display: flex;
            flex-wrap: wrap;
            margin-top:50px;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            width: 100%;
            height: 400px;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .card {
            width: calc(50% - 10px);
            padding: 70px;
            background-color: #fff;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            text-align: center;
        }

    </style>
</head>
<body>


<?php
    // Include common section of the page
    $pageTitleOverride = "Dashboard";
    include 'common-section.php';
?>


    <div id="content">
        <h2>Dashboard</h2>

        <div class="cards-container">
            <div class="row">
                <div class="card">
                    <i class="fas fa-users"></i>
                    <h3>Total Estudiantes</h3>
                    <span><?= count($students)?></span>
                </div>
                <div class="card">
                    <i class="fas fa-graduation-cap"></i>
                    <h3>Total Cursos</h3>
                    <span><?= count($courses)?></span>
                </div>
                <div class="card">
                    <i class="fas fa-book-open"></i>
                    <h3>Total Matricula</h3>
                    <span><?= count($enrollments)?></span>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
