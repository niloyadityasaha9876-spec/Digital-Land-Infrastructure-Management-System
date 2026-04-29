<?php
require_once 'db_connect.php';

$message = "";
$error = "";
$history = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["record_transfer"])) {
    $land_id = $_POST["land_id"];
    $from_user_id = $_POST["from_user_id"];
    $to_user_id = $_POST["to_user_id"];
    $transaction_type = $_POST["transaction_type"];
    $transaction_date = $_POST["transaction_date"];
    $amount = $_POST["amount"];
    $deed_number = $_POST["deed_number"];
    $approved_by = $_POST["approved_by"];

    try {
        $pdo->beginTransaction();

        $checkLand = $pdo->prepare("SELECT * FROM land WHERE land_id = ?");
        $checkLand->execute([$land_id]);

        if ($checkLand->rowCount() == 0) {
            throw new Exception("Land record not found.");
        }

        $ownerCheck = $pdo->prepare("
            SELECT to_user_id
            FROM land_transaction
            WHERE land_id = ?
            ORDER BY approved_at DESC, transaction_id DESC
            LIMIT 1
        ");
        $ownerCheck->execute([$land_id]);
        $latestOwner = $ownerCheck->fetch(PDO::FETCH_ASSOC);

        if ($latestOwner && $latestOwner["to_user_id"] != $from_user_id) {
            throw new Exception("Transfer failed. Seller is not the current owner.");
        }

        $insert = $pdo->prepare("
            INSERT INTO land_transaction
            (
                land_id,
                from_user_id,
                to_user_id,
                transaction_type,
                transaction_date,
                amount,
                deed_number,
                approved_by
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $insert->execute([
            $land_id,
            $from_user_id,
            $to_user_id,
            $transaction_type,
            $transaction_date,
            $amount,
            $deed_number,
            $approved_by
        ]);

        $transaction_id = $pdo->lastInsertId();

        $updateLand = $pdo->prepare("
            UPDATE land 
            SET current_status = 'transferred'
            WHERE land_id = ?
        ");
        $updateLand->execute([$land_id]);

        $historyText = "Land transferred from user ID $from_user_id to user ID $to_user_id. Deed number: $deed_number.";

        $landHistory = $pdo->prepare("
            INSERT INTO land_history
            (
                land_id,
                event_type,
                description,
                event_date,
                recorded_by_admin
            )
            VALUES (?, 'transaction', ?, ?, ?)
        ");

        $landHistory->execute([
            $land_id,
            $historyText,
            $transaction_date,
            $approved_by
        ]);

        $audit = $pdo->prepare("
            INSERT INTO audit_log
            (
                table_name,
                action,
                record_id,
                admin_id,
                remarks
            )
            VALUES ('land_transaction', 'INSERT', ?, ?, ?)
        ");

        $audit->execute([
            $transaction_id,
            $approved_by,
            "New land ownership transfer recorded."
        ]);

        $pdo->commit();
        $message = "Land transfer recorded successfully.";

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["view_history"])) {
    $land_id = $_POST["history_land_id"];
}

if (!empty($land_id)) {
    $sql = "
        SELECT
            lt.transaction_id,
            lt.transaction_type,
            lt.transaction_date,
            lt.amount,
            lt.deed_number,
            fu.full_name AS from_owner,
            tu.full_name AS to_owner,
            a.full_name AS approved_by,
            lt.approved_at
        FROM land_transaction lt
        INNER JOIN user fu ON lt.from_user_id = fu.user_id
        INNER JOIN user tu ON lt.to_user_id = tu.user_id
        LEFT JOIN admin a ON lt.approved_by = a.admin_id
        WHERE lt.land_id = ?
        ORDER BY lt.transaction_date ASC, lt.transaction_id ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$land_id]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<?php include 'header.php'; ?>

<div class="header-section" style="margin-bottom: 2rem;">
    <div class="card">
        <h2 style="color:white;">Land Transaction & Ownership History</h2>

        <?php if ($message): ?>
            <p style="color:#10b981;"><?php echo $message; ?></p>
        <?php endif; ?>

        <?php if ($error): ?>
            <p style="color:#ef4444;"><?php echo $error; ?></p>
        <?php endif; ?>

        <h3 style="color:white;">Record Land Transfer</h3>

        <form method="POST">
            <div class="form-group">
                <label>Land ID</label>
                <input type="number" name="land_id" required>
            </div>

            <div class="form-group">
                <label>From User ID</label>
                <input type="number" name="from_user_id" required>
            </div>

            <div class="form-group">
                <label>To User ID</label>
                <input type="number" name="to_user_id" required>
            </div>

            <div class="form-group">
                <label>Transaction Type</label>
                <select name="transaction_type" required>
                    <option value="sale">Sale</option>
                    <option value="inheritance">Inheritance</option>
                    <option value="gift">Gift</option>
                    <option value="court_order">Court Order</option>
                </select>
            </div>

            <div class="form-group">
                <label>Transaction Date</label>
                <input type="date" name="transaction_date" required>
            </div>

            <div class="form-group">
                <label>Amount</label>
                <input type="number" name="amount" step="0.01" required>
            </div>

            <div class="form-group">
                <label>Deed Number</label>
                <input type="text" name="deed_number" required>
            </div>

            <div class="form-group">
                <label>Approved By Admin ID</label>
                <input type="number" name="approved_by" required>
            </div>

            <button type="submit" name="record_transfer" class="btn btn-primary">
                Record Transfer
            </button>
        </form>

        <hr style="margin:30px 0; border-color:#334155;">

        <h3 style="color:white;">View Ownership History</h3>

        <form method="POST">
            <div class="form-group">
                <label>Land ID</label>
                <input type="number" name="history_land_id" required>
            </div>

            <button type="submit" name="view_history" class="btn btn-primary">
                View History
            </button>
        </form>

        <?php if (!empty($land_id) && empty($history)): ?>
            <p style="color:#f59e0b; margin-top:20px;">No ownership history found for this land.</p>
        <?php endif; ?>

        <?php if (!empty($history)): ?>
            <div style="margin-top:25px; color:white;">
                <h3>Ownership Chain</h3>

                <?php foreach ($history as $row): ?>
                    <div class="card" style="margin-bottom:15px;">
                        <p><strong>Transaction ID:</strong> <?php echo $row['transaction_id']; ?></p>
                        <p><strong>Type:</strong> <?php echo $row['transaction_type']; ?></p>
                        <p><strong>From Owner:</strong> <?php echo $row['from_owner']; ?></p>
                        <p><strong>To Owner:</strong> <?php echo $row['to_owner']; ?></p>
                        <p><strong>Amount:</strong> <?php echo $row['amount']; ?></p>
                        <p><strong>Deed Number:</strong> <?php echo $row['deed_number']; ?></p>
                        <p><strong>Date:</strong> <?php echo $row['transaction_date']; ?></p>
                        <p><strong>Approved By:</strong> <?php echo $row['approved_by'] ?? 'N/A'; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>


<?php include 'footer.php'; ?>