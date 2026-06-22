<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$pageTitle = 'Manage All Users';
$message = '';
$error = '';

// Handle user deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $userId = intval($_GET['delete']);
    if ($userId != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $message = 'User deleted successfully.';
    } else {
        $error = 'You cannot delete your own account.';
    }
}

// Handle role update
if (isset($_GET['role']) && isset($_GET['id'])) {
    $userId = intval($_GET['id']);
    $newRole = $_GET['role'];
    $allowedRoles = ['student', 'lecturer', 'admin'];
    if (in_array($newRole, $allowedRoles)) {
        if ($userId == $_SESSION['user_id'] && $newRole !== 'admin') {
            $error = 'You cannot change your own role.';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$newRole, $userId]);
            $message = 'User role updated successfully.';
        }
    }
}

// Handle password reset
if (isset($_GET['resetpass']) && is_numeric($_GET['resetpass'])) {
    $userId = intval($_GET['resetpass']);
    $defaultPassword = password_hash('password123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$defaultPassword, $userId]);
    $message = 'Password reset to default: <strong>password123</strong>';
}

// Handle profile edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $userId = intval($_POST['user_id']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $matricule = trim($_POST['matricule']);
    $department_id = intval($_POST['department_id']);
    
    $stmt = $pdo->prepare("
        UPDATE users 
        SET name = ?, email = ?, matricule = ?, department_id = ? 
        WHERE id = ?
    ");
    if ($stmt->execute([$name, $email, $matricule, $department_id, $userId])) {
        // Update session if editing self
        if ($userId == $_SESSION['user_id']) {
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
        }
        $message = 'User details updated successfully.';
    } else {
        $error = 'Failed to update user.';
    }
}

// Get all users with department names
$users = $pdo->query("
    SELECT u.*, d.name as department_name 
    FROM users u 
    LEFT JOIN departments d ON u.department_id = d.id 
    ORDER BY u.role, u.name
")->fetchAll();

$departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();

include 'header.php';
?>

<div class="admin-main-content">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
            <h1 style="font-size: 28px; font-weight: 700; margin-bottom: 4px;">
                <i class="fas fa-users-cog" style="color: #f59e0b;"></i> Manage All Users
            </h1>
            <p style="color: #64748b;">View, manage, and control user access to the Questa system.</p>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="register-student.php" class="btn-primary">
                <i class="fas fa-user-plus"></i> Register Student
            </a>
            <a href="manage-lecturers.php" class="btn-primary" style="background: #7c3aed;">
                <i class="fas fa-chalkboard-user"></i> Add Lecturer
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="admin-card">
        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Avatar</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Password</th>
                        <th>Matricule</th>
                        <th>Department</th>
                        <th>Role</th>
                        <th style="min-width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): 
                        // Check if avatar exists and file exists
                        $avatarExists = !empty($user['avatar']) && file_exists($user['avatar']);
                        $avatarPath = $avatarExists ? $user['avatar'] : '';
                        $initials = strtoupper(substr($user['name'], 0, 1));
                    ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td>
                                <?php if ($avatarExists): ?>
                                    <img src="<?php echo $avatarPath; ?>" alt="Avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #f59e0b;">
                                <?php else: ?>
                                    <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #f59e0b, #d97706); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 16px; border: 2px solid #f59e0b;">
                                        <?php echo $initials; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                    <span style="background: #fef3c7; color: #d97706; padding: 2px 8px; border-radius: 12px; font-size: 10px; margin-left: 4px;">You</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span style="font-family: monospace; font-size: 12px; background: #f8fafc; padding: 2px 8px; border-radius: 4px;">
                                    ••••••••
                                </span>
                                <a href="?resetpass=<?php echo $user['id']; ?>" style="font-size: 11px; color: #f59e0b; text-decoration: none;" onclick="return confirm('Reset password to default (password123)?')">
                                    <i class="fas fa-key"></i> Reset
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($user['matricule'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($user['department_name'] ?? '—'); ?></td>
                            <td>
                                <form method="GET" action="" style="display: inline;">
                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                    <select name="role" onchange="this.form.submit()" class="form-select" style="width: auto; padding: 4px 8px; font-size: 12px; border-radius: 8px;">
                                        <option value="student" <?php echo $user['role'] == 'student' ? 'selected' : ''; ?>>Student</option>
                                        <option value="lecturer" <?php echo $user['role'] == 'lecturer' ? 'selected' : ''; ?>>Lecturer</option>
                                        <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                </form>
                            </td>
                            <td style="white-space: nowrap; min-width: 180px;">
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <button class="btn-edit" onclick="openEditModal(
                                        <?php echo $user['id']; ?>,
                                        '<?php echo addslashes($user['name']); ?>',
                                        '<?php echo addslashes($user['email']); ?>',
                                        '<?php echo addslashes($user['matricule']); ?>',
                                        <?php echo $user['department_id'] ?? 0; ?>
                                    )">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <a href="?delete=<?php echo $user['id']; ?>" class="btn-danger" onclick="return confirm('Delete this user?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                <?php else: ?>
                                    <span style="color: #94a3b8; font-size: 12px;">Cannot modify self</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- User Statistics -->
    <div class="admin-card">
        <h3><i class="fas fa-chart-pie"></i> User Statistics</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px;">
            <div style="text-align: center; padding: 16px; background: #f8fafc; border-radius: 12px;">
                <span style="font-size: 28px; font-weight: 800; color: #2563eb;">
                    <?php echo count(array_filter($users, function($u) { return $u['role'] == 'admin'; })); ?>
                </span>
                <p style="font-size: 12px; color: #64748b; margin: 0;">Admins</p>
            </div>
            <div style="text-align: center; padding: 16px; background: #f8fafc; border-radius: 12px;">
                <span style="font-size: 28px; font-weight: 800; color: #059669;">
                    <?php echo count(array_filter($users, function($u) { return $u['role'] == 'lecturer'; })); ?>
                </span>
                <p style="font-size: 12px; color: #64748b; margin: 0;">Lecturers</p>
            </div>
            <div style="text-align: center; padding: 16px; background: #f8fafc; border-radius: 12px;">
                <span style="font-size: 28px; font-weight: 800; color: #7c3aed;">
                    <?php echo count(array_filter($users, function($u) { return $u['role'] == 'student'; })); ?>
                </span>
                <p style="font-size: 12px; color: #64748b; margin: 0;">Students</p>
            </div>
            <div style="text-align: center; padding: 16px; background: #f8fafc; border-radius: 12px;">
                <span style="font-size: 28px; font-weight: 800; color: #0f172a;">
                    <?php echo count($users); ?>
                </span>
                <p style="font-size: 12px; color: #64748b; margin: 0;">Total Users</p>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3>Edit User</h3>
            <button class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" id="edit_name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="edit_email" class="form-input" required>
                </div>
                <div class="form-group">
                    <label>Matriculation Number</label>
                    <input type="text" name="matricule" id="edit_matricule" class="form-input">
                </div>
                <div class="form-group">
                    <label>Department</label>
                    <select name="department_id" id="edit_department" class="form-select">
                        <option value="0">None</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>">
                                <?php echo htmlspecialchars($dept['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="edit_user" class="btn-primary" style="width: 100%; justify-content: center;">
                    <i class="fas fa-save"></i> Update User
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}
.modal-overlay.active {
    display: flex;
}
.modal-container {
    background: white;
    border-radius: 20px;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}
.modal-header {
    padding: 20px 24px;
    background: #f59e0b;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
}
.modal-header h3 {
    font-size: 18px;
    font-weight: 600;
    margin: 0;
}
.modal-close {
    background: none;
    border: none;
    color: white;
    font-size: 28px;
    cursor: pointer;
}
.modal-body {
    padding: 24px;
}
.btn-edit, .btn-danger {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-edit {
    background: #3b82f6;
    color: white;
}
.btn-edit:hover {
    background: #2563eb;
}
.btn-danger {
    background: #ef4444;
    color: white;
}
.btn-danger:hover {
    background: #dc2626;
}
</style>

<script>
function openEditModal(id, name, email, matricule, department) {
    document.getElementById('edit_user_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_matricule').value = matricule || '';
    document.getElementById('edit_department').value = department || 0;
    document.getElementById('editModal').classList.add('active');
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('active');
}

document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});
</script>

<?php include 'footer.php'; ?>