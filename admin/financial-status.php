<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$pageTitle = 'Student Financial Status';
$message = '';
$error = '';

// Create tables if not exists
$pdo->exec("
    CREATE TABLE IF NOT EXISTS financial_records (
        id INT PRIMARY KEY AUTO_INCREMENT,
        student_id INT NOT NULL,
        total_fees INT DEFAULT 350000,
        paid INT DEFAULT 0,
        balance INT DEFAULT 350000,
        status ENUM('paid', 'owing') DEFAULT 'owing',
        deadline DATE DEFAULT '2025-06-30',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
    )
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS payment_history (
        id INT PRIMARY KEY AUTO_INCREMENT,
        student_id INT NOT NULL,
        amount INT NOT NULL,
        type ENUM('add', 'reverse') DEFAULT 'add',
        previous_paid INT NOT NULL,
        new_paid INT NOT NULL,
        admin_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
    )
");

// Handle Add Payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_payment'])) {
    $student_id = intval($_POST['student_id']);
    $amount = intval($_POST['amount']);

    if ($student_id > 0 && $amount > 0) {
        $check = $pdo->prepare("SELECT * FROM financial_records WHERE student_id = ?");
        $check->execute([$student_id]);
        $current = $check->fetch();
        
        if ($current) {
            $new_paid = $current['paid'] + $amount;
            $new_balance = $current['total_fees'] - $new_paid;
            $new_status = $new_balance <= 0 ? 'paid' : 'owing';
            
            $stmt = $pdo->prepare("
                UPDATE financial_records 
                SET paid = ?, balance = ?, status = ?
                WHERE student_id = ?
            ");
            $stmt->execute([$new_paid, $new_balance, $new_status, $student_id]);
            
            $hist = $pdo->prepare("
                INSERT INTO payment_history (student_id, amount, type, previous_paid, new_paid, admin_id) 
                VALUES (?, ?, 'add', ?, ?, ?)
            ");
            $hist->execute([$student_id, $amount, $current['paid'], $new_paid, $_SESSION['user_id']]);
            
            $message = 'Payment added successfully!';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO financial_records (student_id, total_fees, paid, balance, status) 
                VALUES (?, 350000, ?, 350000 - ?, IF(350000 - ? <= 0, 'paid', 'owing'))
            ");
            $stmt->execute([$student_id, $amount, $amount, $amount]);
            $message = 'Payment added successfully!';
        }
    } else {
        $error = 'Please select a student and enter a valid amount.';
    }
}

// Handle Reverse Payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reverse_payment'])) {
    $student_id = intval($_POST['student_id']);
    $amount = intval($_POST['reverse_amount']);

    if ($student_id > 0 && $amount > 0) {
        $check = $pdo->prepare("SELECT * FROM financial_records WHERE student_id = ?");
        $check->execute([$student_id]);
        $current = $check->fetch();
        
        if ($current) {
            $new_paid = max(0, $current['paid'] - $amount);
            $new_balance = $current['total_fees'] - $new_paid;
            $new_status = $new_balance <= 0 ? 'paid' : 'owing';
            
            $stmt = $pdo->prepare("
                UPDATE financial_records 
                SET paid = ?, balance = ?, status = ?
                WHERE student_id = ?
            ");
            $stmt->execute([$new_paid, $new_balance, $new_status, $student_id]);
            
            $hist = $pdo->prepare("
                INSERT INTO payment_history (student_id, amount, type, previous_paid, new_paid, admin_id) 
                VALUES (?, ?, 'reverse', ?, ?, ?)
            ");
            $hist->execute([$student_id, $amount, $current['paid'], $new_paid, $_SESSION['user_id']]);
            
            $message = 'Payment reversed successfully!';
        } else {
            $error = 'No financial record found for this student.';
        }
    } else {
        $error = 'Please select a student and enter a valid amount.';
    }
}

// Get all students
$students = $pdo->query("
    SELECT u.id, u.name, u.email, u.matricule, d.name as department,
           COALESCE(f.total_fees, 350000) as total_fees,
           COALESCE(f.paid, 0) as paid,
           COALESCE(f.balance, 350000) as balance,
           f.status,
           f.deadline
    FROM users u
    LEFT JOIN departments d ON u.department_id = d.id
    LEFT JOIN financial_records f ON u.id = f.student_id
    WHERE u.role = 'student'
    ORDER BY u.name
")->fetchAll();

include 'header.php';
?>

<div class="admin-main-content">
    <h1 style="font-size: 28px; font-weight: 700; margin-bottom: 8px;">
        <i class="fas fa-coins" style="color: #f59e0b;"></i> Student Financial Status
    </h1>
    <p style="color: #64748b; margin-bottom: 24px;">
        View and manage student fee payments and outstanding balances.
    </p>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Two Forms Side by Side -->
    <div class="admin-card">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
            <!-- Add Payment -->
            <div style="background: #f0fdf4; padding: 16px; border-radius: 12px; border: 1px solid #bbf7d0;">
                <h3 style="color: #16a34a; margin-bottom: 12px;">
                    <i class="fas fa-plus-circle"></i> Add Payment
                </h3>
                <form method="POST" style="display: flex; flex-direction: column; gap: 12px;">
                    <div>
                        <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 4px;">Student</label>
                        <select name="student_id" class="form-select" required>
                            <option value="">Select Student</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['id']; ?>">
                                    <?php echo htmlspecialchars($student['name']); ?> (<?php echo $student['matricule']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 4px;">Amount (FCFA)</label>
                        <input type="number" name="amount" class="form-input" placeholder="0" min="1" required>
                    </div>
                    <button type="submit" name="update_payment" style="background: #22c55e; color: white; padding: 10px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                        <i class="fas fa-plus"></i> Add Payment
                    </button>
                </form>
            </div>

            <!-- Reverse Payment -->
            <div style="background: #fef2f2; padding: 16px; border-radius: 12px; border: 1px solid #fecaca;">
                <h3 style="color: #dc2626; margin-bottom: 12px;">
                    <i class="fas fa-minus-circle"></i> Reverse Payment
                </h3>
                <form method="POST" style="display: flex; flex-direction: column; gap: 12px;">
                    <div>
                        <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 4px;">Student</label>
                        <select name="student_id" class="form-select" required>
                            <option value="">Select Student</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['id']; ?>">
                                    <?php echo htmlspecialchars($student['name']); ?> (<?php echo $student['matricule']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 4px;">Amount to Reverse (FCFA)</label>
                        <input type="number" name="reverse_amount" class="form-input" placeholder="0" min="1" required>
                    </div>
                    <button type="submit" name="reverse_payment" style="background: #ef4444; color: white; padding: 10px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;" onclick="return confirm('Are you sure you want to reverse this payment? This action cannot be undone.')">
                        <i class="fas fa-minus"></i> Reverse Payment
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Students Table -->
    <div class="admin-card" style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Matricule</th>
                    <th>Department</th>
                    <th>Total Fees</th>
                    <th>Paid</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Deadline</th>
                </tr>
            </thead>
            <tbody>
                <?php $count = 1; foreach ($students as $student): 
                    $balance = $student['total_fees'] - $student['paid'];
                    $status = $balance <= 0 ? 'Paid' : 'Owing';
                    $statusClass = $balance <= 0 ? 'paid' : 'owing';
                ?>
                <tr>
                    <td><?php echo $count++; ?></td>
                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                    <td><?php echo htmlspecialchars($student['matricule']); ?></td>
                    <td><?php echo htmlspecialchars($student['department']); ?></td>
                    <td><?php echo number_format($student['total_fees'], 0, ',', ' '); ?> FCFA</td>
                    <td><?php echo number_format($student['paid'], 0, ',', ' '); ?> FCFA</td>
                    <td>
                        <strong style="color: <?php echo $balance > 0 ? '#dc2626' : '#10b981'; ?>">
                            <?php echo number_format($balance, 0, ',', ' '); ?> FCFA
                        </strong>
                    </td>
                    <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo $status; ?></span></td>
                    <td><?php echo date('M d, Y', strtotime($student['deadline'] ?? '2025-06-30')); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Payment History -->
    <div class="admin-card">
        <h3><i class="fas fa-history"></i> Payment History Log</h3>
        <?php
        $history = $pdo->query("
            SELECT ph.*, u.name as student_name, a.name as admin_name 
            FROM payment_history ph
            LEFT JOIN users u ON ph.student_id = u.id
            LEFT JOIN users a ON ph.admin_id = a.id
            ORDER BY ph.created_at DESC LIMIT 20
        ")->fetchAll();
        ?>
        <?php if (count($history) > 0): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Previous Paid</th>
                        <th>New Paid</th>
                        <th>Admin</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $entry): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($entry['student_name']); ?></td>
                            <td><?php echo number_format($entry['amount'], 0, ',', ' '); ?> FCFA</td>
                            <td>
                                <span style="color: <?php echo $entry['type'] == 'add' ? '#16a34a' : '#dc2626'; ?>; font-weight: 600;">
                                    <?php echo $entry['type'] == 'add' ? '➕ Added' : '➖ Reversed'; ?>
                                </span>
                            </td>
                            <td><?php echo number_format($entry['previous_paid'], 0, ',', ' '); ?> FCFA</td>
                            <td><?php echo number_format($entry['new_paid'], 0, ',', ' '); ?> FCFA</td>
                            <td><?php echo htmlspecialchars($entry['admin_name']); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($entry['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color: var(--text-muted);">No payment history yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>