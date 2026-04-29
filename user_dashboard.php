<?php
// user_dashboard.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require_once 'db_connect.php';

$user_id = $_SESSION['user_id'];

$my_lands = $pdo->prepare("
    SELECT l.* FROM land l
    JOIN tax t ON l.land_id = t.land_id
    WHERE t.owner_user_id = ?
    GROUP BY l.land_id
");

$my_lands->execute([$user_id]);
$lands = $my_lands->fetchAll();

$my_disputes = $pdo->prepare("SELECT * FROM dispute WHERE raised_by_user = ?");
$my_disputes->execute([$user_id]);
$disputes = $my_disputes->fetchAll();

include 'header.php';
?>

<div class="header-section" style="margin-bottom: 2rem;">
    <h1>Citizen Dashboard</h1>
    <p class="text-secondary">Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?></p>
</div>

<div class="grid grid-cols-2">
    <div>
        <h2>My Properties</h2>
        <div class="card">
            <?php if (count($lands) > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Parcel</th>
                                <th>Type</th>
                                <th>Location</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($lands as $l): ?>
                            <tr>
                                <td><?= htmlspecialchars($l['parcel_number']) ?></td>
                                <td><span class="badge badge-info"><?= ucfirst($l['land_type']) ?></span></td>
                                <td><?= htmlspecialchars($l['location']) ?></td>
                                <td><a href="land_lookup.php?id=<?= $l['land_id'] ?>" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">View</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-secondary">No properties linked to your account.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div>
        <h2>My Disputes</h2>
        <div class="card">
            <?php if (count($disputes) > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Date Filed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($disputes as $d): ?>
                            <tr>
                                <td><?= ucfirst($d['dispute_type']) ?></td>
                                <td>
                                    <?php 
                                        $badge = 'badge-warning';
                                        if($d['status'] == 'resolved') $badge = 'badge-success';
                                        if($d['status'] == 'rejected' || $d['status'] == 'dismissed') $badge = 'badge-danger';
                                    ?>
                                    <span class="badge <?= $badge ?>"><?= ucfirst($d['status']) ?></span>
                                </td>
                                <td><?= date('Y-m-d', strtotime($d['filed_date'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-secondary">You have no active disputes.</p>
            <?php endif; ?>
            <div style="margin-top: 1rem;">
                <a href="disputes.php?action=new" class="btn btn-primary btn-block">File New Dispute</a>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
