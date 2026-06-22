<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$pageTitle = 'Manage Calendar Events';
$message = '';
$error = '';

// Handle add event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $event_date = $_POST['event_date'];
    $event_type = $_POST['event_type'];
    
    $stmt = $pdo->prepare("INSERT INTO calendar_events (title, description, event_date, event_type) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$title, $description, $event_date, $event_type])) {
        $message = 'Event added successfully';
    } else {
        $error = 'Failed to add event';
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM calendar_events WHERE id = ?");
    $stmt->execute([$id]);
    $message = 'Event deleted successfully';
}

$events = $pdo->query("SELECT * FROM calendar_events ORDER BY event_date DESC")->fetchAll();
$eventTypes = ['exam', 'holiday', 'academic', 'registration', 'deadline'];

include 'header.php';
?>

<div class="admin-card">
    <h2>Add Calendar Event</h2>
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>Event Title</label>
            <input type="text" name="title" class="form-input" required>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-input" rows="3"></textarea>
        </div>
        <div class="form-group">
            <label>Event Date</label>
            <input type="date" name="event_date" class="form-input" required>
        </div>
        <div class="form-group">
            <label>Event Type</label>
            <select name="event_type" class="form-select" required>
                <?php foreach ($eventTypes as $type): ?>
                    <option value="<?php echo $type; ?>"><?php echo ucfirst($type); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" name="add_event" class="btn-primary">Add Event</button>
    </form>
</div>

<div class="admin-card">
    <h2>All Calendar Events</h2>
    <table class="admin-table">
        <thead>
            <tr><th>Title</th><th>Date</th><th>Type</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($events as $event): ?>
            <tr>
                <td><?php echo htmlspecialchars($event['title']); ?></td>
                <td><?php echo date('M d, Y', strtotime($event['event_date'])); ?></td>
                <td><?php echo ucfirst($event['event_type']); ?></td>
                <td><a href="?delete=<?php echo $event['id']; ?>" class="btn-danger" onclick="return confirm('Delete this event?')">Delete</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>