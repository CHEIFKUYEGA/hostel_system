<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: index.php");
    exit();
}

$message = "";

try {
    $db = new PDO("mysql:host=localhost;dbname=cbe_hostel_db;charset=utf8", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// 1. SHUGHULIKIA KUONGEZA HOSTELI NA KUPANDISHA PICHA (FILE UPLOAD)
if (isset($_POST['add_hostel'])) {
    $hostel_name = trim($_POST['hostel_name']);
    $gender_allowed = $_POST['gender_allowed'];
    $image_name = "https://images.unsplash.com/photo-1555854877-bab0e564b8d5?q=80&w=500"; // Picha ya default

    // Kama admin amechagua picha kutoka kwenye PC
    if (isset($_FILES['hostel_image']) && $_FILES['hostel_image']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES["hostel_image"]["name"], PATHINFO_EXTENSION);
        $new_filename = "hostel_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;

        // Ruhusu picha tu
        $allowed_types = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array(strtolower($file_extension), $allowed_types)) {
            if (move_uploaded_file($_FILES["hostel_image"]["tmp_name"], $target_file)) {
                $image_name = $target_file; // Hifadhi path ya folda letu la uploads
            }
        } else {
            $message = "<div class='alert danger'>Aina ya picha hairuhusiwi! Tumia JPG, PNG au WEBP.</div>";
        }
    }

    if (!empty($hostel_name) && strpos($message, 'danger') === false) {
        try {
            $stmt = $db->prepare("INSERT INTO hostels (hostel_name, gender_allowed, hostel_image) VALUES (:hname, :gender, :img)");
            $stmt->execute([
                ':hname' => $hostel_name,
                ':gender' => $gender_allowed,
                ':img' => $image_name
            ]);
            $message = "<div class='alert success'>Hosteli na Picha halisi zimehifadhiwa kikamilifu!</div>";
        } catch (PDOException $e) {
            $message = "<div class='alert danger'>Hitilafu: Jina la Hosteli tayari lipo!</div>";
        }
    }
}

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    try {
        $stmt = $db->prepare("DELETE FROM hostels WHERE hostel_id = :id");
        $stmt->execute([':id' => $delete_id]);
        $message = "<div class='alert success'>Hosteli imefutwa!</div>";
    } catch (PDOException $e) {
        $message = "<div class='alert danger'>Haiwezi kufuta Hosteli inayomiliki vyumba!</div>";
    }
}

$hostels = $db->query("SELECT h.*, (SELECT COUNT(*) FROM rooms r WHERE r.hostel_id = h.hostel_id) AS total_rooms FROM hostels h")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <title>Simamia Hosteli - CBE Admin</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 0; }
        .navbar { background-color: #212529; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; background: #dc3545; padding: 6px 12px; border-radius: 4px; font-weight: bold; font-size: 14px; }
        .container { padding: 30px; max-width: 1300px; margin: 0 auto; }
        .menu-card { background: #fff; padding: 15px; margin-bottom: 20px; border-radius: 6px; display: flex; gap: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .menu-card a { text-decoration: none; background: #0056b3; color: white; padding: 10px 20px; border-radius: 4px; font-weight: bold; font-size: 14px; }
        .menu-card a.active { background: #212529; }
        .alert { padding: 12px; margin-bottom: 20px; border-radius: 4px; font-weight: bold; text-align: center; }
        .alert.success { background-color: #d4edda; color: #155724; }
        .alert.danger { background-color: #f8d7da; color: #721c24; }
        .main-content { display: flex; gap: 20px; align-items: flex-start; }
        .left-side { flex: 1; }
        .right-side { flex: 2; }
        .card { background: white; padding: 20px; border-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-top: 4px solid #0056b3; margin-bottom: 20px; }
        .card h2 { margin-top: 0; font-size: 16px; color: #333; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 8px; }
        label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: bold; color: #555; }
        input, select { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-submit { background-color: #28a745; color: white; border: none; padding: 10px; border-radius: 4px; font-weight: bold; cursor: pointer; width: 100%; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; font-size: 13px; }
        th { background-color: #f8f9fa; color: #555; }
        .btn-delete { background-color: #dc3545; color: white; text-decoration: none; padding: 5px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .img-preview { width: 60px; height: 45px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; }
    </style>
</head>
<body>

<div class="navbar">
    <h2>CBE HOSTEL MANAGEMENT - SIMAMIA HOSTELI</h2>
    <a href="logout.php">LOGOUT</a>
</div>

<div class="container">
    <div class="menu-card">
        <a href="admin_dashboard.php">Dashi Bodi Kuu & Vyumba</a>
        <a href="admin_hostels.php" class="active">Simamia Hosteli (Ongeza/Kutoa Hosteli)</a>
    </div>

    <?php echo $message; ?>

    <div class="main-content">
        <div class="left-side">
            <div class="card">
                <h2>Ongeza Hosteli / Jengo Kipya</h2>
                <!-- Tumeongeza enctype="multipart/form-data" ili faili lipande -->
                <form action="admin_hostels.php" method="POST" enctype="multipart/form-data">
                    <label>Jina la Hosteli</label>
                    <input type="text" name="hostel_name" placeholder="Mfano: Jengo A - Kiume" required>

                    <label>Jinsia Inayoruhusiwa</label>
                    <select name="gender_allowed">
                        <option value="Kiume">Kiume</option>
                        <option value="Kike">Kike</option>
                    </select>

                    <label>Pakia Picha ya Jengo (Upload Image)</label>
                    <input type="file" name="hostel_image" accept="image/*" required>

                    <button type="submit" name="add_hostel" class="btn-submit">Hifadhi Hosteli na Picha</button>
                </form>
            </div>
        </div>

        <div class="right-side">
            <div class="card">
                <h2>Orodha ya Majengo ya Hosteli</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Picha</th>
                            <th>Jina la Hosteli</th>
                            <th>Jinsia</th>
                            <th>Vyumba</th>
                            <th>Kitendo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hostels as $h): ?>
                        <tr>
                            <td><img src="<?php echo htmlspecialchars($h['hostel_image']); ?>" class="img-preview"></td>
                            <td><strong><?php echo htmlspecialchars($h['hostel_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($h['gender_allowed']); ?></td>
                            <td><strong><?php echo $h['total_rooms']; ?></strong> vyumba</td>
                            <td>
                                <a href="admin_hostels.php?delete_id=<?php echo $h['hostel_id']; ?>" class="btn-delete" onclick="return confirm('Futa jengo hili?');">Futa</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>