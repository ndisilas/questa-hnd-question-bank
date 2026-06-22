<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$pageTitle = 'Admin Dashboard';

// Get counts
$studentCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$lecturerCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'lecturer'")->fetchColumn();
$courseCount = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$paperCount = $pdo->query("SELECT COUNT(*) FROM question_papers")->fetchColumn();
$newsCount = $pdo->query("SELECT COUNT(*) FROM news")->fetchColumn();

// Get total downloads
$downloads = $pdo->query("SELECT SUM(downloads) FROM question_papers")->fetchColumn();

include 'header.php';
?>

<div class="admin-main-content">
    <div class="admin-welcome">
        <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! 👋</h1>
        <p>Here's what's happening with Questa today.</p>
    </div>

    <!-- Stats Cards -->
    <div class="admin-stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #dbeafe; color: #2563eb;">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $studentCount; ?></h3>
                <p>Students</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #d1fae5; color: #059669;">
                <i class="fas fa-chalkboard-user"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $lecturerCount; ?></h3>
                <p>Lecturers</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fef3c7; color: #d97706;">
                <i class="fas fa-book"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $courseCount; ?></h3>
                <p>Courses</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fce7f3; color: #db2777;">
                <i class="fas fa-file-pdf"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $paperCount; ?></h3>
                <p>Question Papers</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #e0e7ff; color: #4f46e5;">
                <i class="fas fa-newspaper"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $newsCount; ?></h3>
                <p>News Articles</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #d1fae5; color: #10b981;">
                <i class="fas fa-download"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo number_format($downloads ?: 0); ?></h3>
                <p>Total Downloads</p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="admin-section">
        <h2>Quick Actions</h2>
        <div class="quick-actions">
            <a href="register-student.php" class="action-btn primary">
                <i class="fas fa-user-plus"></i> Register Student
            </a>
            <a href="manage-papers.php" class="action-btn success">
                <i class="fas fa-upload"></i> Upload Paper
            </a>
            <a href="manage-news.php" class="action-btn warning">
                <i class="fas fa-plus"></i> Post News
            </a>
            <a href="send-notifications.php" class="action-btn info">
                <i class="fas fa-bell"></i> Send Notification
            </a>
            <a href="manage-timetable.php" class="action-btn secondary">
                <i class="fas fa-calendar-alt"></i> Add Timetable
            </a>
            <a href="manage-exams.php" class="action-btn danger">
                <i class="fas fa-calendar-check"></i> Add Exam Date
            </a>
            <a href="manage-results.php" class="action-btn purple">
                <i class="fas fa-chart-line"></i> Upload Results
            </a>
            <a href="financial-status.php" class="action-btn dark">
                <i class="fas fa-coins"></i> Student Fees
            </a>
        </div>
    </div>
</div>

<style>
.admin-main-content {
    padding: 0;
}
</style>

<?php include 'footer.php'; ?>