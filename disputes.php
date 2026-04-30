<?php
session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'db_connect.php';

$message  = '';
$error    = '';
$is_admin = isset($_SESSION['admin_id']);


if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && $_POST['action'] === 'file_dispute'
    && !$is_admin) {

    $land_id  = $_POST['land_id'];
    $user_id  = $_SESSION['user_id'];
    $opponent = $_POST['opponent_party'];
    $type     = $_POST['dispute_type'];

    $check = $pdo->prepare("SELECT land_id FROM land WHERE land_id = ?");
    $check->execute([$land_id]);
    $land = $check->fetch();

    if (!$land) {
        $error = "Land not found.";

    } else {

        try {
            $pdo->beginTransaction();

            $pdo->prepare(
                "INSERT INTO dispute
                 (land_id, raised_by_user, opponent_party,
                  dispute_type, status, escalation_level)
                 VALUES (?, ?, ?, ?, 'filed', 0)"
            )->execute([$land_id, $user_id, $opponent, $type]);

            $pdo->prepare(
                "UPDATE land
                 SET current_status = 'frozen'
                 WHERE land_id = ?"
            )->execute([$land_id]);

            $pdo->prepare(
                "INSERT INTO land_history
                 (land_id, event_type, description, event_date)
                 VALUES (?, 'dispute_filed',
                         'Dispute filed by user.', CURDATE())"
            )->execute([$land_id]);

            $pdo->commit();
            $message = "Dispute filed successfully. Land is now frozen.";

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Database Error: " . $e->getMessage();
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && $_POST['action'] === 'resolve_dispute'
    && $is_admin) {

    $dispute_id = $_POST['dispute_id'];
    $new_status = $_POST['new_status'];
    $notes      = $_POST['resolution_notes'];
    $admin_id   = $_SESSION['admin_id'];

    // Get land_id from dispute
    $dInfo = $pdo->prepare("SELECT land_id FROM dispute WHERE dispute_id = ?");
    $dInfo->execute([$dispute_id]);
    $dispute = $dInfo->fetch();

    if ($dispute) {
        try {
            $pdo->beginTransaction();

            $pdo->prepare(
                "UPDATE dispute
                 SET status           = ?,
                     resolution_notes = ?,
                     handled_by_admin = ?,
                     last_updated     = NOW()
                 WHERE dispute_id     = ?"
            )->execute([$new_status, $notes, $admin_id, $dispute_id]);

                      if (in_array($new_status,
                ['resolved', 'dismissed', 'rejected'])) {
                $pdo->prepare(
                    "UPDATE land
                     SET current_status = 'active'
                     WHERE land_id = ?"
                )->execute([$dispute['land_id']]);
            }

            $pdo->commit();
            $message = "Dispute status updated.";

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Update Error: " . $e->getMessage();
        }
    }
}

include 'header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<?php if (!$is_admin): ?>
<div>
    <h2>File a Dispute</h2>
    <div class="card">
        <form method="POST" action="">

            <input type="hidden" name="action"
                   value="file_dispute">

            <div class="form-group">
                <label>Land ID</label>
                <input type="number" name="land_id" required>
            </div>

            <div class="form-group">
                <label>Opponent Party</label>
                <input type="text" name="opponent_party">
            </div>

            <div class="form-group">
                <label>Dispute Type</label>
                <select name="dispute_type" required>
                    <option value="ownership">Ownership</option>
                    <option value="boundary">Boundary</option>
                    <option value="mutation">Mutation</option>
                    <option value="encroachment">Encroachment</option>
                    <option value="tax">Tax</option>
                </select>
            </div>

            <button type="submit" class="btn btn-warning">
                File Formal Dispute
            </button>
        </form>
    </div>
</div>
<?php endif; ?>


<?php if ($is_admin): ?>
<div>
    <h2>Resolve a Dispute</h2>
    <div class="card">
        <form method="POST" action="">

            <input type="hidden" name="action"
                   value="resolve_dispute">

            <div class="form-group">
                <label>Dispute ID</label>
                <input type="number" name="dispute_id" required>
            </div>

            <div class="form-group">
                <label>New Status</label>
                <select name="new_status" required>
                    <option value="under_review">Under Review</option>
                    <option value="resolved">Resolved</option>
                    <option value="dismissed">Dismissed</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>

            <div class="form-group">
                <label>Resolution Notes</label>
                <input type="text" name="resolution_notes">
            </div>

            <button type="submit" class="btn btn-primary">
                Update Dispute
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

<?php include 'footer.php'; ?>

