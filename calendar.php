<?php
require_once 'includes/config.php';
requireLogin();

$pageTitle = 'Academic Calendar';
$showSidebar = true;

$currentUser = getCurrentUser($pdo);
$userDeptId = $currentUser['department_id'] ?? 0;

// Get current month and year
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Adjust navigation
if ($month < 1) { $month = 12; $year--; }
elseif ($month > 12) { $month = 1; $year++; }

// Calendar calculations
$firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date('t', $firstDayOfMonth);
$startingDay = date('w', $firstDayOfMonth);

// Get events for this month
$startDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
$endDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-$daysInMonth";

$stmt = $pdo->prepare("
    SELECT * FROM calendar_events 
    WHERE event_date BETWEEN ? AND ?
    ORDER BY event_date
");
$stmt->execute([$startDate, $endDate]);
$events = $stmt->fetchAll();

// Group events by date
$eventsByDate = [];
foreach ($events as $event) {
    $date = $event['event_date'];
    if (!isset($eventsByDate[$date])) {
        $eventsByDate[$date] = [];
    }
    $eventsByDate[$date][] = $event;
}

$monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }

$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1><i class="fas fa-calendar-week" style="color: #f59e0b;"></i> Academic Calendar</h1>
            <p>View important academic events, exams, and holidays</p>
        </div>
        <button class="notif-btn" id="enableNotifications">
            <i class="fas fa-bell"></i> Enable Notifications
        </button>
    </div>

    <!-- Notification Banner -->
    <div class="notification-banner" id="notificationBanner">
        <div class="notification-icon">
            <i class="fas fa-bell"></i>
        </div>
        <div class="notification-text">
            <strong>Stay Updated!</strong>
            <p>Enable notifications to receive reminders about upcoming events, exams, and deadlines.</p>
        </div>
        <button class="notif-btn-small" id="enableNotifSmall">
            <i class="fas fa-bell"></i> Enable
        </button>
    </div>

    <!-- Calendar Header -->
    <div class="calendar-header">
        <button class="nav-btn" onclick="window.location.href='?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>'">
            <i class="fas fa-chevron-left"></i>
        </button>
        <span class="current-month"><?php echo $monthNames[$month - 1]; ?> <?php echo $year; ?></span>
        <button class="nav-btn" onclick="window.location.href='?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>'">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>

    <!-- Calendar Grid - COMPACT -->
    <div class="calendar-container">
        <div class="weekdays-compact">
            <div class="weekday-compact">M</div>
            <div class="weekday-compact">T</div>
            <div class="weekday-compact">W</div>
            <div class="weekday-compact">T</div>
            <div class="weekday-compact">F</div>
            <div class="weekday-compact">S</div>
            <div class="weekday-compact">S</div>
        </div>

        <div class="calendar-grid-compact">
            <?php
            $today = date('Y-m-d');
            
            // Empty cells before first day
            for ($i = 0; $i < $startingDay; $i++) {
                echo '<div class="calendar-cell other-month"></div>';
            }
            
            // Current month days
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $dateKey = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($day, 2, '0', STR_PAD_LEFT);
                $dayEvents = isset($eventsByDate[$dateKey]) ? $eventsByDate[$dateKey] : [];
                $isToday = ($dateKey == $today);
                $hasEvents = count($dayEvents) > 0;
                
                echo '<div class="calendar-cell ' . ($isToday ? 'today' : '') . ($hasEvents ? 'has-event' : '') . '" onclick="showEvents(\'' . $dateKey . '\')">';
                echo '<span class="cell-day">' . $day . '</span>';
                
                if ($hasEvents) {
                    $firstType = $dayEvents[0]['event_type'];
                    echo '<span class="cell-dot ' . $firstType . '"></span>';
                    if (count($dayEvents) > 1) {
                        echo '<span class="cell-dot ' . $dayEvents[1]['event_type'] . '"></span>';
                    }
                }
                
                echo '</div>';
            }
            
            // Empty cells after last day
            $remainingCells = (7 - (($startingDay + $daysInMonth) % 7)) % 7;
            for ($i = 1; $i <= $remainingCells; $i++) {
                echo '<div class="calendar-cell other-month"></div>';
            }
            ?>
        </div>
    </div>

    <!-- Legend -->
    <div class="calendar-legend-compact">
        <div class="legend-item"><div class="legend-color exam"></div><span>Exam</span></div>
        <div class="legend-item"><div class="legend-color holiday"></div><span>Holiday</span></div>
        <div class="legend-item"><div class="legend-color academic"></div><span>Academic</span></div>
        <div class="legend-item"><div class="legend-color registration"></div><span>Registration</span></div>
        <div class="legend-item"><div class="legend-color deadline"></div><span>Deadline</span></div>
    </div>
</div>

<!-- Event Modal -->
<div id="eventModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 id="modalDate">Events</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body" id="modalBody">
            <div class="no-events">No events scheduled for this day</div>
        </div>
    </div>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 16px;
}

.page-header-left h1 {
    font-size: 24px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 2px;
}

.page-header-left p {
    color: var(--text-muted);
    font-size: 14px;
    margin: 0;
}

.notif-btn {
    background: #f59e0b;
    color: white;
    border: none;
    padding: 8px 18px;
    border-radius: 30px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
}

.notif-btn:hover {
    background: #d97706;
    transform: translateY(-2px);
}

/* Notification Banner - Compact */
.notification-banner {
    background: linear-gradient(135deg, #1e293b, #0f172a);
    border-radius: 12px;
    padding: 12px 20px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    border-left: 3px solid #f59e0b;
    flex-wrap: wrap;
}

.notification-icon i {
    font-size: 20px;
    color: #f59e0b;
}

.notification-text strong {
    display: block;
    font-size: 13px;
    color: white;
}

.notification-text p {
    font-size: 12px;
    color: #94a3b8;
    margin: 0;
}

.notif-btn-small {
    background: #f59e0b;
    color: white;
    border: none;
    padding: 6px 14px;
    border-radius: 30px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 12px;
    margin-left: auto;
}

.notif-btn-small:hover {
    background: #d97706;
}

/* Calendar Header - Compact */
.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.nav-btn {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    padding: 4px 14px;
    border-radius: 20px;
    color: var(--text-primary);
    cursor: pointer;
    transition: all 0.2s;
    font-size: 13px;
}

.nav-btn:hover {
    border-color: #f59e0b;
    color: #f59e0b;
}

.current-month {
    font-size: 18px;
    font-weight: 700;
    color: var(--text-primary);
}

/* Calendar Container - COMPACT */
.calendar-container {
    background: var(--bg-secondary);
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--border-color);
}

/* Weekdays - Compact */
.weekdays-compact {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: var(--bg-primary);
    border-bottom: 1px solid var(--border-color);
}

.weekday-compact {
    padding: 6px;
    text-align: center;
    font-weight: 700;
    font-size: 11px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

/* Calendar Grid - COMPACT */
.calendar-grid-compact {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
}

.calendar-cell {
    min-height: 44px;
    padding: 4px 6px;
    border-right: 1px solid var(--border-color);
    border-bottom: 1px solid var(--border-color);
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 3px;
    flex-wrap: wrap;
    position: relative;
}

.calendar-cell:hover {
    background: var(--bg-primary);
}

.calendar-cell.other-month {
    background: var(--bg-primary);
}

.calendar-cell.other-month .cell-day {
    color: #94a3b8;
}

.calendar-cell.today {
    background: rgba(245, 158, 11, 0.08);
    border: 1.5px solid #f59e0b;
}

.calendar-cell.has-event {
    cursor: pointer;
}

.cell-day {
    font-size: 13px;
    font-weight: 500;
    color: var(--text-primary);
    min-width: 20px;
}

.cell-dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    display: inline-block;
    flex-shrink: 0;
}

.cell-dot.exam { background: #dc2626; }
.cell-dot.holiday { background: #10b981; }
.cell-dot.academic { background: #3b82f6; }
.cell-dot.registration { background: #f59e0b; }
.cell-dot.deadline { background: #8b5cf6; }

.today .cell-day {
    background: #f59e0b;
    color: white;
    border-radius: 50%;
    padding: 0 6px;
    font-weight: 700;
}

/* Legend - Compact */
.calendar-legend-compact {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    padding: 10px 14px;
    background: var(--bg-secondary);
    border-radius: 12px;
    margin-top: 12px;
    border: 1px solid var(--border-color);
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    color: var(--text-muted);
}

.legend-color {
    width: 10px;
    height: 10px;
    border-radius: 3px;
}

.legend-color.exam { background: #dc2626; }
.legend-color.holiday { background: #10b981; }
.legend-color.academic { background: #3b82f6; }
.legend-color.registration { background: #f59e0b; }
.legend-color.deadline { background: #8b5cf6; }

/* Modal */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal-overlay.active {
    display: flex;
}

.modal-container {
    background: var(--bg-secondary);
    border-radius: 16px;
    width: 90%;
    max-width: 450px;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}

.modal-header {
    padding: 16px 20px;
    background: #f59e0b;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
}

.modal-header h3 {
    font-size: 17px;
    font-weight: 600;
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    color: white;
    font-size: 22px;
    cursor: pointer;
}

.modal-body {
    padding: 20px;
}

.event-card {
    padding: 14px;
    border-radius: 10px;
    margin-bottom: 10px;
    border-left: 3px solid;
}

.event-card.exam { border-left-color: #dc2626; background: rgba(220,38,38,0.05); }
.event-card.holiday { border-left-color: #10b981; background: rgba(16,185,129,0.05); }
.event-card.academic { border-left-color: #3b82f6; background: rgba(59,130,246,0.05); }
.event-card.registration { border-left-color: #f59e0b; background: rgba(245,158,11,0.05); }
.event-card.deadline { border-left-color: #8b5cf6; background: rgba(139,92,246,0.05); }

.event-title {
    font-weight: 700;
    font-size: 14px;
    margin-bottom: 4px;
}

.event-desc {
    font-size: 12px;
    color: var(--text-muted);
    margin-bottom: 4px;
}

.event-date {
    font-size: 11px;
    color: #f59e0b;
}

.no-events {
    text-align: center;
    padding: 30px;
    color: var(--text-muted);
    font-size: 14px;
}

/* Responsive */
@media (max-width: 768px) {
    .calendar-cell {
        min-height: 38px;
        padding: 3px 4px;
    }
    .cell-day {
        font-size: 11px;
    }
    .cell-dot {
        width: 4px;
        height: 4px;
    }
    .notification-banner {
        padding: 10px 14px;
        flex-wrap: wrap;
    }
    .notif-btn {
        font-size: 12px;
        padding: 6px 14px;
    }
    .notif-btn-small {
        margin-left: 0;
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .calendar-cell {
        min-height: 32px;
        padding: 2px 3px;
    }
    .cell-day {
        font-size: 10px;
        min-width: 16px;
    }
    .cell-dot {
        width: 3px;
        height: 3px;
    }
    .weekday-compact {
        font-size: 9px;
        padding: 4px;
    }
    .current-month {
        font-size: 15px;
    }
    .nav-btn {
        padding: 3px 10px;
        font-size: 11px;
    }
    .calendar-legend-compact {
        gap: 8px;
        padding: 8px 10px;
    }
    .legend-item {
        font-size: 9px;
    }
    .legend-color {
        width: 8px;
        height: 8px;
    }
}
</style>

<script>
// ========== EVENT MODAL ==========
const eventsData = <?php echo json_encode($eventsByDate); ?>;

function showEvents(date) {
    const modal = document.getElementById('eventModal');
    const modalBody = document.getElementById('modalBody');
    const modalDate = document.getElementById('modalDate');
    
    const dateObj = new Date(date);
    const formattedDate = dateObj.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    modalDate.textContent = formattedDate;
    
    const dayEvents = eventsData[date] || [];
    
    if (dayEvents.length === 0) {
        modalBody.innerHTML = '<div class="no-events">No events scheduled for this day</div>';
    } else {
        let html = '';
        dayEvents.forEach(event => {
            let typeClass = event.event_type;
            html += `
                <div class="event-card ${typeClass}">
                    <div class="event-title">${escapeHtml(event.title)}</div>
                    <div class="event-desc">${escapeHtml(event.description || 'No additional details')}</div>
                    <div class="event-date"><i class="far fa-clock"></i> All Day</div>
                </div>
            `;
        });
        modalBody.innerHTML = html;
    }
    
    modal.classList.add('active');
}

function closeModal() {
    document.getElementById('eventModal').classList.remove('active');
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.getElementById('eventModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

// ========== NOTIFICATION SYSTEM ==========
document.getElementById('enableNotifications').addEventListener('click', function() {
    enableNotifications(this);
});

document.getElementById('enableNotifSmall').addEventListener('click', function() {
    enableNotifications(document.getElementById('enableNotifications'));
});

function enableNotifications(btn) {
    if ('Notification' in window) {
        Notification.requestPermission().then(permission => {
            if (permission === 'granted') {
                btn.innerHTML = '<i class="fas fa-check"></i> Notifications On';
                btn.style.background = '#10b981';
                showNotif('Notifications Enabled', 'You will now receive reminders for upcoming events!');
            } else {
                alert('Please allow notifications to get event reminders.');
            }
        });
    } else {
        alert('Your browser does not support notifications.');
    }
}

function showNotif(title, body) {
    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification(title, { body: body, icon: 'https://via.placeholder.com/64' });
    }
}

// ========== CHECK FOR TODAY'S EVENTS ==========
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date();
    const todayKey = today.getFullYear() + '-' + 
                     String(today.getMonth() + 1).padStart(2, '0') + '-' + 
                     String(today.getDate()).padStart(2, '0');
    
    const todayEvents = eventsData[todayKey] || [];
    
    if (todayEvents.length > 0 && Notification.permission === 'granted') {
        const eventNames = todayEvents.map(e => e.title).join(', ');
        showNotif('📅 Today\'s Events', 'You have ' + todayEvents.length + ' event(s) today: ' + eventNames);
    }
});
</script>

<?php include 'includes/footer.php'; ?>