<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'db_connect.php';


$landCountSql = "SELECT COUNT(*) FROM land";
$pendingDisputeSql = "SELECT COUNT(*) FROM dispute WHERE status IN ('filed', 'under_review')";
$recentTransactionSql = "SELECT COUNT(*) FROM land_transaction WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
$recentAuditsSql = "SELECT * FROM audit_log ORDER BY log_date DESC LIMIT 5";

$stats = [
    'total_land' => $pdo->query($landCountSql)->fetchColumn(),
    'pending_disputes' => $pdo->query($pendingDisputeSql)->fetchColumn(),
    'recent_transactions' => $pdo->query($recentTransactionSql)->fetchColumn(),
];

$recent_audits = $pdo->query($recentAuditsSql)->fetchAll();

include 'header.php';
?>

<div class="header-section" style="margin-bottom: 2rem;">
    <h1>Administrator Dashboard</h1>
    <p class="text-secondary">Welcome back, <?= htmlspecialchars($_SESSION['admin_name']) ?> (<?= htmlspecialchars($_SESSION['admin_role']) ?>)</p>
</div>

<div class="grid grid-cols-3" style="margin-bottom: 2rem;">
    <div class="card">
        <h3>Total Parcels</h3>
        <p style="font-size: 2rem; font-weight: 700; color: var(--primary-color);"><?= $stats['total_land'] ?></p>
    </div>
    <div class="card">
        <h3>Pending Disputes</h3>
        <p style="font-size: 2rem; font-weight: 700; color: var(--warning-color);"><?= $stats['pending_disputes'] ?></p>
    </div>
    <div class="card">
        <h3>Recent Transactions (30d)</h3>
        <p style="font-size: 2rem; font-weight: 700; color: var(--success-color);"><?= $stats['recent_transactions'] ?></p>
    </div>
</div>

<div class="grid grid-cols-2">
    <div>
        <h2>Management Modules</h2>
        <div class="grid grid-cols-2">
            <a href="land_lookup.php" class="card" style="display: block; text-align: center;">
                <h4>Land Database</h4>
            </a>
            <a href="transactions.php" class="card" style="display: block; text-align: center;">
                <h4>Transactions</h4>
            </a>
            <a href="surveys.php" class="card" style="display: block; text-align: center;">
                <h4>Surveys</h4>
            </a>
            <a href="disputes.php" class="card" style="display: block; text-align: center;">
                <h4>Disputes</h4>
            </a>
            <a href="infrastructure.php" class="card" style="display: block; text-align: center;">
                <h4>Infrastructure</h4>
            </a>
            <a href="taxes.php" class="card" style="display: block; text-align: center;">
                <h4>Tax & Revenue</h4>
            </a>
        </div>
    </div>
    <div>
        <h2>Recent Activity Log</h2>
        <div class="card">
            <?php if (count($recent_audits) > 0): ?>
                <table style="font-size: 0.875rem;">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Table</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recent_audits as $log): ?>
                        <tr>
                            <td><span class="badge <?= $log['action'] === 'DELETE' ? 'badge-danger' : ($log['action'] === 'INSERT' ? 'badge-success' : 'badge-info') ?>"><?= $log['action'] ?></span></td>
                            <td><?= htmlspecialchars($log['table_name']) ?></td>
                            <td><?= date('M d, H:i', strtotime($log['log_date'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-secondary">No recent activity detected.</p>
            <?php endif; ?>
            <div style="margin-top: 1rem;">
                <a href="audit_log.php" class="btn btn-secondary btn-block">View Full Audit Log</a>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
