<?php
require_once 'includes/config.php';
requireLogin();

$pageTitle = 'Exam Dates';
$showSidebar = true;

$currentUser = getCurrentUser($pdo);
$userDeptId = $currentUser['department_id'] ?? 0;
$userDeptName = '';

if ($userDeptId > 0) {
    $deptStmt = $pdo->prepare("SELECT name FROM departments WHERE id = ?");
    $deptStmt->execute([$userDeptId]);
    $userDept = $deptStmt->fetch();
    $userDeptName = $userDept ? $userDept['name'] : 'Your Department';
}

// Get all exam dates for user's department
$stmt = $pdo->prepare("
    SELECT * FROM exam_dates 
    WHERE department_id = ? 
    ORDER BY exam_date ASC
");
$stmt->execute([$userDeptId]);
$allExams = $stmt->fetchAll();

// Split by semester
$semester1Exams = [];
$semester2Exams = [];

foreach ($allExams as $exam) {
    if ($exam['semester'] == 1) {
        $semester1Exams[] = $exam;
    } else {
        $semester2Exams[] = $exam;
    }
}

// Find next upcoming exam
$today = new DateTime();
$nextExam = null;

foreach ($allExams as $exam) {
    $examDate = new DateTime($exam['exam_date']);
    if ($examDate >= $today) {
        $nextExam = $exam;
        break;
    }
}

// Countdown
$daysLeft = 0;
$hoursLeft = 0;
$minutesLeft = 0;
$secondsLeft = 0;

if ($nextExam) {
    $examDateTime = new DateTime($nextExam['exam_date'] . ' ' . ($nextExam['start_time'] ?? '08:00:00'));
    $interval = $today->diff($examDateTime);
    $daysLeft = $interval->days;
    $hoursLeft = $interval->h;
    $minutesLeft = $interval->i;
    $secondsLeft = $interval->s;
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="fas fa-calendar-check" style="color: #f59e0b;"></i> Exam Dates</h1>
        <p>View your upcoming examination schedule</p>
    </div>

    <?php if (count($allExams) == 0): ?>
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <h3>No Exam Dates Available</h3>
            <p>No examinations have been scheduled for your department yet.</p>
            <p style="font-size: 13px; color: var(--text-muted);">Please check back later or contact your HOD.</p>
        </div>
    <?php else: ?>

    <!-- Countdown Banner -->
    <?php if ($nextExam): ?>
    <div class="countdown-banner">
        <div class="countdown-text">
            <i class="fas fa-hourglass-half"></i>
            <div>
                <strong>Next Exam:</strong>
                <span class="exam-name"><?php echo htmlspecialchars($nextExam['course_name']); ?> (<?php echo htmlspecialchars($nextExam['course_code']); ?>)</span>
                <small><?php echo date('l, F j, Y', strtotime($nextExam['exam_date'])); ?> at <?php echo date('h:i A', strtotime($nextExam['start_time'])); ?></small>
            </div>
        </div>
        <div class="countdown-timer" id="countdownTimer">
            <div class="timer-block"><span id="days">00</span><span class="timer-label">Days</span></div>
            <div class="timer-block"><span id="hours">00</span><span class="timer-label">Hours</span></div>
            <div class="timer-block"><span id="minutes">00</span><span class="timer-label">Minutes</span></div>
            <div class="timer-block"><span id="seconds">00</span><span class="timer-label">Seconds</span></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Department Header -->
    <div class="dept-header">
        <i class="fas fa-building"></i>
        <span><?php echo htmlspecialchars($userDeptName); ?> Department</span>
    </div>

    <!-- First Semester -->
    <?php if (count($semester1Exams) > 0): ?>
    <div class="semester-section">
        <h2 class="section-title">📖 First Semester Examinations</h2>
        <div class="exam-grid">
            <?php foreach ($semester1Exams as $exam):
                $status = 'upcoming';
                $examDateObj = new DateTime($exam['exam_date']);
                if ($examDateObj < $today) {
                    $status = 'completed';
                } elseif ($examDateObj->format('Y-m-d') == $today->format('Y-m-d')) {
                    $status = 'ongoing';
                }
            ?>
            <div class="exam-card <?php echo $status; ?>">
                <span class="exam-badge <?php echo $status; ?>">
                    <?php echo $status == 'upcoming' ? 'Upcoming' : ($status == 'ongoing' ? 'Ongoing' : 'Completed'); ?>
                </span>
                <div class="exam-title"><?php echo htmlspecialchars($exam['course_name']); ?></div>
                <div class="exam-code"><?php echo htmlspecialchars($exam['course_code']); ?></div>
                <div class="exam-details">
                    <div class="exam-detail">
                        <i class="fas fa-calendar-day"></i>
                        <span><?php echo date('l, F j, Y', strtotime($exam['exam_date'])); ?></span>
                    </div>
                    <div class="exam-detail">
                        <i class="fas fa-clock"></i>
                        <span><?php echo date('h:i A', strtotime($exam['start_time'])); ?> - <?php echo date('h:i A', strtotime($exam['end_time'])); ?></span>
                    </div>
                    <div class="exam-detail">
                        <i class="fas fa-location-dot"></i>
                        <span><?php echo htmlspecialchars($exam['venue']); ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Second Semester -->
    <?php if (count($semester2Exams) > 0): ?>
    <div class="semester-section">
        <h2 class="section-title">📚 Second Semester Examinations</h2>
        <div class="exam-grid">
            <?php foreach ($semester2Exams as $exam):
                $status = 'upcoming';
                $examDateObj = new DateTime($exam['exam_date']);
                if ($examDateObj < $today) {
                    $status = 'completed';
                } elseif ($examDateObj->format('Y-m-d') == $today->format('Y-m-d')) {
                    $status = 'ongoing';
                }
            ?>
            <div class="exam-card <?php echo $status; ?>">
                <span class="exam-badge <?php echo $status; ?>">
                    <?php echo $status == 'upcoming' ? 'Upcoming' : ($status == 'ongoing' ? 'Ongoing' : 'Completed'); ?>
                </span>
                <div class="exam-title"><?php echo htmlspecialchars($exam['course_name']); ?></div>
                <div class="exam-code"><?php echo htmlspecialchars($exam['course_code']); ?></div>
                <div class="exam-details">
                    <div class="exam-detail">
                        <i class="fas fa-calendar-day"></i>
                        <span><?php echo date('l, F j, Y', strtotime($exam['exam_date'])); ?></span>
                    </div>
                    <div class="exam-detail">
                        <i class="fas fa-clock"></i>
                        <span><?php echo date('h:i A', strtotime($exam['start_time'])); ?> - <?php echo date('h:i A', strtotime($exam['end_time'])); ?></span>
                    </div>
                    <div class="exam-detail">
                        <i class="fas fa-location-dot"></i>
                        <span><?php echo htmlspecialchars($exam['venue']); ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

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

/* Countdown Banner */
.countdown-banner {
    background: linear-gradient(135deg, #1e293b, #0f172a);
    border-radius: 16px;
    padding: 24px 32px;
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
    border: 1px solid #334155;
}

.countdown-text {
    display: flex;
    align-items: center;
    gap: 16px;
    color: white;
}

.countdown-text i {
    font-size: 28px;
    color: #f59e0b;
}

.countdown-text strong {
    font-size: 12px;
    color: #94a3b8;
}

.exam-name {
    font-size: 18px;
    font-weight: 700;
    display: block;
    margin: 2px 0;
}

.countdown-text small {
    font-size: 11px;
    color: #94a3b8;
}

.countdown-timer {
    display: flex;
    gap: 20px;
}

.timer-block {
    text-align: center;
    background: rgba(255,255,255,0.05);
    padding: 8px 16px;
    border-radius: 12px;
    min-width: 60px;
}

.timer-block span:first-child {
    display: block;
    font-size: 24px;
    font-weight: 800;
    color: #f59e0b;
    line-height: 1;
}

.timer-label {
    display: block;
    font-size: 10px;
    color: #94a3b8;
    margin-top: 4px;
}

/* Department Header */
.dept-header {
    background: var(--bg-secondary);
    padding: 10px 20px;
    border-radius: 40px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 24px;
    border: 1px solid var(--border-color);
}

.dept-header i {
    color: #f59e0b;
}

/* Semester Sections */
.semester-section {
    margin-bottom: 40px;
}

.section-title {
    font-size: 20px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 16px;
    padding-bottom: 8px;
    border-bottom: 2px solid #f59e0b;
    display: inline-block;
}

.exam-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 20px;
}

.exam-card {
    background: var(--bg-secondary);
    border-radius: 16px;
    padding: 20px;
    border: 1px solid var(--border-color);
    position: relative;
    transition: transform 0.2s;
}

.exam-card:hover {
    transform: translateY(-3px);
}

.exam-card.upcoming { border-left: 4px solid #10b981; }
.exam-card.ongoing { border-left: 4px solid #f59e0b; background: rgba(245,158,11,0.05); }
.exam-card.completed { border-left: 4px solid #64748b; opacity: 0.7; }

.exam-badge {
    position: absolute;
    top: 16px;
    right: 16px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}

.exam-badge.upcoming { background: #10b981; color: white; }
.exam-badge.ongoing { background: #f59e0b; color: white; }
.exam-badge.completed { background: #64748b; color: white; }

.exam-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 4px;
    padding-right: 70px;
}

.exam-code {
    font-size: 12px;
    color: var(--text-muted);
    margin-bottom: 16px;
}

.exam-details {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.exam-detail {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    color: var(--text-primary);
}

.exam-detail i {
    width: 18px;
    color: #f59e0b;
}

/* Responsive */
@media (max-width: 768px) {
    .countdown-banner {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }
    .countdown-text {
        flex-direction: column;
        text-align: center;
    }
    .countdown-timer {
        gap: 12px;
        flex-wrap: wrap;
        justify-content: center;
    }
    .timer-block {
        padding: 6px 12px;
        min-width: 50px;
    }
    .timer-block span:first-child {
        font-size: 18px;
    }
    .exam-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Countdown timer
let daysLeft = <?php echo $daysLeft; ?>;
let hoursLeft = <?php echo $hoursLeft; ?>;
let minutesLeft = <?php echo $minutesLeft; ?>;
let secondsLeft = <?php echo $secondsLeft; ?>;

function updateCountdown() {
    if (secondsLeft > 0) {
        secondsLeft--;
    } else if (minutesLeft > 0) {
        minutesLeft--;
        secondsLeft = 59;
    } else if (hoursLeft > 0) {
        hoursLeft--;
        minutesLeft = 59;
        secondsLeft = 59;
    } else if (daysLeft > 0) {
        daysLeft--;
        hoursLeft = 23;
        minutesLeft = 59;
        secondsLeft = 59;
    }
    
    document.getElementById('days').textContent = String(daysLeft).padStart(2, '0');
    document.getElementById('hours').textContent = String(hoursLeft).padStart(2, '0');
    document.getElementById('minutes').textContent = String(minutesLeft).padStart(2, '0');
    document.getElementById('seconds').textContent = String(secondsLeft).padStart(2, '0');
}

setInterval(updateCountdown, 1000);
</script>

<?php include 'includes/footer.php'; ?>