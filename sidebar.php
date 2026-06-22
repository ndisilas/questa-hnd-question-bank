<?php
$currentUser = getCurrentUser($pdo);
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <div class="sidebar-logo-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="sidebar-logo-text">
                <h2>Questa</h2>
                <p>HIPTEX Question Bank</p>
            </div>
        </div>
    </div>

    <div class="sidebar-profile">
        <?php if (!empty($currentUser['avatar']) && file_exists($currentUser['avatar'])): ?>
            <img src="<?php echo $currentUser['avatar']; ?>" alt="Profile" class="sidebar-avatar">
        <?php else: ?>
            <div class="sidebar-avatar-placeholder">
                <?php echo strtoupper(substr($currentUser['name'], 0, 1)); ?>
            </div>
        <?php endif; ?>
        <div class="sidebar-profile-info">
            <span class="sidebar-profile-name"><?php echo htmlspecialchars($currentUser['name']); ?></span>
            <span class="sidebar-profile-role"><?php echo ucfirst($currentUser['role']); ?></span>
        </div>
    </div>

    <div class="nav-menu">
        <!-- Home -->
        <div class="nav-section">🏠 Home</div>
        <a href="dashboard.php" class="nav-item <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i> Dashboard
        </a>

        <!-- Academic -->
        <div class="nav-section">📚 Academic</div>
        <a href="timetable.php" class="nav-item <?php echo $currentPage == 'timetable.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i> Timetable
        </a>
        <a href="exam-dates.php" class="nav-item <?php echo $currentPage == 'exam-dates.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-check"></i> Exam Dates
        </a>
        <a href="calendar.php" class="nav-item <?php echo $currentPage == 'calendar.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-week"></i> Calendar
        </a>
        <a href="question-bank.php" class="nav-item <?php echo $currentPage == 'question-bank.php' ? 'active' : ''; ?>">
            <i class="fas fa-database"></i> Question Bank
        </a>
        <a href="exam-results.php" class="nav-item <?php echo $currentPage == 'exam-results.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i> Exam Results
        </a>

        <!-- Resources -->
        <div class="nav-section">📰 Resources</div>
        <a href="news.php" class="nav-item <?php echo $currentPage == 'news.php' ? 'active' : ''; ?>">
            <i class="fas fa-newspaper"></i> News & Updates
        </a>
        <a href="library.php" class="nav-item <?php echo $currentPage == 'library.php' ? 'active' : ''; ?>">
            <i class="fas fa-book"></i> Library & Resources
        </a>

        <!-- Account -->
        <div class="nav-section">👤 Account</div>
        <a href="profile.php" class="nav-item <?php echo $currentPage == 'profile.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-circle"></i> Profile
        </a>
        <a href="financial-status.php" class="nav-item <?php echo $currentPage == 'financial-status.php' ? 'active' : ''; ?>">
            <i class="fas fa-coins"></i> Financial Status
        </a>

        <!-- Theme Switcher -->
        <div class="nav-section">🎨 Theme</div>
        <div class="theme-switcher" style="padding: 8px 16px;">
            <button class="theme-btn" data-theme="light" onclick="setTheme('light')" title="Light Theme">☀️</button>
            <button class="theme-btn" data-theme="dark" onclick="setTheme('dark')" title="Dark Theme">🌙</button>
            <button class="theme-btn" data-theme="navy" onclick="setTheme('navy')" title="Navy Theme">⚓</button>
        </div>

        <!-- Logout -->
        <div class="logout-item">
            <a href="logout.php" class="nav-item">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</div>

<style>
/* Sidebar styles */
.sidebar {
    width: 280px;
    background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
    color: white;
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    z-index: 100;
    transition: transform 0.3s ease;
    overflow-y: auto;
    box-shadow: 2px 0 20px rgba(0,0,0,0.2);
}

.sidebar-header {
    padding: 24px 20px 16px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    margin-bottom: 16px;
}

.sidebar-logo {
    display: flex;
    align-items: center;
    gap: 12px;
}

.sidebar-logo-icon {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.sidebar-logo-icon i {
    font-size: 22px;
    color: white;
}

.sidebar-logo-text h2 {
    font-size: 22px;
    font-weight: 800;
    background: linear-gradient(135deg, #ffffff 0%, #f59e0b 50%, #d97706 100%);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.sidebar-logo-text p {
    font-size: 10px;
    color: rgba(255,255,255,0.4);
    margin-top: 2px;
}

/* Profile in sidebar */
.sidebar-profile {
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    margin-bottom: 16px;
}

.sidebar-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #f59e0b;
}

.sidebar-avatar-placeholder {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: 700;
    color: white;
    border: 2px solid #f59e0b;
}

.sidebar-profile-info {
    display: flex;
    flex-direction: column;
}

.sidebar-profile-name {
    font-size: 14px;
    font-weight: 600;
    color: white;
    line-height: 1.3;
}

.sidebar-profile-role {
    font-size: 11px;
    color: rgba(255,255,255,0.4);
}

/* Navigation */
.nav-menu {
    padding: 0 16px;
}

.nav-section {
    font-size: 10px;
    text-transform: uppercase;
    color: rgba(255,255,255,0.25);
    padding: 16px 12px 8px;
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
    color: rgba(255,255,255,0.6);
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s;
}

.nav-item i {
    width: 20px;
    font-size: 15px;
    text-align: center;
    color: rgba(255,255,255,0.3);
}

.nav-item:hover {
    background: rgba(245, 158, 11, 0.08);
    color: white;
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

.logout-item {
    margin-top: 16px;
    border-top: 1px solid rgba(255,255,255,0.05);
    padding-top: 16px;
}

.logout-item .nav-item {
    color: rgba(255,100,100,0.6);
}

.logout-item .nav-item:hover {
    color: #ff6b6b;
}

/* Theme Switcher */
.theme-switcher {
    display: flex;
    gap: 8px;
    padding: 8px 14px;
}

.theme-btn {
    padding: 6px 12px;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 30px;
    cursor: pointer;
    background: transparent;
    color: rgba(255,255,255,0.5);
    font-size: 14px;
    transition: all 0.2s;
}

.theme-btn:hover {
    background: rgba(245, 158, 11, 0.1);
    border-color: #f59e0b;
}

.theme-btn.active {
    background: rgba(245, 158, 11, 0.15);
    border-color: #f59e0b;
    color: #f59e0b;
}

/* Mobile overlay */
.sidebar-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 90;
}

@media (max-width: 992px) {
    .sidebar {
        transform: translateX(-100%);
        width: 280px;
    }
    .sidebar.open {
        transform: translateX(0);
    }
    .sidebar-overlay.active {
        display: block;
    }
}
</style>