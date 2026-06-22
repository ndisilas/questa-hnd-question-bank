<?php
// Check if user is logged in and is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title><?php echo $pageTitle ?? 'Admin Panel'; ?> · Questa Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            min-height: 100vh;
        }
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }
        /* Sidebar styles are in sidebar.php */
        .admin-main {
            flex: 1;
            padding: 24px 32px;
            background: #f8fafc;
            overflow-x: auto;
        }
        .admin-topbar {
            background: white;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .admin-topbar .admin-user {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .admin-topbar .admin-user span {
            font-weight: 500;
            color: #1e293b;
        }
        .admin-topbar .admin-user a {
            color: #94a3b8;
            text-decoration: none;
            transition: color 0.2s;
            font-size: 14px;
        }
        .admin-topbar .admin-user a:hover {
            color: #dc2626;
        }
        .admin-topbar .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #1e293b;
        }
        .admin-welcome {
            margin-bottom: 32px;
        }
        .admin-welcome h1 {
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 8px;
        }
        .admin-welcome p {
            color: #64748b;
        }
        .admin-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            border: 1px solid #e2e8f0;
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .stat-info h3 {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }
        .stat-info p {
            font-size: 12px;
            color: #64748b;
            margin: 0;
        }
        .admin-section {
            margin-bottom: 32px;
        }
        .admin-section h2 {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 16px;
        }
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 12px;
        }
        .action-btn {
            padding: 14px 20px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .action-btn.primary { background: #2563eb; color: white; }
        .action-btn.success { background: #059669; color: white; }
        .action-btn.warning { background: #d97706; color: white; }
        .action-btn.info { background: #0891b2; color: white; }
        .action-btn.secondary { background: #64748b; color: white; }
        .action-btn.danger { background: #dc2626; color: white; }
        .action-btn.purple { background: #7c3aed; color: white; }
        .action-btn.dark { background: #1e293b; color: white; }

        .admin-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid #e2e8f0;
            margin-bottom: 24px;
            overflow-x: auto;
        }
        .admin-card h2 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 16px;
        }
        .admin-card h3 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 12px;
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .admin-table th {
            text-align: left;
            padding: 12px 8px;
            background: #f8fafc;
            font-weight: 600;
            color: #1e293b;
            border-bottom: 2px solid #e2e8f0;
        }
        .admin-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }
        .admin-table tr:hover td {
            background: #f8fafc;
        }
        .btn-primary {
            background: #f59e0b;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 30px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-primary:hover {
            background: #d97706;
        }
        .btn-danger {
            background: #ef4444;
            color: white;
            padding: 6px 14px;
            border: none;
            border-radius: 20px;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-danger:hover {
            background: #dc2626;
        }
        .btn-edit {
            background: #3b82f6;
            color: white;
            padding: 6px 14px;
            border: none;
            border-radius: 20px;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-edit:hover {
            background: #2563eb;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 4px;
        }
        .form-input, .form-select {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            background: white;
        }
        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: #f59e0b;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .alert-error {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-badge.paid {
            background: #d1fae5;
            color: #065f46;
        }
        .status-badge.owing {
            background: #fee2e2;
            color: #dc2626;
        }
        .status-badge.active {
            background: #d1fae5;
            color: #065f46;
        }
        .status-badge.inactive {
            background: #fee2e2;
            color: #dc2626;
        }

        @media (max-width: 992px) {
            .admin-topbar .menu-toggle {
                display: block;
            }
            .quick-actions {
                grid-template-columns: 1fr 1fr;
            }
        }
        @media (max-width: 768px) {
            .admin-main {
                padding: 16px;
            }
            .admin-stats-grid {
                grid-template-columns: 1fr 1fr;
            }
            .quick-actions {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 480px) {
            .admin-stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="admin-wrapper">
    <!-- Sidebar is included here -->
    <?php include 'sidebar.php'; ?>

    <div class="admin-main">
        <!-- Top Bar -->
        <div class="admin-topbar">
            <button class="menu-toggle" onclick="toggleAdminSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <div class="admin-user">
                <span><i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></span>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>