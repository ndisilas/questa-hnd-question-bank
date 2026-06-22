<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$pageTitle = 'Manage Timetable';
$message = '';
$error = '';

// Handle add timetable entry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_entry'])) {
    $department_id = intval($_POST['department_id']);
    $course_name = trim($_POST['course_name']);
    $course_code = trim($_POST['course_code']);
    $day_of_week = $_POST['day_of_week'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $lecturer = trim($_POST['lecturer']);
    $venue = trim($_POST['venue']);
    
    $stmt = $pdo->prepare("INSERT INTO timetable (department_id, course_name, course_code, day_of_week, start_time, end_time, lecturer, venue) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$department_id, $course_name, $course_code, $day_of_week, $start_time, $end_time, $lecturer, $venue])) {
        $message = 'Timetable entry added successfully';
    } else {
        $error = 'Failed to add entry';
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM timetable WHERE id = ?");
    $stmt->execute([$id]);
    $message = 'Entry deleted successfully';
}

// Get all timetable entries
$entries = $pdo->query("
    SELECT t.*, d.name as department_name 
    FROM timetable t 
    LEFT JOIN departments d ON t.department_id = d.id 
    ORDER BY d.name, FIELD(t.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), t.start_time
")->fetchAll();

$departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

include 'header.php';
?>

<div class="admin-card">
    <h2>Add Timetable Entry</h2>
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
            <label>Day of Week</label>
            <select name="day_of_week" class="form-select" required>
                <?php foreach ($days as $day): ?>
                    <option value="<?php echo $day; ?>"><?php echo $day; ?></option>
                <?php endforeach; ?>
            </select>
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
            <label>Lecturer</label>
            <input type="text" name="lecturer" class="form-input" required>
        </div>
        <div class="form-group">
            <label>Venue / Room</label>
            <input type="text" name="venue" class="form-input" required>
        </div>
        <button type="submit" name="add_entry" class="btn-primary">Add Entry</button>
    </form>
</div>

<div class="admin-card">
    <h2>All Timetable Entries</h2>
    <table class="admin-table">
        <thead>
            <tr><th>Department</th><th>Course</th><th>Day</th><th>Time</th><th>Lecturer</th><th>Venue</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($entries as $entry): ?>
            <tr>
                <td><?php echo htmlspecialchars($entry['department_name']); ?></td>
                <td><?php echo htmlspecialchars($entry['course_name']); ?> (<?php echo htmlspecialchars($entry['course_code']); ?>)</td>
                <td><?php echo $entry['day_of_week']; ?></td>
                <td><?php echo date('h:i A', strtotime($entry['start_time'])); ?> - <?php echo date('h:i A', strtotime($entry['end_time'])); ?></td>
                <td><?php echo htmlspecialchars($entry['lecturer']); ?></td>
                <td><?php echo htmlspecialchars($entry['venue']); ?></td>
                <td><a href="?delete=<?php echo $entry['id']; ?>" class="btn-danger" onclick="return confirm('Delete this entry?')">Delete</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>