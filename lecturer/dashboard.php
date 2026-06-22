<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'lecturer') {
    header('Location: ../login.php');
    exit();
}

$pageTitle = 'Lecturer Dashboard';
$showSidebar = true;

$currentUser = getCurrentUser($pdo);
$userId = $currentUser['id'];

// Get statistics
$uploadStmt = $pdo->prepare("SELECT COUNT(*) FROM question_papers WHERE uploaded_by = ?");
$uploadStmt->execute([$userId]);
$totalUploads = $uploadStmt->fetchColumn();

$downloadStmt = $pdo->prepare("SELECT SUM(downloads) FROM question_papers WHERE uploaded_by = ?");
$downloadStmt->execute([$userId]);
$totalDownloads = $downloadStmt->fetchColumn() ?: 0;

// Get department name
$deptStmt = $pdo->prepare("
    SELECT d.name FROM departments d 
    JOIN users u ON u.department_id = d.id 
    WHERE u.id = ?
");
$deptStmt->execute([$userId]);
$deptName = $deptStmt->fetchColumn() ?: 'Not Assigned';

// Get recent uploads
$recentStmt = $pdo->prepare("
    SELECT qp.*, c.name as course_name, c.code as course_code 
    FROM question_papers qp
    JOIN courses c ON qp.course_id = c.id
    WHERE qp.uploaded_by = ? 
    ORDER BY qp.upload_date DESC LIMIT 10
");
$recentStmt->execute([$userId]);
$recentPapers = $recentStmt->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="welcome-text">
            <h1>Welcome, <?php echo htmlspecialchars($currentUser['name']); ?> 👨‍🏫</h1>
            <p>Manage your uploaded question papers and track their performance.</p>
        </div>
        <div class="welcome-stats">
            <div class="mini-stat">
                <span class="mini-number"><?php echo $totalUploads; ?></span>
                <span class="mini-label">Papers</span>
            </div>
            <div class="mini-stat">
                <span class="mini-number"><?php echo number_format($totalDownloads); ?></span>
                <span class="mini-label">Downloads</span>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #dbeafe; color: #2563eb;">
                <i class="fas fa-file-pdf"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $totalUploads; ?></h3>
                <p>Total Papers Uploaded</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #d1fae5; color: #059669;">
                <i class="fas fa-download"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo number_format($totalDownloads); ?></h3>
                <p>Total Downloads</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fef3c7; color: #d97706;">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo htmlspecialchars($deptName); ?></h3>
                <p>Department</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fce7f3; color: #db2777;">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo count($recentPapers); ?></h3>
                <p>Recent Uploads</p>
            </div>
        </div>
    </div>

    <!-- Quick Action Button -->
    <div style="margin: 24px 0 32px;">
        <a href="upload-papers.php" class="upload-btn">
            <i class="fas fa-upload"></i> Upload New Paper
        </a>
    </div>

    <!-- Recent Uploads Section -->
    <div class="section-header">
        <h2>📄 My Recent Uploads</h2>
        <span class="paper-count"><?php echo count($recentPapers); ?> papers</span>
    </div>

    <?php if (count($recentPapers) > 0): ?>
        <div class="recent-papers">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Semester</th>
                        <th>Downloads</th>
                        <th>Upload Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentPapers as $paper): ?>
                        <tr>
                            <td>
                                <span class="paper-title"><?php echo htmlspecialchars($paper['title']); ?></span>
                            </td>
                            <td>
                                <span class="course-code"><?php echo htmlspecialchars($paper['course_code']); ?></span>
                            </td>
                            <td><?php echo $paper['year']; ?></td>
                            <td>Semester <?php echo $paper['semester']; ?></td>
                            <td>
                                <span class="download-count">
                                    <i class="fas fa-download"></i> <?php echo $paper['downloads']; ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($paper['upload_date'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-folder-open"></i>
            <h3>No papers uploaded yet</h3>
            <p>Click "Upload New Paper" to get started sharing your question papers with students.</p>
            <a href="upload-papers.php" class="empty-btn">
                <i class="fas fa-upload"></i> Upload Your First Paper
            </a>
        </div>
    <?php endif; ?>
</div>

<style>
/* Lecturer Dashboard Styles */
.welcome-banner {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    border-radius: 20px;
    padding: 28px 32px;
    margin-bottom: 32px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
    border: 1px solid #334155;
    position: relative;
    overflow: hidden;
}

.welcome-banner::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(245, 158, 11, 0.05) 0%, transparent 70%);
    border-radius: 50%;
}

.welcome-text h1 {
    font-size: 28px;
    font-weight: 700;
    color: white;
    margin-bottom: 8px;
}

.welcome-text p {
    color: #94a3b8;
    font-size: 15px;
}

.welcome-stats {
    display: flex;
    gap: 24px;
}

.mini-stat {
    text-align: center;
    background: rgba(255,255,255,0.05);
    padding: 8px 20px;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.05);
}

.mini-number {
    display: block;
    font-size: 24px;
    font-weight: 800;
    color: #f59e0b;
}

.mini-label {
    font-size: 11px;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 16px;
}

.stat-card {
    background: var(--bg-secondary);
    border-radius: 16px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    border: 1px solid var(--border-color);
}

.stat-icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.stat-info h3 {
    font-size: 24px;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
}

.stat-info p {
    font-size: 13px;
    color: var(--text-muted);
    margin: 0;
}

/* Upload Button */
.upload-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
    padding: 14px 32px;
    border-radius: 40px;
    font-size: 16px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 20px rgba(245, 158, 11, 0.3);
}

.upload-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(245, 158, 11, 0.4);
    color: white;
}

/* Section Header */
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 32px 0 20px;
    flex-wrap: wrap;
    gap: 12px;
}

.section-header h2 {
    font-size: 22px;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
}

.paper-count {
    font-size: 13px;
    color: var(--text-muted);
    background: var(--bg-secondary);
    padding: 4px 14px;
    border-radius: 30px;
    border: 1px solid var(--border-color);
}

/* Recent Papers */
.recent-papers {
    background: var(--bg-secondary);
    border-radius: 16px;
    padding: 24px;
    border: 1px solid var(--border-color);
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.data-table th {
    text-align: left;
    padding: 12px 8px;
    color: var(--text-muted);
    font-weight: 600;
    font-size: 12px;
    border-bottom: 1px solid var(--border-color);
}

.data-table td {
    padding: 14px 8px;
    color: var(--text-primary);
    border-bottom: 1px solid var(--border-color);
}

.paper-title {
    font-weight: 500;
}

.course-code {
    background: var(--bg-primary);
    padding: 2px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    color: var(--text-muted);
}

.download-count {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #f59e0b;
    font-weight: 600;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: var(--bg-secondary);
    border-radius: 16px;
    border: 1px solid var(--border-color);
}

.empty-state i {
    font-size: 56px;
    color: var(--text-muted);
    margin-bottom: 16px;
    opacity: 0.5;
}

.empty-state h3 {
    font-size: 20px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 8px;
}

.empty-state p {
    color: var(--text-muted);
    margin-bottom: 24px;
}

.empty-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
    padding: 12px 28px;
    border-radius: 40px;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.empty-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3);
    color: white;
}

/* Responsive */
@media (max-width: 768px) {
    .welcome-banner {
        padding: 20px;
        flex-direction: column;
        text-align: center;
    }
    .welcome-stats {
        width: 100%;
        justify-content: center;
    }
    .welcome-text h1 {
        font-size: 22px;
    }
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .section-header {
        flex-direction: column;
        align-items: flex-start;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../includes/footer.php'; ?>