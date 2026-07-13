<?php
session_start();
$error = "";

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Bypass ya haraka kwa ajili ya Farida (Admin)
    if (strtolower($username) === 'farida' && $password === '1122') {
        $_SESSION['user_id'] = 1;
        $_SESSION['role_id'] = 1;
        $_SESSION['username'] = 'farida';
        header("Location: admin_dashboard.php");
        exit();
    }

    try {
        $db = new PDO("mysql:host=localhost;dbname=cbe_hostel_db;charset=utf8", "root", "");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $db->prepare("SELECT * FROM users WHERE LOWER(username) = LOWER(:user)");
        $stmt->execute([':user' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['username'] = $user['username'];

            if ($user['role_id'] == 1) {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            $error = "Username au Password sio sahihi!";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CBE Hostel Portal</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 350px; border-top: 5px solid #0056b3; }
        h2 { text-align: center; color: #333; margin-bottom: 20px; font-size: 22px; }
        input { width: 100%; padding: 11px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 14px; }
        button { width: 100%; padding: 12px; background-color: #0056b3; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 15px; }
        button:hover { background-color: #004085; }
        .error-msg { color: red; text-align: center; font-weight: bold; margin-bottom: 15px; font-size: 14px; }
        
        /* Tumeongeza staili kwa ajili ya link ya forgot password */
        .forgot-container { text-align: right; margin-top: -5px; margin-bottom: 20px; }
        .forgot-container a { color: #dc3545; text-decoration: none; font-size: 13px; font-weight: bold; }
        .forgot-container a:hover { text-decoration: underline; }

        .link-reg { text-align: center; margin-top: 20px; font-size: 14px; color: #555; border-top: 1px solid #eee; padding-top: 15px; }
        .link-reg a { color: #28a745; text-decoration: none; font-weight: bold; }
        .link-reg a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="login-card">
    <h2>CBE HOSTEL LOGIN</h2>
    
    <?php if(!empty($error)): ?>
        <div class="error-msg"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="index.php" method="POST" autocomplete="off">
        <input type="text" name="username" placeholder="Ingiza Username" autocomplete="new-username" required>
        <input type="password" name="password" placeholder="Ingiza Password" autocomplete="new-password" required>
        
        <div class="forgot-container">
            <a href="forgot_password.php">Umesahau Nenosiri?</a>
        </div>

        <button type="submit" name="login">INGIA PORTAL</button>
    </form>

    <div class="link-reg">
        Huna akaunti bado? <a href="register.php">Jisajili Hapa</a>
    </div>
</div>

</body>
</html>