<?php
// Get current user data
$currentUser = getCurrentUser($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' · Questa' : 'Questa · HIPTEX Question Bank'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">

   
    <style>
        /* ========== TOP HEADER / NAVBAR ========== */
        .top-header {
            background: var(--bg-secondary);
            padding: 12px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 50;
            border-bottom: 1px solid var(--border-color);
            height: 64px;
        }

        .top-header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 22px;
            color: var(--text-primary);
            cursor: pointer;
            padding: 6px 10px;
            border-radius: 10px;
            transition: background 0.2s;
        }

        .menu-toggle:hover {
            background: var(--bg-primary);
        }

        .top-header-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .top-header-title span {
            color: #f59e0b;
        }

        .top-header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        /* Theme Switcher in Header */
        .header-theme-switcher {
            display: flex;
            gap: 4px;
            background: var(--bg-primary);
            padding: 4px;
            border-radius: 30px;
            border: 1px solid var(--border-color);
        }

        .header-theme-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-size: 13px;
            background: transparent;
            color: var(--text-muted);
            transition: all 0.2s;
        }

        .header-theme-btn.active {
            background: #f59e0b;
            color: white;
        }

        .header-theme-btn:hover {
            background: rgba(245, 158, 11, 0.1);
        }

        /* User Profile in Header */
        .header-user {
            display: flex;
            align-items: center;
            gap: 14px;
            cursor: pointer;
            padding: 4px 12px 4px 4px;
            border-radius: 40px;
            transition: background 0.2s;
            text-decoration: none;
            border: 1px solid transparent;
        }

        .header-user:hover {
            background: var(--bg-primary);
            border-color: var(--border-color);
        }

        .header-user-info {
            text-align: right;
            line-height: 1.3;
        }

        .header-user-name {
            font-weight: 600;
            font-size: 14px;
            color: var(--text-primary);
        }

        .header-user-role {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .header-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 16px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            flex-shrink: 0;
            background-size: cover;
            background-position: center;
            border: 2px solid #f59e0b;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .top-header {
                padding: 10px 20px;
            }
            .menu-toggle {
                display: block;
            }
            .header-theme-switcher {
                display: none;
            }
            .header-user-info {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .top-header-title {
                font-size: 15px;
            }
            .top-header {
                padding: 8px 16px;
                height: 56px;
            }
            .header-avatar {
                width: 32px;
                height: 32px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>

<!-- TOP HEADER -->
<div class="top-header">
    <div class="top-header-left">
        <button class="menu-toggle" id="menuToggle" aria-label="Toggle sidebar">
            <i class="fas fa-bars"></i>
        </button>
        <div class="top-header-title">
            Questa <span>HIPTEX</span>
        </div>
    </div>

    <div class="top-header-right">
        <!-- Theme Switcher (Desktop) -->
        <div class="header-theme-switcher">
            <button class="header-theme-btn" data-theme="light" onclick="setTheme('light')" title="Light Theme">☀️</button>
            <button class="header-theme-btn" data-theme="dark" onclick="setTheme('dark')" title="Dark Theme">🌙</button>
            <button class="header-theme-btn" data-theme="navy" onclick="setTheme('navy')" title="Navy Theme">⚓</button>
        </div>

        <!-- User Profile -->
        <a href="profile.php" class="header-user">
            <div class="header-user-info">
                <div class="header-user-name" id="headerUserName">
                    <?php echo htmlspecialchars($currentUser['name'] ?? 'User'); ?>
                </div>
                <div class="header-user-role" id="headerUserRole">
                    <?php echo ucfirst($currentUser['role'] ?? 'student'); ?>
                </div>
            </div>
            <div class="header-avatar" id="headerAvatar">
                <?php
                if (!empty($currentUser['avatar']) && file_exists($currentUser['avatar'])) {
                    echo '<img src="' . $currentUser['avatar'] . '" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">';
                } else {
                    $initials = strtoupper(substr($currentUser['name'] ?? 'U', 0, 1));
                    echo $initials;
                }
                ?>
            </div>
        </a>
    </div>
</div>

<script>
    // ========== PROFILE SYNC ==========
    // This function updates the header when profile changes
    function updateHeaderProfile(name, role, avatar) {
        // Update name
        const nameEl = document.getElementById('headerUserName');
        if (nameEl) nameEl.textContent = name;
        
        // Update role
        const roleEl = document.getElementById('headerUserRole');
        if (roleEl) roleEl.textContent = role.charAt(0).toUpperCase() + role.slice(1);
        
        // Update avatar
        const avatarEl = document.getElementById('headerAvatar');
        if (avatarEl) {
            if (avatar && avatar !== '') {
                avatarEl.style.backgroundImage = 'url(' + avatar + ')';
                avatarEl.style.backgroundSize = 'cover';
                avatarEl.style.backgroundPosition = 'center';
                avatarEl.textContent = '';
            } else {
                avatarEl.style.backgroundImage = '';
                const initials = name.split(' ').map(n => n[0]).join('').toUpperCase();
                avatarEl.textContent = initials || 'U';
            }
        }
    }

    // ========== THEME SWITCHING ==========
    function setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('questa_theme', theme);
        
        // Update header theme buttons
        document.querySelectorAll('.header-theme-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.theme === theme) {
                btn.classList.add('active');
            }
        });
        
        // Also update sidebar theme buttons if they exist
        document.querySelectorAll('.theme-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.theme === theme) {
                btn.classList.add('active');
            }
        });
    }

    // ========== LOAD SAVED THEME ==========
    document.addEventListener('DOMContentLoaded', function() {
        const savedTheme = localStorage.getItem('questa_theme');
        if (savedTheme) {
            setTheme(savedTheme);
        } else {
            setTheme('light');
        }
        
        // Load saved profile from localStorage (if profile page updated it)
        const savedProfile = localStorage.getItem('questaUserProfile');
        if (savedProfile) {
            try {
                const profile = JSON.parse(savedProfile);
                updateHeaderProfile(profile.name, profile.role || 'student', profile.avatarImage || null);
            } catch(e) {}
        }
    });

    // ========== LISTEN FOR STORAGE CHANGES (profile updates from other tabs) ==========
    window.addEventListener('storage', function(e) {
        if (e.key === 'questaUserProfile') {
            try {
                const profile = JSON.parse(e.newValue);
                if (profile) {
                    updateHeaderProfile(profile.name, profile.role || 'student', profile.avatarImage || null);
                }
            } catch(e) {}
        }
    });

    // ========== MOBILE MENU TOGGLE ==========
    document.getElementById('menuToggle')?.addEventListener('click', function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        if (sidebar) {
            sidebar.classList.toggle('open');
            if (overlay) overlay.classList.toggle('active');
        }
    });
</script>