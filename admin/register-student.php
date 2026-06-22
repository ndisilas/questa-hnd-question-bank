<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$pageTitle = 'Register Student';
$message = '';
$error = '';

// Get departments for dropdown
$departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();

// Handle student registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_student'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $matricule = trim($_POST['matricule']);
    $department_id = intval($_POST['department_id']);
    $password = password_hash('password123', PASSWORD_DEFAULT);
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($matricule) || empty($department_id)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if email already exists
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = 'Email already exists. Please use a different email.';
        } else {
            // Check if matricule already exists
            $checkMat = $pdo->prepare("SELECT id FROM users WHERE matricule = ?");
            $checkMat->execute([$matricule]);
            if ($checkMat->fetch()) {
                $error = 'Matriculation number already exists. Please use a different one.';
            } else {
                // Insert student
                $stmt = $pdo->prepare("
                    INSERT INTO users (name, email, password, role, matricule, department_id) 
                    VALUES (?, ?, ?, 'student', ?, ?)
                ");
                if ($stmt->execute([$name, $email, $password, $matricule, $department_id])) {
                    $message = 'Student registered successfully! Default password is: <strong>password123</strong>';
                    
                    // Optionally create financial record for the student
                    $studentId = $pdo->lastInsertId();
                    $stmt = $pdo->prepare("
                        INSERT INTO financial_records (student_id, total_fees, paid, balance, status) 
                        VALUES (?, 350000, 0, 350000, 'owing')
                    ");
                    $stmt->execute([$studentId]);
                } else {
                    $error = 'Failed to register student. Please try again.';
                }
            }
        }
    }
}
?>

<?php include 'header.php'; ?>

<div class="admin-main-content">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
            <h1 style="font-size: 28px; font-weight: 700; margin-bottom: 4px;">
                <i class="fas fa-user-plus" style="color: #f59e0b;"></i> Register New Student
            </h1>
            <p style="color: #64748b;">Create a new student account in the Questa system.</p>
        </div>
        <a href="manage-students.php" class="btn-primary" style="background: #64748b;">
            <i class="fas fa-arrow-left"></i> Back to Students
        </a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="admin-card" style="max-width: 700px; margin: 0 auto;">
        <form method="POST">
            <div class="form-group">
                <label>Full Name <span style="color: #dc2626;">*</span></label>
                <input type="text" name="name" class="form-input" placeholder="e.g., John Doe" required>
            </div>

            <div class="form-group">
                <label>Email Address <span style="color: #dc2626;">*</span></label>
                <input type="email" name="email" class="form-input" placeholder="e.g., student@questa.com" required>
            </div>

            <div class="form-group">
                <label>Matriculation Number <span style="color: #dc2626;">*</span></label>
                <input type="text" name="matricule" class="form-input" placeholder="e.g., HIPTEX/SWE/001" required>
                <small style="color: #94a3b8; font-size: 12px;">This must be unique for each student.</small>
            </div>

            <div class="form-group">
                <label>Department <span style="color: #dc2626;">*</span></label>
                <select name="department_id" class="form-select" required>
                    <option value="">Select Department</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>">
                            <?php echo htmlspecialchars($dept['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="background: #fef3c7; padding: 16px; border-radius: 12px; margin-bottom: 20px; border: 1px solid #fde68a;">
                <p style="margin: 0; font-size: 14px; color: #92400e;">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Default Password:</strong> <code style="background: #fde68a; padding: 2px 8px; border-radius: 4px;">password123</code>
                    <br>
                    <span style="font-size: 12px;">The student will be able to change their password after their first login.</span>
                </p>
            </div>

            <button type="submit" name="register_student" class="btn-primary" style="width: 100%; justify-content: center; padding: 14px;">
                <i class="fas fa-user-plus"></i> Register Student
            </button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>