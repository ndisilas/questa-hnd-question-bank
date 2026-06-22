<?php
require_once 'includes/config.php';
requireLogin();

$pageTitle = 'Financial Status';
$showSidebar = true;

$currentUser = getCurrentUser($pdo);

// Get student's financial record
$stmt = $pdo->prepare("
    SELECT * FROM financial_records WHERE student_id = ?
");
$stmt->execute([$currentUser['id']]);
$financial = $stmt->fetch();

// If no record exists, create one with default values
if (!$financial) {
    // Check if table exists
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'financial_records'");
    if ($tableCheck->rowCount() == 0) {
        // Create table if it doesn't exist
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
    }
    
    // Insert default record
    $stmt = $pdo->prepare("
        INSERT INTO financial_records (student_id, total_fees, paid, balance, status, deadline) 
        VALUES (?, 350000, 0, 350000, 'owing', '2025-06-30')
    ");
    $stmt->execute([$currentUser['id']]);
    
    // Fetch the new record
    $stmt = $pdo->prepare("SELECT * FROM financial_records WHERE student_id = ?");
    $stmt->execute([$currentUser['id']]);
    $financial = $stmt->fetch();
}

$totalFees = $financial['total_fees'] ?? 350000;
$paid = $financial['paid'] ?? 0;
$balance = $financial['balance'] ?? 350000;
$status = $financial['status'] ?? 'owing';
$deadline = $financial['deadline'] ?? '2025-06-30';

// Calculate payment percentage
$percentage = $totalFees > 0 ? round(($paid / $totalFees) * 100) : 0;

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="fas fa-coins" style="color: #f59e0b;"></i> Financial Status</h1>
        <p>View your fee payment status and outstanding balance</p>
    </div>

    <div class="financial-summary-grid">
        <!-- Main Financial Card -->
        <div class="financial-card main-card">
            <div class="financial-header">
                <div class="financial-header-left">
                    <span class="financial-label">Total Fees</span>
                    <span class="financial-amount"><?php echo number_format($totalFees, 0, ',', ' '); ?> FCFA</span>
                </div>
                <div class="status-badge <?php echo $status == 'paid' ? 'paid' : 'owing'; ?>">
                    <?php echo $status == 'paid' ? '✅ Paid' : '⚠️ Owing'; ?>
                </div>
            </div>

            <div class="progress-section">
                <div class="progress-info">
                    <span>Payment Progress</span>
                    <span><?php echo $percentage; ?>%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $percentage; ?>%;"></div>
                </div>
            </div>

            <div class="financial-breakdown">
                <div class="breakdown-item">
                    <span class="breakdown-label">Amount Paid</span>
                    <span class="breakdown-value paid"><?php echo number_format($paid, 0, ',', ' '); ?> FCFA</span>
                </div>
                <div class="breakdown-item">
                    <span class="breakdown-label">Balance</span>
                    <span class="breakdown-value <?php echo $balance > 0 ? 'owing' : 'clear'; ?>">
                        <?php echo number_format($balance, 0, ',', ' '); ?> FCFA
                    </span>
                </div>
                <div class="breakdown-item">
                    <span class="breakdown-label">Deadline</span>
                    <span class="breakdown-value"><?php echo date('F j, Y', strtotime($deadline)); ?></span>
                </div>
            </div>

            <?php if ($balance > 0): ?>
                <div class="fee-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>Outstanding Balance</strong>
                        <p>You have an outstanding balance of <strong><?php echo number_format($balance, 0, ',', ' '); ?> FCFA</strong>. 
                        Please make payment before the deadline to avoid penalties.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="fee-success">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Fully Paid</strong>
                        <p>Your fees are fully paid. You are in good standing.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Payment History / Quick Stats -->
        <div class="financial-side-cards">
            <div class="financial-card side-card">
                <h4><i class="fas fa-chart-pie"></i> Payment Summary</h4>
                <div class="payment-stats">
                    <div class="payment-stat">
                        <span class="stat-number" style="color: #10b981;"><?php echo number_format($paid, 0, ',', ' '); ?></span>
                        <span class="stat-label">Paid</span>
                    </div>
                    <div class="payment-stat">
                        <span class="stat-number" style="color: <?php echo $balance > 0 ? '#ef4444' : '#10b981'; ?>;">
                            <?php echo number_format($balance, 0, ',', ' '); ?>
                        </span>
                        <span class="stat-label">Balance</span>
                    </div>
                    <div class="payment-stat">
                        <span class="stat-number" style="color: #f59e0b;"><?php echo $percentage; ?>%</span>
                        <span class="stat-label">Complete</span>
                    </div>
                </div>
            </div>

            <div class="financial-card side-card">
                <h4><i class="fas fa-info-circle"></i> Payment Information</h4>
                <ul class="payment-info-list">
                    <li><i class="fas fa-university"></i> Bank: <strong>Ecobank Cameroon</strong></li>
                    <li><i class="fas fa-hashtag"></i> Account: <strong>0012345678</strong></li>
                    <li><i class="fas fa-user"></i> Name: <strong>HIPTEX Bertoua</strong></li>
                    <li><i class="fas fa-envelope"></i> Reference: <strong><?php echo htmlspecialchars($currentUser['matricule']); ?></strong></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Recent Payments (Placeholder) -->
    <div class="financial-card recent-payments">
        <h4><i class="fas fa-clock"></i> Recent Payments</h4>
        <?php if ($paid > 0): ?>
            <table class="payment-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo date('M d, Y'); ?></td>
                        <td>Tuition Fee Payment</td>
                        <td><?php echo number_format($paid, 0, ',', ' '); ?> FCFA</td>
                        <td><span class="status-badge paid" style="font-size: 10px;">Confirmed</span></td>
                    </tr>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-payments">
                <i class="fas fa-receipt"></i>
                <p>No payments recorded yet.</p>
            </div>
        <?php endif; ?>
    </div>
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

/* Financial Summary Grid */
.financial-summary-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 20px;
    margin-bottom: 24px;
}

.financial-card {
    background: var(--bg-secondary);
    border-radius: 16px;
    padding: 24px;
    border: 1px solid var(--border-color);
}

.main-card {
    position: relative;
    overflow: hidden;
}

.main-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(245,158,11,0.03) 0%, transparent 70%);
    border-radius: 50%;
}

.financial-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.financial-label {
    display: block;
    font-size: 13px;
    color: var(--text-muted);
    margin-bottom: 4px;
}

.financial-amount {
    display: block;
    font-size: 32px;
    font-weight: 800;
    color: var(--text-primary);
}

.status-badge {
    padding: 6px 16px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 600;
}

.status-badge.paid {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.owing {
    background: #fee2e2;
    color: #dc2626;
}

/* Progress Bar */
.progress-section {
    margin-bottom: 20px;
}

.progress-info {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
    color: var(--text-muted);
    margin-bottom: 6px;
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: var(--bg-primary);
    border-radius: 10px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    border-radius: 10px;
    transition: width 0.8s ease;
}

/* Financial Breakdown */
.financial-breakdown {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 20px;
    padding: 16px;
    background: var(--bg-primary);
    border-radius: 12px;
}

.breakdown-item {
    text-align: center;
}

.breakdown-label {
    display: block;
    font-size: 11px;
    color: var(--text-muted);
    margin-bottom: 4px;
}

.breakdown-value {
    display: block;
    font-size: 18px;
    font-weight: 700;
    color: var(--text-primary);
}

.breakdown-value.paid { color: #10b981; }
.breakdown-value.owing { color: #ef4444; }
.breakdown-value.clear { color: #10b981; }

/* Fee Warning/Success */
.fee-warning, .fee-success {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px 16px;
    border-radius: 12px;
}

.fee-warning {
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
}

.fee-warning i {
    color: #d97706;
    font-size: 20px;
    margin-top: 2px;
}

.fee-warning strong {
    color: #92400e;
    display: block;
    font-size: 14px;
}

.fee-warning p {
    color: #92400e;
    font-size: 13px;
    margin: 0;
}

.fee-success {
    background: #d1fae5;
    border-left: 4px solid #10b981;
}

.fee-success i {
    color: #059669;
    font-size: 20px;
    margin-top: 2px;
}

.fee-success strong {
    color: #065f46;
    display: block;
    font-size: 14px;
}

.fee-success p {
    color: #065f46;
    font-size: 13px;
    margin: 0;
}

/* Side Cards */
.side-card h4 {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.payment-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

.payment-stat {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 22px;
    font-weight: 800;
}

.stat-label {
    display: block;
    font-size: 11px;
    color: var(--text-muted);
}

.payment-info-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.payment-info-list li {
    padding: 8px 0;
    font-size: 13px;
    color: var(--text-muted);
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    gap: 10px;
}

.payment-info-list li:last-child {
    border-bottom: none;
}

.payment-info-list i {
    width: 18px;
    color: #f59e0b;
}

/* Recent Payments */
.recent-payments h4 {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.payment-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.payment-table th {
    text-align: left;
    padding: 10px 8px;
    color: var(--text-muted);
    font-weight: 600;
    font-size: 12px;
    border-bottom: 1px solid var(--border-color);
}

.payment-table td {
    padding: 10px 8px;
    color: var(--text-primary);
    border-bottom: 1px solid var(--border-color);
}

.no-payments {
    text-align: center;
    padding: 30px;
    color: var(--text-muted);
}

.no-payments i {
    font-size: 32px;
    margin-bottom: 8px;
    opacity: 0.5;
}

/* Responsive */
@media (max-width: 992px) {
    .financial-summary-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .financial-breakdown {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    .payment-stats {
        grid-template-columns: 1fr 1fr 1fr;
    }
    .financial-amount {
        font-size: 24px;
    }
    .financial-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    .fee-warning, .fee-success {
        flex-direction: column;
    }
}

@media (max-width: 480px) {
    .payment-stats {
        grid-template-columns: 1fr;
    }
    .payment-table {
        font-size: 12px;
        display: block;
        overflow-x: auto;
    }
}
</style>

<?php include 'includes/footer.php'; ?>