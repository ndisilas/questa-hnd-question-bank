<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$pageTitle = 'Send Notifications';
$message = '';
$error = '';

// Create notifications table if it doesn't exist
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS notifications (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT,
            title VARCHAR(255),
            message TEXT,
            type VARCHAR(50),
            sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
} catch (PDOException $e) {
    // Table might already exist
}

// ===== THE FUNCTION IS ONLY DEFINED HERE, NOT IN config.php =====
function sendNotification($pdo, $userId, $title, $message, $type = 'general') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, title, message, type, sent_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([$userId, $title, $message, $type]);
    } catch (PDOException $e) {
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notification_type = $_POST['notification_type'] ?? 'general';
    $notification_title = trim($_POST['title'] ?? '');
    $notification_message = trim($_POST['message'] ?? '');
    $recipient_type = $_POST['recipient_type'] ?? 'all';
    $department_id = isset($_POST['department_id']) ? intval($_POST['department_id']) : 0;

    if (empty($notification_title) || empty($notification_message)) {
        $error = 'Please enter both a title and a message.';
    } else {
        // Get recipients
        if ($recipient_type === 'all') {
            $recipients = $pdo->query("SELECT id, email FROM users WHERE role = 'student'")->fetchAll();
        } elseif ($recipient_type === 'department' && $department_id > 0) {
            $stmt = $pdo->prepare("SELECT id, email FROM users WHERE role = 'student' AND department_id = ?");
            $stmt->execute([$department_id]);
            $recipients = $stmt->fetchAll();
        } elseif ($recipient_type === 'lecturers') {
            $recipients = $pdo->query("SELECT id, email FROM users WHERE role = 'lecturer'")->fetchAll();
        } else {
            $recipients = $pdo->query("SELECT id, email FROM users WHERE role = 'student'")->fetchAll();
        }

        if (count($recipients) > 0) {
            $sentCount = 0;
            foreach ($recipients as $recipient) {
                if (sendNotification($pdo, $recipient['id'], $notification_title, $notification_message, $notification_type)) {
                    $sentCount++;
                }
            }
            $message = 'Notification sent successfully to ' . $sentCount . ' users.';
        } else {
            $error = 'No recipients found.';
        }
    }
}

$departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();

include 'header.php';
?>

<div class="admin-main-content">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
            <h1 style="font-size: 28px; font-weight: 700; margin-bottom: 4px;">
                <i class="fas fa-bell" style="color: #f59e0b;"></i> Send Notifications
            </h1>
            <p style="color: #64748b;">Send email and in-app notifications to students, lecturers, or all users.</p>
        </div>
        <a href="dashboard.php" class="btn-primary" style="background: #64748b; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
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
                <label>Notification Type</label>
                <select name="notification_type" class="form-select" required>
                    <option value="exam">📅 Exam Reminder</option>
                    <option value="deadline">⏰ Deadline Reminder</option>
                    <option value="fee">💰 Fee Payment Reminder</option>
                    <option value="general">📢 General Announcement</option>
                    <option value="upload">📄 New Paper Uploaded</option>
                </select>
            </div>

            <div class="form-group">
                <label>Notification Title</label>
                <input type="text" name="title" class="form-input" placeholder="e.g., Exam Reminder: First Semester Exams" required>
            </div>

            <div class="form-group">
                <label>Recipient Type</label>
                <select name="recipient_type" class="form-select" id="recipientType" required>
                    <option value="all">All Students</option>
                    <option value="department">Students by Department</option>
                    <option value="lecturers">All Lecturers</option>
                </select>
            </div>

            <div class="form-group" id="departmentSelect" style="display: none;">
                <label>Department</label>
                <select name="department_id" class="form-select">
                    <option value="0">Select Department</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>">
                            <?php echo htmlspecialchars($dept['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Message</label>
                <textarea name="message" class="form-input" rows="6" required
                    placeholder="Type your notification message here..."></textarea>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="send_email" checked> 
                    Send via Email
                </label>
                <br>
                <label>
                    <input type="checkbox" name="send_sms"> 
                    Send via SMS (coming soon)
                </label>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 14px;">
                <i class="fas fa-paper-plane"></i> Send Notification
            </button>
        </form>
    </div>

    <div class="admin-card" style="margin-top: 24px;">
        <h3><i class="fas fa-history"></i> Recent Notifications</h3>
        <?php
        $recent = $pdo->query("
            SELECT n.*, u.name as user_name 
            FROM notifications n 
            LEFT JOIN users u ON n.user_id = u.id 
            ORDER BY n.sent_at DESC LIMIT 10
        ")->fetchAll();
        ?>
        <?php if (count($recent) > 0): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>Sent At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent as $notif): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($notif['title']); ?></td>
                            <td><?php echo htmlspecialchars($notif['user_name'] ?? 'All Users'); ?></td>
                            <td><span class="status-badge" style="font-size: 10px;"><?php echo ucfirst($notif['type']); ?></span></td>
                            <td><?php echo date('M d, Y H:i', strtotime($notif['sent_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color: var(--text-muted);">No notifications sent yet.</p>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('recipientType').addEventListener('change', function() {
    document.getElementById('departmentSelect').style.display = this.value === 'department' ? 'block' : 'none';
});
</script>

<?php include 'footer.php'; ?>