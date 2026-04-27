<?php
require_once 'db_connect.php';

$land = null;
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $parcel_number = trim($_POST["parcel_number"]);

    $sql = "
        SELECT 
            l.*,
            u.full_name AS current_owner_name,
            u.nid_number AS current_owner_nid,
            u.phone AS current_owner_phone
        FROM land l
        LEFT JOIN land_transaction lt
            ON lt.transaction_id = (
                SELECT transaction_id
                FROM land_transaction
                WHERE land_id = l.land_id
                ORDER BY approved_at DESC, transaction_id DESC
                LIMIT 1
            )
        LEFT JOIN user u ON lt.to_user_id = u.user_id
        WHERE l.parcel_number = ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$parcel_number]);
    $land = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$land) {
        $error = "No land found with this parcel number.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Land Lookup</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container">
    <div class="card">
        <h2 style="color:white;">Land Lookup</h2>

        <form method="POST">
            <div class="form-group">
                <label>Parcel Number</label>
                <input type="text" name="parcel_number" required>
            </div>

            <button type="submit" class="btn btn-primary">Search Land</button>
        </form>

        <?php if ($error): ?>
            <p style="color:#ef4444; margin-top:20px;"><?php echo $error; ?></p>
        <?php endif; ?>

        <?php if ($land): ?>
            <div style="margin-top:25px; color:white;">
                <h3>Parcel Details</h3>
                <p><strong>Parcel Number:</strong> <?php echo $land['parcel_number']; ?></p>
                <p><strong>Area Size:</strong> <?php echo $land['area_size']; ?></p>
                <p><strong>Land Type:</strong> <?php echo $land['land_type']; ?></p>
                <p><strong>Location:</strong> <?php echo $land['location']; ?></p>
                <p><strong>Upazila:</strong> <?php echo $land['upazila']; ?></p>
                <p><strong>District:</strong> <?php echo $land['district']; ?></p>
                <p><strong>Current Status:</strong> <?php echo $land['current_status']; ?></p>

                <h3>Current Owner</h3>
                <p><strong>Name:</strong> <?php echo $land['current_owner_name'] ?? 'No owner recorded'; ?></p>
                <p><strong>NID:</strong> <?php echo $land['current_owner_nid'] ?? 'N/A'; ?></p>
                <p><strong>Phone:</strong> <?php echo $land['current_owner_phone'] ?? 'N/A'; ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>