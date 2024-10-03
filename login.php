<?php

// check if the user is already logged in , redirect to the dashboard page
session_start();

 if(isset($_SESSION['email']))
 {
    header('Location: dashboard.php');
 } 


?>


<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Polinomio - Sistema de Matricula UNFV</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
	<link rel="stylesheet" href="style/common-style.css">
	<style>
		* { box-sizing: border-box; }

		body {
			background-color: #3498db;
			font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
			margin: 0;
			display: flex;
			justify-content: center;
			align-items: center;
			height: 100vh;
		}

		.container {
			width: 100%;
			max-width: 400px;
			padding: 30px;
			background-color: #fff;
			border-radius: 10px;
			box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
		}

		h1 {
			text-align: center;
			margin-bottom: 20px;
			color: #3498db;
		}

		form {
			display: flex;
			flex-direction: column;
			align-items: center;
		}

		input[type="email"],
		input[type="password"] {
			width: 100%;
			padding: 15px;
			margin-bottom: 20px;
			border: none;
			border-radius: 5px;
			background-color: #ecf0f1;
			color: #333;
			font-size: 16px;
			box-shadow: 0px 0px 5px rgba(00, 00, 00, 0.1);
			transition: box-shadow 0.3s ease-in-out; /* Combine transitions */
			outline: none;
		}

		input[type="email"]:focus,
		input[type="password"]:focus {
			box-shadow: 0px 0px 5px rgba(55, 127, 80, 1);
		}


		button[type="submit"] {
			padding: 10px;
			border: none;
			border-radius: 5px;
			background-color: #3498db;
			color: #fff;
			font-size: 16px;
			font-weight: bold;
			box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.1);
			cursor: pointer;
			transition: background-color 0.3s ease-in-out;
			width: 100%;
		}

		button[type="submit"]:hover {
			background-color: #2980b9;
		}

		.error {
			color: #e74c3c;
			font-size: 14px;
			margin-bottom: 10px;
			text-align: center;
		}
	</style>
</head>
<body>
	<div class="container">
		<h1><i class="fas fa-graduation-cap"></i>Polinomio</h1>
		
		<!-- include the alert file -->
		<?php include 'alert-file.php'; ?>

		<form method="POST" action="login_script.php">
			<input type="email" name="email" placeholder="Email" required>
			<input type="password" name="password" placeholder="Contraseña" required>
			<button type="submit" name="login">Ingresar</button>
		</form>
	</div>

<script src="js/close-msg.js"></script>

</body>
</html>


