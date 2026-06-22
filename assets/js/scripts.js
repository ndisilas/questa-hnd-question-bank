// ========================================
// QUESTA MASTER JAVASCRIPT
// Theme switching, profile sync, mobile menu
// ========================================

// ---------- THEME SWITCHING ----------
function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('questa_theme', theme);
    
    // Update active button styling
    document.querySelectorAll('.theme-btn').forEach(btn => {
        if (btn.dataset.theme === theme) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
}

// Load saved theme on page load
function loadSavedTheme() {
    const savedTheme = localStorage.getItem('questa_theme');
    if (savedTheme) {
        setTheme(savedTheme);
    } else {
        setTheme('light');
    }
}

// ---------- MOBILE SIDEBAR TOGGLE ----------
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (sidebar) {
        sidebar.classList.toggle('open');
        if (overlay) overlay.classList.toggle('active');
    }
}

function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (sidebar) sidebar.classList.remove('open');
    if (overlay) overlay.classList.remove('active');
}

// ---------- PROFILE PICTURE UPDATE (Sync across pages) ----------
function updateAvatar(avatarData, userName) {
    // Update header avatar
    const headerAvatar = document.getElementById('userAvatar');
    if (headerAvatar) {
        if (avatarData && avatarData !== '') {
            headerAvatar.style.backgroundImage = `url(${avatarData})`;
            headerAvatar.style.backgroundSize = 'cover';
            headerAvatar.style.backgroundPosition = 'center';
            headerAvatar.textContent = '';
        } else {
            headerAvatar.style.backgroundImage = '';
            const initials = userName ? userName.split(' ').map(n => n[0]).join('') : 'U';
            headerAvatar.textContent = initials;
        }
    }
    
    // Update welcome message
    const welcomeName = document.getElementById('welcomeName');
    if (welcomeName && userName) {
        welcomeName.textContent = userName.split(' ')[0];
    }
}

// Listen for profile updates from other tabs
window.addEventListener('storage', function(e) {
    if (e.key === 'questa_user') {
        const userData = JSON.parse(e.newValue);
        if (userData) {
            updateAvatar(userData.avatar, userData.name);
        }
    }
});

// ---------- NOTIFICATION PERMISSION ----------
function enableNotifications() {
    if ('Notification' in window) {
        Notification.requestPermission().then(permission => {
            if (permission === 'granted') {
                alert('Notifications enabled!');
            }
        });
    } else {
        alert('Your browser does not support notifications');
    }
}

// ---------- INITIALIZE ON PAGE LOAD ----------
document.addEventListener('DOMContentLoaded', function() {
    loadSavedTheme();
    
    // Mobile menu toggle
    const menuToggle = document.getElementById('menuToggle');
    if (menuToggle) {
        menuToggle.addEventListener('click', toggleSidebar);
    }
    
    const overlay = document.getElementById('sidebarOverlay');
    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }
    
    // Close sidebar when clicking nav item on mobile
    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                closeSidebar();
            }
        });
    });
});