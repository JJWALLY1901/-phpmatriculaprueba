<?php

class Course
{
    // Properties
    private $id;
    private $name;
    private $description;
    private $duration;
    private $instructor;
    private $level;
    private $fee;

    // Constructor
    public function __construct($id = null, $name = null, $description = null, $duration = null, $instructor = null, $level = null, $fee = null) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->duration = $duration;
        $this->instructor = $instructor;
        $this->level = $level;
        $this->fee = $fee;
    }

    // Getters and Setters

    // Getter for id
    public function getId()
    {
        return $this->id;
    }

    // Setter for id
    public function setId($id)
    {
        $this->id = $id;
    }

    // Getter for name
    public function getName()
    {
        return $this->name;
    }

    // Setter for name
    public function setName($name)
    {
        $this->name = $name;
    }

    // Getter for description
    public function getDescription()
    {
        return $this->description;
    }

    // Setter for description
    public function setDescription($description)
    {
        $this->description = $description;
    }

    // Getter for duration
    public function getDuration()
    {
        return $this->duration;
    }

    // Setter for duration
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    // Getter for instructor
    public function getInstructor()
    {
        return $this->instructor;
    }

    // Setter for instructor
    public function setInstructor($instructor)
    {
        $this->instructor = $instructor;
    }

    // Getter for level
    public function getLevel()
    {
        return $this->level;
    }

    // Setter for level
    public function setLevel($level)
    {
        $this->level = $level;
    }

    // Getter for fee
    public function getFee()
    {
        return $this->fee;
    }

    // Setter for fee
    public function setFee($fee)
    {
        $this->fee = $fee;
    }

    // Guardar la base de datos
    public function saveToDatabase($conn)
    {
        $stmt = $conn->prepare("INSERT 
                                    INTO `course`( `course_name`, `course_description`, `course_duration`, `course_instructor`, `course_level`, `course_fee`) 
                                VALUES 
                                    (:course_name,:course_description,:course_duration,:course_instructor,:course_level,:course_fee)");

        $stmt->bindParam(':course_name', $this->name);
        $stmt->bindParam(':course_description', $this->description);
        $stmt->bindParam(':course_duration', $this->duration);
        $stmt->bindParam(':course_instructor', $this->instructor);
        $stmt->bindParam(':course_level', $this->level);
        $stmt->bindParam(':course_fee', $this->fee);

        return $stmt->execute();
    }


    // Check if a course name already exists in the database
    public static function isCourseNameExists(PDO $conn, $courseName)
    {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM `course` WHERE `course_name` = :course_name");
        $stmt->bindParam(':course_name', $courseName);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
         // If the count is greater than 0, it means the course name already exists
        return $count > 0;
    }


    // Retrieve Course by ID
    public static function getById(PDO $conn, $courseId)
    {
        $stmt = $conn->prepare("SELECT * FROM course WHERE course_id = :id");
        $stmt->bindParam(':id', $courseId);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return new self(
                $result['course_id'],
                $result['course_name'],
                $result['course_description'],
                $result['course_duration'],
                $result['course_instructor'],
                $result['course_level'],
                $result['course_fee']
            );
        }

        return null;
    }

    // Update Course
    public function update(PDO $conn, $courseId)
    {
        $stmt = $conn->prepare("UPDATE course 
                               SET course_name = :course_name,
                               course_description = :course_description,
                               course_duration = :course_duration,
                               course_instructor = :course_instructor,
                               course_level = :course_level,
                               course_fee = :course_fee
                               WHERE course_id = :course_id");

        $stmt->bindParam(':course_name', $this->name);
        $stmt->bindParam(':course_description', $this->description);
        $stmt->bindParam(':course_duration', $this->duration);
        $stmt->bindParam(':course_instructor', $this->instructor);
        $stmt->bindParam(':course_level', $this->level);
        $stmt->bindParam(':course_fee', $this->fee);
        $stmt->bindParam(':course_id', $courseId);

        return $stmt->execute();
    }

    // Eliminar curso
    public static function deleteById(PDO $conn, $courseId)
    {
        // the related enrollments records will also be deleted
        
        $stmt = $conn->prepare("DELETE FROM course WHERE course_id = :course_id");
        $stmt->bindParam(':course_id', $courseId);
        return $stmt->execute();
    }

    // Get All Courses
    public static function getAll(PDO $conn)
    {
        $stmt = $conn->query("SELECT * FROM course");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $courses = [];
        foreach ($results as $result) {
            $courses[] = new self(
                $result['course_id'],
                $result['course_name'],
                $result['course_description'],
                $result['course_duration'],
                $result['course_instructor'],
                $result['course_level'],
                $result['course_fee']
            );
        }

        return $courses;
    }

    // Buscar cursos
    public static function search(PDO $conn, $query, $level = null)
    {
        if ($level) {
            $stmt = $conn->prepare("SELECT * FROM course
                                    WHERE (course_name LIKE :query OR course_description LIKE :query OR course_instructor LIKE :query)
                                    AND course_level = :course_level");
            $stmt->bindValue(':course_level', $level, PDO::PARAM_STR);
        } else {
            $stmt = $conn->prepare("SELECT * FROM course
                                    WHERE course_name LIKE :query OR course_description LIKE :query OR course_instructor LIKE :query");
        }

        $stmt->bindValue(':query', "%$query%", PDO::PARAM_STR);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $courses = [];
        foreach ($results as $result) {
            $courses[] = new self(
                $result['course_id'],
                $result['course_name'],
                $result['course_description'],
                $result['course_duration'],
                $result['course_instructor'],
                $result['course_level'],
                $result['course_fee']
            );
        }

        return $courses;
    }

    // Get most popular course
    public static function getMostPopularCourse($conn) {
        // Consulta para contar el número de inscripciones para cada curso y recuperar los detalles del curso.
        $sql = "SELECT enrollments.course_id, COUNT(*) AS enrollment_count, course.course_name 
                FROM enrollments 
                INNER JOIN course ON enrollments.course_id = course.course_id 
                GROUP BY enrollments.course_id 
                ORDER BY enrollment_count DESC 
                LIMIT 1";

        // Preparar y ejecutar la sentencia SQL
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        // Obtener un resultado
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Retrieve the course ID, enrollment count, and course name
            $courseId = $result['course_id'];
            $enrollmentCount = $result['enrollment_count'];
            $courseName = $result['course_name'];

            // Return the course details as an associative array
            return array(
                'course_id' => $courseId,
                'course_name' => $courseName,
                'enrollment_count' => $enrollmentCount
            );
        } else {
            // No se encontraron inscripciones, devuelva nulo o maneje el caso según sea necesario
            return null;
        }
    }

    // Get total course hours
    public static function getTotalCourseHours(PDO $conn) {
        try {
            // Prepare a SQL query to select the durations of all courses
            $stmt = $conn->prepare("SELECT SUM(course_duration) AS total_hours FROM course");

            // Ejecutar la consulta
            $stmt->execute();

            // Obtener el resultado
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Retrieve the total hours from the result
            $totalHours = $result['total_hours'];

            // Retornar el total de horas
            return $totalHours;
        } catch (PDOException $e) {
            // Handle any errors that occur during the execution of the query
            echo "Error: " . $e->getMessage();
            return 0; // Return 0 if an error occurs
        }
    }
}

?>
