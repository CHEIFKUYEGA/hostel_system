<?php
session_start();
$message = "";

if (isset($_POST['reset_password'])) {
    $reg_number = trim($_POST['reg_number']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($reg_number) || empty($new_password) || empty($confirm_password)) {
        $message = "<div class='alert danger'>Tafadhali jaza sehemu zote!</div>";
    } elseif ($new_password !== $confirm_password) {
        $message = "<div class='alert danger'>Nenosiri hazifanani! Rudia tena.</div>";
    } else {
        try {
            $db = new PDO("mysql:host=localhost;dbname=cbe_hostel_db;charset=utf8", "root", "");
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // 1. Hakiki kama huyu mwanafunzi yupo kwenye mfumo kwa kutumia Reg Number
            $stmt = $db->prepare("SELECT user_id FROM student_profiles WHERE reg_number = :reg");
            $stmt->execute([':reg' => $reg_number]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($student) {
                $user_id = $student['user_id'];
                
                // 2. Badilisha nenosiri kwenye jedwali la users (Tunai-hash kwa usalama wa hali ya juu)
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                $update_stmt = $db->prepare("UPDATE users SET password = :pass WHERE user_id = :uid");
                $update_stmt->execute([':pass' => $hashed_password, ':uid' => $user_id]);

                $message = "<div class='alert success'>Nenosiri limebadilishwa kikamilifu! <a href='index.php' style='color:#155724; font-weight:bold;'>Bofya hapa Kuingia (Login)</a></div>";
            } else {
                $message = "<div class='alert danger'>Samahani! Namba hiyo ya Usajili (Reg No) haimo kwenye mfumo.</div>";
            }
        } catch (PDOException $e) {
            $message = "<div class='alert danger'>Hitilafu ya kiufundi imetokea!</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <title>Kusahau Nenosiri - CBE Hostel Portal</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .reset-card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 400px; border-top: 5px solid #dc3545; }
        .reset-card h2 { margin-top: 0; color: #333; text-align: center; margin-bottom: 20px; font-size: 22px; }
        label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: bold; color: #555; }
        input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 14px; }
        .btn-reset { background-color: #dc3545; color: white; border: none; padding: 12px; border-radius: 4px; font-weight: bold; cursor: pointer; width: 100%; font-size: 15px; }
        .btn-reset:hover { background-color: #bd2130; }
        .alert { padding: 12px; margin-bottom: 15px; border-radius: 4px; font-weight: bold; text-align: center; font-size: 13px; }
        .alert.success { background-color: #d4edda; color: #155724; }
        .alert.danger { background-color: #f8d7da; color: #721c24; }
        .back-link { display: block; text-align: center; margin-top: 15px; font-size: 13px; color: #0056b3; text-decoration: none; }
    </style>
</head>
<body>

<div class="reset-card">
    <h2>Rejesha Nenosiri Lako</h2>
    
    <?php echo $message; ?>

    <form action="forgot_password.php" method="POST">
        <label>Namba yako ya Usajili (Registration Number)</label>
        <input type="text" name="reg_number" placeholder="Mfano: 03.7678.01.01.2024" required>

        <label>Nenosiri Jipya (New Password)</label>
        <input type="password" name="new_password" placeholder="Weka password mpya..." required>

        <label>Thibitisha Nenosiri Jipya (Confirm Password)</label>
        <input type="password" name="confirm_password" placeholder="Rudia password mpya..." required>

        <button type="submit" name="reset_password" class="btn-reset">Badilisha Nenosiri</button>
    </form>

    <a href="index.php" class="back-link">← Rudi Kwenye Login</a>
</div>

</body>
</html>