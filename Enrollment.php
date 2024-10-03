<?php

class Enrollment
{
    private $id;
    private $studentID;
    private $studentName;
    private $courseID;
    private $courseName;
    private $enrollmentDate;
    private $enrollmentStatus;

    // Constructor
    public function __construct($id = null, $studentID = null, $studentName = null, $courseID = null, $courseName = null, $enrollmentDate = null, $enrollmentStatus = null) {
        $this->id = $id;
        $this->studentID = $studentID;
        $this->studentName = $studentName;
        $this->courseID = $courseID;
        $this->courseName = $courseName;
        $this->enrollmentDate = $enrollmentDate;
        $this->enrollmentStatus = $enrollmentStatus;
    }

    // Getters and Setters

    // Getter for ID
    public function getId()
    {
        return $this->id;
    }

    // Setter for ID
    public function setId($id)
    {
        $this->id = $id;
    }

    // Getter for Student ID
    public function getStudentID()
    {
        return $this->studentID;
    }

    // Getter for Student Name
    public function getStudentName()
    {
        return $this->studentName;
    }

    // Setter for Student ID
    public function setStudentID($studentID)
    {
        $this->studentID = $studentID;
    }

    // Getter for Course ID
    public function getCourseID()
    {
        return $this->courseID;
    }

    // Setter for Course ID
    public function setCourseID($courseID)
    {
        $this->courseID = $courseID;
    }

    // Getter for Course Name
    public function getCourseName()
    {
        return $this->courseName;
    }

    // Getter for Enrollment Date
    public function getEnrollmentDate()
    {
        return $this->enrollmentDate;
    }

    // Setter for Enrollment Date
    public function setEnrollmentDate($enrollmentDate)
    {
        $this->enrollmentDate = $enrollmentDate;
    }

    // Getter for Enrollment Status
    public function getEnrollmentStatus()
    {
        return $this->enrollmentStatus;
    }

    // Setter for Enrollment Status
    public function setEnrollmentStatus($enrollmentStatus)
    {
        $this->enrollmentStatus = $enrollmentStatus;
    }


    // Check if active enrollment exists for student in course
    public static function hasActiveEnrollment($conn, $studentID, $courseID)
    {
        $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM enrollments 
                                WHERE student_id = :student_id AND course_id = :course_id AND enrollment_status = 'active'");
        $stmt->bindParam(':student_id', $studentID);
        $stmt->bindParam(':course_id', $courseID);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }


    // Save to Database with check for existing active enrollment
    public function saveToDatabase($conn)
    {
        if (self::hasActiveEnrollment($conn, $this->studentID, $this->courseID)) {
            // Active enrollment already exists
            $_SESSION['error'] = "Active enrollment already exists";
            header('Location: enrollments.php?error=active_enrollment_exists');
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO `enrollments`(`student_id`, `course_id`, `enrollment_date`, `enrollment_status`) VALUES (:student_id, :course_id, :enrollment_date, :enrollment_status)");

        $stmt->bindParam(':student_id', $this->studentID);
        $stmt->bindParam(':course_id', $this->courseID);
        $stmt->bindParam(':enrollment_date', $this->enrollmentDate);
        $stmt->bindParam(':enrollment_status', $this->enrollmentStatus);

        return $stmt->execute();
    }


    // Retrieve Enrollment by ID
    public static function getById(PDO $conn, $enrollmentId)
    {
        $stmt = $conn->prepare("SELECT * FROM enrollments WHERE id = :id");
        $stmt->bindParam(':id', $enrollmentId);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return new self(
                $result['id'],
                $result['student_id'],
                $result['course_id'],
                $result['enrollment_date'],
                $result['enrollment_status']
            );
        }

        return null;
    }



    // Update Enrollment
    public function update(PDO $conn, $enrollmentId)
    {
        // Check if the enrollment being updated is already inactive
        if ($this->enrollmentStatus === 'inactive') {
            // If it's already inactive, no need to check for active enrollment
            return $this->updateEnrollment($conn, $enrollmentId);
        }

        // Check if active enrollment exists for student in course
        if (self::hasActiveEnrollment($conn, $this->studentID, $this->courseID)) {
            // Active enrollment already exists
            $_SESSION['error'] = "Active enrollment already exists";
            header('Location: enrollments.php?error=active_enrollment_exists');
            exit();
        }

        // Update the enrollment
        return $this->updateEnrollment($conn, $enrollmentId);
    }

    // Function to update enrollment status
    private function updateEnrollment(PDO $conn, $enrollmentId)
    {
        $stmt = $conn->prepare("UPDATE enrollments SET student_id = :student_id, course_id = :course_id, enrollment_date = :enrollment_date, enrollment_status = :enrollment_status WHERE enrollment_id = :enrollment_id");

        $stmt->bindParam(':student_id', $this->studentID);
        $stmt->bindParam(':course_id', $this->courseID);
        $stmt->bindParam(':enrollment_date', $this->enrollmentDate);
        $stmt->bindParam(':enrollment_status', $this->enrollmentStatus);
        $stmt->bindParam(':enrollment_id', $enrollmentId);

        return $stmt->execute();
    }


    // Delete Enrollment
    public static function deleteById(PDO $conn, $enrollmentId)
    {
        $stmt = $conn->prepare("DELETE FROM enrollments WHERE enrollment_id = :enrollment_id");
        $stmt->bindParam(':enrollment_id', $enrollmentId);
        return $stmt->execute();
    }

    // Get All Enrollments
    public static function getAll(PDO $conn)
    {
        $stmt = $conn->query("SELECT enrollments.*, student.full_name AS student_name, course.course_name as course_name 
                              FROM enrollments INNER JOIN student ON enrollments.student_id = student.id 
                              INNER JOIN course ON enrollments.course_id = course.course_id
                              ORDER BY enrollment_id desc");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $enrollments = [];
        foreach ($results as $result) {
            $enrollments[] = new self(
                $result['enrollment_id'],
                $result['student_id'],
                $result['student_name'],
                $result['course_id'],
                $result['course_name'],
                $result['enrollment_date'],
                $result['enrollment_status']
            );
        }

        return $enrollments;
    }

    
    // Count Enrollments by Status
    public static function countEnrollmentsStatus(PDO $conn)
    {
        $stmt = $conn->query("SELECT enrollments.*, student.full_name AS student_name, course.course_name as course_name FROM enrollments INNER JOIN student ON enrollments.student_id = student.id INNER JOIN course ON enrollments.course_id = course.course_id;");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $activeCount = 0;
        $inactiveCount = 0;

        foreach ($results as $result) {
            if ($result['enrollment_status'] === 'active') {
                $activeCount++;
            } else {
                $inactiveCount++;
            }
        }

        return [
            'activeCount' => $activeCount,
            'inactiveCount' => $inactiveCount
        ];
    }

    // Search Enrollments
    public static function search(PDO $conn, $query, $status = null)
    {
        if ($status) {
            $stmt = $conn->prepare("SELECT enrollments.*, student.full_name AS student_name, course.course_name AS course_name FROM enrollments INNER JOIN student ON enrollments.student_id = student.id INNER JOIN course ON enrollments.course_id = course.course_id WHERE (student.full_name LIKE :query OR course.course_name LIKE :query) AND enrollment_status = :status");
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        } else {
            $stmt = $conn->prepare("SELECT enrollments.*, student.full_name AS student_name, course.course_name AS course_name FROM enrollments INNER JOIN student ON enrollments.student_id = student.id INNER JOIN course ON enrollments.course_id = course.course_id WHERE (student.full_name LIKE :query OR course.course_name LIKE :query)");
        }

        $stmt->bindValue(':query', "%$query%", PDO::PARAM_STR);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $enrollments = [];

        foreach ($results as $result) {
            $enrollments[] = new self(
                $result['enrollment_id'],
                $result['student_id'],
                $result['student_name'],
                $result['course_id'],
                $result['course_name'],
                $result['enrollment_date'],
                $result['enrollment_status']
            );
        }

        return $enrollments;
    }

    // Check if Enrollment has Marks
    public function hasMark(PDO $conn)
    {
        $enrollmentIddd = $this->getId();
        $sql = "SELECT COUNT(*) FROM marks WHERE enrollment_id = :enrollmentId";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':enrollmentId', $enrollmentIddd, PDO::PARAM_INT);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        return ($count > 0);
    }
}

?>
