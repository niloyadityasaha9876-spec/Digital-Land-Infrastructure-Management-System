<?php

session_start();
require_once 'db_connect.php';


if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$message = "";
$tax_details = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $land_id = $_POST['land_id'];
    $year    = $_POST['year'];

  
    $land_sql = "SELECT * FROM land WHERE land_id = ?";
    $land_stmt = $pdo->prepare($land_sql);
    $land_stmt->execute([$land_id]);
    $land = $land_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$land) {
        $message = "Land not found.";
    } else {
        
        $avg_sql = "SELECT AVG(transaction_value) AS avg_val
                    FROM (
                        SELECT transaction_value
                        FROM land_transaction
                        WHERE land_id = ?
                        ORDER BY transaction_date DESC
                        LIMIT 3
                    ) AS recent";
        $avg_stmt = $pdo->prepare($avg_sql);
        $avg_stmt->execute([$land_id]);
        $avg_row = $avg_stmt->fetch(PDO::FETCH_ASSOC);
        $avg_val = $avg_row['avg_val'] ?? 0;

        $rates = [
            'agricultural' => 0.005,
            'residential'  => 0.010,
            'commercial'   => 0.020,
            'industrial'   => 0.015,
        ];
        $rate = $rates[$land['land_use_type']] ?? 0.010;

        $tax_assessed = $avg_val * $rate;
        $due_date     = $year . '-06-30';
        $owner_id     = $land['owner_user_id'];

       
        $insert = "INSERT INTO tax
                   (land_id, owner_user_id, tax_financial_year,
                    tax_rate, tax_assessed, due_date, tax_status)
                   VALUES
                   (?, ?, ?, ?, ?, ?, 'unpaid')";
        $insert_stmt = $pdo->prepare($insert);
        $done = $insert_stmt->execute([
            $land_id,
            $owner_id,
            $year,
            $rate,
            $tax_assessed,
            $due_date
        ]);

        if ($done) {
            $message = "Tax calculated successfully!";
            $tax_details = [
                'land_type'    => $land['land_use_type'],
                'avg_value'    => number_format($avg_val, 2),
                'rate'         => ($rate * 100) . '%',
                'tax_assessed' => number_format($tax_assessed, 2),
                'due_date'     => $due_date,
            ];
        } else {
            $message = "Error: " . $pdo->errorInfo()[2];
        }
    }
}


try {
    $pdo->exec("
        UPDATE tax
        SET tax_status = 'overdue',
            last_updated = CURRENT_TIMESTAMP
        WHERE tax_status = 'unpaid'
        AND due_date < CURDATE()
    ");
} catch (PDOException $e) {
    
    error_log("Tax update error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tax Auto Estimation</title>
    <style>
        body { font-family: Arial; padding: 30px; background: #f4f4f4; }
        .box { background: white; padding: 20px;
               border-radius: 8px; max-width: 500px; margin: auto; }
        input, select { width: 100%; padding: 8px;
                        margin: 8px 0; border: 1px solid #ccc;
                        border-radius: 4px; }
        button { background: #4CAF50; color: white;
                 padding: 10px 20px; border: none;
                 border-radius: 4px; cursor: pointer; }
        .result { background: #e8f5e9; padding: 15px;
                  border-radius: 6px; margin-top: 20px; }
        .error  { background: #ffebee; padding: 15px;
                  border-radius: 6px; margin-top: 20px; }
    </style>
</head>
<body>
<div class="box">
    <h2>Tax Auto Estimation</h2>

    <form method="POST">
        <label>Land ID:</label>
        <input type="text" name="land_id" required>

        <label>Financial Year:</label>
        <input type="number" name="year"
               value="2024" min="2000" max="2099" required>

        <button type="submit">Calculate Tax</button>
    </form>

    <?php if ($message): ?>
        <div class="<?php echo $tax_details ? 'result' : 'error'; ?>">
            <strong><?php echo $message; ?></strong>

            <?php if ($tax_details): ?>
                <hr>
                <p>Land Type: <b><?php echo $tax_details['land_type'];?></b></p>
                <p>Avg Transaction Value: <b><?php echo $tax_details['avg_value'];?></b></p>
                <p>Tax Rate: <b><?php echo $tax_details['rate']; ?></b></p>
                <p>Tax Assessed: <b><?php echo $tax_details['tax_assessed'];?></b></p>
                <p>Due Date: <b><?php echo $tax_details['due_date']; ?></b></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>