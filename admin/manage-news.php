<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$pageTitle = 'Manage News';
$message = '';
$error = '';

// Handle add news
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_news'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $category = $_POST['category'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    $author_name = trim($_POST['author_name']);
    $excerpt = substr($content, 0, 200);
    
    $stmt = $pdo->prepare("INSERT INTO news (title, content, excerpt, category, featured, author_name) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$title, $content, $excerpt, $category, $featured, $author_name])) {
        $message = 'News posted successfully';
    } else {
        $error = 'Failed to post news';
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
    $stmt->execute([$id]);
    $message = 'News deleted successfully';
}

// Get all news
$news = $pdo->query("SELECT * FROM news ORDER BY featured DESC, created_at DESC")->fetchAll();
$categories = ['academic', 'event', 'announcement', 'deadline', 'achievement'];

include 'header.php';
?>

<div class="admin-card">
    <h2>Post New News</h2>
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" class="form-input" required>
        </div>
        <div class="form-group">
            <label>Category</label>
            <select name="category" class="form-select" required>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat; ?>"><?php echo ucfirst($cat); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Author Name</label>
            <input type="text" name="author_name" class="form-input" placeholder="e.g., Academic Board" required>
        </div>
        <div class="form-group">
            <label>Content</label>
            <textarea name="content" class="form-input" rows="6" required></textarea>
        </div>
        <div class="form-group">
            <label>
                <input type="checkbox" name="featured" value="1"> Feature this news (show on banner)
            </label>
        </div>
        <button type="submit" name="add_news" class="btn-primary">Post News</button>
    </form>
</div>

<div class="admin-card">
    <h2>All News</h2>
    <table class="admin-table">
        <thead>
            <tr><th>Title</th><th>Category</th><th>Featured</th><th>Date</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($news as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['title']); ?></td>
                <td><?php echo ucfirst($item['category']); ?></td>
                <td><?php echo $item['featured'] ? '★ Featured' : '-'; ?></td>
                <td><?php echo date('M d, Y', strtotime($item['created_at'])); ?></td>
                <td><a href="?delete=<?php echo $item['id']; ?>" class="btn-danger" onclick="return confirm('Delete this news?')">Delete</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>