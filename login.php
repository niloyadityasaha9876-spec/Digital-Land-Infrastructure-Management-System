 <!DOCTYPE html>
 <html lang="en">
 <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Document</title>
 </head>
 <body>
    <div class="container">
        <div style="max-width: 400px; margin: 4rem auto;">
            <div class="card">
                <h2 style="text-align: center;color:white; margin-bottom: 2rem;">Welcome Back</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="login_role">Login As</label>
                        <select name="login_role" id="login_role">
                            <option value="user">Citizen / User</option>
                            <option value="admin">Administrator / Officer</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="nid_number">NID Number</label>
                        <input type="text" id="nid_number" name="nid_number" required="">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required="">
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                </form>
            </div>
        </div>
 
    </div>
        
 </body>
 </html>
 
 <?php
    require_once "db_connect.php";
    if($_SERVER["REQUEST_METHOD"]=="POST"){
        $role = $_POST['login_role'];
        $nid = $_POST['nid_number'];
        $pass = $_POST['password'];
    }
    if ($role === "admin") {
        $table = "admin";
    } else {
        $table = "user";
    }
    try{
        $sql = "SELECT * FROM $table WHERE nid_number=:nid";
        $query = $pdo->prepare($sql);
        $query->execute([
            ':nid' => $nid,
        ]);
        $user = $query->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            if ($pass === $user['password_hash']) {
                echo"✅ Login successful! Welcome, " . htmlspecialchars($user['full_name']);
            }
            else{echo "❌ Invalid password.";}
        } else {
            echo "❌ No $role found with that NID.";
        }
    } 
    catch (PDOException $e) {
        echo "Query failed: " . $e->getMessage();
    }

 ?>
 
<?php include 'footer.php'; ?>