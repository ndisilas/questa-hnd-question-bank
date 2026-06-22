<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$pageTitle = 'Manage Students';
$message = '';
$error = '';

// Handle student registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_student'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $matricule = trim($_POST['matricule']);
    $department_id = intval($_POST['department_id']);
    $password = password_hash('password123', PASSWORD_DEFAULT); // Default password
    
    // Check if email exists
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        $error = 'Email already exists';
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, matricule, department_id) VALUES (?, ?, ?, 'student', ?, ?)");
        if ($stmt->execute([$name, $email, $password, $matricule, $department_id])) {
            $message = 'Student registered successfully. Default password is: password123';
        } else {
            $error = 'Failed to register student';
        }
    }
}

// Handle student deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'student'");
    $stmt->execute([$id]);
    $message = 'Student deleted successfully';
}

// Get all students
$students = $pdo->query("
    SELECT u.*, d.name as department_name 
    FROM users u 
    LEFT JOIN departments d ON u.department_id = d.id 
    WHERE u.role = 'student' 
    ORDER BY u.id DESC
")->fetchAll();

// Get departments for dropdown
$departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();

include 'header.php';
?>

<div class="admin-card">
    <h2>Register New Student</h2>
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" class="form-input" required>
        </div>
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" class="form-input" required>
        </div>
        <div class="form-group">
            <label>Matriculation Number</label>
            <input type="text" name="matricule" class="form-input" placeholder="e.g., HIPTEX/SWE/001" required>
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
        <button type="submit" name="register_student" class="btn-primary">Register Student</button>
    </form>
</div>

<div class="admin-card">
    <h2>All Students</h2>
    <table class="admin-table">
        <thead>
            <tr><th>ID</th><th>Name</th><th>Email</th><th>Matricule</th><th>Department</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($students as $student): ?>
            <tr>
                <td><?php echo $student['id']; ?></td>
                <td><?php echo htmlspecialchars($student['name']); ?></td>
                <td><?php echo htmlspecialchars($student['email']); ?></td>
                <td><?php echo htmlspecialchars($student['matricule']); ?></td>
                <td><?php echo htmlspecialchars($student['department_name']); ?></td>
                <td>
                    <a href="?delete=<?php echo $student['id']; ?>" class="btn-danger" onclick="return confirm('Delete this student?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>