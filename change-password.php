<?php
require_once 'includes/config.php';
requireLogin();

$pageTitle = 'Change Password';
$showSidebar = true;
$currentUser = getCurrentUser($pdo);
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!password_verify($current_password, $user['password'])) {
        $error = 'Current password is incorrect';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed_password, $_SESSION['user_id']]);
        $message = 'Password changed successfully!';
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <div class="card" style="max-width: 500px; margin: 0 auto;">
        <h2>Change Password</h2>
        <p style="margin-bottom: 24px;">Update your password to keep your account secure</p>
        
        <?php if ($message): ?>
            <div style="background: #d1fae5; color: #065f46; padding: 12px; border-radius: 12px; margin-bottom: 20px;"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #dc2626; padding: 12px; border-radius: 12px; margin-bottom: 20px;"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Current Password</label>
                <input type="password" name="current_password" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">New Password</label>
                <input type="password" name="new_password" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="confirm_password" class="form-input" required>
            </div>
            <button type="submit" class="btn-primary" style="width: 100%;">Update Password</button>
        </form>
        
        <div style="margin-top: 20px; text-align: center;">
            <a href="profile.php" style="color: var(--accent); text-decoration: none;">← Back to Profile</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>