<?php
require_once 'includes/config.php';
requireLogin();

$pageTitle = 'My Timetable';
$showSidebar = true;

$currentUser = getCurrentUser($pdo);
$userDeptId = $currentUser['department_id'] ?? 0;

// Get timetable for user's department
$stmt = $pdo->prepare("
    SELECT * FROM timetable 
    WHERE department_id = ? 
    ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), start_time
");
$stmt->execute([$userDeptId]);
$timetable = $stmt->fetchAll();

// Group by day
$grouped = [
    'Monday' => [],
    'Tuesday' => [],
    'Wednesday' => [],
    'Thursday' => [],
    'Friday' => []
];

foreach ($timetable as $row) {
    $day = $row['day_of_week'];
    if (isset($grouped[$day])) {
        $grouped[$day][] = $row;
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="fas fa-calendar-alt" style="color: #f59e0b;"></i> Academic Timetable</h1>
        <p>View your weekly lecture schedule</p>
    </div>

    <?php if (count($timetable) == 0): ?>
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <h3>No Timetable Available</h3>
            <p>Your department timetable has not been uploaded yet.</p>
            <p style="font-size: 13px; color: var(--text-muted);">Please check back later or contact your HOD.</p>
        </div>
    <?php else: ?>
        <!-- Day Selector for Mobile -->
        <div class="day-selector">
            <button class="day-btn active" data-day="Monday">Mon</button>
            <button class="day-btn" data-day="Tuesday">Tue</button>
            <button class="day-btn" data-day="Wednesday">Wed</button>
            <button class="day-btn" data-day="Thursday">Thu</button>
            <button class="day-btn" data-day="Friday">Fri</button>
        </div>

        <!-- Desktop View -->
        <div class="desktop-timetable">
            <table class="timetable-table">
                <thead>
                    <tr>
                        <th width="100">Time</th>
                        <th>Monday</th>
                        <th>Tuesday</th>
                        <th>Wednesday</th>
                        <th>Thursday</th>
                        <th>Friday</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $timeSlots = ['08:00:00', '09:00:00', '10:00:00', '11:00:00', '12:00:00', '13:00:00', '14:00:00', '15:00:00', '16:00:00'];
                    
                    function getCourseAtTime($dayCourses, $time) {
                        foreach ($dayCourses as $course) {
                            if ($course['start_time'] <= $time && $course['end_time'] > $time) {
                                return $course;
                            }
                        }
                        return null;
                    }
                    
                    foreach ($timeSlots as $slot):
                        $timeDisplay = date('h:i A', strtotime($slot));
                    ?>
                    <tr>
                        <td class="time-slot"><?php echo $timeDisplay; ?></td>
                        <?php foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day): ?>
                            <td class="course-cell">
                                <?php $course = getCourseAtTime($grouped[$day], $slot); ?>
                                <?php if ($course): ?>
                                    <div class="course-info">
                                        <strong><?php echo htmlspecialchars($course['course_code']); ?></strong><br>
                                        <?php echo htmlspecialchars($course['course_name']); ?><br>
                                        <small><?php echo htmlspecialchars($course['lecturer']); ?></small><br>
                                        <span class="venue">📍 <?php echo htmlspecialchars($course['venue']); ?></span>
                                    </div>
                                <?php else: ?>
                                    <div class="empty-slot">—</div>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile View -->
        <div class="mobile-timetable">
            <?php foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day): ?>
                <div class="day-schedule" data-day="<?php echo $day; ?>" style="display: <?php echo $day == 'Monday' ? 'block' : 'none'; ?>">
                    <div class="day-header">
                        <h3><?php echo $day; ?></h3>
                    </div>
                    <?php if (count($grouped[$day]) > 0): ?>
                        <?php foreach ($grouped[$day] as $course): ?>
                            <div class="schedule-card">
                                <div class="schedule-time">
                                    <i class="fas fa-clock"></i>
                                    <?php echo date('h:i A', strtotime($course['start_time'])); ?> - <?php echo date('h:i A', strtotime($course['end_time'])); ?>
                                </div>
                                <div class="schedule-course">
                                    <strong><?php echo htmlspecialchars($course['course_code']); ?>:</strong> <?php echo htmlspecialchars($course['course_name']); ?>
                                </div>
                                <div class="schedule-details">
                                    <div><i class="fas fa-chalkboard-user"></i> <?php echo htmlspecialchars($course['lecturer']); ?></div>
                                    <div><i class="fas fa-location-dot"></i> <?php echo htmlspecialchars($course['venue']); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-class">
                            <i class="fas fa-coffee"></i>
                            <p>No classes scheduled</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
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

/* Day Selector */
.day-selector {
    display: none;
    gap: 8px;
    margin-bottom: 20px;
    overflow-x: auto;
    padding-bottom: 8px;
}

.day-btn {
    padding: 10px 20px;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 30px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s;
    flex: 1;
}

.day-btn:hover {
    border-color: #f59e0b;
}

.day-btn.active {
    background: #f59e0b;
    border-color: #f59e0b;
    color: white;
}

/* Desktop Timetable */
.desktop-timetable {
    background: var(--bg-secondary);
    border-radius: 16px;
    overflow-x: auto;
    border: 1px solid var(--border-color);
    display: block;
}

.timetable-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 700px;
}

.timetable-table th {
    background: var(--bg-primary);
    padding: 14px 12px;
    text-align: center;
    font-weight: 700;
    color: var(--text-primary);
    border-bottom: 2px solid var(--border-color);
    font-size: 13px;
}

.timetable-table td {
    padding: 14px 8px;
    border: 1px solid var(--border-color);
    vertical-align: top;
}

.time-slot {
    background: var(--bg-primary);
    font-weight: 600;
    color: var(--text-primary);
    text-align: center;
    white-space: nowrap;
}

.course-cell {
    vertical-align: top;
    min-height: 80px;
}

.course-info {
    font-size: 13px;
    line-height: 1.5;
}

.course-info strong {
    color: #f59e0b;
    font-size: 13px;
}

.course-info small {
    color: var(--text-muted);
    font-size: 11px;
}

.venue {
    display: inline-block;
    background: #e0e7ff;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 10px;
    margin-top: 4px;
    color: #4f46e5;
}

.empty-slot {
    color: #94a3b8;
    text-align: center;
    padding: 12px 0;
}

/* Mobile Timetable */
.mobile-timetable {
    display: none;
}

.day-header {
    margin-bottom: 16px;
    padding-bottom: 8px;
    border-bottom: 2px solid #f59e0b;
}

.day-header h3 {
    font-size: 20px;
    font-weight: 700;
    color: var(--text-primary);
}

.schedule-card {
    background: var(--bg-secondary);
    border-radius: 16px;
    padding: 16px;
    margin-bottom: 12px;
    border: 1px solid var(--border-color);
}

.schedule-time {
    font-size: 12px;
    color: #f59e0b;
    margin-bottom: 8px;
}

.schedule-time i {
    margin-right: 6px;
}

.schedule-course {
    font-size: 14px;
    margin-bottom: 8px;
}

.schedule-details {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    font-size: 12px;
    color: var(--text-muted);
}

.schedule-details i {
    width: 16px;
    margin-right: 4px;
    color: #f59e0b;
}

.no-class {
    text-align: center;
    padding: 40px;
    background: var(--bg-primary);
    border-radius: 16px;
    color: var(--text-muted);
}

.no-class i {
    font-size: 32px;
    margin-bottom: 8px;
}

/* Responsive */
@media (max-width: 768px) {
    .desktop-timetable {
        display: none;
    }
    .day-selector {
        display: flex;
    }
    .mobile-timetable {
        display: block;
    }
}
</style>

<script>
// Mobile day selector
document.querySelectorAll('.day-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const day = this.dataset.day;
        
        document.querySelectorAll('.day-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        document.querySelectorAll('.day-schedule').forEach(schedule => {
            schedule.style.display = schedule.dataset.day === day ? 'block' : 'none';
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>