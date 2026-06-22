<?php
$currentUser = getCurrentUser($pdo);
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar" id="sidebar">
    <div class="sidebar-overlay-content">
        <!-- Sidebar Header with Logo -->
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <div class="sidebar-logo-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="sidebar-logo-text">
                    <h2>questa</h2>
                    <p>HIPTEX Question Bank</p>
                </div>
            </div>
        </div>

        <!-- User Profile Section -->
        <div class="sidebar-profile">
            <div class="sidebar-avatar" id="sidebarAvatar">
                <?php
                if (!empty($currentUser['avatar']) && file_exists($currentUser['avatar'])) {
                    echo '<img src="' . $currentUser['avatar'] . '" alt="Profile" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">';
                } else {
                    $initials = strtoupper(substr($currentUser['name'] ?? 'U', 0, 1));
                    echo $initials;
                }
                ?>
            </div>
            <div class="sidebar-profile-info">
                <span class="sidebar-profile-name" id="sidebarProfileName"><?php echo htmlspecialchars($currentUser['name'] ?? 'User'); ?></span>
                <span class="sidebar-profile-role" id="sidebarProfileRole"><?php echo ucfirst($currentUser['role'] ?? 'student'); ?></span>
            </div>
        </div>

        <!-- Navigation Menu -->
        <div class="nav-menu">
            <!-- 📍 Main -->
            <div class="nav-section">📍 Main</div>
            <a href="dashboard.php" class="nav-item <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Home
            </a>

            <!-- 📚 Academic -->
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

            <!-- 📰 Resources -->
            <div class="nav-section">📰 Resources</div>
            <a href="news.php" class="nav-item <?php echo $currentPage == 'news.php' ? 'active' : ''; ?>">
                <i class="fas fa-newspaper"></i> News &amp; Updates
            </a>
            <a href="library.php" class="nav-item <?php echo $currentPage == 'library.php' ? 'active' : ''; ?>">
                <i class="fas fa-book"></i> Library &amp; Resources
            </a>

            <!-- 👤 Account -->
            <div class="nav-section">👤 Account</div>
            <a href="profile.php" class="nav-item <?php echo $currentPage == 'profile.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-circle"></i> Profile
            </a>
            <a href="financial-status.php" class="nav-item <?php echo $currentPage == 'financial-status.php' ? 'active' : ''; ?>">
                <i class="fas fa-coins"></i> Financial Status
            </a>

            <!-- 🎨 Theme -->
            <div class="nav-section">🎨 Theme</div>
            <div class="theme-switcher">
                <button class="theme-btn active" data-theme="light" onclick="setTheme('light')" title="Light Theme">☀️</button>
                <button class="theme-btn" data-theme="dark" onclick="setTheme('dark')" title="Dark Theme">🌙</button>
                <button class="theme-btn" data-theme="navy" onclick="setTheme('navy')" title="Navy Theme">⚓</button>
            </div>

            <!-- Logout -->
            <div class="logout-item">
                <a href="logout.php" class="nav-item logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<style>
/* ========== SIDEBAR STYLES ========== */
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
    box-shadow: 2px 0 20px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
}

.sidebar::-webkit-scrollbar {
    width: 4px;
}
.sidebar::-webkit-scrollbar-track {
    background: transparent;
}
.sidebar::-webkit-scrollbar-thumb {
    background: rgba(245, 158, 11, 0.3);
    border-radius: 4px;
}

/* Sidebar Header */
.sidebar-header {
    padding: 24px 20px 16px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    flex-shrink: 0;
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
    flex-shrink: 0;
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
    margin: 0;
    letter-spacing: -0.5px;
}

.sidebar-logo-text p {
    font-size: 10px;
    color: rgba(255, 255, 255, 0.4);
    margin: 2px 0 0;
    letter-spacing: 0.3px;
}

/* User Profile Section */
.sidebar-profile {
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    flex-shrink: 0;
}

.sidebar-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: 700;
    color: white;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    flex-shrink: 0;
    border: 2px solid #f59e0b;
    overflow: hidden;
}

.sidebar-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.sidebar-profile-info {
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.sidebar-profile-name {
    font-size: 14px;
    font-weight: 600;
    color: white;
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sidebar-profile-role {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.4);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Navigation Menu */
.nav-menu {
    padding: 8px 16px 20px;
    flex: 1;
    overflow-y: auto;
}

.nav-section {
    font-size: 10px;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.25);
    padding: 16px 12px 8px;
    letter-spacing: 0.8px;
    font-weight: 700;
}

.nav-section:first-of-type {
    padding-top: 8px;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 10px 14px;
    margin-bottom: 2px;
    border-radius: 10px;
    color: rgba(255, 255, 255, 0.6);
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.nav-item i {
    width: 20px;
    font-size: 15px;
    text-align: center;
    color: rgba(255, 255, 255, 0.3);
    transition: color 0.2s;
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

/* Logout */
.logout-item {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.nav-item.logout {
    color: rgba(255, 100, 100, 0.6);
}

.nav-item.logout i {
    color: rgba(255, 100, 100, 0.3);
}

.nav-item.logout:hover {
    background: rgba(255, 100, 100, 0.08);
    color: #ff6b6b;
}

.nav-item.logout:hover i {
    color: #ff6b6b;
}

/* Theme Switcher */
.theme-switcher {
    display: flex;
    gap: 8px;
    padding: 4px 12px 8px;
}

.theme-btn {
    flex: 1;
    padding: 8px 4px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 30px;
    cursor: pointer;
    background: transparent;
    color: rgba(255, 255, 255, 0.4);
    font-size: 14px;
    transition: all 0.2s ease;
    text-align: center;
}

.theme-btn:hover {
    background: rgba(245, 158, 11, 0.1);
    border-color: rgba(245, 158, 11, 0.3);
}

.theme-btn.active {
    background: rgba(245, 158, 11, 0.15);
    border-color: #f59e0b;
    color: #f59e0b;
    box-shadow: 0 0 20px rgba(245, 158, 11, 0.05);
}

/* Mobile Overlay */
.sidebar-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 90;
    backdrop-filter: blur(4px);
}

.sidebar-overlay.active {
    display: block;
}

/* Mobile */
@media (max-width: 992px) {
    .sidebar {
        transform: translateX(-100%);
        width: 300px;
    }
    .sidebar.open {
        transform: translateX(0);
    }
}

@media (max-width: 480px) {
    .sidebar {
        width: 280px;
    }
    .sidebar-profile-name {
        font-size: 13px;
    }
}
</style>

<script>
// ========== SIDEBAR PROFILE SYNC ==========
function updateSidebarProfile(name, role, avatar) {
    // Update name
    const nameEl = document.getElementById('sidebarProfileName');
    if (nameEl) nameEl.textContent = name;
    
    // Update role
    const roleEl = document.getElementById('sidebarProfileRole');
    if (roleEl) roleEl.textContent = role.charAt(0).toUpperCase() + role.slice(1);
    
    // Update avatar
    const avatarEl = document.getElementById('sidebarAvatar');
    if (avatarEl) {
        if (avatar && avatar !== '') {
            avatarEl.innerHTML = '<img src="' + avatar + '" alt="Profile" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">';
        } else {
            const initials = name.split(' ').map(n => n[0]).join('').toUpperCase();
            avatarEl.innerHTML = initials || 'U';
            avatarEl.style.background = 'linear-gradient(135deg, #f59e0b, #d97706)';
        }
    }
}

// ========== LISTEN FOR PROFILE UPDATES ==========
window.addEventListener('storage', function(e) {
    if (e.key === 'questaUserProfile') {
        try {
            const profile = JSON.parse(e.newValue);
            if (profile) {
                updateSidebarProfile(profile.name, profile.role || 'student', profile.avatarImage || null);
            }
        } catch(e) {}
    }
});

// ========== LOAD SAVED PROFILE ON PAGE LOAD ==========
document.addEventListener('DOMContentLoaded', function() {
    const savedProfile = localStorage.getItem('questaUserProfile');
    if (savedProfile) {
        try {
            const profile = JSON.parse(savedProfile);
            updateSidebarProfile(profile.name, profile.role || 'student', profile.avatarImage || null);
        } catch(e) {}
    }
});

// ========== THEME SWITCHING ==========
function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('questa_theme', theme);
    
    // Update sidebar theme buttons
    document.querySelectorAll('.theme-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.theme === theme) {
            btn.classList.add('active');
        }
    });
    
    // Also update header theme buttons if they exist
    document.querySelectorAll('.header-theme-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.theme === theme) {
            btn.classList.add('active');
        }
    });
}

// ========== MOBILE SIDEBAR TOGGLE ==========
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        });
    }
    
    if (overlay) {
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        });
    }
});
</script>