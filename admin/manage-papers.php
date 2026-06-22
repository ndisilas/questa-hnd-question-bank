<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$pageTitle = 'Manage Question Papers';
$message = '';
$error = '';

// Create uploads directory if not exists
$uploadDir = '../uploads/papers/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_paper'])) {
    $title = trim($_POST['title']);
    $course_id = intval($_POST['course_id']);
    $year = $_POST['year'];
    $semester = intval($_POST['semester']);
    
    if ($_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $title) . '.pdf';
        $filePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $filePath)) {
            $stmt = $pdo->prepare("INSERT INTO question_papers (title, course_id, year, semester, file_path, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$title, $course_id, $year, $semester, $filePath, $_SESSION['user_id']])) {
                $message = 'Question paper uploaded successfully';
            } else {
                $error = 'Failed to save to database';
            }
        } else {
            $error = 'Failed to upload file';
        }
    } else {
        $error = 'Please select a PDF file';
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Get file path first
    $stmt = $pdo->prepare("SELECT file_path FROM question_papers WHERE id = ?");
    $stmt->execute([$id]);
    $paper = $stmt->fetch();
    if ($paper && file_exists($paper['file_path'])) {
        unlink($paper['file_path']);
    }
    $stmt = $pdo->prepare("DELETE FROM question_papers WHERE id = ?");
    $stmt->execute([$id]);
    $message = 'Paper deleted successfully';
}

// Get all papers
$papers = $pdo->query("
    SELECT qp.*, c.name as course_name, c.code as course_code, d.name as department_name
    FROM question_papers qp
    JOIN courses c ON qp.course_id = c.id
    JOIN departments d ON c.department_id = d.id
    ORDER BY qp.id DESC
")->fetchAll();

$courses = $pdo->query("SELECT c.*, d.name as dept_name FROM courses c JOIN departments d ON c.department_id = d.id ORDER BY d.name, c.name")->fetchAll();

include 'header.php';
?>

<div class="admin-card">
    <h2>Upload Question Paper</h2>
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Paper Title</label>
            <input type="text" name="title" class="form-input" placeholder="e.g., Database Management Systems Past Paper" required>
        </div>
        <div class="form-group">
            <label>Course</label>
            <select name="course_id" class="form-select" required>
                <option value="">Select Course</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['dept_name']); ?> - <?php echo htmlspecialchars($course['name']); ?> (<?php echo $course['code']; ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Academic Year</label>
            <select name="year" class="form-select" required>
                <option value="2025">2025</option>
                <option value="2024">2024</option>
                <option value="2023">2023</option>
                <option value="2022">2022</option>
            </select>
        </div>
        <div class="form-group">
            <label>Semester</label>
            <select name="semester" class="form-select" required>
                <option value="1">First Semester</option>
                <option value="2">Second Semester</option>
            </select>
        </div>
        <div class="form-group">
            <label>PDF File</label>
            <input type="file" name="pdf_file" accept=".pdf" class="form-input" required>
        </div>
        <button type="submit" name="upload_paper" class="btn-primary">Upload Paper</button>
    </form>
</div>

<div class="admin-card">
    <h2>All Question Papers</h2>
    <table class="admin-table">
        <thead>
            <tr><th>Title</th><th>Course</th><th>Year</th><th>Semester</th><th>Downloads</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($papers as $paper): ?>
            <tr>
                <td><?php echo htmlspecialchars($paper['title']); ?></td>
                <td><?php echo htmlspecialchars($paper['course_name']); ?></td>
                <td><?php echo $paper['year']; ?></td>
                <td><?php echo $paper['semester'] == 1 ? 'First' : 'Second'; ?></td>
                <td><?php echo $paper['downloads']; ?></td>
                <td><a href="?delete=<?php echo $paper['id']; ?>" class="btn-danger" onclick="return confirm('Delete this paper?')">Delete</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>