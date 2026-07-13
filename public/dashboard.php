<?php
session_start();

// Ulinzi
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

try {
    $db = new PDO("mysql:host=localhost;dbname=cbe_hostel_db;charset=utf8", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database Connection failed.");
}

// 1. SHUGHULIKIA KUPANDISHA PICHA YA PROFAILI (PROFILE IMAGE UPLOAD)
if (isset($_POST['upload_profile_pic'])) {
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES["profile_pic"]["name"], PATHINFO_EXTENSION);
        $new_filename = "student_" . $user_id . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;

        $allowed_types = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array(strtolower($file_extension), $allowed_types)) {
            if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
                // Update path ya picha kwenye database
                $stmt_img = $db->prepare("UPDATE student_profiles SET profile_image = :img WHERE user_id = :uid");
                $stmt_img->execute([':img' => $target_file, ':uid' => $user_id]);
                $message = "<div class='alert success'>Picha ya profaili imesasishwa kikamilifu!</div>";
            }
        } else {
            $message = "<div class='alert danger'>Aina ya picha hairuhusiwi! Tumia JPG, PNG au WEBP.</div>";
        }
    } else {
        $message = "<div class='alert danger'>Tafadhali chagua picha kwanza!</div>";
    }
}

// 2. VUTA PROFAILI YA MWANAFUNZI
$stmt = $db->prepare("SELECT * FROM student_profiles WHERE user_id = :uid");
$stmt->execute([':uid' => $user_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);
$student_gender = $student['gender'] ?? 'Kiume';
$profile_image = !empty($student['profile_image']) ? $student['profile_image'] : 'https://www.w3schools.com/howto/img_avatar.png';

// 3. SHUGHULIKIA MAOMBI YA CHUMBA (STUDENT BOOKS ROOM)
if (isset($_GET['book_room_id'])) {
    $room_id = intval($_GET['book_room_id']);

    $check = $db->prepare("SELECT allocation_id FROM allocations WHERE user_id = :uid");
    $check->execute([':uid' => $user_id]);
    
    if ($check->fetch()) {
        $message = "<div class='alert danger'>Ushatuma ombi la chumba tayari! Huwezi kuomba mara mbili.</div>";
    } else {
        $db->beginTransaction();
        try {
            $stmt1 = $db->prepare("INSERT INTO allocations (user_id, room_id, status) VALUES (:uid, :rid, 'Pending')");
            $stmt1->execute([':uid' => $user_id, ':rid' => $room_id]);

            $control_num = "994400" . rand(100000, 999999);
            $stmt2 = $db->prepare("INSERT INTO payments (user_id, control_number, amount, status) VALUES (:uid, :cnum, 450000.00, 'Pending')");
            $stmt2->execute([':uid' => $user_id, ':cnum' => $control_num]);

            $db->commit();
            $message = "<div class='alert success'>Ombi lako limepokelewa! Tafadhali nenda kafanye malipo chini.</div>";
        } catch (Exception $e) {
            $db->rollBack();
            $message = "<div class='alert danger'>Hitilafu imetokea wakati wa kutuma ombi!</div>";
        }
    }
}

// 4. MFUMO WA SEARCH KWA AJILI YA VYUMBA VILIZOPO
$search_room = "";
$rooms_query = "SELECT r.*, h.hostel_name, h.hostel_image FROM rooms r 
                JOIN hostels h ON r.hostel_id = h.hostel_id 
                WHERE h.gender_allowed = :gender AND r.available_beds > 0";

if (isset($_GET['search_room']) && !empty(trim($_GET['search_room']))) {
    $search_room = trim($_GET['search_room']);
    $rooms_query .= " AND (r.room_number LIKE :search OR h.hostel_name LIKE :search)";
}

$stmt_rooms = $db->prepare($rooms_query);
$params = [':gender' => $student_gender];
if (!empty($search_room)) {
    $params[':search'] = "%" . $search_room . "%";
}
$stmt_rooms->execute($params);
$available_rooms = $stmt_rooms->fetchAll(PDO::FETCH_ASSOC);

// 5. VUTA TAARIFA ZA CHUMBA ALICHOPEWA NA MALIPO YAKE
$my_allocation = $db->prepare("SELECT a.status AS alloc_status, r.room_number, h.hostel_name, p.control_number, p.amount, p.status AS pay_status
                               FROM allocations a
                               JOIN rooms r ON a.room_id = r.room_id
                               JOIN hostels h ON r.hostel_id = h.hostel_id
                               LEFT JOIN payments p ON a.user_id = p.user_id
                               WHERE a.user_id = :uid");
$my_allocation->execute([':uid' => $user_id]);
$my_status = $my_allocation->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard - CBE Hostel Portal</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 0; }
        .navbar { background-color: #0056b3; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; background: #dc3545; padding: 6px 12px; border-radius: 4px; font-weight: bold; }
        .container { padding: 30px; max-width: 1200px; margin: 0 auto; }
        
        .alert { padding: 12px; margin-bottom: 20px; border-radius: 5px; font-weight: bold; text-align: center; }
        .alert.success { background-color: #d4edda; color: #155724; }
        .alert.danger { background-color: #f8d7da; color: #721c24; }

        .profile-section { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 25px; display: flex; align-items: center; gap: 20px; }
        .profile-img-container { text-align: center; }
        .profile-img { width: 100px; height: 100px; object-fit: cover; border-radius: 50%; border: 3px solid #0056b3; margin-bottom: 8px; }
        .profile-data { flex: 1; }
        .profile-data h3 { margin: 5px 0; color: #333; }
        
        .upload-box { font-size: 11px; display: flex; flex-direction: column; gap: 4px; }
        .upload-box input[type="file"] { font-size: 11px; width: 150px; }
        .btn-upload { background: #6c757d; color: white; border: none; padding: 3px 8px; border-radius: 3px; cursor: pointer; font-weight: bold; }

        .search-panel { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .search-panel form { display: flex; gap: 10px; }
        .search-panel input { flex: 1; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px; }
        .search-panel button { padding: 12px 25px; background: #0056b3; color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; }

        .hostel-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; margin-top: 15px; }
        .hostel-card { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 3px 10px rgba(0,0,0,0.05); border: 1px solid #eee; display: flex; flex-direction: column; }
        .hostel-card img { width: 100%; height: 160px; object-fit: cover; }
        .hostel-details { padding: 15px; flex: 1; display: flex; flex-direction: column; justify-content: space-between; }
        .hostel-details h4 { margin: 0 0 10px 0; font-size: 18px; color: #212529; }
        .hostel-details p { margin: 5px 0; font-size: 13px; color: #555; }
        .btn-book { display: block; text-align: center; background: #28a745; color: white; text-decoration: none; padding: 10px; border-radius: 5px; font-weight: bold; margin-top: 15px; font-size: 14px; }
        
        .status-card { background: #fff; padding: 25px; border-radius: 8px; border-top: 5px solid #ffc107; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .btn-receipt { display: inline-block; margin-top: 15px; background: #0056b3; color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px; font-weight: bold; font-size: 14px; }
    </style>
</head>
<body>

<div class="navbar">
    <h2>CBE HOSTEL PORTAL - MWANAFUNZI</h2>
    <a href="logout.php">LOGOUT</a>
</div>

<div class="container">
    
    <?php echo $message; ?>

    <div class="profile-section">
        <div class="profile-img-container">
            <img src="<?php echo htmlspecialchars($profile_image); ?>" class="profile-img" alt="Avatar">
            <form action="dashboard.php" method="POST" enctype="multipart/form-data" class="upload-box">
                <input type="file" name="profile_pic" accept="image/*" required>
                <button type="submit" name="upload_profile_pic" class="btn-upload">Badili Picha</button>
            </form>
        </div>
        <div class="profile-data">
            <h3>Mwanafunzi: <strong><?php echo htmlspecialchars($student['full_name'] ?? $_SESSION['username']); ?></strong></h3>
            <p style="margin:0; color:#666;">Reg No: <?php echo htmlspecialchars($student['reg_number'] ?? 'Bado'); ?> | Jinsia: <?php echo htmlspecialchars($student_gender); ?></p>
        </div>
        <div style="text-align: right;">
            <span style="background: #e2eafc; color: #0056b3; padding: 6px 12px; border-radius: 20px; font-weight: bold; font-size: 13px;">Kundi: <?php echo htmlspecialchars($student_gender); ?> Only</span>
        </div>
    </div>

    <?php if (!$my_status): ?>
        <div class="search-panel">
            <h3 style="margin-top:0; margin-bottom:10px; font-size:16px; color:#444;">Tafuta Chumba Kilichopo</h3>
            <form action="dashboard.php" method="GET">
                <input type="text" name="search_room" placeholder="Andika namba ya chumba au jina la jengo..." value="<?php echo htmlspecialchars($search_room); ?>">
                <button type="submit">Tafuta Vyumba</button>
                <?php if(!empty($search_room)): ?>
                    <a href="dashboard.php" style="padding:12px; background:#6c757d; color:white; text-decoration:none; border-radius:5px; font-weight:bold;">Ondoa</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="search-panel" style="background: none; padding: 0; box-shadow: none;">
            <h3 style="color:#333;">Vyumba Vilivyopo (Chagua Hosteli Unayotaka Kutokana na Picha)</h3>
            <div class="hostel-grid">
                <?php if (count($available_rooms) > 0): ?>
                    <?php foreach ($available_rooms as $room): ?>
                        <div class="hostel-card">
                            <img src="<?php echo htmlspecialchars($room['hostel_image']); ?>" alt="Hostel Image">
                            <div class="hostel-details">
                                <div>
                                    <h4><?php echo htmlspecialchars($room['hostel_name']); ?></h4>
                                    <p>Namba ya Chumba: <strong style="color:#0056b3; font-size:15px;"><?php echo htmlspecialchars($room['room_number']); ?></strong></p>
                                    <p>Uwezo: Vitanda <?php echo $room['capacity']; ?> | Vilivyobaki: <strong style="color:red;"><?php echo $room['available_beds']; ?></strong></p>
                                </div>
                                <a href="dashboard.php?book_room_id=<?php echo $room['room_id']; ?>" class="btn-book" onclick="return confirm('Je, una uhakika unataka kuomba chumba hiki?');">
                                    Omba Chumba Hiki
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #666;">Samahani! Hakuna vyumba vilivyopatikana kwa ajili ya jinsia yako kwa sasa.</p>
                <?php endif; ?>
            </div>
        </div>

    <?php else: ?>
        
        <div class="status-card">
            <h2 style="margin-top:0; color:#28a745;">✓ Ombi Lako la Chumba Limefanikiwa</h2>
            <hr style="border:0; border-top:1px solid #eee; margin:15px 0;">
            <p>Umeomba Jengo la: <strong><?php echo htmlspecialchars($my_status['hostel_name']); ?></strong></p>
            <p>Namba ya Chumba Chako: <strong style="font-size:18px; color:#0056b3;"><?php echo htmlspecialchars($my_status['room_number']); ?></strong></p>
            <p>Hali ya Ombi: <span style="background:#ffc107; padding:3px 8px; border-radius:4px; font-weight:bold; font-size:12px;"><?php echo htmlspecialchars($my_status['alloc_status']); ?></span></p>
            
            <div style="background:#f8f9fa; padding:15px; border-radius:6px; margin-top:20px; border-left:4px solid #28a745;">
                <h4 style="margin-top:0; color:#333;">Namba ya Malipo ya Serikali (GePG Control Number)</h4>
                <p style="font-family:monospace; font-size:20px; color:blue; font-weight:bold; margin:5px 0;"><?php echo htmlspecialchars($my_status['control_number']); ?></p>
                <p style="margin:5px 0;">Kiasi cha Kulipia: <strong>Tsh <?php echo number_format($my_status['amount']); ?>/=</strong></p>
                <p style="margin:5px 0;">Hali ya Malipo: 
                    <strong style="color: <?php echo ($my_status['pay_status'] == 'Paid') ? 'green' : 'red'; ?>;">
                        <?php echo ($my_status['pay_status'] == 'Paid') ? 'Ushamlipa (Paid)' : 'Inasubiri Malipo (Pending)'; ?>
                    </strong>
                </p>

                <?php if ($my_status['pay_status'] == 'Paid'): ?>
                    <a href="receipt.php" target="_blank" class="btn-receipt">
                        🖨️ Fungua & Print Risiti ya Malipo (PDF)
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

</body>
</html>