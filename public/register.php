<?php
session_start();
$message = "";

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $full_name = trim($_POST['full_name']);
    $reg_number = trim($_POST['reg_number']);
    $phone_number = trim($_POST['phone_number']);
    $gender = $_POST['gender'];

    try {
        $db = new PDO("mysql:host=localhost;dbname=cbe_hostel_db;charset=utf8", "root", "");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check kama username au reg number tayari vimeshatumika
        $check = $db->prepare("SELECT user_id FROM users WHERE LOWER(username) = LOWER(:user)");
        $check->execute([':user' => $username]);
        
        if ($check->fetch()) {
            $message = "<div class='alert danger'>Username hiyo tayari imetumika!</div>";
        } else {
            // 1. Ingiza kwenye Table ya Users (Role_id = 2 kwa ajili ya mwanafunzi)
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO users (username, password, role_id) VALUES (:user, :pass, 2)");
            $stmt->execute([':user' => $username, ':pass' => $hashed_password]);
            $user_id = $db->lastInsertId();

            // 2. Ingiza kwenye Table ya Student Profiles
            $stmt2 = $db->prepare("INSERT INTO student_profiles (user_id, full_name, reg_number, gender, phone_number) VALUES (:uid, :fname, :rnum, :gen, :phone)");
            $stmt2->execute([
                ':uid' => $user_id,
                ':fname' => $full_name,
                ':rnum' => $reg_number,
                ':gen' => $gender,
                ':phone' => $phone_number
            ]);

            $message = "<div class='alert success'>Akaunti imetengenezwa! <a href='index.php'>Bofya hapa Kulogin</a></div>";
        }
    } catch (PDOException $e) {
        $message = "<div class='alert danger'>Hitilafu ya Mfumo: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usajili wa Mwanafunzi - CBE Hostel</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; box-sizing: border-box; }
        .register-card { background: white; padding: 35px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); width: 100%; max-width: 450px; border-top: 5px solid #28a745; }
        h2 { text-align: center; color: #333; margin-bottom: 5px; font-size: 24px; font-weight: 600; }
        p.subtitle { text-align: center; color: #666; font-size: 14px; margin-bottom: 25px; }
        
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: #444; text-transform: uppercase; letter-spacing: 0.5px; }
        input, select { width: 100%; padding: 11px; border: 1px solid #cccccc; border-radius: 5px; box-sizing: border-box; font-size: 14px; background-color: #fafafa; transition: all 0.3s ease; }
        input:focus, select:focus { border-color: #28a745; background-color: #fff; outline: none; box-shadow: 0 0 5px rgba(40,167,69,0.2); }
        
        .row-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        
        button { width: 100%; padding: 13px; background-color: #28a745; color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; font-size: 16px; margin-top: 15px; transition: background 0.2s ease; }
        button:hover { background-color: #218838; }
        
        .alert { padding: 12px; border-radius: 5px; font-size: 14px; font-weight: bold; text-align: center; margin-bottom: 20px; }
        .alert.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert.danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert a { color: #155724; text-decoration: underline; }
        
        .link-login { text-align: center; margin-top: 25px; font-size: 14px; color: #555; }
        .link-login a { color: #0056b3; text-decoration: none; font-weight: bold; }
        .link-login a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="register-card">
    <h2>Fomu ya Usajili</h2>
    <p class="subtitle">Unda akaunti yako ya mfumo wa hosteli CBE</p>
    
    <?php echo $message; ?>

    <form action="register.php" method="POST" autocomplete="off">
        
        <div class="form-group">
            <label>Majina Kamili</label>
            <input type="text" name="full_name" placeholder="Mfano: Salumu Juma Amani" required>
        </div>

        <div class="form-group">
            <label>Namba ya Usajili (Reg No)</label>
            <input type="text" name="reg_number" placeholder="Mfano: 03.7678.01.01.2024" required>
        </div>

        <div class="row-grid">
            <div class="form-group">
                <label>Namba ya Simu</label>
                <input type="text" name="phone_number" placeholder="Mfano: 0617175328" required>
            </div>
            <div class="form-group">
                <label>Jinsia</label>
                <select name="gender" required>
                    <option value="Kiume">Kiume</option>
                    <option value="Kike">Kike</option>
                </select>
            </div>
        </div>

        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

        <div class="form-group">
            <label>Username ya Login</label>
            <input type="text" name="username" placeholder="Unda jina la kuingilia (Mfn: salumu)" autocomplete="new-username" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Unda neno la siri la mfumo" autocomplete="new-password" required>
        </div>

        <button type="submit" name="register">SAJILI AKAUNTI YAKO</button>
    </form>

    <div class="link-login">
        Tayari una akaunti? <a href="index.php">Ingia Hapa (Login)</a>
    </div>
</div>

</body>
</html>