<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<div class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-header">
        <h2><i class="fas fa-crown"></i> Questa</h2>
        <p>Admin Panel</p>
    </div>

    <div class="admin-sidebar-menu">
        <!-- Dashboard -->
        <a href="dashboard.php" class="nav-item <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
        </a>

        <!-- User Management -->
        <div class="nav-section">👥 Users</div>
        <a href="manage-students.php" class="nav-item <?php echo $currentPage == 'manage-students.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-graduate"></i> <span>Students</span>
        </a>
        <a href="manage-lecturers.php" class="nav-item <?php echo $currentPage == 'manage-lecturers.php' ? 'active' : ''; ?>">
            <i class="fas fa-chalkboard-user"></i> <span>Lecturers</span>
        </a>
        <a href="manage-users.php" class="nav-item <?php echo $currentPage == 'manage-users.php' ? 'active' : ''; ?>">
            <i class="fas fa-users-cog"></i> <span>All Users</span>
        </a>

        <!-- Academic -->
        <div class="nav-section">📚 Academic</div>
        <a href="manage-courses.php" class="nav-item <?php echo $currentPage == 'manage-courses.php' ? 'active' : ''; ?>">
            <i class="fas fa-book"></i> <span>Courses</span>
        </a>
        <a href="manage-papers.php" class="nav-item <?php echo $currentPage == 'manage-papers.php' ? 'active' : ''; ?>">
            <i class="fas fa-file-pdf"></i> <span>Question Papers</span>
        </a>
        <a href="manage-results.php" class="nav-item <?php echo $currentPage == 'manage-results.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i> <span>Results</span>
        </a>

        <!-- Scheduling -->
        <div class="nav-section">📅 Schedule</div>
        <a href="manage-timetable.php" class="nav-item <?php echo $currentPage == 'manage-timetable.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i> <span>Timetable</span>
        </a>
        <a href="manage-exams.php" class="nav-item <?php echo $currentPage == 'manage-exams.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-check"></i> <span>Exam Dates</span>
        </a>
        <a href="manage-calendar.php" class="nav-item <?php echo $currentPage == 'manage-calendar.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-week"></i> <span>Calendar</span>
        </a>

        <!-- Content -->
        <div class="nav-section">📰 Content</div>
        <a href="manage-news.php" class="nav-item <?php echo $currentPage == 'manage-news.php' ? 'active' : ''; ?>">
            <i class="fas fa-newspaper"></i> <span>News</span>
        </a>
        <a href="manage-library.php" class="nav-item <?php echo $currentPage == 'manage-library.php' ? 'active' : ''; ?>">
            <i class="fas fa-book"></i> <span>Library</span>
        </a>

        <!-- Financial -->
        <div class="nav-section">💰 Finance</div>
        <a href="financial-status.php" class="nav-item <?php echo $currentPage == 'financial-status.php' ? 'active' : ''; ?>">
            <i class="fas fa-coins"></i> <span>Student Fees</span>
        </a>

        <!-- Communications -->
        <div class="nav-section">📢 Communications</div>
        <a href="send-notifications.php" class="nav-item <?php echo $currentPage == 'send-notifications.php' ? 'active' : ''; ?>">
            <i class="fas fa-bell"></i> <span>Notifications</span>
        </a>

        <!-- Registration -->
        <div class="nav-section">📝 Registration</div>
        <a href="register-student.php" class="nav-item <?php echo $currentPage == 'register-student.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-plus"></i> <span>Register Student</span>
        </a>

        <!-- Logout -->
        <div class="nav-section"></div>
        <a href="../logout.php" class="nav-item logout">
            <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
        </a>
    </div>
</div>

<style>
.admin-sidebar {
    width: 270px;
    background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
    min-height: 100vh;
    padding: 20px 0;
    position: sticky;
    top: 0;
    overflow-y: auto;
    border-right: 1px solid #334155;
    flex-shrink: 0;
    transition: transform 0.3s ease;
}

.admin-sidebar-header {
    padding: 0 20px 20px 20px;
    border-bottom: 1px solid #334155;
    margin-bottom: 20px;
}

.admin-sidebar-header h2 {
    font-size: 22px;
    font-weight: 800;
    color: #f59e0b;
    margin: 0;
}

.admin-sidebar-header h2 i {
    margin-right: 10px;
}

.admin-sidebar-header p {
    font-size: 12px;
    color: #94a3b8;
    margin: 4px 0 0 0;
}

.admin-sidebar-menu {
    padding: 0 12px;
}

.nav-section {
    font-size: 10px;
    text-transform: uppercase;
    color: #64748b;
    padding: 16px 12px 8px 12px;
    letter-spacing: 0.8px;
    font-weight: 700;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 10px 14px;
    margin-bottom: 2px;
    border-radius: 10px;
    color: #94a3b8;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s;
}

.nav-item i {
    width: 20px;
    font-size: 15px;
    text-align: center;
    color: #64748b;
}

.nav-item:hover {
    background: rgba(245, 158, 11, 0.08);
    color: #f1f5f9;
}

.nav-item:hover i {
    color: #f59e0b;
}

.nav-item.active {
    background: rgba(245, 158, 11, 0.12);
    color: #f59e0b;
    border-left: 3px solid #f59e0b;
}

.nav-item.active i {
    color: #f59e0b;
}

.nav-item.logout {
    color: #f87171;
    margin-top: 12px;
    border-top: 1px solid #334155;
    padding-top: 16px;
}

.nav-item.logout:hover {
    background: rgba(248, 113, 113, 0.08);
    color: #fca5a5;
}

@media (max-width: 992px) {
    .admin-sidebar {
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        z-index: 1000;
        transform: translateX(-100%);
        width: 280px;
    }
    .admin-sidebar.open {
        transform: translateX(0);
    }
    .admin-sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 999;
    }
    .admin-sidebar-overlay.active {
        display: block;
    }
}
</style>

<!-- Overlay for mobile -->
<div class="admin-sidebar-overlay" id="adminSidebarOverlay" onclick="toggleAdminSidebar()"></div>

<script>
function toggleAdminSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('adminSidebarOverlay');
    sidebar.classList.toggle('open');
    overlay.classList.toggle('active');
}
</script>