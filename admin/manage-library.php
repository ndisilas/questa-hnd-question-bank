<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$pageTitle = 'Manage Library';
$message = '';
$error = '';

// Handle add resource
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_resource'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $type = $_POST['type'];
    $url = trim($_POST['url']);
    $author = trim($_POST['author']);
    
    $stmt = $pdo->prepare("INSERT INTO library_resources (title, description, type, url, author) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$title, $description, $type, $url, $author])) {
        $message = 'Resource added successfully';
    } else {
        $error = 'Failed to add resource';
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM library_resources WHERE id = ?");
    $stmt->execute([$id]);
    $message = 'Resource deleted successfully';
}

$resources = $pdo->query("SELECT * FROM library_resources ORDER BY type, title")->fetchAll();
$resourceTypes = ['ebook', 'article', 'video', 'guide', 'database'];

include 'header.php';
?>

<div class="admin-card">
    <h2>Add Library Resource</h2>
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>Resource Title</label>
            <input type="text" name="title" class="form-input" required>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-input" rows="3" required></textarea>
        </div>
        <div class="form-group">
            <label>Type</label>
            <select name="type" class="form-select" required>
                <?php foreach ($resourceTypes as $type): ?>
                    <option value="<?php echo $type; ?>"><?php echo ucfirst($type); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>URL / Link</label>
            <input type="url" name="url" class="form-input" placeholder="https://..." required>
        </div>
        <div class="form-group">
            <label>Author / Source</label>
            <input type="text" name="author" class="form-input">
        </div>
        <button type="submit" name="add_resource" class="btn-primary">Add Resource</button>
    </form>
</div>

<div class="admin-card">
    <h2>All Library Resources</h2>
    <table class="admin-table">
        <thead>
            <tr><th>Title</th><th>Type</th><th>Author</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($resources as $resource): ?>
            <tr>
                <td><?php echo htmlspecialchars($resource['title']); ?></td>
                <td><?php echo ucfirst($resource['type']); ?></td>
                <td><?php echo htmlspecialchars($resource['author']); ?></td>
                <td><a href="?delete=<?php echo $resource['id']; ?>" class="btn-danger" onclick="return confirm('Delete this resource?')">Delete</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>