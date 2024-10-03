<?php

class User
{
    // Private properties of the User class
    private $id;
    private $fullName;
    private $email;
    private $password;
    private $phoneNumber;
    private $userType;

    // Constructor method to initialize object properties
    public function __construct($id = null, $fullName = null, $email = null, $password = null, $phoneNumber = null, $userType = null) {
        $this->id = $id;
        $this->fullName = $fullName;
        $this->email = $email;
        $this->password = $password;
        $this->phoneNumber = $phoneNumber;
        $this->userType = $userType;
    }

    // Getters and Setters for class properties
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getFullName()
    {
        return $this->fullName;
    }

    public function setFullName($fullName)
    {
        $this->fullName = $fullName;
    }

    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getUserType()
    {
        return $this->userType;
    }

    public function setUserType($userType)
    {
        $this->userType = $userType;
    }


    // Check if an email address already exists in the database
    public static function isEmailAddressExists(PDO $conn, $email)
    {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM `user` WHERE `email` = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $count = $stmt->fetchColumn();
            
        // If the count is greater than 0, it means the email address already exists
        return $count > 0;
    }

    // Method to save user data to the database
    public function saveToDatabase($conn)
    {
        $stmt = $conn->prepare("INSERT INTO user (fullname, email, password_, phone_number, user_role) 
                               VALUES (:fullname, :email, :password_, :phone_number, :user_role)");

        $stmt->bindParam(':fullname', $this->fullName);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password_', $this->password);
        $stmt->bindParam(':phone_number', $this->phoneNumber);
        $stmt->bindParam(':user_role', $this->userType);

        return $stmt->execute();

    }

    // Method to retrieve a user by ID from the database
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



    // Method to update user data in the database
    public function update(PDO $conn, $userId, $new_password)
    {

        // Prepare the SQL statement based on whether password is being updated or not
        if (!empty(trim($new_password))) {
            $stmt = $conn->prepare("UPDATE `user` SET 
                                                    `fullname`=:fullname,
                                                    `email`=:email,
                                                    `password_`=:password_,
                                                    `phone_number`=:phone_number,
                                                    `user_role`=:user_role
                                                    WHERE `id`=:id");
            $stmt->bindParam(':password_', $this->password);
        } else {
            $stmt = $conn->prepare("UPDATE `user` SET 
                                                    `fullname`=:fullname,
                                                    `email`=:email,
                                                    `phone_number`=:phone_number,
                                                    `user_role`=:user_role
                                                    WHERE `id`=:id");
        }

        // Bind parameters and execute the statement
        $stmt->bindParam(':fullname', $this->fullName);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone_number', $this->phoneNumber);
        $stmt->bindParam(':user_role', $this->userType);
        $stmt->bindParam(':id', $userId);

        return $stmt->execute(); // Return the execution result
    }


    // Method to delete a user by ID from the database
    public static function deleteById(PDO $conn, $userId)
    {
        $stmt = $conn->prepare("DELETE FROM user WHERE id = :id");
        $stmt->bindParam(':id', $userId);
        return $stmt->execute();
    }

    // Method to retrieve all users from the database
    public static function getAll(PDO $conn)
    {
        $stmt = $conn->query("SELECT * FROM user");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $users = []; 
        foreach ($results as $result) {
            $users[] = new self(
                $result['id'],
                $result['fullname'],
                $result['email'],
                $result['password_'],
                $result['phone_number'],
                $result['user_role']
            );
        }

        return $users;
    }

    // Method to search for users in the database
    public static function search(PDO $conn, $query, $userType = null)
    {
        if ($userType) {
            $stmt = $conn->prepare("SELECT * FROM user
                                    WHERE (fullname LIKE :query OR email LIKE :query OR phone_number LIKE :query)
                                    AND user_role = :user_role");
            $stmt->bindValue(':user_role', $userType, PDO::PARAM_STR);
        } else {
            $stmt = $conn->prepare("SELECT * FROM user
                                    WHERE fullname LIKE :query OR email LIKE :query OR phone_number LIKE :query");
        }

        $stmt->bindValue(':query', "%$query%", PDO::PARAM_STR);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $users = [];
        foreach ($results as $result) {
            $users[] = new self(
                $result['id'],
                $result['fullname'],
                $result['email'],
                $result['password_'],
                $result['phone_number'],
                $result['user_role']
            );
        }

        return $users;
    }


}

?>
