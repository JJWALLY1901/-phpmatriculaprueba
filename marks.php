<?php 

    // Include necessary files
    include 'redirection.php';
    require_once 'DatabaseConnection.php';
    require_once 'Mark.php';
    require_once 'Enrollment.php';
    require_once 'Student.php';
    require_once 'Course.php';

    // Fetch data from the database
    $marks = Mark::getAll($conn);
    $enrollments = Enrollment::getAll($conn);
    $students = Student::getAll($conn);
    $courses = Course::getAll($conn);

    // Handle search functionality
    if (isset($_POST['search'])) {
        $query = $_POST['query'];
        $status = $_POST['status'];
    
        // Check if any field is empty
        if (!empty(trim($query))) {
            // Use the search method with the status parameter
            $marks = Mark::search($conn, $query, $status);
        } else {
            // Handle the case when the query is empty
            $marks = Mark::search($conn, '', $status);
        }
    }

    // Initialize variables for highest mark, lowest mark, sum of marks, and total number of marks
    $highestMark = 00;
    $lowestMark = PHP_INT_MAX;
    $totalMarks = 0;
    $totalCount = count($marks);

    // Iterate over marks data to calculate statistics
    foreach ($marks as $mark) {
        $currentMark = $mark->getMarkValue();
        
        // Calculate highest mark
        if ($currentMark > $highestMark) {
            $highestMark = $currentMark;
        }
        
        // Calculate lowest mark
        if ($currentMark < $lowestMark) {
            $lowestMark = $currentMark;
        }
        
        // Sum up marks
        $totalMarks += $currentMark;
    }

    // If no marks were found, set lowest mark to 0
    if ($totalCount === 0) {
        $lowestMark = 0;
    }

    // Calculate average mark
    $averageMark = ($totalCount > 0) ? ($totalMarks / $totalCount) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marks Page</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style/common-style.css">
</head>
<body>

<?php
    // Include common section of the page
    $pageTitleOverride = "Marks";
    include 'common-section.php';
?>

<div id="content">
    <h2>Marks</h2>

    <!-- Search bar -->
    <div class="search-bar">
        <form action="marks.php" method="post">
            <input type="text" id="mark-search" class="search-field" name="query" placeholder="Search marks...">
            <select id="status-search" class="search-field" name="status">
                <option value="">Search by status</option>
                <option value="Pass">Pass</option>
                <option value="Fail">Fail</option>
            </select>
            <button class="search-btn" type="submit" name="search"><i class="fas fa-search"></i> Search</button>
        </form> 
    </div>

    <!-- Add mark button -->
    
    <!-- <button class="enroll-student-btn" onclick="openMarkForm('add')"><i class="fas fa-plus"></i> Add Student Mark</button> -->

    <!-- Include alert file -->
    <?php include 'alert-file.php'; ?>

    <!-- Marks table -->
    <table id="marksTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Enrollment</th>
                <th>Student</th>
                <th>Course</th>
                <th>Marks</th>
                <th>Status</th>
                <th>Remark</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>

        <?php $i = 0; ?>
        <?php foreach ($marks as $mark): ?>
            <tr>
                <td><?= $mark->getId() ?></td>
                <td><?= $mark->getEnrollmentID() ?></td>
                <td>
                    <input type="hidden" id="enrollmentID" name="id" value="<?= $mark->getStudentName() ?>">
                    <?= $mark->getStudentName() ?>
                </td>
                <td>
                    <input type="hidden" id="courseID" name="id" value="<?= $mark->getCourseName() ?>">
                    <?= $mark->getCourseName() ?>
                </td>
                <td><?= $mark->getMarkValue() ?></td>
                <td><?= $mark->getStatus() ?></td>
                <td><?= $mark->getRemark() ?></td>
                <td><?= $mark->getMarkDate() ?></td>
                <td>
                    <div class="action-buttons">
                        <button class="edit-btn" data-row-index="<?= $i ?>" onclick="openMarkForm('edit')"><i class="fas fa-edit"></i>Edit</button>
                        <button class="delete-btn" data-row-index="<?= $i ?>" onclick="openRemoveMarkForm()"><i class="fas fa-trash-alt"></i>Delete</button>
                    </div>
                </td>
            </tr>
            <?php $i++; ?>
        <?php endforeach; ?>

        </tbody>
    </table>

    <!-- Cards for displaying statistics -->
    <div class="cards-container">
        <div class="card">
            <i class="fas fa-trophy"></i>
            <h3>Highest Mark</h3>
            <span><?= $highestMark ?></span>
        </div>
        <div class="card">
            <i class="fas fa-award"></i>
            <h3>Lowest Mark</h3>
            <span><?= $lowestMark ?></span>
        </div>
        <div class="card">
            <i class="fas fa-chart-line"></i>
            <h3>Average Mark</h3>
            <span><?= number_format($averageMark, 2) ?></span>
        </div>
    </div>

</div>




<!-- Add Mark Modal -->
<div class="modal" id="addMarkModal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeMarkForm()">&times;</span>
        <h2 id='modal-title'>Add Mark</h2>
        <!-- Mark form -->
        <form id="markForm" action="process_mark.php" method="post">
          <input type="hidden" class="form-control" id="markID" name="markID">
          <div class="form-group">
            <label for="markEnrollmentID">Enrollment ID</label>
            <input type="text" class="form-control" id="markEnrollmentID" name="markEnrollmentID" placeholder="Enter enrollment ID">
          </div>
            <div class="form-group">
                <label for="markCourseName">Course:</label>
                <!-- <input type="hidden" class="form-control" id="markCourseID" name="markCourseID"> -->
                <input type="text" class="form-control" id="markCourseName" name="markCourseName"  readonly>
            </div>
            <div class="form-group">
                <label for="markStudentName">Student:</label>
                <!-- <input type="hidden" class="form-control" id="markStudentID" name="markStudentID"> -->
                <input type="text" class="form-control" id="markStudentName" name="markStudentName"  readonly>
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




<!-- Remove Enrollment Modal -->
<div class="modal" id="removeMarkForm">
    <div class="modal-content">
        <span class="close-btn" onclick="closeRemoveMarkForm()">&times;</span>
        <h2 id='modal-remove-title'>Remove Mark</h2>
        <p>Are you sure you want to remove this Mark?</p>
        <form action="process_mark.php" method="post">
            <input type="hidden" id="removeMarkID" name="removeMarkID" required>
            <button id='remove' class="enroll-btn" type="submit" name="remove">Delete Mark</button>
        </form>
    </div>
</div>






<script>
    // Function to open the mark form for adding or editing
    function openMarkForm(add_OR_edit) {
        // Check if the action is to add
        if(add_OR_edit == "add"){
            // Display the add mark modal
            document.getElementById("addMarkModal").style.display = "flex";
            // Set modal title
            document.getElementById('modal-title').innerText = "Add Mark";
            // Set form action to add
            document.getElementById('add-edit').name = "add";
            // Set button text
            document.getElementById('add-edit').innerText = "Add";
            // Clear form fields
            document.getElementById('markEnrollmentID').value = "";
/*             document.getElementById('markStudentID').value = "";
            document.getElementById('markCourseID').value = ""; */
            document.getElementById('markStudentName').value = "";
            document.getElementById('markCourseName').value = "";
            document.getElementById('status').value = "";
        }
    }

    // Function to open the remove mark form
    function openRemoveMarkForm() {
        document.getElementById("removeMarkForm").style.display = "flex";
    }

    // Function to close the mark form
    function closeMarkForm() {
        document.getElementById("addMarkModal").style.display = "none";
    }

    // Function to close the remove mark form
    function closeRemoveMarkForm() {
        document.getElementById("removeMarkForm").style.display = "none";
    }

    // Get all the edit buttons
    const editButtons = document.querySelectorAll('.edit-btn');
    // Get all the delete buttons
    const removeButtons = document.querySelectorAll('.delete-btn');

    // Add a click event listener to each edit button
    editButtons.forEach((button, index)=>{
        button.addEventListener('click', (event) => {
            
            // Get the table row based on the row index
            const tableRow = document.querySelector(`table tbody tr:nth-child(${index + 1})`);
            
            // Get the values from the table row cells
            const markID = tableRow.cells[0].textContent;
            const enrollmentID = tableRow.cells[1].textContent;
            const studentName = tableRow.cells[2].textContent.trim();
            const courseName = tableRow.cells[3].textContent.trim();
            const mark = tableRow.cells[4].textContent.trim();
            console.log(mark);
            const status = tableRow.cells[5].textContent.trim();
            const remark = tableRow.cells[6].textContent.trim();
            const markDate = tableRow.cells[7].textContent;
            
            // Populate the form fields in the modal with the retrieved values
            document.getElementById('markID').value = markID;
            document.getElementById('markEnrollmentID').value = enrollmentID;
            document.getElementById('markStudentName').value = studentName;
            document.getElementById('markCourseName').value = courseName;
            document.getElementById('mark').value = mark;
            document.getElementById('remark').value = remark;
            
            // Select the corresponding status in the dropdown menu
            const statusDropdown = document.getElementById('status');
            for (let i = 0; i < statusDropdown.options.length; i++) {
                if (statusDropdown.options[i].value === status) {
                    statusDropdown.options[i].selected = true;
                    break;
                }
            } 
            
            // Show the modal
            document.getElementById("addMarkModal").style.display = "flex";
            document.getElementById('modal-title').innerText = "Edit Mark";
            document.getElementById('add-edit').name = "edit";
            document.getElementById('add-edit').innerText = "Edit";
        });
    });

    // Add a click event listener to each delete button
    removeButtons.forEach((button, index)=>{
        button.addEventListener('click', (event) => {
            
            // Get the table row based on the row index
            const tableRow = document.querySelector(`table tbody tr:nth-child(${index + 1})`);
            
            // Get the values from the table row cells
            const markID = tableRow.cells[0].textContent;
            
            // Populate the form field in the modal with the retrieved value
            document.getElementById('removeMarkID').value = markID;

            // Show the modal
            document.getElementById("removeMarkForm").style.display = "flex";
        });
    });
</script>

<!-- Include pagination script -->
<script src="js/pagination.js"></script>

<!-- Call handlePagination() for "marksTable" -->
<script>
  handlePagination('marksTable', 3);
</script>

<!-- Include close message script -->
<script src="js/close-msg.js"></script>



</body>
</html>
