<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'lecturer') {
    header('Location: ../login.php');
    exit();
}

$pageTitle = 'Upload Papers';
$showSidebar = true;
$currentUser = getCurrentUser($pdo);
$message = '';
$error = '';

// Create upload directory if not exists
$uploadDir = '../uploads/papers/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_paper'])) {
    $title = trim($_POST['title']);
    $course_id = intval($_POST['course_id']);
    $year = $_POST['year'];
    $semester = intval($_POST['semester']);
    $has_answer = isset($_POST['has_answer']) ? 1 : 0;
    $tags = trim($_POST['tags']);

    if ($_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
        $fileExt = pathinfo($_FILES['pdf_file']['name'], PATHINFO_EXTENSION);
        if (strtolower($fileExt) !== 'pdf') {
            $error = 'Only PDF files are allowed.';
        } else {
            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $title) . '.pdf';
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $filePath)) {
                $stmt = $pdo->prepare("
                    INSERT INTO question_papers 
                    (title, course_id, year, semester, file_path, uploaded_by, tags, has_answer) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                if ($stmt->execute([$title, $course_id, $year, $semester, $filePath, $_SESSION['user_id'], $tags, $has_answer])) {
                    $message = 'Question paper uploaded successfully!';
                } else {
                    $error = 'Failed to save to database.';
                }
            } else {
                $error = 'Failed to upload file.';
            }
        }
    } else {
        $error = 'Please select a PDF file.';
    }
}

// Get courses for dropdown (only those in lecturer's department)
$courseStmt = $pdo->prepare("
    SELECT c.* FROM courses c 
    WHERE c.department_id = (SELECT department_id FROM users WHERE id = ?)
    ORDER BY c.name
");
$courseStmt->execute([$_SESSION['user_id']]);
$courses = $courseStmt->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="upload-container">
        <h1><i class="fas fa-upload"></i> Upload Question Paper</h1>
        <p>Upload a new past question paper for students to access.</p>

        <?php if ($message): ?>
            <div class="alert success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="upload-card">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Paper Title</label>
                    <input type="text" name="title" class="form-input" placeholder="e.g., Database Management Systems Past Paper" required>
                </div>

                <div class="form-group">
                    <label>Course</label>
                    <select name="course_id" class="form-input" required>
                        <option value="">Select Course</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course['id']; ?>">
                                <?php echo htmlspecialchars($course['name']); ?> (<?php echo $course['code']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex:1;">
                        <label>Academic Year</label>
                        <select name="year" class="form-input" required>
                            <option value="2025">2025</option>
                            <option value="2024">2024</option>
                            <option value="2023">2023</option>
                            <option value="2022">2022</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label>Semester</label>
                        <select name="semester" class="form-input" required>
                            <option value="1">First Semester</option>
                            <option value="2">Second Semester</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Tags / Keywords</label>
                    <input type="text" name="tags" class="form-input" placeholder="e.g., SQL, Normalization, Transactions">
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="has_answer" value="1"> 
                        This paper has a solution/answer key
                    </label>
                </div>

                <div class="form-group">
                    <label>PDF File</label>
                    <input type="file" name="pdf_file" accept=".pdf" class="form-input" required>
                </div>

                <button type="submit" name="upload_paper" class="btn-primary">
                    <i class="fas fa-upload"></i> Upload Paper
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.upload-container {
    max-width: 700px;
    margin: 0 auto;
}
.upload-card {
    background: var(--bg-secondary);
    border-radius: 20px;
    padding: 32px;
    border: 1px solid var(--border-color);
}
.form-row {
    display: flex;
    gap: 20px;
}
.alert {
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 20px;
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
@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
    }
}
</style>

<?php include '../includes/footer.php'; ?>