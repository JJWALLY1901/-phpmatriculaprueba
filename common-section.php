<?php

// Establecer título de página predeterminado
$pageTitle = "Dashboard";
// OCULTAR EL ENLACE DE USUARIO DEL MENÚ SI EL ROL DE USUARIO ES 'USUARIO'
$user_role = $_SESSION['user_role'];

$manage_users = "";

if($user_role == 'admin'){
    $manage_users = '<li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>';
}

// Check if a custom page title is provided
if (isset($pageTitleOverride)) {
    $pageTitle = $pageTitleOverride;
}

// Mostrar la barra lateral
echo '<div id="sidebar">
    <h1>'.$pageTitle.'</h1>
    <ul>
        <li><a href="dashboard.php"><i class="fas fa-home"></i> Inicio</a></li>
        <li><a href="courses.php"><i class="fas fa-book"></i> Cursos</a></li>
        <li><a href="enrollments.php"><i class="fas fa-graduation-cap"></i> Matricula</a></li>
        
        '. $manage_users .'        
    </ul>
    <div id="user-info">
        <p>Bienvenido, '.$_SESSION['fullname'].'</p>
        <p><a href="logout.php">Cerrar Sesion</a></p>
    </div>
</div>';

//<li><a href="dashboard.php"><i class="fas fa-home"></i> Inicio</a></li>
//<li><a href="students.php"><i class="fas fa-user"></i> Estudiantes</a></li>
//<li><a href="courses.php"><i class="fas fa-book"></i> Cursos</a></li>
//<li><a href="enrollments.php"><i class="fas fa-graduation-cap"></i> Matricula</a></li>
//<li><a href="marks.php"><i class="fas fa-tag"></i> Marks</a></li>

?>

