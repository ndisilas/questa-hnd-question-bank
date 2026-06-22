<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$pageTitle = 'Manage Courses';
$message = '';
$error = '';

// Handle add course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    $name = trim($_POST['name']);
    $code = trim($_POST['code']);
    $department_id = intval($_POST['department_id']);
    
    if ($name && $code && $department_id) {
        $stmt = $pdo->prepare("INSERT INTO courses (name, code, department_id) VALUES (?, ?, ?)");
        if ($stmt->execute([$name, $code, $department_id])) {
            $message = 'Course added successfully';
        } else {
            $error = 'Failed to add course';
        }
    } else {
        $error = 'Please fill all fields';
    }
}

// Handle delete course
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->execute([$id]);
    $message = 'Course deleted successfully';
}

// Handle edit course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_course'])) {
    $id = intval($_POST['course_id']);
    $name = trim($_POST['name']);
    $code = trim($_POST['code']);
    $department_id = intval($_POST['department_id']);
    
    $stmt = $pdo->prepare("UPDATE courses SET name = ?, code = ?, department_id = ? WHERE id = ?");
    $stmt->execute([$name, $code, $department_id, $id]);
    $message = 'Course updated successfully';
}

// Get all courses with department names
$courses = $pdo->query("
    SELECT c.*, d.name as department_name 
    FROM courses c 
    LEFT JOIN departments d ON c.department_id = d.id 
    ORDER BY d.name, c.name
")->fetchAll();

$departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();

include 'header.php';
?>

<div class="admin-card">
    <h2>Add New Course</h2>
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>Course Name</label>
            <input type="text" name="name" class="form-input" required>
        </div>
        <div class="form-group">
            <label>Course Code</label>
            <input type="text" name="code" class="form-input" placeholder="e.g., SWE 301" required>
        </div>
        <div class="form-group">
            <label>Department</label>
            <select name="department_id" class="form-select" required>
                <option value="">Select Department</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" name="add_course" class="btn-primary">Add Course</button>
    </form>
</div>

<div class="admin-card">
    <h2>All Courses</h2>
    <table class="admin-table">
        <thead>
            <tr><th>ID</th><th>Course Code</th><th>Course Name</th><th>Department</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($courses as $course): ?>
            <tr>
                <form method="POST" style="display: contents;">
                    <td><?php echo $course['id']; ?></td>
                    <td><input type="text" name="code" value="<?php echo htmlspecialchars($course['code']); ?>" class="form-input" style="width: 100px;"></td>
                    <td><input type="text" name="name" value="<?php echo htmlspecialchars($course['name']); ?>" class="form-input" style="width: 200px;"></td>
                    <td>
                        <select name="department_id" class="form-select" style="width: 150px;">
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>" <?php echo $dept['id'] == $course['department_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($dept['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                        <button type="submit" name="edit_course" class="btn-edit" style="margin-right: 8px;">Save</button>
                        <a href="?delete=<?php echo $course['id']; ?>" class="btn-danger" onclick="return confirm('Delete this course?')">Delete</a>
                    </td>
                </form>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>