<?php

// Include the database connection
require_once 'DatabaseConnection.php';
// Check if the studentId parameter is provided in the URL
if (isset($_GET['studentId'])) {
    $studentId = $_GET['studentId'];

    try {
        // Prepare SQL statement to fetch enrolled courses for the given student ID
        $sql = "SELECT enrollments.enrollment_id, course.course_name, marks.mark 
                FROM enrollments
                LEFT JOIN course ON enrollments.course_id = course.course_id
                LEFT JOIN marks ON enrollments.enrollment_id = marks.enrollment_id
                WHERE enrollments.student_id = :studentId";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':studentId', $studentId);
        $stmt->execute();
        
        // Fetch all enrolled courses data
        $enrolledCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Return the enrolled courses data as JSON
        echo json_encode($enrolledCourses);
    } catch (PDOException $e) {
        // Handle database connection errors
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    // Return an error if studentId parameter is not provided
    echo json_encode(['error' => 'Missing studentId parameter']);
}

?>
