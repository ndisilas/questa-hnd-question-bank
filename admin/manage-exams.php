<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$pageTitle = 'Manage Exam Dates';
$message = '';
$error = '';

// Handle add exam date
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_exam'])) {
    $course_name = trim($_POST['course_name']);
    $course_code = trim($_POST['course_code']);
    $exam_date = $_POST['exam_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $venue = trim($_POST['venue']);
    $semester = intval($_POST['semester']);
    $academic_year = trim($_POST['academic_year']);
    $department_id = intval($_POST['department_id']);
    
    $stmt = $pdo->prepare("INSERT INTO exam_dates (department_id, course_name, course_code, exam_date, start_time, end_time, venue, semester, academic_year) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$department_id, $course_name, $course_code, $exam_date, $start_time, $end_time, $venue, $semester, $academic_year])) {
        $message = 'Exam date added successfully';
    } else {
        $error = 'Failed to add exam date';
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM exam_dates WHERE id = ?");
    $stmt->execute([$id]);
    $message = 'Exam date deleted successfully';
}

// Handle edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_exam'])) {
    $id = intval($_POST['exam_id']);
    $course_name = trim($_POST['course_name']);
    $course_code = trim($_POST['course_code']);
    $exam_date = $_POST['exam_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $venue = trim($_POST['venue']);
    $semester = intval($_POST['semester']);
    $academic_year = trim($_POST['academic_year']);
    $department_id = intval($_POST['department_id']);
    
    $stmt = $pdo->prepare("UPDATE exam_dates SET department_id = ?, course_name = ?, course_code = ?, exam_date = ?, start_time = ?, end_time = ?, venue = ?, semester = ?, academic_year = ? WHERE id = ?");
    $stmt->execute([$department_id, $course_name, $course_code, $exam_date, $start_time, $end_time, $venue, $semester, $academic_year, $id]);
    $message = 'Exam date updated successfully';
}

// Get all exam dates
$exams = $pdo->query("
    SELECT e.*, d.name as department_name 
    FROM exam_dates e 
    LEFT JOIN departments d ON e.department_id = d.id 
    ORDER BY e.exam_date ASC
")->fetchAll();

$departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();

include 'header.php';
?>

<div class="admin-card">
    <h2>Add New Exam Date</h2>
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>Department</label>
            <select name="department_id" class="form-select" required>
                <option value="">Select Department</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Course Name</label>
            <input type="text" name="course_name" class="form-input" required>
        </div>
        <div class="form-group">
            <label>Course Code</label>
            <input type="text" name="course_code" class="form-input" placeholder="e.g., SWE 301" required>
        </div>
        <div class="form-group">
            <label>Exam Date</label>
            <input type="date" name="exam_date" class="form-input" required>
        </div>
        <div class="form-group">
            <label>Start Time</label>
            <input type="time" name="start_time" class="form-input" required>
        </div>
        <div class="form-group">
            <label>End Time</label>
            <input type="time" name="end_time" class="form-input" required>
        </div>
        <div class="form-group">
            <label>Venue / Room</label>
            <input type="text" name="venue" class="form-input" required>
        </div>
        <div class="form-group">
            <label>Semester</label>
            <select name="semester" class="form-select" required>
                <option value="1">First Semester</option>
                <option value="2">Second Semester</option>
            </select>
        </div>
        <div class="form-group">
            <label>Academic Year</label>
            <input type="text" name="academic_year" class="form-input" placeholder="e.g., 2025/2026" value="2025/2026" required>
        </div>
        <button type="submit" name="add_exam" class="btn-primary">Add Exam Date</button>
    </form>
</div>

<div class="admin-card">
    <h2>All Exam Dates</h2>
    <table>
        <thead>
            <tr><th>Department</th><th>Course</th><th>Code</th><th>Date</th><th>Time</th><th>Venue</th><th>Semester</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($exams as $exam): ?>
            <form method="POST" style="display: contents;">
                <tr>
                    <td><?php echo htmlspecialchars($exam['department_name']); ?></td>
                    <td><input type="text" name="course_name" value="<?php echo htmlspecialchars($exam['course_name']); ?>" class="form-input" style="width: 150px;"></td>
                    <td><input type="text" name="course_code" value="<?php echo htmlspecialchars($exam['course_code']); ?>" class="form-input" style="width: 80px;"></td>
                    <td><input type="date" name="exam_date" value="<?php echo $exam['exam_date']; ?>" class="form-input" style="width: 120px;"></td>
                    <td>
                        <input type="time" name="start_time" value="<?php echo $exam['start_time']; ?>" style="width: 70px;"> - 
                        <input type="time" name="end_time" value="<?php echo $exam['end_time']; ?>" style="width: 70px;">
                    </td>
                    <td><input type="text" name="venue" value="<?php echo htmlspecialchars($exam['venue']); ?>" class="form-input" style="width: 100px;"></td>
                    <td>
                        <select name="semester" class="form-select" style="width: 100px;">
                            <option value="1" <?php echo $exam['semester'] == 1 ? 'selected' : ''; ?>>First</option>
                            <option value="2" <?php echo $exam['semester'] == 2 ? 'selected' : ''; ?>>Second</option>
                        </select>
                    </td>
                    <td>
                        <input type="hidden" name="exam_id" value="<?php echo $exam['id']; ?>">
                        <input type="hidden" name="department_id" value="<?php echo $exam['department_id']; ?>">
                        <input type="hidden" name="academic_year" value="<?php echo $exam['academic_year']; ?>">
                        <button type="submit" name="edit_exam" class="btn-edit" style="margin-right: 8px;">Save</button>
                        <a href="?delete=<?php echo $exam['id']; ?>" class="btn-danger" onclick="return confirm('Delete this exam date?')">Delete</a>
                    </td>
                </tr>
                </form>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>