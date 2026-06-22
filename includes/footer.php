<script src="assets/js/scripts.js"></script>
<?php if (isset($extraScripts)) echo $extraScripts; ?>
<script>
    // ========== REAL-TIME NOTIFICATIONS ==========

// ========== REAL-TIME NOTIFICATIONS ==========
function checkNotifications() {
    fetch('includes/check-notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.count > 0) {
                data.notifications.forEach(notification => {
                    showNotificationPopup(notification.title, notification.message);
                });
            }
        })
        .catch(error => console.log('Notification check error:', error));
}

function showNotificationPopup(title, message) {
    // Browser notification
    if (Notification.permission === 'granted') {
        new Notification('📢 ' + title, {
            body: message,
            icon: 'https://via.placeholder.com/64'
        });
    }
    
    // In-app toast
    showToast(title, message);
}

function showToast(title, message) {
    // Remove existing toast
    const existing = document.getElementById('questaToast');
    if (existing) existing.remove();
    
    const toast = document.createElement('div');
    toast.id = 'questaToast';
    toast.innerHTML = `
        <div style="display: flex; align-items: flex-start; gap: 12px;">
            <div style="background: #f59e0b; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-bell" style="color: white;"></i>
            </div>
            <div style="flex: 1;">
                <strong style="font-size: 14px; display: block;">${escapeHtml(title)}</strong>
                <p style="font-size: 13px; margin: 4px 0 0; color: #94a3b8;">${escapeHtml(message)}</p>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: #64748b; cursor: pointer; font-size: 18px;">×</button>
        </div>
    `;
    toast.style.cssText = `
        position: fixed;
        bottom: 24px;
        right: 24px;
        max-width: 400px;
        width: 100%;
        background: #1e293b;
        color: white;
        padding: 16px 20px;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        border-left: 4px solid #f59e0b;
        z-index: 9999;
        animation: slideUp 0.3s ease;
        font-family: 'Inter', sans-serif;
    `;
    
    // Add animation styles if not exists
    if (!document.getElementById('toastStyles')) {
        const style = document.createElement('style');
        style.id = 'toastStyles';
        style.textContent = `
            @keyframes slideUp {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            @keyframes slideDown {
                from { opacity: 1; transform: translateY(0); }
                to { opacity: 0; transform: translateY(20px); }
            }
        `;
        document.head.appendChild(style);
    }
    
    document.body.appendChild(toast);
    
    // Auto remove after 8 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.style.animation = 'slideDown 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }
    }, 8000);
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ========== START CHECKING FOR NOTIFICATIONS ==========
// Check every 5 seconds
<?php if (isset($_SESSION['user_id'])): ?>
    // Check immediately
    setTimeout(checkNotifications, 2000);
    // Then every 5 seconds
    setInterval(checkNotifications, 5000);
<?php endif; ?>

// ========== REQUEST NOTIFICATION PERMISSION ==========
// Add a small button to enable notifications (if not already granted)
document.addEventListener('DOMContentLoaded', function() {
    if ('Notification' in window && Notification.permission === 'default') {
        // Show a prompt to enable notifications
        const enableBtn = document.querySelector('.enable-notif-btn');
        if (enableBtn) {
            enableBtn.style.display = 'inline-flex';
        }
    }
});

function requestNotificationPermission() {
    if ('Notification' in window) {
        Notification.requestPermission().then(permission => {
            if (permission === 'granted') {
                showToast('✅ Notifications Enabled', 'You will now receive real-time updates.');
            }
        });
    }
}

</script>
</body>
</html>