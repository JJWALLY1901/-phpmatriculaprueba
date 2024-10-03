<?php
// Include the database connection file
require_once "DatabaseConnection.php";

// Check if the login form was submitted
if(isset($_POST['login']))
{
    // Start the session
    session_start();

    // Get the user input from the form
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if the email exists in the database
    $email_check_query = "SELECT * FROM `user` WHERE `email`=:email";
    $stmt = $conn->prepare($email_check_query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // If the email exists, verify the password
    if($result)
    {  
        if(password_verify($password, $result['password_']))
        {
            // Retrieve the user info from the database
            $_SESSION['fullname'] = $result['fullname'];
            $_SESSION['email'] = $result['email'];
            $_SESSION['user_role'] = $result['user_role'];
            
            header("Location: dashboard.php");
            exit();
        }
        else
        {
            $_SESSION['error'] = 'Contraseña Incorrecta';
            header("Location: login.php");
            exit();
        }
    }
    else
    {
        $_SESSION['error'] = 'Email Incorrecto';
        header("Location: login.php");
        exit();
    }
    
    // Clear any existing error messages
    unset($_SESSION['error']);
}
?>

