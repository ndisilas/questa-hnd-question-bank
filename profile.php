<?php
require_once 'includes/config.php';
requireLogin();

$pageTitle = 'My Profile';
$showSidebar = true;

$currentUser = getCurrentUser($pdo);
$message = '';
$error = '';

// Get department name
$deptName = '';
if ($currentUser['department_id']) {
    $deptStmt = $pdo->prepare("SELECT name FROM departments WHERE id = ?");
    $deptStmt->execute([$currentUser['department_id']]);
    $dept = $deptStmt->fetch();
    $deptName = $dept ? $dept['name'] : 'Not assigned';
}

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $uploadDir = 'uploads/avatars/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    
    $fileExt = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
    $fileName = time() . '_' . $_SESSION['user_id'] . '.' . $fileExt;
    $uploadPath = $uploadDir . $fileName;
    
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array(strtolower($fileExt), $allowedTypes)) {
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath)) {
            $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
            $stmt->execute([$uploadPath, $_SESSION['user_id']]);
            $_SESSION['user_avatar'] = $uploadPath;
            $message = 'Profile picture updated successfully!';
            $currentUser = getCurrentUser($pdo);
            
            // Update localStorage for real-time sync
            updateLocalStorage($currentUser);
        } else {
            $error = 'Failed to upload image';
        }
    } else {
        $error = 'Only JPG, PNG, GIF, WEBP files are allowed';
    }
}

// Handle name update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_name'])) {
    $newName = trim($_POST['name']);
    if (!empty($newName) && strlen($newName) >= 3) {
        $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmt->execute([$newName, $_SESSION['user_id']]);
        $_SESSION['user_name'] = $newName;
        $message = 'Name updated successfully!';
        $currentUser = getCurrentUser($pdo);
        updateLocalStorage($currentUser);
    } else {
        $error = 'Name must be at least 3 characters';
    }
}

// Function to update localStorage for real-time sync
function updateLocalStorage($user) {
    $profileData = [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
        'avatarInitials' => implode('', array_map(function($word) { return $word[0]; }, explode(' ', $user['name']))),
        'avatarImage' => $user['avatar'] ?? null,
        'matricule' => $user['matricule']
    ];
    echo '<script>
        localStorage.setItem("questaUserProfile", ' . json_encode($profileData) . ');
        localStorage.setItem("questaUser", ' . json_encode([
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'avatarInitials' => $profileData['avatarInitials'],
            'avatarImage' => $user['avatar'] ?? null,
            'role' => $user['role']
        ]) . ');
        // Dispatch storage event for other tabs
        window.dispatchEvent(new StorageEvent("storage", {
            key: "questaUserProfile",
            newValue: ' . json_encode(json_encode($profileData)) . '
        }));
    </script>';
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <div class="profile-container">
        <div class="profile-header-section">
            <h1><i class="fas fa-user-circle" style="color: #f59e0b;"></i> My Profile</h1>
            <p>Manage your personal information and account settings</p>
        </div>

        <?php if ($message): ?>
            <div class="alert success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="profile-grid">
            <!-- Profile Picture Card -->
            <div class="profile-card">
                <h3><i class="fas fa-camera" style="color: #f59e0b;"></i> Profile Picture</h3>
                <div class="profile-picture-section">
                    <div class="profile-avatar" id="profileAvatar">
                        <?php if (!empty($currentUser['avatar']) && file_exists($currentUser['avatar'])): ?>
                            <img src="<?php echo $currentUser['avatar']; ?>" alt="Profile Picture" id="profileAvatarImg">
                        <?php else: ?>
                            <span id="profileAvatarText"><?php echo strtoupper(substr($currentUser['name'], 0, 1)); ?></span>
                        <?php endif; ?>
                    </div>
                    <form method="POST" enctype="multipart/form-data" class="upload-form" id="avatarForm">
                        <label class="upload-btn">
                            <i class="fas fa-camera"></i> Change Picture
                            <input type="file" name="avatar" accept="image/*" onchange="document.getElementById('avatarForm').submit()">
                        </label>
                        <p class="upload-hint">JPG, PNG, GIF, WEBP. Max 2MB</p>
                    </form>
                </div>
            </div>

            <!-- Personal Information Card -->
            <div class="profile-card">
                <h3><i class="fas fa-user" style="color: #f59e0b;"></i> Personal Information</h3>
                <form method="POST" class="profile-form">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($currentUser['name']); ?>" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" value="<?php echo htmlspecialchars($currentUser['email']); ?>" class="form-input" readonly style="background: var(--bg-primary);">
                    </div>
                    <button type="submit" name="update_name" class="btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>

            <!-- Account Information Card -->
            <div class="profile-card">
                <h3><i class="fas fa-id-card" style="color: #f59e0b;"></i> Account Information</h3>
                <div class="info-row">
                    <span class="info-label">Matriculation No.</span>
                    <span class="info-value"><?php echo htmlspecialchars($currentUser['matricule'] ?? 'Not assigned'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Department</span>
                    <span class="info-value"><?php echo htmlspecialchars($deptName); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Role</span>
                    <span class="info-value"><?php echo ucfirst($currentUser['role']); ?></span>
                </div>
            </div>

            <!-- Security Card -->
            <div class="profile-card">
                <h3><i class="fas fa-lock" style="color: #f59e0b;"></i> Security</h3>
                <p class="security-note">
                    <i class="fas fa-shield-alt"></i> 
                    For security reasons, your password cannot be viewed or changed here.
                </p>
                <a href="change-password.php" class="btn-secondary">
                    <i class="fas fa-key"></i> Change Password
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.profile-container {
    max-width: 1000px;
    margin: 0 auto;
}

.profile-header-section {
    margin-bottom: 32px;
}

.profile-header-section h1 {
    font-size: 28px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 8px;
}

.profile-header-section p {
    color: var(--text-muted);
}

.profile-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 24px;
}

.profile-card {
    background: var(--bg-secondary);
    border-radius: 20px;
    padding: 24px;
    border: 1px solid var(--border-color);
}

.profile-card h3 {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 20px;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Profile Picture Section */
.profile-picture-section {
    text-align: center;
}

.profile-avatar {
    width: 140px;
    height: 140px;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    overflow: hidden;
    border: 3px solid #f59e0b;
    box-shadow: 0 8px 25px rgba(245, 158, 11, 0.2);
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-avatar span {
    font-size: 56px;
    font-weight: 700;
    color: white;
}

.upload-form {
    margin-bottom: 8px;
}

.upload-btn {
    background: #f59e0b;
    color: white;
    padding: 10px 20px;
    border-radius: 30px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 500;
    transition: background 0.2s;
}

.upload-btn:hover {
    background: #d97706;
}

.upload-btn input {
    display: none;
}

.upload-hint {
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 8px;
}

/* Profile Form */
.profile-form .form-group {
    margin-bottom: 20px;
}

.profile-form label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 6px;
    color: var(--text-primary);
}

.form-input {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid var(--border-color);
    border-radius: 12px;
    background: var(--bg-primary);
    color: var(--text-primary);
    font-size: 14px;
}

.form-input:focus {
    outline: none;
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
}

.btn-primary {
    background: #f59e0b;
    color: white;
    padding: 10px 24px;
    border: none;
    border-radius: 30px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary:hover {
    background: #d97706;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
}

/* Info Rows */
.info-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid var(--border-color);
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 500;
    color: var(--text-muted);
}

.info-value {
    font-weight: 600;
    color: var(--text-primary);
}

.security-note {
    font-size: 13px;
    color: var(--text-muted);
    margin-bottom: 20px;
    padding: 12px;
    background: var(--bg-primary);
    border-radius: 12px;
}

.btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: transparent;
    border: 1px solid var(--border-color);
    color: var(--text-primary);
    padding: 10px 20px;
    border-radius: 30px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s;
}

.btn-secondary:hover {
    border-color: #f59e0b;
    color: #f59e0b;
}

/* Alerts */
.alert {
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 24px;
    font-size: 14px;
}

.alert.success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.alert.error {
    background: #fee2e2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

/* Responsive */
@media (max-width: 768px) {
    .profile-grid {
        grid-template-columns: 1fr;
    }
    .info-row {
        flex-direction: column;
        gap: 4px;
    }
    .profile-avatar {
        width: 100px;
        height: 100px;
    }
    .profile-avatar span {
        font-size: 40px;
    }
}
</style>

<script>
// ========== PROFILE SYNC - Update localStorage when profile changes ==========
function updateProfileSync(name, avatar) {
    // Update header
    const headerAvatar = document.querySelector('.header-avatar');
    const headerName = document.querySelector('.header-user-name');
    if (headerAvatar) {
        if (avatar && avatar !== '') {
            headerAvatar.style.backgroundImage = 'url(' + avatar + ')';
            headerAvatar.style.backgroundSize = 'cover';
            headerAvatar.style.backgroundPosition = 'center';
            headerAvatar.textContent = '';
        } else {
            headerAvatar.style.backgroundImage = '';
            const initials = name.split(' ').map(n => n[0]).join('').toUpperCase();
            headerAvatar.textContent = initials || 'U';
        }
    }
    if (headerName) headerName.textContent = name;
    
    // Update sidebar
    const sidebarAvatar = document.querySelector('.sidebar-avatar');
    const sidebarName = document.querySelector('.sidebar-profile-name');
    if (sidebarAvatar) {
        if (avatar && avatar !== '') {
            sidebarAvatar.innerHTML = '<img src="' + avatar + '" alt="Profile" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">';
        } else {
            const initials = name.split(' ').map(n => n[0]).join('').toUpperCase();
            sidebarAvatar.innerHTML = initials || 'U';
            sidebarAvatar.style.background = 'linear-gradient(135deg, #f59e0b, #d97706)';
        }
    }
    if (sidebarName) sidebarName.textContent = name;
}

// ========== LISTEN FOR STORAGE CHANGES (cross-tab sync) ==========
window.addEventListener('storage', function(e) {
    if (e.key === 'questaUserProfile') {
        try {
            const profile = JSON.parse(e.newValue);
            if (profile) {
                updateProfileSync(profile.name, profile.avatarImage);
                // Update profile page elements
                const avatarImg = document.getElementById('profileAvatarImg');
                const avatarText = document.getElementById('profileAvatarText');
                if (profile.avatarImage && avatarImg) {
                    avatarImg.src = profile.avatarImage;
                    avatarImg.style.display = 'block';
                    if (avatarText) avatarText.style.display = 'none';
                } else if (avatarText) {
                    avatarText.style.display = 'block';
                    avatarText.textContent = profile.avatarInitials || profile.name.charAt(0).toUpperCase();
                }
            }
        } catch(e) {}
    }
});
</script>

<?php include 'includes/footer.php'; ?>