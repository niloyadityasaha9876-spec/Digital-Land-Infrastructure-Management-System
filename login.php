<?php

session_start();
require_once 'db_connect.php';

if(isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php");
    exit;
}
elseif(isset($_SESSION['user_id'])) {
    header("Location: user_dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nid = $_POST['nid_number'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['login_role'] ?? 'user';

    if(empty($nid) || empty($password)) {
        $error = "Please fill in all fields.";
    } 
    else{
        if($role === 'admin') {
            $sql = "SELECT * FROM admin WHERE nid_number = ? AND is_active = 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nid]);
            $admin = $stmt->fetch();

            if ($admin && ($password === $admin['password_hash'])) {
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_role'] = $admin['role'];
                $_SESSION['admin_name'] = $admin['full_name'];
                header("Location: admin_dashboard.php");
                exit;
            } else {
                $error = "Invalid admin credentials or account inactive.";
            }
        } else {
            $stmt = $pdo->prepare("SELECT * FROM user WHERE nid_number = ?");
            $stmt->execute([$nid]);
            $user = $stmt->fetch();

            
            if ($user && $password === $user['phone']) { 
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['full_name'];
                header("Location: user_dashboard.php");
                exit;
            } else {
                $error = "Invalid user credentials. (Hint: Use phone number as password for users)";
            }
        }
    }
}
?>

<?php include 'header.php'; ?>

<div style="max-width: 400px; margin: 4rem auto;">
    <div class="card">
        <h2 style="text-align: center; margin-bottom: 2rem;">Welcome Back</h2>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

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
                <input type="text" id="nid_number" name="nid_number" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>