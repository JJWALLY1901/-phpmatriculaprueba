<?php 

    // Include necessary files
    include 'redirection.php';
    require_once 'Enrollment.php';
    require_once 'Course.php';
    require_once 'Student.php';
    require_once 'DatabaseConnection.php';

    // Retrieve data from the database
    $students = Student::getAll($conn);
    $courses = Course::getAll($conn);
    $enrollments = Enrollment::getAll($conn);
    $activeEnrollments = Enrollment::countEnrollmentsStatus($conn)['activeCount'];
    $inactiveEnrollments = Enrollment::countEnrollmentsStatus($conn)['inactiveCount'];


    // Check if search form is submitted
    if (isset($_POST['search'])) {
        // Retrieve search parameters from the form
        $query = $_POST['query'];
        $status = $_POST['status'];

        // Check if any field is empty
        if (!empty(trim($query))) {
            // Call the search function to fetch enrollments based on the provided parameters
            $enrollments = Enrollment::search($conn, $query, $status);
        } else {
            // Handle the case when the query is empty
            $enrollments = Enrollment::search($conn, $query, $status);
        }

    }
    
    
    
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matricula Dashboard</title>
    <!-- Include necessary stylesheets -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style/common-style.css">
</head>
<body>


<?php
    // Include common section
    $pageTitleOverride = "Matricula";
    include 'common-section.php';
?>


<div id="content">
    <h2>Matricula</h2>
    <!-- Search form -->
    <div class="search-bar">
        <form action="enrollments.php" method="post">
            <input type="text" id="enrollment-search" class="search-field" name="query" placeholder="Buscar estudiante por curso">
            <select id="status-search" class="search-field" name="status">
                <option value="">Buscar por estado</option>
                <option value="active">Activo</option>
                <option value="inactive">Inactivo</option>
            </select>
            <button class="search-btn" type="submit" name="search"><i class="fas fa-search"></i> Buscar</button>
        </form> 
    </div>

    <!-- Add enrollment button -->
    <button class="enroll-student-btn" onclick="openEnrollmentForm('add')"><i class="fas fa-plus"></i> Agregar nueva Matricula</button>

    <!-- Alert section -->
    <?php include 'alert-file.php'; ?>

    <!-- Enrollment table -->
    <table id="enrollmentTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre Estudiante</th>
                <th>Curso</th>
                <th>Fecha Matricula</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            
        
        <?php $i = 0; ?>
        <?php foreach ($enrollments as $enrollment): ?>

        <?php
            // Check if the enrollment has a mark assigned to it
            $disabled = ($enrollment->hasMark($conn)) ? 'disabled' : ''; // Using the method hasMark() in Enrollment class
        ?>

            <tr>
                <td><?= $enrollment->getId() ?></td>
                <td><input type="hidden" id="stdID" name="id" value="<?= $enrollment->getStudentID() ?>"><?= $enrollment->getStudentName() ?></td>
                <td><input type="hidden" id="crsID" name="id" value="<?= $enrollment->getCourseID() ?>"><?= $enrollment->getCourseName() ?></td>
                <td><?= $enrollment->getEnrollmentDate() ?></td>
                <td><?= $enrollment->getEnrollmentStatus() ?></td>
                <td>
                    <div class="action-buttons">
                        <!-- Edit enrollment button -->
                        <button class="edit-btn" data-row-index="<?= $i ?>" data-student-id="<?= $enrollment->getStudentID() ?>" data-course-id="<?= $enrollment->getCourseID() ?>" onclick="openEnrollmentForm('edit')"><i class="fas fa-edit"></i> Edit</button>
                        <!-- Remove enrollment button -->
                        <button class="delete-btn" data-row-index="<?= $i ?>" onclick="openRemoveEnrollmentForm()"><i class="fas fa-trash-alt"></i> Delete</button>
                        <!-- Add mark button -->
                        <button class="mark-btn" data-row-index="<?= $i ?>" data-student-id="<?= $enrollment->getStudentID() ?>" data-course-id="<?= $enrollment->getCourseID() ?>" onclick="openMarkForm()" <?= $disabled ?>><i class="fas fa-plus-circle"></i>Mark</button>
                    </div>
                </td>
            </tr>
            <?php $i++; ?>
        <?php endforeach; ?>


        </tbody>
    </table>

    <!-- Enrollment cards -->
    <div class="cards-container">
        <div class="card">
            <i class="fas fa-users"></i>
            <h3>Total Matricula</h3>
            <span><?= count(Enrollment::getAll($conn))?></span>
        </div>
        <div class="card">
            <i class="fas fa-check-circle"></i>
            <h3>Matriculas Activas</h3>
            <span><?= $activeEnrollments ?></span>
        </div>
        <div class="card">
            <i class="fas fa-times-circle"></i>
            <h3>Matriculas Inactivas</h3>
            <span><?= $inactiveEnrollments ?></span>
        </div>
    </div>
</div>




<!-- Add Enrollment Modal -->
<!-- Modal for adding new enrollment -->
<div class="modal" id="addEnrollmentModal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeEnrollmentForm()">&times;</span>
        <h2 id='modal-title'>Agregar nueva matricula</h2>
        <!-- Form for adding new enrollment -->
        <form action="process_enrollment.php" method="post">
            <input type="hidden" id="enrollmentID" name="enrollmentID" required>
            <div class="form-group">
                <label for="studentSelect">Seleccionar estudiante:</label>
                <!-- Options for selecting student -->
                <select id="studentID" name="studentID" required>
                    <?php foreach ($students as $student): ?>
                        <option value="<?= $student->getId() ?>"><?= $student->getFullName() ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="courseSelect">Seleccionar curso:</label>
                <!-- Options for selecting course -->
                <select id="courseID" name="courseID" required>
                    <?php foreach ($courses as $course): ?>
                            <option value="<?= $course->getId() ?>"><?= $course->getName() ?></option>
                        <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="enrollmentDate">Fecha de matricula:</label>
                <input type="date" id="enrollmentDate" name="enrollmentDate" required>
            </div>
            <div class="form-group">
                <label for="enrollmentStatus">Estado:</label>
                <select id="enrollmentStatus" name="enrollmentStatus" required>
                    <option value="active">Activo</option>
                    <option value="inactive">Inactivo</option>
                </select>
            </div>
            <button id='add-edit' class="enroll-btn" type="submit" name="add">Agregar Matricula</button>
        </form>
    </div>
</div>





<!-- Remove Enrollment Modal -->
<!-- Modal for removing enrollment -->
<div class="modal" id="removeEnrollmentForm">
    <div class="modal-content">
        <span class="close-btn" onclick="closeRemoveEnrollmentForm()">&times;</span>
        <h2 id='modal-remove-title'>Remover matricula</h2>
        <p>¿Está seguro de que desea eliminar esta inscripción?</p>
        <!-- Form for removing enrollment -->
        <form action="process_enrollment.php" method="post">
            <input type="hidden" id="removeEnrollmentID" name="removeEnrollmentID" required>
            <button id='remove' class="enroll-btn" type="submit" name="remove">Borrar Matricula</button>
        </form>
    </div>
</div>
<!-- Remove Enrollment Modal -->




<!-- Add Mark Modal -->
<!-- Modal for adding mark -->
<div class="modal" id="addMarkModal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeMarkForm()">&times;</span>
        <h2 id='modal-title'>Agregar Nota</h2>
        <!-- Mark form -->
        <form id="markForm" action="process_mark.php" method="post">
          <div class="form-group">
            <label for="markEnrollmentID">Matricula ID</label>
            <input type="text" class="form-control" id="markEnrollmentID" name="markEnrollmentID" value="" readonly>
          </div>
          <div class="form-group">
            <label for="markStudentName">Estudiante</label>
            <input type="hidden" class="form-control" id="markStudentID" name="markStudentID">
            <input type="text" class="form-control" id="markStudentName" name="markStudentName"  readonly>
          </div>
          <div class="form-group">
            <label for="markCourseName">Cursos</label>
            <input type="hidden" class="form-control" id="markCourseID" name="markCourseID">
            <input type="text" class="form-control" id="markCourseName" name="markCourseName"  readonly>
          </div>
          <div class="form-group">
            <label for="mark">Comentario</label>
            <input type="text" class="form-control" id="mark" name="mark" placeholder="Enter mark" required>
          </div>
          <div class="form-group">
            <label for="status">Estado</label>
            <select class="form-control" id="status" name="status">
              <option value="pass">Aprobado</option>
              <option value="fail">Error</option>
            </select>
          </div>
          <div class="form-group">
            <label for="remark">Comentario</label>
            <textarea class="form-control" id="remark" name="remark" rows="3" placeholder="Agregar comentario"></textarea>
          </div>
          <button id='add-mark' class="enroll-btn" type="submit" name="addMark">Agrgar comentario</button>
        </form>
        
    </div>
</div>






<script>
    // Function to open the add enrollment form
    function openEnrollmentForm(add_OR_edit) {
        if(add_OR_edit == "add"){
            // Show the add enrollment modal
            document.getElementById("addEnrollmentModal").style.display = "flex";
            // Set modal title to "Add Enrollment"
            document.getElementById('modal-title').innerText = "Agregar Matricula";
            // Set button name to "add"
            document.getElementById('add-edit').name = "add";
            // Set button text to "Add"
            document.getElementById('add-edit').innerText = "Agregar";
            // Clear form fields
            document.getElementById('enrollmentID').value = "";
            document.getElementById('studentID').value = "";
            document.getElementById('courseID').value = "";
            document.getElementById('enrollmentDate').value = "";
            document.getElementById('enrollmentStatus').value = "";
        }
    }

    // Function to open the add mark form
    function openMarkForm() {
            // Show the add mark modal
            document.getElementById("addMarkModal").style.display = "flex";
            // Clear form fields
            document.getElementById('markEnrollmentID').value = "";
            document.getElementById('markStudentID').value = "";
            document.getElementById('markCourseID').value = "";
            document.getElementById('mark').value = "";
            document.getElementById('status').value = "";
            document.getElementById('remark').value = "";
    }

    // Function to close the add mark form
    function closeMarkForm() {
        document.getElementById("addMarkModal").style.display = "none";
    }

    // Function to open the remove enrollment form
    function openRemoveEnrollmentForm() {
            // Show the remove enrollment modal
            document.getElementById("removeEnrollmentForm").style.display = "flex";
    }

    // Function to close the add enrollment form
    function closeEnrollmentForm() {
        document.getElementById("addEnrollmentModal").style.display = "none";
    }

    // Function to close the remove enrollment form
    function closeRemoveEnrollmentForm() {
        document.getElementById("removeEnrollmentForm").style.display = "none";
    }

    // Get all the edit buttons
    const editButtons = document.querySelectorAll('.edit-btn');
    // Get all the delete buttons
    const removeButtons = document.querySelectorAll('.delete-btn');
    // Get all the mark buttons
    const markButtons = document.querySelectorAll('.mark-btn');

    // Add a click event listener to each edit button
    editButtons.forEach((button, index)=>{
        button.addEventListener('click', (event) => {

            const studentID = event.target.dataset.studentId;
            const courseID = event.target.dataset.courseId;
            console.log('Raw studentId:', studentID);
            console.log('Raw courseId:', courseID);

            // Get the table row based on the row index
            const tableRow = document.querySelector(`table tbody tr:nth-child(${index + 1})`);

            // Get the values from the table row cells
            const enrollmentID = tableRow.cells[0].textContent;
            const studentName = tableRow.cells[1].textContent;
            const courseName = tableRow.cells[2].textContent;
            const enrollmentDate = tableRow.cells[3].textContent;
            const enrollmentStatus = tableRow.cells[4].textContent;

            // Populate the form fields in the modal with the retrieved values
            document.getElementById('enrollmentID').value = enrollmentID;
            document.getElementById('enrollmentDate').value = enrollmentDate;
            document.getElementById('enrollmentStatus').value = enrollmentStatus;

            // Select the corresponding category in the dropdown menu
            const studentDropdown = document.getElementById('studentID');
            for (let i = 0; i < studentDropdown.options.length; i++) {
                if (studentDropdown.options[i].value === studentID) {
                    studentDropdown.options[i].selected = true;
                    break;
                }
            } 

            // Select the corresponding category in the dropdown menu
            const courseDropdown = document.getElementById('courseID');
            for (let i = 0; i < courseDropdown.options.length; i++) {
                if (courseDropdown.options[i].value === courseID) {
                    courseDropdown.options[i].selected = true;
                    break;
                }
            } 

            // Show the modal
            document.getElementById("addEnrollmentModal").style.display = "flex";
            document.getElementById('modal-title').innerText = "Edit Enrollment";
            document.getElementById('add-edit').name = "edit";
            document.getElementById('add-edit').innerText = "Edit";
        });
    });

    // Add a click event listener to each delete button
    removeButtons.forEach((button, index)=>{
        button.addEventListener('click', (event) => {

            // Get the table row based on the row index
            const tableRow = document.querySelector(`table tbody tr:nth-child(${parseInt(index) + 1})`);

            // Get the values from the table row cells
            const enrollmentID = tableRow.cells[0].textContent;

            // Populate the form fields in the modal with the retrieved values
            document.getElementById('removeEnrollmentID').value = enrollmentID;

            // Show the modal
            document.getElementById("removeEnrollmentForm").style.display = "flex";
        });
    });

    // Add a click event listener to each mark button
    markButtons.forEach((button, index)=>{
        button.addEventListener('click', (event) => {

            const studentID = event.target.dataset.studentId;
            const courseID = event.target.dataset.courseId;
            console.log('Raw studentId:', studentID);
            console.log('Raw courseId:', courseID);

            // Get the table row based on the row index
            const tableRow = document.querySelector(`table tbody tr:nth-child(${parseInt(index) + 1})`);

            // Get the values from the table row cells
            const enrollmentID = tableRow.cells[0].textContent;
            const studentName = tableRow.cells[1].textContent;
            const courseName = tableRow.cells[2].textContent;

            // Populate the form fields in the modal with the retrieved values
            document.getElementById('markEnrollmentID').value = enrollmentID;
            document.getElementById('markStudentID').value = studentID;
            document.getElementById('markCourseID').value = courseID;
            document.getElementById('markStudentName').value = studentName;
            document.getElementById('markCourseName').value = courseName;
            console.log('Raw markStudentName:', studentName);
            console.log('Raw markCourseName:', courseName);

            // Show the modal
            document.getElementById("addMarkModal").style.display = "flex";

        });
    });

</script>

<script src="js/pagination.js"></script>

<script>
  // Call handlePagination() for "enrollmentTable"
  handlePagination('enrollmentTable', 3);
</script>

<script src="js/close-msg.js"></script>


</body>
</html>
