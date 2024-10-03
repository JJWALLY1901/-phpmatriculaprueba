<?php

// Include redirection and necessary files
include 'redirection.php';
require_once 'DatabaseConnection.php';
require_once 'Student.php';

// Obtener todos los estudiantes de la base de datos
$students = Student::getAll($conn);

// Calcular el numero total de estudiantes
$totalStudents = count($students);
// Initialize variables for gender distribution
$maleStudents = 0;
$femaleStudents = 0;
$malePercentage = 0;
$femalePercentage = 0;
// Initialize variable for average age
$averageAge = 0;

//Calcular la distribución por género y la edad promedio si hay estudiantes.
if ($totalStudents > 0) {
    $maleStudents = count(Student::getByGender($conn, 'male'));
    $femaleStudents = count(Student::getByGender($conn, 'female'));
    $malePercentage = ($maleStudents / $totalStudents) * 100;
    $femalePercentage = ($femaleStudents / $totalStudents) * 100;

    // Funcion parta calcular la edad a partir de la fecha de nacimiento
    function calculateAge($dob) {
        $today = new DateTime();
        $birthdate = new DateTime($dob);
        $age = $today->diff($birthdate)->y;
        return $age;
    } 

    // Calculate el total de edad de los estudiantes
    $totalAge = 0; 
    foreach ($students as $student) {
        $totalAge += calculateAge($student->getDateOfBirth());
    } 

    // Calcular prommedio de edad de los estudiantes
    $averageAge = $totalAge / $totalStudents;
}


// Confirmar si se envió el formulario de busqueda
if (isset($_POST['search'])) {
    $query = $_POST['query'];
    $gender = $_POST['gender'];

    // Check if any field is empty
    if (!empty(trim($query))) {
        // Use the search method with the gender parameter
        $students = Student::search($conn, $query, $gender);
    } else {
        // Handle the case when the query is empty
        $students = Student::search($conn, '', $gender);
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Dashboard de los estudiantes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style/common-style.css">
</head>
<body>


<?php
    // Page title override
    $pageTitleOverride = "Estudiantes";
    // Include common section
    include 'common-section.php';
?>



<div id="content">

    <h2>Estudiantes</h2>
    <div class="search-bar">
        <form action="students.php" method="post">
            <input type="text" id="student-search" class="search-field" name="query" placeholder="Buscar por estudiante">
            <select id="gender-search" class="search-field" name="gender">
                <option value="">Buscar por Género</option>
                <option value="male">Masculino </option>
                <option value="female">Femenino</option>
            </select>
            <button class="search-btn" type="submit" name="search"><i class="fas fa-search"></i> Buscar</button>
        </form> 
    </div>

    <button class="enroll-student-btn" onclick="openEnrollForm('add')"><i class="fas fa-plus"></i> Agregar nuevo</button>

    <!-- include the alert file -->
    <?php include 'alert-file.php'; ?>

<!-- Student table -->
<table id="studentTable">
    <thead>
        <tr>
            <th>ID</th>
            <th>Imagen</th>
            <th>Nombre</th>
            <th>Nacimiento</th>
            <th>Genero</th>
            <th>Dirección</th>
            <th>Celular</th>
            <th>Email</th>
            <th>Curso Matriculado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>


    <?php $i = 1; ?>
    <?php foreach ($students as $student): ?>
        <tr>
            <td> <?= $student->getId() ?> </td>
            <td style='width:75px; height:75px;'> <img src="<?= $student->getImage() ?>" style='width:100%; height:100%;'> </td>
            <td> <?= $student->getFullName() ?> </td>
            <td> <?= $student->getDateOfBirth() ?> </td>
            <td> <?= $student->getGender() ?> </td>
            <td> <?= $student->getAddress() ?> </td>
            <td> <?= $student->getPhoneNumber() ?> </td>
            <td> <?= $student->getEmail() ?> </td>
            <td> <button class='view-btn' onclick="fetchEnrolledCourses(<?= $student->getId() ?>)">Vista</button> </td>
            <td>
                <div class="action-buttons">
                    <button class="edit-btn" data-row-index="<?= $i ?>"><i class="fas fa-edit"></i> Editar</button>
                    <button class="delete-btn" data-row-index="<?=$i?>"><i class="fas fa-trash-alt"></i> Eliminar</button>
                </div>
            </td>
        </tr>
        <?php $i++; ?>
    <?php endforeach; ?>

        
    </tbody>
</table>



<!-- Table to display courses enrolled in -->
<table id="enrolledInCoursesTable">
    <thead>
        <tr>
            <th>Matricula ID</th>
            <th>Curso</th>
            <th>Nota</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <!-- Courses Enrolled in will be populated dynamically -->
    </tbody>
</table>





    <!-- Cards container -->
    <div class="cards-container">
        <div class="card">
            <i class="fas fa-users"></i>
            <h3>Total Estudiantes</h3>
            <span><?= $totalStudents ?></span>
        </div>
        <div class="card">
            <i class="fas fa-birthday-cake"></i>
            <h3>Edad Promedio</h3>
            <span><?= number_format($averageAge, 2) ?></span>
        </div>
        <div class="card">
            <i class="fas fa-venus-mars"></i>
            <h3>Distribucion por género</h3>
            <span><i class="fas fa-mars"></i>: <?= number_format($malePercentage, 2) ?>%, <i class="fas fa-venus"></i>: <?= number_format($femalePercentage, 2) ?>%</span>
        </div>
    </div>


<!-- Enroll Form Modal -->
<div class="modal" id="enrollForm">
    <div class="modal-content">
        <span class="close-btn" onclick="closeEnrollForm()">&times;</span>
        <h2 id='modal-title'>Agregar estudiante</h2>
        <form action="process_student.php" method="post" enctype="multipart/form-data">
            <input type="hidden" id="studentID" name="id" required>
            <div class="form-group">
                <label for="studentName">Nombre</label>
                <input type="text" id="studentName" name="fullname" required>
            </div>
            <div class="form-group">
                <label for="birthdate">Nacimiento</label>
                <input type="date" id="birthdate" name="birthdate" required>
            </div>
            <div class="form-group">
                <label for="gender">Genero</label>
                <select id="gender" name="gender" placeholder="gender" required>
                    <option value="male" name="male">Masculino</option>
                    <option value="female" name="female">Femenino</option>
                </select>
            </div>
            <div class="form-group">
                <label for="studentAddress">Dirección</label>
                <input type="text" id="studentAddress" name="address" required>
            </div>
            <div class="form-group">
                <label for="studentPhone">Celular</label>
                <input type="text" id="studentPhone" name="phone" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
   
            <!-- Add the input file element for selecting an image -->
            <div class="form-group">
                <label for="Student-image">Imagen de estudiante:</label>
                <input type="file" id="Student-image" name="student-image" value="">
            </div>

            <button id='add-edit' class="enroll-btn" type="submit" name="add">Agregar</button>
        </form>
    </div>
</div>




</div>



<!-- Remove Student Form -->
<div class="modal" id="removeStudentForm">
    <div class="modal-content">
        <span class="close-btn" onclick="closeRemoveStudentForm()">&times;</span>
        <h2 id='modal-remove-title'>Remove Student</h2>
        <p>Are you sure you want to remove this student?</p>
        <form action="process_student.php" method="post">
            <input type="hidden" id="removeStudentId" name="removeStudentId" required>
            <input type="hidden" id="student-image-delete" name="student-image-delete" value="1">
            <button id='remove' class="enroll-btn" type="submit" name="remove">Delete Student</button>
        </form>
    </div>
</div>



<!-- Modal for setting mark -->
<div class="modal" id="setMarkModal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeSetMarkModal()">&times;</span>
        <h2 id='modal-title'>Add Mark</h2>
        <!-- Mark form -->
        <form id="markForm" action="process_mark.php" method="post">
          <div class="form-group">
            <label for="enrollmentId">Enrollment ID</label>
            <input type="text" class="form-control" id="markEnrollmentID" name="markEnrollmentID" readonly>
          </div>
          <div class="form-group">
            <label for="mark">Mark</label>
            <input type="text" class="form-control" id="mark" name="mark" placeholder="Enter mark">
          </div>
          <div class="form-group">
            <label for="status">Status</label>
            <select class="form-control" id="status" name="status">
              <option value="pass">Pass</option>
              <option value="fail">Fail</option>
            </select>
          </div>
          <div class="form-group">
            <label for="remark">Remark</label>
            <textarea class="form-control" id="remark" name="remark" rows="3" placeholder="Enter remark"></textarea>
          </div>
          <button id='add-edit' class="enroll-btn" type="submit" name="addMark">Add Mark</button>
        </form>
    </div>
</div>








<script>
// Function to open the enrollment form for adding or editing a student
function openEnrollForm(add_OR_edit) {
    // Check if the form should be opened for adding a student
    if (add_OR_edit == "add") {
        // Display the enrollment form
        document.getElementById("enrollForm").style.display = "flex";
        // Set modal title for adding a student
        document.getElementById('modal-title').innerText = "Agregar Estudiante";
        // Set the form action for adding
        document.getElementById('add-edit').name = "add";
        // Set the button text for adding
        document.getElementById('add-edit').innerText = "Agregar";
        // Clear input fields
        document.getElementById('studentID').value = "";
        document.getElementById('studentName').value = "";
        document.getElementById('birthdate').value = "";
        document.getElementById('gender').value = "";
        document.getElementById('studentAddress').value = "";
        document.getElementById('studentPhone').value = "";
        document.getElementById('email').value = "";
    }
}

// Function to open the form for removing a student
function openRemoveStudentForm() {
    // Display the remove student form
    document.getElementById("removeStudentForm").style.display = "flex";
}

// Function to close the enrollment form
function closeEnrollForm() {
    // Hide the enrollment form
    document.getElementById("enrollForm").style.display = "none";
}

// Function to close the remove student form
function closeRemoveStudentForm() {
    // Hide the remove student form
    document.getElementById("removeStudentForm").style.display = "none";
}

// Get all the edit buttons
var editButtons = document.querySelectorAll(".edit-btn");

// Loop through each edit button and add click event listener
editButtons.forEach((button, index)=>{
    button.addEventListener('click', ()=>{
        // Get the corresponding table row
        const tableRow = document.querySelector(`table tbody tr:nth-child(${index + 1})`);

        // Get the values from the table row cells
        const studentID = tableRow.cells[0].textContent;
        const studentName = tableRow.cells[2].textContent;
        const gender = tableRow.cells[4].textContent.trim();
        const studentAddress = tableRow.cells[5].textContent;
        const studentPhone = tableRow.cells[6].textContent;
        const email = tableRow.cells[7].textContent;

        // Populate the form fields in the modal with the retrieved values
        document.getElementById('studentID').value = studentID;
        document.getElementById('studentName').value = studentName.trim();
        const birthdate = tableRow.cells[3].textContent.trim();
        document.getElementById('birthdate').value = birthdate;

        document.getElementById('studentAddress').value = studentAddress.trim();
        document.getElementById('studentPhone').value = studentPhone.trim();
        document.getElementById('email').value = email.trim();

        // Select the corresponding category in the dropdown menu
        const gender2 = tableRow.cells[4].textContent.trim();
        const genderDropdown = document.getElementById('gender');
        for (let i = 0; i < genderDropdown.options.length; i++) {
            if (genderDropdown.options[i].value.trim() === gender) {
                genderDropdown.options[i].selected = true;
                break;
            }
        }

        // Show the modal
        document.getElementById("enrollForm").style.display = "flex";
        document.getElementById('modal-title').innerText = "Edit Student";
        document.getElementById('add-edit').name = "edit";
        document.getElementById('add-edit').innerText = "Edit";
    });
});

// Get all the remove buttons
var removeButtons = document.querySelectorAll('.delete-btn');

// Add click event listener to each remove button
removeButtons.forEach((button, index)=>{
  button.addEventListener('click', (event) => {

    // Get the corresponding table row
    const tableRow = document.querySelector(`table tbody tr:nth-child(${index + 1})`);
    
    // Get student ID from the table row
    const studentID = tableRow.cells[0].textContent;

    // Get student picture if available
    const pictureCell = tableRow.cells[1];
    let picture;

    // Check if the picture is stored as an attribute or within an <img> tag
    if (pictureCell.hasAttribute("data-picture")) {
        picture = pictureCell.getAttribute("data-picture");
    } else if (pictureCell.querySelector("img")) {
        const img = pictureCell.querySelector("img");
        picture = img.getAttribute("src");
    } else {
        // Handle the case when the picture is not found
        picture = "";
    }

    // Populate the form fields in the modal with the retrieved values
    document.getElementById('removeStudentId').value = studentID;
    document.getElementById("student-image-delete").value = picture;

    // Show the modal
    document.getElementById("removeStudentForm").style.display = "flex";
    
  });
});

</script>




<script src="js/close-msg.js"></script>

<script src="js/pagination.js"></script>

<script>
  // Call handlePagination() for "productTable"
  handlePagination('studentTable', 5);
</script>



<script>

/* fetchEnrolledCourses(studentId): This function fetches enrolled courses for a given student ID from the server, 
populates the enrolled courses table with the fetched data, and handles any errors that occur during the fetch operation. */

    // Function to fetch enrolled courses for a student
    function fetchEnrolledCourses(studentId) {
        // Fetch data from the server using studentId
        fetch(`getEnrolledCourses.php?studentId=${studentId}`)
            // Convert the response to JSON format
            .then(response => response.json())
            // Process the fetched data
            .then(data => {
                // Select the table body where enrolled courses will be displayed
                const enrolledInCoursesTableBody = document.querySelector('#enrolledInCoursesTable tbody');
                // Clear existing table rows
                enrolledInCoursesTableBody.innerHTML = '';

                // Iterate over each course in the fetched data
                data.forEach(course => {
                    // Create a new row for each course
                    const row = document.createElement('tr');
                    // Populate the row with course data
                    row.innerHTML = `
                        <td>${course.enrollment_id}</td>
                        <td>${course.course_name}</td>
                        <td>${course.mark ? course.mark : ''}</td>
                        <!-- Add more columns if needed -->
                        <td>${course.mark ? 'Marked' : `<button class="mark-btn" onclick="openSetMarkModal(${course.enrollment_id})"><i class="fas fa-plus-circle"></i>Set Mark</button>`}</td>
                    `;
                    // Append the row to the table body
                    enrolledInCoursesTableBody.appendChild(row);
                });
            })
            // Handle errors if any occur during the fetch operation
            .catch(error => console.error('Error fetching enrolled courses:', error));
            // Scroll to the enrolledInCoursesTable
            var table = document.getElementById('enrolledInCoursesTable');
            if (table) {
                table.scrollIntoView({ behavior: 'smooth' });
            }
    }

    // Function to open the set mark modal for a specific enrollment
    function openSetMarkModal(enroll_id) {
        // Display the set mark modal
        document.getElementById("setMarkModal").style.display = "flex";
        // Set the enrollment ID in the modal form
        document.getElementById('markEnrollmentID').value = enroll_id;
        // Clear mark and remark fields in the modal form
        document.getElementById('mark').value = "";
        document.getElementById('remark').value = "";
    }

    // Function to close the set mark modal
    function closeSetMarkModal() {
        // Hide the set mark modal
        document.getElementById("setMarkModal").style.display = "none";
    }
</script>




</body>
</html>



