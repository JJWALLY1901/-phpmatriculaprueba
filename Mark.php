<?php

class Mark
{
    // Properties
    private $id;
    private $enrollmentId;
    private $studentName;
    private $courseName;
    private $markValue;
    private $status;
    private $remark;
    private $markDate;

    // Constructor
    public function __construct($id = null, $enrollmentId = null, $studentName = null, $courseName = null, $markValue = null, $status = null, $remark = null, $markDate = null) {
        // Initialize object properties
        $this->id = $id;
        $this->enrollmentId = $enrollmentId;
        $this->studentName = $studentName;
        $this->courseName = $courseName;
        $this->markValue = $markValue;
        $this->status = $status;
        $this->remark = $remark;
        $this->markDate = $markDate;
    }

    // Getters and Setters

    // Get ID
    public function getId()
    {
        return $this->id;
    }

    // Set ID
    public function setId($id)
    {
        $this->id = $id;
    }

    // Get Enrollment ID
    public function getEnrollmentID()
    {
        return $this->enrollmentId;
    }

    // Set Enrollment ID
    public function setEnrollmentID($enrollmentId)
    {
        $this->enrollmentId = $enrollmentId;
    }

    // Get Student Name
    public function getStudentName()
    {
        return $this->studentName;
    }

    // Get Course Name
    public function getCourseName()
    {
        return $this->courseName;
    }

    // Get Mark Value
    public function getMarkValue()
    {
        return $this->markValue;
    }

    // Set Mark Value
    public function setMarkValue($markValue)
    {
        $this->markValue = $markValue;
    }

    // Get Status
    public function getStatus()
    {
        return $this->status;
    }

    // Set Status
    public function setStatus($status)
    {
        $this->status = $status;
    }

    // Get Remark
    public function getRemark()
    {
        return $this->remark;
    }

    // Set Remark
    public function setRemark($remark)
    {
        $this->remark = $remark;
    }

    // Get Mark Date
    public function getMarkDate()
    {
        return $this->markDate;
    }

    // Set Mark Date
    public function setMarkDate($markDate)
    {
        $this->markDate = $markDate;
    }

    // Save to Database
    public function saveToDatabase($conn)
    {
        // Prepare SQL statement for inserting data
        $stmt = $conn->prepare("INSERT INTO `marks`(`enrollment_id`, `mark`, `status`, `remark`, `mark_date`) VALUES (:enrollment_id, :mark, :status, :remark, NOW())");

        // Bind parameters
        $stmt->bindParam(':enrollment_id', $this->enrollmentId);
        $stmt->bindParam(':mark', $this->markValue);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':remark', $this->remark);

        // Execute the statement
        return $stmt->execute();
    }

    // Retrieve Mark by ID
    public static function getById(PDO $conn, $markId)
    {
        // Prepare SQL statement for retrieving data by ID
        $stmt = $conn->prepare("SELECT * FROM enrollments WHERE id = :id");
        $stmt->bindParam(':id', $enrollmentId);
        $stmt->execute();

        // Fetch result
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If result exists, create and return a new instance of the class
        if ($result) {
            return new self(
                $result['id'],
                $result['student_id'],
                $result['course_id'],
                $result['enrollment_date'],
                $result['enrollment_status']
            );
        }

        // If no result, return null
        return null;
    }

    // Update Mark
    public function update(PDO $conn, $markId)
    {
        // Prepare SQL statement for updating data
        $stmt = $conn->prepare("UPDATE marks SET mark = :mark, status = :_status, remark = :remark, mark_date = NOW() WHERE mark_id = :mark_id");

        // Bind parameters
        $stmt->bindParam(':mark', $this->markValue);
        $stmt->bindParam(':_status', $this->status);
        $stmt->bindParam(':remark', $this->remark);
        $stmt->bindParam(':mark_id', $markId);

        // Execute the statement
        return $stmt->execute();
    }

    // Delete Mark
    public static function deleteById(PDO $conn, $markId)
    {
        // Prepare SQL statement for deleting data by ID
        $stmt = $conn->prepare("DELETE FROM marks WHERE mark_id = :mark_id");
        $stmt->bindParam(':mark_id', $markId);
        
        // Execute the statement
        return $stmt->execute();
    }

    // Retrieve all Marks
    public static function getAll(PDO $conn)
    {
        // Prepare SQL statement for retrieving all marks
        $stmt = $conn->query("SELECT marks.*, enrollments.student_id, student.full_name AS student_name, 
                                     enrollments.course_id, course.course_name AS course_name 
                              FROM marks 
                              INNER JOIN enrollments ON marks.enrollment_id = enrollments.enrollment_id 
                              INNER JOIN student ON enrollments.student_id = student.id 
                              INNER JOIN course ON enrollments.course_id = course.course_id
                              ORDER BY marks.mark_id DESC;");
        
        // Fetch results
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $marks = [];

        // Create instances of the class and add them to the array
        foreach ($results as $result) {
            $marks[] = new self(
                $result['mark_id'],
                $result['enrollment_id'],
                $result['student_name'],
                $result['course_name'],
                $result['mark'],
                $result['status'],
                $result['remark'],
                $result['mark_date']
            );
        }

        // Return the array of marks
        return $marks;
    }

    // Search Marks
    public static function search(PDO $conn, $query, $status = null)
    {
        // Initialize SQL query
        $sql = "SELECT marks.*, enrollments.student_id, student.full_name AS student_name, enrollments.course_id, course.course_name AS course_name FROM marks INNER JOIN enrollments ON marks.enrollment_id = enrollments.enrollment_id INNER JOIN student ON enrollments.student_id = student.id INNER JOIN course ON enrollments.course_id = course.course_id WHERE (student.full_name LIKE :query OR course.course_name LIKE :query OR enrollments.student_id LIKE :query OR enrollments.course_id LIKE :query)";
        
        // Append conditions based on provided parameters
        if ($status) {
            $sql .= " AND marks.status = :status";
        }

        // Prepare the statement
        $stmt = $conn->prepare($sql);
        
        // Bind query parameter
        $stmt->bindValue(':query', "%$query%", PDO::PARAM_STR);

        // Bind status parameter if provided
        if ($status) {
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        }

        // Execute the query
        $stmt->execute();

        // Fetch results
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $marks = [];

        // Create instances of the class and add them to the array
        foreach ($results as $result) {
            $marks[] = new self(
                $result['mark_id'],
                $result['enrollment_id'],
                $result['student_name'],
                $result['course_name'],
                $result['mark'],
                $result['status'],
                $result['remark'],
                $result['mark_date']
            );
        }

        // Return the array of marks
        return $marks;
    }

}

?>
