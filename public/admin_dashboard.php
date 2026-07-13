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
    die("Database connection failed.");
}

// 1. ACTION: APPROVE APPLICATION AND CONFIRM PAYMENT
if (isset($_GET['action']) && isset($_GET['id'])) {
    $alloc_id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'approve') {
        $db->beginTransaction();
        try {
            // Badili status ya Allocation na ya Payment kuwa Paid kwa pamoja
            $stmt1 = $db->prepare("UPDATE allocations SET status = 'Umepewa' WHERE allocation_id = :aid");
            $stmt1->execute([':aid' => $alloc_id]);

            // Vuta user_id wa huyu mwanafunzi
            $stmt_uid = $db->prepare("SELECT user_id, room_id FROM allocations WHERE allocation_id = :aid");
            $stmt_uid->execute([':aid' => $alloc_id]);
            $alloc_data = $stmt_uid->fetch(PDO::FETCH_ASSOC);

            if ($alloc_data) {
                // Update malipo yake yawe Paid
                $stmt2 = $db->prepare("UPDATE payments SET status = 'Paid' WHERE user_id = :uid");
                $stmt2->execute([':uid' => $alloc_data['user_id']]);

                // Punguza kitanda kimoja kwenye chumba husika
                $stmt3 = $db->prepare("UPDATE rooms SET available_beds = available_beds - 1 WHERE room_id = :rid AND available_beds > 0");
                $stmt3->execute([':rid' => $alloc_data['room_id']]);
            }

            $db->commit();
            $message = "<div class='alert success'>Ombi limeidhinishwa na malipo yamethibitishwa!</div>";
        } catch (Exception $e) {
            $db->rollBack();
            $message = "<div class='alert danger'>Hitilafu imetokea wakati wa kuidhinisha!</div>";
        }
    } elseif ($action === 'reject') {
        // Futa ombi kama admin akikataa au mwanafunzi akifukuzwa
        $stmt = $db->prepare("DELETE FROM allocations WHERE allocation_id = :aid");
        $stmt->execute([':aid' => $alloc_id]);
        $message = "<div class='alert danger'>Ombi limekataliwa na kufutwa kwenye mfumo!</div>";
    }
}

// 2. SHUGHULIKIA KUONGEZA CHUMBA KIPYA
if (isset($_POST['add_room'])) {
    $hostel_id = $_POST['hostel_id'];
    $room_number = trim($_POST['room_number']);
    $capacity = intval($_POST['capacity']);

    if (!empty($room_number) && $capacity > 0) {
        $stmt = $db->prepare("INSERT INTO rooms (hostel_id, room_number, capacity, available_beds) VALUES (:hid, :rnum, :cap, :avb)");
        $stmt->execute([':hid' => $hostel_id, ':rnum' => $room_number, ':cap' => $capacity, ':avb' => $capacity]);
        $message = "<div class='alert success'>Chumba kimeongezwa!</div>";
    }
}

// VUTA TAKWIMU
$total_students = $db->query("SELECT COUNT(*) FROM student_profiles")->fetchColumn();
$total_hostels = $db->query("SELECT COUNT(*) FROM hostels")->fetchColumn();
$total_rooms = $db->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$total_payments = $db->query("SELECT SUM(amount) FROM payments WHERE status = 'Paid'")->fetchColumn();

$hostels_list = $db->query("SELECT * FROM hostels")->fetchAll(PDO::FETCH_ASSOC);

// MFUMO WA SEARCH
$search_query = "";
$params = [];
$query = "SELECT a.allocation_id, sp.full_name, sp.reg_number, sp.gender, h.hostel_name, r.room_number, p.control_number, a.status AS alloc_status, p.status AS payment_status
          FROM allocations a
          JOIN student_profiles sp ON a.user_id = sp.user_id
          JOIN rooms r ON a.room_id = r.room_id
          JOIN hostels h ON r.hostel_id = h.hostel_id
          LEFT JOIN payments p ON a.user_id = p.user_id";

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_query = trim($_GET['search']);
    $query .= " WHERE sp.full_name LIKE :search OR sp.reg_number LIKE :search";
    $params[':search'] = "%" . $search_query . "%";
}

$stmt = $db->prepare($query);
$stmt->execute($params);
$allocations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - CBE Hostel</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 0; }
        .navbar { background-color: #212529; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; background: #dc3545; padding: 6px 12px; border-radius: 4px; font-weight: bold; font-size: 14px; }
        .container { padding: 30px; max-width: 1350px; margin: 0 auto; }
        .menu-card { background: #fff; padding: 15px; margin-bottom: 20px; border-radius: 6px; display: flex; gap: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .menu-card a { text-decoration: none; background: #0056b3; color: white; padding: 10px 20px; border-radius: 4px; font-weight: bold; font-size: 14px; }
        .menu-card a.active { background: #212529; }
        .search-container { background: white; padding: 15px; border-radius: 6px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; gap: 10px; }
        .search-container input { flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        .search-container button { padding: 10px 20px; background: #0056b3; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-left: 5px solid #0056b3; }
        .stat-card p { margin: 10px 0 0 0; font-size: 24px; font-weight: bold; }
        .main-content { display: flex; gap: 20px; align-items: flex-start; }
        .left-side { flex: 0.8; }
        .right-side { flex: 2.2; }
        .card { background: white; padding: 20px; border-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-top: 4px solid #212529; }
        label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: bold; color: #555; }
        input, select { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; font-size: 13px; }
        th { background-color: #f8f9fa; }
        .badge { padding: 4px 8px; border-radius: 4px; color: white; font-size: 11px; font-weight: bold; }
        .paid { background-color: #28a745; }
        .pending { background-color: #ffc107; color: black; }
        .btn-action { text-decoration: none; padding: 5px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; margin-right: 5px; color: white; }
        .btn-approve { background-color: #28a745; }
        .btn-reject { background-color: #dc3545; }
        .alert { padding: 12px; margin-bottom: 20px; border-radius: 4px; font-weight: bold; text-align: center; }
        .alert.success { background-color: #d4edda; color: #155724; }
        .alert.danger { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<div class="navbar">
    <h2>CBE HOSTEL MANAGEMENT - PANELI YA ADMIN</h2>
    <a href="logout.php">LOGOUT</a>
</div>

<div class="container">
    <div class="menu-card">
        <a href="admin_dashboard.php" class="active">Dashi Bodi Kuu & Vyumba</a>
        <a href="admin_hostels.php">Simamia Hosteli (Ongeza/Kutoa Hosteli)</a>
    </div>

    <form action="admin_dashboard.php" method="GET" class="search-container">
        <input type="text" name="search" placeholder="Tafuta mwanafunzi kwa Jina au Namba ya Usajili (Reg No)..." value="<?php echo htmlspecialchars($search_query); ?>">
        <button type="submit">TAFUTA</button>
    </form>

    <?php echo $message; ?>

    <div class="stats-grid">
        <div class="stat-card"><h3>Wanafunzi</h3><p><?php echo $total_students; ?></p></div>
        <div class="stat-card" style="border-left-color: #28a745;"><h3>Majengo</h3><p><?php echo $total_hostels; ?></p></div>
        <div class="stat-card" style="border-left-color: #ffc107;"><h3>Vyumba</h3><p><?php echo $total_rooms; ?></p></div>
        <div class="stat-card" style="border-left-color: #20c997;"><h3>Mapato</h3><p>Tsh <?php echo number_format($total_payments ?: 0); ?>/=</p></div>
    </div>

    <div class="main-content">
        <div class="left-side">
            <div class="card">
                <h2>Ongeza Chumba Kipya</h2>
                <form action="admin_dashboard.php" method="POST">
                    <label>Chagua Hosteli</label>
                    <select name="hostel_id" required>
                        <?php foreach ($hostels_list as $hostel): ?>
                            <option value="<?php echo $hostel['hostel_id']; ?>"><?php echo htmlspecialchars($hostel['hostel_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>Namba ya Chumba</label>
                    <input type="text" name="room_number" required>
                    <label>Idadi ya Vitanda</label>
                    <select name="capacity"><option value="4">4</option><option value="6">6</option></select>
                    <button type="submit" name="add_room" style="background:#28a745; color:white; border:none; padding:10px; width:100%; border-radius:4px; font-weight:bold; cursor:pointer;">Hifadhi</button>
                </form>
            </div>
        </div>

        <div class="right-side">
            <div class="card">
                <h2>Maombi ya Vyumba na Maamuzi ya Admin</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Mwanafunzi</th>
                            <th>Reg Number</th>
                            <th>Jengo & Chumba</th>
                            <th>Control No</th>
                            <th>Malipo</th>
                            <th>Maamuzi (Action)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allocations as $row): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['full_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['reg_number']); ?></td>
                            <td><?php echo htmlspecialchars($row['hostel_name']); ?> - <strong><?php echo $row['room_number']; ?></strong></td>
                            <td style="color:blue; font-weight:bold; font-family:monospace;"><?php echo $row['control_number']; ?></td>
                            <td><span class="badge <?php echo ($row['payment_status'] == 'Paid') ? 'paid' : 'pending'; ?>"><?php echo $row['payment_status'] ?: 'Pending'; ?></span></td>
                            <td>
                                <?php if ($row['alloc_status'] == 'Pending'): ?>
                                    <a href="admin_dashboard.php?action=approve&id=<?php echo $row['allocation_id']; ?>" class="btn-action btn-approve" onclick="return confirm('Thibitisha malipo na ulipe chumba?');">Approve</a>
                                    <a href="admin_dashboard.php?action=reject&id=<?php echo $row['allocation_id']; ?>" class="btn-action btn-reject" onclick="return confirm('Kataa ombi hili?');">Reject</a>
                                <?php else: ?>
                                    <span style="color: green; font-weight: bold; font-size:12px;">✓ Umepewa tayari</span>
                                <?php endif; ?>
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