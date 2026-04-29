<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'db_connect.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'new_infrastructure') {
    $parcel = $_POST['parcel_number'] ?? '';
    $name = $_POST['asset_name'] ?? '';
    $type = $_POST['asset_type'] ?? '';
    $date = $_POST['construction_date'] ?? '';
    $cond = $_POST['condition_status'] ?? '';
    $owner = $_POST['owner_entity'] ?? '';
    
    $stmt = $pdo->prepare("SELECT land_id FROM land WHERE parcel_number = ?");
    $stmt->execute([$parcel]);
    $land = $stmt->fetch();
    
    if($land) {
        try {
            $pdo->prepare("INSERT INTO infrastructure (land_id, asset_name, asset_type, construction_date, condition_status, owner_entity) VALUES (?, ?, ?, ?, ?, ?)")
                ->execute([$land['land_id'], $name, $type, $date, $cond, $owner]);
            $message = "Infrastructure asset recorded.";
        } catch(Exception $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    } else {
        $error = "Parcel not found.";
    }
}

$sql = "SELECT i.*, l.parcel_number 
                       FROM infrastructure i 
                       JOIN land l ON i.land_id = l.land_id 
                       ORDER BY i.asset_id DESC LIMIT 50";
$assets = $pdo->query($sql)->fetchAll();

include 'header.php';
?>

<div class="header-section" style="margin-bottom: 2rem;">
    <h1>Infrastructure Management</h1>
    <p class="text-secondary">Register and classify assets like buildings, roads, and utilities.</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="grid grid-cols-2">
    <div>
        <h2>Register Asset</h2>
        <div class="card">
            <form method="POST" action="">
                <input type="hidden" name="action" value="new_infrastructure">
                <div class="form-group">
                    <label>Land Parcel</label>
                    <input type="text" name="parcel_number" required>
                </div>
                <div class="form-group">
                    <label>Asset Name</label>
                    <input type="text" name="asset_name" required>
                </div>
                <div class="grid grid-cols-2">
                    <div class="form-group">
                        <label>Asset Type</label>
                        <select name="asset_type">
                            <option value="building">Building</option>
                            <option value="road">Road</option>
                            <option value="drainage">Drainage</option>
                            <option value="bridge">Bridge</option>
                            <option value="utility">Utility</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Condition</label>
                        <select name="condition_status">
                            <option value="excellent">Excellent</option>
                            <option value="good">Good</option>
                            <option value="fair">Fair</option>
                            <option value="poor">Poor</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2">
                    <div class="form-group">
                        <label>Construction Date</label>
                        <input type="date" name="construction_date">
                    </div>
                    <div class="form-group">
                        <label>Owner/Governing Entity</label>
                        <input type="text" name="owner_entity">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Register Asset</button>
            </form>
        </div>
    </div>
    
    <div>
        <h2>Registered Assets</h2>
        <div class="card log-card">
            <?php if (count($assets) > 0): ?>
                <table style="font-size:0.8rem;">
                    <thead>
                        <tr>
                            <th>Parcel</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Condition</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($assets as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['parcel_number']) ?></td>
                            <td><?= htmlspecialchars($a['asset_name']) ?></td>
                            <td><?= ucfirst($a['asset_type']) ?></td>
                            <td>
                                <?php 
                                    $b = 'badge-info';
                                    if($a['condition_status'] === 'critical') $b = 'badge-danger';
                                    if($a['condition_status'] === 'poor') $b = 'badge-warning';
                                    if($a['condition_status'] === 'excellent') $b = 'badge-success';
                                ?>
                                <span class="badge <?= $b ?>"><?= ucfirst($a['condition_status']) ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-secondary">No infrastructure assets found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
