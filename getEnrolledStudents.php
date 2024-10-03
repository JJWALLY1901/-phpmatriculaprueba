<?php
// Include database connection
require_once 'DatabaseConnection.php';

// Check if the courseId parameter is provided in the URL
if (isset($_GET['courseId'])) {
    // Sanitize and store the courseId parameter
    $courseId = filter_input(INPUT_GET, 'courseId', FILTER_SANITIZE_NUMBER_INT);

    // Prepare and execute a SQL query to retrieve enrolled students for the given course ID
    $sql = "SELECT student.id, student.full_name AS name, COALESCE(marks.mark, '') AS mark, enrollments.enrollment_id
            FROM enrollments
            INNER JOIN student ON enrollments.student_id = student.id
            LEFT JOIN marks ON marks.enrollment_id = enrollments.enrollment_id
            WHERE enrollments.course_id = :courseId";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':courseId', $courseId, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch the results as an associative array
    $enrolledStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the results as JSON
    header('Content-Type: application/json');
    echo json_encode($enrolledStudents);
} else {
    // If courseId parameter is not provided, return an error response
    http_response_code(400); // Bad Request
    echo json_encode(array('error' => 'Course ID parameter is missing'));
}
?>
