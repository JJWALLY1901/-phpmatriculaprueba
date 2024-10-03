<?php

// Including necessary files
require_once 'DatabaseConnection.php'; // Include file for database connection
require_once 'User.php'; // Include file for User class

// Starting session
session_start();

// Adding a new user
if (isset($_POST['add'])) { // Check if the 'add' button was clicked

    // Getting user input data from form
    $userName = $_POST['fullname']; // Get full name from form
    $password = $_POST['password']; // Get password from form
    $hashed_password = password_hash($password, PASSWORD_BCRYPT); // Hashing the password
    $userPhone = $_POST['phone']; // Get phone number from form
    $userType = $_POST['userType']; // Get user type from form
    $email = $_POST['email']; // Get email from form

    // Validating if all fields are not empty
    if (!empty(trim($userName)) && !empty(trim($password)) && !empty(trim($userPhone)) && !empty(trim($userType)) && !empty(trim($email))) {

        // Check if the email address already exists in the database
        if(User::isEmailAddressExists($conn,$email)){
            $_SESSION['error'] = "This Email address already exists";
        }

        else{
            // Creating a new User object with provided data
            $newUser = new User(0, $userName, $email, $hashed_password, $userPhone, $userType);

            try {
                // Saving the new user to the database
                if ($newUser->saveToDatabase($conn)) {
                    $_SESSION['success'] = "New user added"; // Setting success message
                } else {
                    $_SESSION['error'] = "User not added"; // Setting error message
                }
            } catch (PDOException $ex) {
                $_SESSION['error'] = "User not added - " . $ex->getMessage(); // Setting error message with exception details
            }
        }

    } else {
        $_SESSION['error'] = "User data cannot be empty"; // Setting error message if any field is empty
    }

    // Redirecting back to the dashboard
    header('Location: users.php'); // Redirecting to users.php page
    exit(); // Exit script

}




    // Function to get email by user ID
    function getEmailById(PDO $conn, $id)
    {
        $stmt = $conn->prepare("SELECT email FROM `user` WHERE `id` = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if the query returned a result
        if ($result) {
            return $result['email'];
        } else {
            return null; // Return null if no email found for the given ID
        }
    }


// Editing an existing user
if (isset($_POST['edit'])) { // Check if the 'edit' button was clicked

    // Getting user input data from form
    $id = $_POST['id']; // Get user ID
    $userName = $_POST['fullname']; // Get full name
    $password = $_POST['password']; // Get password
    $userPhone = $_POST['phone']; // Get phone number
    $userType = $_POST['userType']; // Get user type
    $email = $_POST['email']; // Get email

    // Retrieve the current email of the user
    $oldEmail = getEmailById($conn, $id); // You need to implement this function

    // Validating if required fields are not empty
    if (!empty(trim($userName)) && !empty(trim($userPhone)) && !empty(trim($userType)) && !empty(trim($email))) {

        // Check if the email address already exists in the database
        if ($email !== $oldEmail && User::isEmailAddressExists($conn, $email)) {
            $_SESSION['error'] = "This Email address already exists - " . $email . " - " .$oldEmail;
        } else {
            
            

                            // Update the user's details
                            if (!empty(trim($_POST['password']))) { // Check if password field is not empty

                                // Hashing the new password
                                $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            
                                // Creating a new User object with updated data
                                $newUser = new User(0, $userName, $email, $hashed_password, $userPhone, $userType);
            
                                // Updating user data in the database
                                if($newUser->update($conn, $id, $hashed_password)){
                                    $_SESSION['success'] = "User data updated"; // Setting success message
                                }
            
                            } else {
                                // Creating a new User object with updated data (without password)
                                $newUser = new User(0, $userName, $email, null, $userPhone, $userType);
            
                                // Updating user data in the database
                                if($newUser->update($conn, $id, '')){
                                    $_SESSION['success'] = "User data updated"; // Setting success message
                                }
                            }



        }
    } else {
        $_SESSION['error'] = "Enter the user data"; // Setting error message if any required field is empty
    }

    // Redirecting back to the dashboard
    header('Location: users.php'); // Redirecting to users.php page
    exit(); // Exit script
}





// Editing an existing user
/* if (isset($_POST['edit'])) { // Check if the 'edit' button was clicked

    // Getting user input data from form
    $id = $_POST['id']; // Get user ID
    $userName = $_POST['fullname']; // Get full name
    $password = $_POST['password']; // Get password
    $userPhone = $_POST['phone']; // Get phone number
    $userType = $_POST['userType']; // Get user type
    $email = $_POST['email']; // Get email

    // Validating if required fields are not empty
    if (!empty(trim($userName)) && !empty(trim($userPhone)) && !empty(trim($userType)) && !empty(trim($email))) {

            // Check if the email address already exists in the database
            if(User::isEmailAddressExists($conn,$email)){
                $_SESSION['error'] = "This Email address already exists";
            }

            else {
                // Update the user's details
                if (!empty(trim($_POST['password']))) { // Check if password field is not empty

                    // Hashing the new password
                    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

                    // Creating a new User object with updated data
                    $newUser = new User(0, $userName, $email, $hashed_password, $userPhone, $userType);

                    // Updating user data in the database
                    if($newUser->update($conn, $id, $hashed_password)){
                        $_SESSION['success'] = "User data updated"; // Setting success message
                    }

                } else {
                    // Creating a new User object with updated data (without password)
                    $newUser = new User(0, $userName, $email, null, $userPhone, $userType);

                    // Updating user data in the database
                    if($newUser->update($conn, $id, '')){
                        $_SESSION['success'] = "User data updated"; // Setting success message
                    }
                }

        }

    } else {
        $_SESSION['error'] = "Enter the student data"; // Setting error message if any required field is empty
    }

    // Redirecting back to the dashboard
    header('Location: users.php'); // Redirecting to users.php page
    exit(); // Exit script
} */

// Removing a user
elseif (isset($_POST['remove'])) { // Check if the 'remove' button was clicked

    $id = $_POST['removeUserId']; // Get user ID from form

    // Check if user ID is not empty
    if (!empty(trim($id))) {

        // Create a new User object
        $newUser = new User();

        // Delete user by ID from the database
        if($newUser->deleteById($conn, $id))
        {
            $_SESSION['success'] = "User deleted"; // Setting success message
        }

        else
        {
            $_SESSION['error'] = "User Not Deleted"; // Setting error message
        }

    } else {
        $_SESSION['error'] = "Invalid User ID"; // Setting error message if user ID is empty
    }

    // Redirecting back to the dashboard
    header('Location: users.php'); // Redirecting to users.php page
    exit(); // Exit script

}

?>
