<?php
require_once 'includes/config.php';
requireLogin();

$pageTitle = 'My Exam Results';
$showSidebar = true;

$currentUser = getCurrentUser($pdo);
$studentId = $currentUser['id'];
$studentName = $currentUser['name'];
$studentMatricule = $currentUser['matricule'] ?? 'Not assigned';
$studentDept = '';

// Get department name
if ($currentUser['department_id']) {
    $deptStmt = $pdo->prepare("SELECT name FROM departments WHERE id = ?");
    $deptStmt->execute([$currentUser['department_id']]);
    $dept = $deptStmt->fetch();
    $studentDept = $dept ? $dept['name'] : 'Not assigned';
}

// Get selected semester
$selectedSemester = isset($_GET['semester']) ? intval($_GET['semester']) : 1;
$selectedYear = isset($_GET['year']) ? $_GET['year'] : '2025/2026';

// Get results
$stmt = $pdo->prepare("
    SELECT * FROM exam_results 
    WHERE student_id = ? AND semester = ? AND academic_year = ?
    ORDER BY course_code
");
$stmt->execute([$studentId, $selectedSemester, $selectedYear]);
$results = $stmt->fetchAll();

// Calculate GPA
$totalPoints = 0;
$totalCredits = 0;
$totalScoreSum = 0;
$gradePoints = [
    'A' => 4.0, 'A-' => 3.7, 'B+' => 3.3, 'B' => 3.0,
    'B-' => 2.7, 'C+' => 2.3, 'C' => 2.0, 'C-' => 1.7,
    'D' => 1.0, 'F' => 0.0
];

foreach ($results as $result) {
    $point = isset($gradePoints[$result['grade']]) ? $gradePoints[$result['grade']] : 0;
    $totalPoints += $point * $result['credit'];
    $totalCredits += $result['credit'];
    $totalScoreSum += $result['total_score'];
}

$gpa = $totalCredits > 0 ? number_format($totalPoints / $totalCredits, 2) : 0;
$averageScore = count($results) > 0 ? floor($totalScoreSum / count($results)) : 0;

// Get available semesters
$semesterStmt = $pdo->prepare("
    SELECT DISTINCT semester, academic_year FROM exam_results 
    WHERE student_id = ? ORDER BY academic_year DESC, semester DESC
");
$semesterStmt->execute([$studentId]);
$availableSemesters = $semesterStmt->fetchAll();

function getOverallGrade($gpa) {
    if ($gpa >= 3.5) return 'First Class Honours';
    if ($gpa >= 3.0) return 'Second Class Honours (Upper)';
    if ($gpa >= 2.5) return 'Second Class Honours (Lower)';
    if ($gpa >= 2.0) return 'Third Class Honours';
    if ($gpa >= 1.0) return 'Pass';
    return 'Fail';
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="fas fa-chart-line" style="color: #f59e0b;"></i> My Exam Results</h1>
        <p>View your semester results, GPA, and download your result slip</p>
    </div>

    <!-- Semester Selector -->
    <div class="semester-selector">
        <span class="selector-label"><i class="fas fa-calendar-alt"></i> Select Semester:</span>
        <div class="semester-buttons">
            <?php if (count($availableSemesters) > 0): ?>
                <?php foreach ($availableSemesters as $sem): ?>
                    <?php if ($sem['semester'] == $selectedSemester && $sem['academic_year'] == $selectedYear): ?>
                        <span class="semester-active">
                            Semester <?php echo $sem['semester']; ?> (<?php echo $sem['academic_year']; ?>)
                        </span>
                    <?php else: ?>
                        <a href="?semester=<?php echo $sem['semester']; ?>&year=<?php echo urlencode($sem['academic_year']); ?>" class="semester-link">
                            Semester <?php echo $sem['semester']; ?> (<?php echo $sem['academic_year']; ?>)
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <span class="semester-active">No results available yet</span>
            <?php endif; ?>
        </div>
    </div>

    <?php if (count($results) > 0): ?>
        <!-- Result Slip -->
        <div class="result-slip" id="resultSlip">
            <!-- Header -->
            <div class="result-header">
                <div class="logo-section">
                    <div class="logo-icon-large">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="institution-info">
                        <h2>HIGHER INSTITUTE FOR PROFESSIONALISM AND EXCELLENCE (HIPTEX)</h2>
                        <p>Auth No: 21-00601/N/MINESUP/SG/DDES/ESUP/SDA/aosb</p>
                        <p class="motto">"Lighting the Path to Professionalism"</p>
                    </div>
                </div>
                <div class="title-section">
                    <h3>STATEMENT OF RESULT</h3>
                    <p><?php echo $selectedSemester == 1 ? 'FIRST SEMESTER' : 'SECOND SEMESTER'; ?> <?php echo $selectedYear; ?> ACADEMIC SESSION</p>
                </div>
            </div>

            <!-- Student Info -->
            <div class="student-info-box">
                <div class="info-grid">
                    <div class="info-item"><span class="label">NAME:</span> <span class="value"><?php echo strtoupper(htmlspecialchars($studentName)); ?></span></div>
                    <div class="info-item"><span class="label">MATRICULE:</span> <span class="value"><?php echo htmlspecialchars($studentMatricule); ?></span></div>
                </div>
                <div class="info-grid">
                    <div class="info-item"><span class="label">DEPARTMENT:</span> <span class="value"><?php echo strtoupper(htmlspecialchars($studentDept)); ?></span></div>
                    <div class="info-item"><span class="label">LEVEL:</span> <span class="value">HND 2</span></div>
                </div>
            </div>

            <!-- Results Table -->
            <table class="results-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>CODE</th>
                        <th>COURSE TITLE</th>
                        <th>CREDIT</th>
                        <th>CA</th>
                        <th>EXAM</th>
                        <th>TOTAL</th>
                        <th>GRADE</th>
                        <th>GP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $count = 1; foreach ($results as $result):
                        $gradePoint = isset($gradePoints[$result['grade']]) ? $gradePoints[$result['grade']] : 0;
                    ?>
                    <tr>
                        <td><?php echo $count++; ?></td>
                        <td><?php echo htmlspecialchars($result['course_code']); ?></td>
                        <td style="text-align: left;"><?php echo htmlspecialchars($result['course_name']); ?></td>
                        <td><?php echo $result['credit']; ?></td>
                        <td><?php echo $result['ca_score']; ?></td>
                        <td><?php echo $result['exam_score']; ?></td>
                        <td><strong><?php echo $result['total_score']; ?></strong></td>
                        <td class="grade-<?php echo strtolower($result['grade']); ?>"><strong><?php echo $result['grade']; ?></strong></td>
                        <td><?php echo number_format($gradePoint, 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3"><strong>TOTAL</strong></td>
                        <td><strong><?php echo $totalCredits; ?></strong></td>
                        <td colspan="4"></td>
                        <td><strong><?php echo number_format($totalPoints, 2); ?></strong></td>
                    </tr>
                </tfoot>
            </table>

            <!-- GPA Section -->
            <div class="gpa-section">
                <div class="gpa-box">
                    <span class="gpa-label">GPA</span>
                    <span class="gpa-value"><?php echo $gpa; ?></span>
                </div>
                <div class="gpa-box">
                    <span class="gpa-label">REMARK</span>
                    <span class="gpa-remark"><?php echo getOverallGrade($gpa); ?></span>
                </div>
                <div class="gpa-box">
                    <span class="gpa-label">AVERAGE</span>
                    <span class="gpa-value"><?php echo $averageScore; ?>%</span>
                </div>
            </div>

            <!-- Footer -->
            <div class="result-footer">
                <p>NB: This result slip is not valid for official purposes without the signature of the Dean and stamp of the institution.</p>
                <div class="signatures">
                    <div class="signature-line">
                        <span>_________________________</span>
                        <span>Dean of Academic Affairs</span>
                    </div>
                    <div class="signature-line">
                        <span>_________________________</span>
                        <span>Date</span>
                    </div>
                    <div class="signature-line">
                        <span>_________________________</span>
                        <span>Registrar</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <button class="btn-print-result" onclick="printResult()">
                <i class="fas fa-print"></i> Print / Save as PDF
            </button>
        </div>

    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-chart-line"></i>
            <h3>No Results Available</h3>
            <p>Your results for <?php echo $selectedSemester == 1 ? 'First Semester' : 'Second Semester'; ?> <?php echo $selectedYear; ?> are not yet published.</p>
            <p style="font-size: 13px; color: var(--text-muted);">Please check back later or contact the academic office.</p>
        </div>
    <?php endif; ?>
</div>

<style>
.page-header {
    margin-bottom: 24px;
}

.page-header h1 {
    font-size: 26px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 4px;
}

.page-header p {
    color: var(--text-muted);
}

.semester-selector {
    background: var(--bg-secondary);
    padding: 16px 24px;
    border-radius: 16px;
    margin-bottom: 24px;
    border: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.selector-label {
    font-weight: 600;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 8px;
}

.semester-buttons {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.semester-link {
    background: var(--bg-primary);
    padding: 8px 20px;
    border-radius: 30px;
    text-decoration: none;
    color: var(--text-primary);
    font-size: 13px;
    border: 1px solid var(--border-color);
    transition: all 0.2s;
}

.semester-link:hover {
    border-color: #f59e0b;
    color: #f59e0b;
}

.semester-active {
    background: #f59e0b;
    padding: 8px 20px;
    border-radius: 30px;
    color: white;
    font-size: 13px;
    font-weight: 600;
}

.result-slip {
    background: var(--bg-secondary);
    border-radius: 16px;
    padding: 32px;
    margin-bottom: 24px;
    border: 1px solid var(--border-color);
}

.result-header {
    text-align: center;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 2px solid #1e293b;
}

.logo-section {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    margin-bottom: 20px;
}

.logo-icon-large {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #1e293b, #0f172a);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.logo-icon-large i {
    font-size: 28px;
    color: white;
}

.institution-info h2 {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
}

.institution-info p {
    font-size: 12px;
    color: var(--text-muted);
    margin: 2px 0;
}

.motto {
    font-style: italic;
}

.title-section h3 {
    font-size: 18px;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
}

.title-section p {
    font-size: 13px;
    color: var(--text-muted);
    margin-top: 4px;
}

.student-info-box {
    background: var(--bg-primary);
    padding: 16px;
    border-radius: 12px;
    margin-bottom: 24px;
}

.info-grid {
    display: flex;
    gap: 40px;
    margin-bottom: 8px;
    flex-wrap: wrap;
}

.info-grid:last-child {
    margin-bottom: 0;
}

.info-item .label {
    font-weight: 600;
    color: var(--text-muted);
}

.info-item .value {
    font-weight: 500;
    color: var(--text-primary);
}

.results-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 24px;
    font-size: 13px;
}

.results-table th {
    background: var(--bg-primary);
    padding: 10px 8px;
    text-align: center;
    font-weight: 600;
    color: var(--text-primary);
    border: 1px solid var(--border-color);
}

.results-table td {
    padding: 10px 8px;
    text-align: center;
    border: 1px solid var(--border-color);
}

.results-table td:nth-child(3) {
    text-align: left;
}

.grade-a { color: #10b981; font-weight: 700; }
.grade-b { color: #3b82f6; font-weight: 700; }
.grade-c { color: #f59e0b; font-weight: 700; }
.grade-d, .grade-f { color: #ef4444; font-weight: 700; }

.gpa-section {
    display: flex;
    justify-content: flex-end;
    gap: 20px;
    margin-bottom: 24px;
    padding-top: 16px;
    border-top: 1px solid var(--border-color);
}

.gpa-box {
    text-align: center;
    min-width: 120px;
    padding: 12px;
    background: var(--bg-primary);
    border-radius: 12px;
}

.gpa-label {
    display: block;
    font-size: 11px;
    color: var(--text-muted);
}

.gpa-value {
    display: block;
    font-size: 24px;
    font-weight: 800;
    color: var(--text-primary);
}

.gpa-remark {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #10b981;
}

.result-footer {
    text-align: center;
    font-size: 11px;
    color: var(--text-muted);
    border-top: 1px solid var(--border-color);
    padding-top: 16px;
}

.signatures {
    display: flex;
    justify-content: space-around;
    margin-top: 24px;
}

.signature-line {
    display: flex;
    flex-direction: column;
    gap: 8px;
    text-align: center;
}

.signature-line span:first-child {
    font-family: monospace;
    letter-spacing: 1px;
}

.action-buttons {
    text-align: center;
}

.btn-print-result {
    background: linear-gradient(135deg, #1e293b, #0f172a);
    color: white;
    padding: 14px 32px;
    border: none;
    border-radius: 40px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.btn-print-result:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: var(--bg-secondary);
    border-radius: 20px;
    border: 1px solid var(--border-color);
}

.empty-state i {
    font-size: 56px;
    color: var(--text-muted);
    margin-bottom: 16px;
    opacity: 0.5;
}

.empty-state h3 {
    font-size: 20px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 8px;
}

@media print {
    .sidebar, .top-header, .menu-toggle, .semester-selector, .action-buttons, .ai-chatbot {
        display: none !important;
    }
    .main-content {
        margin: 0 !important;
        padding: 0 !important;
    }
    .result-slip {
        box-shadow: none;
        padding: 20px;
        margin: 0;
    }
    body {
        background: white;
    }
}

@media (max-width: 768px) {
    .results-table {
        font-size: 11px;
        display: block;
        overflow-x: auto;
    }
    .results-table th, .results-table td {
        padding: 6px 4px;
    }
    .gpa-section {
        flex-direction: column;
        align-items: stretch;
    }
    .info-grid {
        flex-direction: column;
        gap: 8px;
    }
    .logo-section {
        flex-direction: column;
    }
    .signatures {
        flex-direction: column;
        gap: 20px;
    }
    .semester-selector {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<script>
function printResult() {
    window.print();
}
</script>

<?php include 'includes/footer.php'; ?>