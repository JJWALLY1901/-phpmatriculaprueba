<?php
// Including necessary files
    include 'redirection.php'; // Redirects users if not logged in
    require_once 'User.php'; // Include User class
    require_once 'DatabaseConnection.php'; // Include DatabaseConnection class


    // Check the user's role
    $user_role = $_SESSION['user_role'];

    // If the user is not an admin, redirect to the dashboard page
    if($user_role != 'admin'){
    header('Location: dashboard.php');
    }


    // Retrieve all users from the database
    $users = User::getAll($conn); 

    // Handle search functionality if form is submitted
    if (isset($_POST['search'])) {
        $query = $_POST['query']; // Get search query
        $userType = $_POST['user-type']; // Get user type

        // Check if any field is empty
        if (!empty(trim($query))) {
            // Use the search method with the userType parameter
            $users = User::search($conn, $query, $userType);
        } else {
            // Handle the case when the query is empty
            $users = User::search($conn, '', $userType);
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style/common-style.css">
</head>
<body>

<?php
    // Include common section for header
    $pageTitleOverride = "Usuarios";
    include 'common-section.php';
?>

<div id="content">
    <h2>Usuarios</h2>
    <!-- Search bar -->
    <div class="search-bar">
        <form action="users.php" method="post">
            <input type="text" id="user-search" class="search-field" name="query" placeholder="Buscar por usuario">
            <select id="user-type-search" class="search-field" name="user-type">
                <option value="">Buscar por tipo de usuario</option>
                <option value="admin">Administrador</option>
                <option value="user">Usuario</option>
            </select>
            <button class="search-btn" type="submit" name="search"><i class="fas fa-search"></i> Buscar</button>
        </form> 
    </div>

    <!-- Button to add a new user -->
    <button class="enroll-student-btn" onclick="openUserForm('add')"><i class="fas fa-plus"></i> Agregar nuevo usuario </button>

    <!-- Include the alert file -->
    <?php include 'alert-file.php'; ?>

    <!-- Table to display users -->
    <table id="userTable">
        <thead>
            <tr>
                <th>Codigo de alumno</th>
                <th>Nombre completo</th>
                <th>Email</th>
                <th>Contraseña</th>
                <th>Celular</th>
                <th>Rol de usuario</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <!-- Loop through users to display in table -->
        <?php $i = 0; ?>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user->getId() ?></td>
                <td><?= $user->getFullName() ?></td>
                <td><?= $user->getEmail() ?></td>
                <td>********</td>
                <td><?= $user->getPhoneNumber() ?></td>
                <td><?= $user->getUserType() ?></td>
                
                <td>
                    <div class="action-buttons">
                        <!-- Button to edit user -->
                        <button class="edit-btn" data-row-index="<?= $i ?>" onclick="openUserForm('edit')"><i class="fas fa-edit"></i> Editar</button>
                        <!-- Button to delete user -->
                        <button class="delete-btn" data-row-index="<?= $i ?>" onclick="openRemoveUserForm()"><i class="fas fa-trash-alt"></i> Eliminar</button>
                    </div>
                </td>
            </tr>
            <?php $i++; ?>
        <?php endforeach; ?>

        <tbody>


        </tbody>
    </table>

</div>



<!-- Add User Modal -->
<div class="modal" id="addUser">
    <div class="modal-content">
        <span class="close-btn" onclick="closeAddForm()">&times;</span>
        <h2 id='modal-title'>Agregar nuevo usuario</h2>
        <form action="process_user.php" method="post" onsubmit="return validatePassword()">
            <input type="hidden" id="userID" name="id" required>
            <div class="form-group">
                <label for="userName">Usuario</label>
                <input type="text" id="userName" name="fullname" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="userPhone">Celular</label>
                <input type="text" id="userPhone" name="phone" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="c-password">Confirmar Contraseña</label>
                <input type="password" id="c-password" name="c-password" placeholder="Confirm password" required>
                <span id="password-error" style="color: red;"></span>
            </div>
            <div class="form-group">
                <label for="userType">Tipo de usuario</label>
                <select id="userType" name="userType" required>
                    <option value="admin">Administrador</option>
                    <option value="user">Usuario</option>
                </select>
            </div>
            <button id='add-edit' class="enroll-btn" type="submit" name="add">Agregar usuario</button>
        </form>
    </div>
</div>



<!-- Remove User Modal -->
<div class="modal" id="removeUserForm">
    <div class="modal-content">
        <span class="close-btn" onclick="closeRemoveUserForm()">&times;</span>
        <h2 id='modal-remove-title'>Remover usuario</h2>
        <p>¿Estás seguro de que quieres eliminar a este usuario?</p>
        <form action="process_user.php" method="post">
            <input type="hidden" id="removeUserId" name="removeUserId" required>
            <button id='remove' class="enroll-btn" type="submit" name="remove">Borrar usuario</button>
        </form>
    </div>
</div>




<script>
    
    // Function to validate password and confirm password fields
function validatePassword() {
    var password = document.getElementById("password").value;
    var confirmPassword = document.getElementById("c-password").value;

    // Check if passwords match
    if (password !== confirmPassword) {
        document.getElementById("password-error").innerText = "Las contraseñas no coinciden";
        return false; // Prevent form submission
    } else {
        document.getElementById("password-error").innerText = "";
        return true; // Allow form submission
    }
}

// Function to open user form for adding or editing a user
function openUserForm(add_OR_edit) {
    if(add_OR_edit == "add"){
        // Display the add user form
        document.getElementById("addUser").style.display = "flex";
        document.getElementById('modal-title').innerText = "Agrgar usuario";
        document.getElementById('add-edit').name = "add";
        document.getElementById('add-edit').innerText = "Agregar";
        // Clear form fields
        document.getElementById('userID').value = "";
        document.getElementById('userName').value = "";
        document.getElementById('password').value = "";
        document.getElementById('c-password').value = "";
        document.getElementById('password').setAttribute('required', '');
        document.getElementById('c-password').setAttribute('required', '');
        document.getElementById('userPhone').value = "";
        document.getElementById('email').value = "";
        document.getElementById('userType').value = "";
    }
}

// Function to open remove user form
function openRemoveUserForm() {
    document.getElementById("removeUserForm").style.display = "flex";
}

// Function to close the add user form
function closeAddForm() {
    document.getElementById("addUser").style.display = "none";
}

// Function to close the remove user form
function closeRemoveUserForm() {
    document.getElementById("removeUserForm").style.display = "none";
}

// Get all the edit buttons
const editButtons = document.querySelectorAll('.edit-btn');

// Get all the delete buttons
const removeButtons = document.querySelectorAll('.delete-btn');

// Add a click event listener to each edit button
editButtons.forEach((button, index) => {
    button.addEventListener('click', (event) => {
        // Get the corresponding table row
        const tableRow = document.querySelector(`table tbody tr:nth-child(${index + 1})`);

        // Get the values from the table row cells
        const userID = tableRow.cells[0].textContent;
        const userName = tableRow.cells[1].textContent;
        const email = tableRow.cells[2].textContent;
        const password = tableRow.cells[3].textContent;
        const userPhone = tableRow.cells[4].textContent;
        const userType = tableRow.cells[5].textContent.trim();
        
        // Populate the form fields in the modal with the retrieved values
        document.getElementById('userID').value = userID;
        document.getElementById('userName').value = userName;
        document.getElementById('password').removeAttribute('required');
        document.getElementById('c-password').removeAttribute('required');
        document.getElementById('userPhone').value = userPhone;
        document.getElementById('email').value = email;
        
        // Select the corresponding userType in the dropdown menu
        const userTypeDropdown = document.getElementById('userType');
        for (let i = 0; i < userTypeDropdown.options.length; i++) {
            if (userTypeDropdown.options[i].value === userType) {
                userTypeDropdown.options[i].selected = true;
                break;
            }
        }
        
        // Show the modal
        document.getElementById("addUser").style.display = "flex";
        document.getElementById('modal-title').innerText = "Editar usuario";
        document.getElementById('add-edit').name = "edit";
        document.getElementById('add-edit').innerText = "Editar";
    });
});

// Add a click event listener to each remove button
removeButtons.forEach((button, index) => {
    button.addEventListener('click', (event) => {
        // Get the table row based on the row index
        const tableRow = document.querySelector(`table tbody tr:nth-child(${parseInt(index) + 1})`);
        
        // Get the values from the table row cells
        const userID = tableRow.cells[0].textContent;
        
        // Populate the form fields in the modal with the retrieved values
        document.getElementById('removeUserId').value = userID;

        // Show the modal
        document.getElementById("removeUserForm").style.display = "flex";
    });
});


</script>

<!-- Include pagination script -->
<script src="js/pagination.js"></script>

<!-- Call handlePagination() for "userTable" -->
<script>
    handlePagination('userTable', 3);
</script>

<!-- Include close message script -->
<script src="js/close-msg.js"></script>


</body>
</html>



