<?php
session_start();
require_once 'config.php';

// Return JSON
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$userId = $_SESSION['user_id'];

// Get unread notifications
$stmt = $pdo->prepare("
    SELECT * FROM notifications 
    WHERE user_id = ? AND is_read = 0
    ORDER BY sent_at DESC
");
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll();

// Mark them as read
if (count($notifications) > 0) {
    $ids = array_column($notifications, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $update = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id IN ($placeholders)");
    $update->execute($ids);
}

echo json_encode([
    'success' => true,
    'count' => count($notifications),
    'notifications' => $notifications
]);