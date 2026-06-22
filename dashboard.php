<?php
require_once 'includes/config.php';
requireLogin();

$pageTitle = 'Dashboard';
$showSidebar = true;

// Get current user
$currentUser = getCurrentUser($pdo);
// Add user ID as data attribute for JavaScript
$userId = $currentUser['id'];

// Get statistics
$paperCount = $pdo->query("SELECT COUNT(*) FROM question_papers")->fetchColumn();
$downloadCount = $pdo->query("SELECT SUM(downloads) FROM question_papers")->fetchColumn();
$programCount = $pdo->query("SELECT COUNT(*) FROM departments")->fetchColumn();

// Get recent papers
$recentPapers = $pdo->query("
    SELECT qp.*, c.name as course_name, d.name as department_name 
    FROM question_papers qp
    JOIN courses c ON qp.course_id = c.id
    JOIN departments d ON c.department_id = d.id
    ORDER BY qp.upload_date DESC LIMIT 5
")->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="welcome-text">
            <h1>Welcome back, <?php echo htmlspecialchars($currentUser['name']); ?>! 🎉</h1>
            <p>Your exam preparation hub. Access past questions, AI assistance, track progress, and more.</p>
        </div>
        <div class="welcome-stats">
            <div class="mini-stat">
                <span class="mini-number"><?php echo $paperCount ?: 0; ?></span>
                <span class="mini-label">Papers</span>
            </div>
            <div class="mini-stat">
                <span class="mini-number"><?php echo number_format($downloadCount ?: 0); ?></span>
                <span class="mini-label">Downloads</span>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #dbeafe; color: #2563eb;">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $paperCount ?: 0; ?></h3>
                <p>Total Question Papers</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #d1fae5; color: #059669;">
                <i class="fas fa-download"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo number_format($downloadCount ?: 0); ?></h3>
                <p>Total Downloads</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fef3c7; color: #d97706;">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $programCount ?: 0; ?></h3>
                <p>HND Programs</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fce7f3; color: #db2777;">
                <i class="fas fa-calendar"></i>
            </div>
            <div class="stat-info">
                <h3>2021-2025</h3>
                <p>Archive Years</p>
            </div>
        </div>
    </div>

    <!-- Quick Access -->
    <div class="section-header">
        <h2>🚀 Quick Access</h2>
    </div>
    <div class="features-grid">
        <a href="chatbot.php" class="feature-card">
            <div class="feature-icon" style="background: #fef3c7;"><i class="fas fa-robot" style="color: #d97706;"></i></div>
            <div class="feature-content">
                <h4>🤖 AI Exam Assistant</h4>
                <p>Get instant help with exam questions using AI chatbot</p>
            </div>
        </a>
        <a href="question-bank.php" class="feature-card">
            <div class="feature-icon" style="background: #e0e7ff;"><i class="fas fa-pen-fancy" style="color: #4f46e5;"></i></div>
            <div class="feature-content">
                <h4>📝 Try Before Answer</h4>
                <p>Attempt questions first, then check the solution</p>
            </div>
        </a>
        <a href="question-bank.php" class="feature-card">
            <div class="feature-icon" style="background: #d1fae5;"><i class="fas fa-lightbulb" style="color: #059669;"></i></div>
            <div class="feature-content">
                <h4>💡 Solutions & Answers</h4>
                <p>Model answers and detailed explanations</p>
            </div>
        </a>
        <a href="question-bank.php" class="feature-card">
            <div class="feature-icon" style="background: #fce7f3;"><i class="fas fa-search" style="color: #db2777;"></i></div>
            <div class="feature-content">
                <h4>🔍 Smart Search</h4>
                <p>Search by subject name, topic, or course code</p>
            </div>
        </a>
        <a href="#" class="feature-card">
            <div class="feature-icon" style="background: #ede9fe;"><i class="fas fa-chart-line" style="color: #7c3aed;"></i></div>
            <div class="feature-content">
                <h4>📊 Track Your Progress</h4>
                <p>Monitor your learning outcomes and performance</p>
            </div>
        </a>
        <a href="#" class="feature-card">
            <div class="feature-icon" style="background: #fef3c7;"><i class="fas fa-sticky-note" style="color: #b45309;"></i></div>
            <div class="feature-content">
                <h4>📚 Study Notes</h4>
                <p>Summarized notes on frequently tested topics</p>
            </div>
        </a>
        <a href="#" class="feature-card">
            <div class="feature-icon" style="background: #dbeafe;"><i class="fas fa-users" style="color: #2563eb;"></i></div>
            <div class="feature-content">
                <h4>👥 Peer Collaboration</h4>
                <p>Connect with senior students for help</p>
            </div>
        </a>
        <a href="question-bank.php" class="feature-card">
            <div class="feature-icon" style="background: #e0e7ff;"><i class="fas fa-print" style="color: #4338ca;"></i></div>
            <div class="feature-content">
                <h4>🖨️ Print & Download</h4>
                <p>Save or print past questions directly</p>
            </div>
        </a>
        <a href="exam-dates.php" class="feature-card">
            <div class="feature-icon" style="background: #fee2e2;"><i class="fas fa-calendar-check" style="color: #dc2626;"></i></div>
            <div class="feature-content">
                <h4>📅 Upcoming Exams</h4>
                <p>View exam schedule and countdown</p>
            </div>
        </a>
    </div>

    <!-- Recent Papers -->
    <div class="section-header">
        <h2>📄 Recently Added Question Papers</h2>
        <a href="question-bank.php" class="view-all-link">Browse All Papers →</a>
    </div>
    <div class="recent-papers">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Course</th>
                    <th>Program</th>
                    <th>Year</th>
                    <th>Semester</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($recentPapers) > 0): ?>
                    <?php foreach ($recentPapers as $paper): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($paper['title']); ?></td>
                            <td><?php echo htmlspecialchars($paper['department_name']); ?></td>
                            <td><?php echo $paper['year']; ?></td>
                            <td>Semester <?php echo $paper['semester']; ?></td>
                            <td>
                                <a href="<?php echo $paper['file_path']; ?>" class="download-link" target="_blank">
                                    <i class="fas fa-download"></i> Download PDF
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 30px; color: #64748b;">
                            <i class="fas fa-folder-open" style="font-size: 24px; display: block; margin-bottom: 8px;"></i>
                            No question papers available yet.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div data-user-id="<?php echo $userId; ?>" style="display: none;"></div>

<style>
/* Dashboard Styles */
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

.view-all-link {
    color: #f59e0b;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.2s;
}

.view-all-link:hover {
    text-decoration: underline;
    color: #d97706;
}

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

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
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

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 16px;
}

.feature-card {
    background: var(--bg-secondary);
    border-radius: 16px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    text-decoration: none;
    border: 1px solid var(--border-color);
    transition: all 0.2s ease;
}

.feature-card:hover {
    transform: translateY(-3px);
    border-color: #f59e0b;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
}

.feature-icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.feature-icon i {
    font-size: 24px;
}

.feature-content h4 {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 4px;
}

.feature-content p {
    font-size: 12px;
    color: var(--text-muted);
    margin: 0;
}

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

.download-link {
    background: #f59e0b;
    color: white;
    padding: 6px 14px;
    border-radius: 20px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 500;
    transition: background 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.download-link:hover {
    background: #d97706;
}

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
    .features-grid {
        grid-template-columns: 1fr;
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

<?php include 'includes/footer.php'; ?>