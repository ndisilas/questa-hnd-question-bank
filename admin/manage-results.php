<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$pageTitle = 'Manage Results';
$message = '';
$error = '';

// Handle add result
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_result'])) {
    $student_id = intval($_POST['student_id']);
    $course_code = trim($_POST['course_code']);
    $course_name = trim($_POST['course_name']);
    $credit = intval($_POST['credit']);
    $ca_score = intval($_POST['ca_score']);
    $exam_score = intval($_POST['exam_score']);
    $total_score = $ca_score + $exam_score;
    
    if ($total_score >= 70) $grade = 'A';
    elseif ($total_score >= 60) $grade = 'B';
    elseif ($total_score >= 50) $grade = 'C';
    elseif ($total_score >= 45) $grade = 'D';
    else $grade = 'F';
    
    $semester = intval($_POST['semester']);
    $academic_year = trim($_POST['academic_year']);
    
    $check = $pdo->prepare("SELECT id FROM exam_results WHERE student_id = ? AND course_code = ? AND semester = ? AND academic_year = ?");
    $check->execute([$student_id, $course_code, $semester, $academic_year]);
    
    if ($check->fetch()) {
        $error = 'Result for this course already exists for this student in this semester';
    } else {
        $stmt = $pdo->prepare("INSERT INTO exam_results (student_id, course_code, course_name, credit, ca_score, exam_score, total_score, grade, semester, academic_year) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$student_id, $course_code, $course_name, $credit, $ca_score, $exam_score, $total_score, $grade, $semester, $academic_year])) {
            $message = 'Result added successfully';
        } else {
            $error = 'Failed to add result';
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM exam_results WHERE id = ?");
    $stmt->execute([$id]);
    $message = 'Result deleted successfully';
}

// Handle edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_result'])) {
    $id = intval($_POST['result_id']);
    $ca_score = intval($_POST['ca_score']);
    $exam_score = intval($_POST['exam_score']);
    $total_score = $ca_score + $exam_score;
    
    if ($total_score >= 70) $grade = 'A';
    elseif ($total_score >= 60) $grade = 'B';
    elseif ($total_score >= 50) $grade = 'C';
    elseif ($total_score >= 45) $grade = 'D';
    else $grade = 'F';
    
    $stmt = $pdo->prepare("UPDATE exam_results SET ca_score = ?, exam_score = ?, total_score = ?, grade = ? WHERE id = ?");
    $stmt->execute([$ca_score, $exam_score, $total_score, $grade, $id]);
    $message = 'Result updated successfully';
}

// Get all students
$students = $pdo->query("SELECT id, name, matricule FROM users WHERE role = 'student' ORDER BY name")->fetchAll();

// Get all results
$results = $pdo->query("
    SELECT r.*, u.name as student_name, u.matricule 
    FROM exam_results r 
    JOIN users u ON r.student_id = u.id 
    ORDER BY r.academic_year DESC, r.semester DESC, u.name
")->fetchAll();

include 'header.php';
?>

<div class="admin-main-content">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
            <h1 style="font-size: 28px; font-weight: 700; margin-bottom: 4px;">
                <i class="fas fa-chart-line" style="color: #f59e0b;"></i> Manage Student Results
            </h1>
            <p style="color: #64748b;">Add, edit, or delete student examination results.</p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Add Result Form -->
    <div class="admin-card">
        <h3><i class="fas fa-plus-circle"></i> Add New Result</h3>
        <form method="POST">
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;">
                <div class="form-group">
                    <label>Student</label>
                    <select name="student_id" class="form-select" required>
                        <option value="">Select Student</option>
                        <?php foreach ($students as $student): ?>
                            <option value="<?php echo $student['id']; ?>">
                                <?php echo htmlspecialchars($student['name']); ?> (<?php echo $student['matricule']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Course Code</label>
                    <input type="text" name="course_code" class="form-input" placeholder="e.g., SWE 301" required>
                </div>
                <div class="form-group">
                    <label>Course Name</label>
                    <input type="text" name="course_name" class="form-input" placeholder="e.g., Database Management" required>
                </div>
                <div class="form-group">
                    <label>Credit Hours</label>
                    <select name="credit" class="form-select" required>
                        <option value="4">4</option>
                        <option value="3">3</option>
                        <option value="2">2</option>
                        <option value="1">1</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>CA Score</label>
                    <input type="number" name="ca_score" class="form-input" min="0" max="30" required>
                </div>
                <div class="form-group">
                    <label>Exam Score</label>
                    <input type="number" name="exam_score" class="form-input" min="0" max="70" required>
                </div>
                <div class="form-group">
                    <label>Semester</label>
                    <select name="semester" class="form-select" required>
                        <option value="1">First Semester</option>
                        <option value="2">Second Semester</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Academic Year</label>
                    <input type="text" name="academic_year" class="form-input" placeholder="e.g., 2025/2026" value="2025/2026" required>
                </div>
            </div>
            <button type="submit" name="add_result" class="btn-primary" style="margin-top: 8px;">
                <i class="fas fa-plus"></i> Add Result
            </button>
        </form>
    </div>

    <!-- Results Table -->
    <div class="admin-card">
        <h3><i class="fas fa-table"></i> All Student Results</h3>
        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Matricule</th>
                        <th>Course Code</th>
                        <th>Course</th>
                        <th>Credit</th>
                        <th>CA</th>
                        <th>Exam</th>
                        <th>Total</th>
                        <th>Grade</th>
                        <th>Sem</th>
                        <th>Year</th>
                        <th style="min-width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $result): ?>
                    <form method="POST" style="display: contents;">
                        <tr>
                            <td><?php echo htmlspecialchars($result['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($result['matricule']); ?></td>
                            <td><?php echo htmlspecialchars($result['course_code']); ?></td>
                            <td><?php echo htmlspecialchars($result['course_name']); ?></td>
                            <td><?php echo $result['credit']; ?></td>
                            <td><input type="number" name="ca_score" value="<?php echo $result['ca_score']; ?>" class="form-input" style="width: 60px; padding: 4px 6px; text-align: center;" required></td>
                            <td><input type="number" name="exam_score" value="<?php echo $result['exam_score']; ?>" class="form-input" style="width: 60px; padding: 4px 6px; text-align: center;" required></td>
                            <td><strong><?php echo $result['total_score']; ?></strong></td>
                            <td class="grade-<?php echo strtolower($result['grade']); ?>"><strong><?php echo $result['grade']; ?></strong></td>
                            <td><?php echo $result['semester'] == 1 ? '1st' : '2nd'; ?></td>
                            <td><?php echo $result['academic_year']; ?></td>
                            <td>
                                <input type="hidden" name="result_id" value="<?php echo $result['id']; ?>">
                                <button type="submit" name="edit_result" class="btn-edit" style="margin-right: 6px;">
                                    <i class="fas fa-save"></i> Save
                                </button>
                                <a href="?delete=<?php echo $result['id']; ?>" class="btn-danger" onclick="return confirm('Delete this result?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    </form>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.grade-a { color: #10b981; font-weight: 700; }
.grade-b { color: #3b82f6; font-weight: 700; }
.grade-c { color: #f59e0b; font-weight: 700; }
.grade-d, .grade-f { color: #ef4444; font-weight: 700; }

/* Action buttons side by side */
.admin-table td:last-child {
    white-space: nowrap;
    min-width: 140px;
}

.btn-edit, .btn-danger {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-edit {
    background: #3b82f6;
    color: white;
}
.btn-edit:hover {
    background: #2563eb;
}
.btn-danger {
    background: #ef4444;
    color: white;
}
.btn-danger:hover {
    background: #dc2626;
}

@media (max-width: 768px) {
    .admin-table td:last-child {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }
    .btn-edit, .btn-danger {
        font-size: 10px;
        padding: 4px 8px;
    }
}
</style>

<?php include 'footer.php'; ?>