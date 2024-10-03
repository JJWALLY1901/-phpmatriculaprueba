<?php

class Student
{
    // Properties
    private $id;
    private $image;
    private $fullName;
    private $dateOfBirth;
    private $gender;
    private $address;
    private $phoneNumber;
    private $email;

    // Constructor
    public function __construct($id = null, $image = null, $fullName = null, $dateOfBirth = null, $gender = null, $address = null, $phoneNumber = null, $email = null) {
        $this->id = $id;
        $this->image = $image;
        $this->fullName = $fullName;
        $this->dateOfBirth = $dateOfBirth;
        $this->gender = $gender;
        $this->address = $address;
        $this->phoneNumber = $phoneNumber;
        $this->email = $email;
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

    // Get Image
    public function getImage()
    {
        return $this->image;
    }

    // Set Image
    public function setImage($image)
    {
        $this->image = $image;
    }

    // Get Full Name
    public function getFullName()
    {
        return $this->fullName;
    }

    // Set Full Name
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;
    }

    // Get Date of Birth
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    // Set Date of Birth
    public function setDateOfBirth($dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;
    }

    // Get Gender
    public function getGender()
    {
        return $this->gender;
    }

    // Set Gender
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    // Get Address
    public function getAddress()
    {
        return $this->address;
    }

    // Set Address
    public function setAddress($address)
    {
        $this->address = $address;
    }

    // Get Phone Number
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    // Set Phone Number
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    // Get Email
    public function getEmail()
    {
        return $this->email;
    }

    // Set Email
    public function setEmail($email)
    {
        $this->email = $email;
    }

    // Save to Database
    public function saveToDatabase($conn)
    {
        $stmt = $conn->prepare("INSERT INTO student (picture, full_name, date_of_birth, gender, _address, phone_number, email) 
                               VALUES (:picture, :fullName, :dateOfBirth, :gender, :address_, :phoneNumber, :email)");

        $stmt->bindParam(':picture', $this->image);
        $stmt->bindParam(':fullName', $this->fullName);
        $stmt->bindParam(':dateOfBirth', $this->dateOfBirth);
        $stmt->bindParam(':gender', $this->gender);
        $stmt->bindParam(':address_', $this->address);
        $stmt->bindParam(':phoneNumber', $this->phoneNumber);
        $stmt->bindParam(':email', $this->email);

        return $stmt->execute();
    }

    // Retrieve Student by ID
    public static function getById(PDO $conn, $studentId)
    {
        $stmt = $conn->prepare("SELECT * FROM student WHERE id = :id");
        $stmt->bindParam(':id', $studentId);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return new self(
                $result['id'],
                $result['picture'],
                $result['full_name'],
                $result['date_of_birth'],
                $result['gender'],
                $result['_address'],
                $result['phone_number'],
                $result['email']
            );
        }

        return null;
    }

    // Update Student
    public function update(PDO $conn, $studentId)
    {
        $stmt = $conn->prepare("UPDATE student 
                               SET full_name = :fullName,
                                   date_of_birth = :dateOfBirth,
                                   gender = :gender,
                                   _address = :address_,
                                   phone_number = :phoneNumber,
                                   email = :email
                               WHERE id = :id");

        $stmt->bindParam(':fullName', $this->fullName);
        $stmt->bindParam(':dateOfBirth', $this->dateOfBirth);
        $stmt->bindParam(':gender', $this->gender);
        $stmt->bindParam(':address_', $this->address);
        $stmt->bindParam(':phoneNumber', $this->phoneNumber);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':id', $studentId);

        return $stmt->execute();
    }

    // Update Student Image
    public function updateImage(PDO $conn, $studentId, $image)
    {
        $stmt = $conn->prepare("UPDATE student SET picture = :picture WHERE id = :id");

        $stmt->bindParam(':picture', $image);
        $stmt->bindParam(':id', $studentId);

        return $stmt->execute();
    }

    // Delete Student
    public static function deleteById(PDO $conn, $studentId)
    {
        // the related enrollments records will also be deleted

        $stmt = $conn->prepare("DELETE FROM student WHERE id = :id");
        $stmt->bindParam(':id', $studentId);
        return $stmt->execute();
    }

    // Get All Students
    public static function getAll(PDO $conn)
    {
        $stmt = $conn->query("SELECT * FROM student");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $students = [];
        foreach ($results as $result) {
            $students[] = new self(
                $result['id'],
                $result['picture'],
                $result['full_name'],
                $result['date_of_birth'],
                $result['gender'],
                $result['_address'],
                $result['phone_number'],
                $result['email']
            );
        }

        return $students;
    }

    // Search for Students
    public static function search(PDO $conn, $query, $gender = null)
    {
        if ($gender) {
            $stmt = $conn->prepare("SELECT * FROM student
                                    WHERE (full_name LIKE :query OR _address LIKE :query OR email LIKE :query)
                                    AND gender = :gender");
            $stmt->bindValue(':gender', $gender, PDO::PARAM_STR);
        } else {
            $stmt = $conn->prepare("SELECT * FROM student
                                    WHERE full_name LIKE :query OR _address LIKE :query OR email LIKE :query");
        }

        $stmt->bindValue(':query', "%$query%", PDO::PARAM_STR);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $students = [];
        foreach ($results as $result) {
            $students[] = new self(
                $result['id'],
                $result['picture'],
                $result['full_name'],
                $result['date_of_birth'],
                $result['gender'],
                $result['_address'],
                $result['phone_number'],
                $result['email']
            );
        }

        return $students;
    }

    // Get Students by Gender
    public static function getByGender(PDO $conn, $gender)
    {
        $stmt = $conn->prepare("SELECT * FROM student WHERE gender = :gender");
        $stmt->bindParam(':gender', $gender);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $students = [];
        foreach ($results as $result) {
            $students[] = new self(
                $result['full_name'],
                $result['date_of_birth'],
                $result['gender'],
                $result['_address'],
                $result['phone_number'],
                $result['email']
            );
        }

        return $students;
    }
}



?>