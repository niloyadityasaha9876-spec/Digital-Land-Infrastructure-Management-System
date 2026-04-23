<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div style="max-width: 500px; margin: 4rem auto;">
            <div class="card">
                <h2 style="text-align: center;color:white; margin-bottom: 2rem;">Register New User</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" required>
                    </div>

                    <div class="form-group">
                        <label for="nid_number">NID Number</label>
                        <input type="text" id="nid_number" name="nid_number" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" id="phone" name="phone" required>
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Register</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

<?php
require_once "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $nid       = $_POST['nid_number'];
    $email     = $_POST['email'];
    $phone     = $_POST['phone'];
    $address   = $_POST['address'];
    $password  = $_POST['password'];

    try {
        // Insert new user
        $sql = "INSERT INTO user (full_name, nid_number, email, phone, address, is_verified, registered_at, password_hash)
                VALUES (:full_name, :nid, :email, :phone, :address, 0, NOW(), :password)";
        
        $query = $pdo->prepare($sql);
        $query->execute([
            ':full_name' => $full_name,
            ':nid'       => $nid,
            ':email'     => $email,
            ':phone'     => $phone,
            ':address'   => $address,
            ':password'  => $password   // plain text for now
        ]);

        echo "✅ Registration successful! You can now log in.";
    } catch (PDOException $e) {
        echo "❌ Registration failed: " . $e->getMessage();
    }
}
?>
