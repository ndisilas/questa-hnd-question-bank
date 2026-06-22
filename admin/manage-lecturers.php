<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$pageTitle = 'Manage Lecturers';
$message = '';
$error = '';

// Handle lecturer registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_lecturer'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $department_id = intval($_POST['department_id']);
    $password = password_hash('lecturer123', PASSWORD_DEFAULT);
    
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        $error = 'Email already exists';
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, department_id) VALUES (?, ?, ?, 'lecturer', ?)");
        if ($stmt->execute([$name, $email, $password, $department_id])) {
            $message = 'Lecturer registered successfully. Default password is: lecturer123';
        } else {
            $error = 'Failed to register lecturer';
        }
    }
}

// Handle deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'lecturer'");
    $stmt->execute([$id]);
    $message = 'Lecturer deleted successfully';
}

$lecturers = $pdo->query("
    SELECT u.*, d.name as department_name 
    FROM users u 
    LEFT JOIN departments d ON u.department_id = d.id 
    WHERE u.role = 'lecturer' 
    ORDER BY u.id DESC
")->fetchAll();

$departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();

include 'header.php';
?>

<div class="admin-card">
    <h2>Register New Lecturer</h2>
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
            <label>Department</label>
            <select name="department_id" class="form-select" required>
                <option value="">Select Department</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" name="register_lecturer" class="btn-primary">Register Lecturer</button>
    </form>
</div>

<div class="admin-card">
    <h2>All Lecturers</h2>
    <table class="admin-table">
        <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Department</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach ($lecturers as $lecturer): ?>
            <tr>
                <td><?php echo $lecturer['id']; ?></td>
                <td><?php echo htmlspecialchars($lecturer['name']); ?></td>
                <td><?php echo htmlspecialchars($lecturer['email']); ?></td>
                <td><?php echo htmlspecialchars($lecturer['department_name']); ?></td>
                <td><a href="?delete=<?php echo $lecturer['id']; ?>" class="btn-danger" onclick="return confirm('Delete this lecturer?')">Delete</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>