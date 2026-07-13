<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Huruhusiwi kuona ukurasa huu bila kuingia.");
}

try {
    $db = new PDO("mysql:host=localhost;dbname=cbe_hostel_db;charset=utf8", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $user_id = $_SESSION['user_id'];

    $stmt = $db->prepare("SELECT sp.*, r.room_number, h.hostel_name, p.control_number, p.amount, p.status AS pay_status
                           FROM student_profiles sp
                           JOIN allocations a ON sp.user_id = a.user_id
                           JOIN rooms r ON a.room_id = r.room_id
                           JOIN hostels h ON r.hostel_id = h.hostel_id
                           JOIN payments p ON sp.user_id = p.user_id
                           WHERE sp.user_id = :uid AND p.status = 'Paid'");
    $stmt->execute([':uid' => $user_id]);
    $receipt = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$receipt) {
        die("Hujafanya malipo bado au ombi lako halijapitishwa na Admin.");
    }
    $student_img = !empty($receipt['profile_image']) ? $receipt['profile_image'] : 'https://www.w3schools.com/howto/img_avatar.png';
} catch (PDOException $e) {
    die("Hitilafu ya mfumo.");
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <title>Risiti ya Malipo ya Hosteli - CBE</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; background: #fff; padding: 30px; color: #000; }
        .invoice-box { max-width: 650px; margin: auto; border: 2px dashed #000; padding: 20px; position: relative; }
        .text-center { text-align: center; }
        .header-title { font-size: 18px; font-weight: bold; margin: 5px 0; }
        hr { border: 0; border-top: 1px dashed #000; margin: 15px 0; }
        
        /* Box la picha ya pasipoti kwenye risiti */
        .receipt-profile-sec { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
        .receipt-img { width: 90px; height: 95px; object-fit: cover; border: 1px solid #000; padding: 2px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        td { padding: 8px 0; font-size: 14px; }
        .total-row { font-size: 16px; font-weight: bold; }
        .btn-print { background: #000; color: #fff; padding: 8px 15px; border: none; font-weight: bold; cursor: pointer; margin-top: 20px; width: 100%; }
        @media print { .btn-print { display: none; } body { padding: 0; } .invoice-box { border: 2px solid #000; } }
    </style>
</head>
<body>

<div class="invoice-box">
    <div class="text-center">
        <div class="header-title">COLLEGE OF BUSINESS EDUCATION (CBE)</div>
        <div>MFUMO WA ACCOMMODATION & HOSTELI</div>
        <div style="font-size:12px;">RISITI RASMI YA MALIPO YA SERIKALI</div>
    </div>
    
    <hr>
    
    <div class="receipt-profile-sec">
        <div>
            <p style="margin: 3px 0;"><strong>Tarehe:</strong> <?php echo date("Y-m-d H:i:s"); ?></p>
            <p style="margin: 3px 0;"><strong>Namba ya Risiti:</strong> CBE-REC-<?php echo rand(100000, 999999); ?></p>
        </div>
        <img src="<?php echo htmlspecialchars($student_img); ?>" class="receipt-img">
    </div>
    
    <hr>
    
    <table>
        <tr><td>Majina ya Mwanafunzi:</td><td><strong><?php echo htmlspecialchars($receipt['full_name']); ?></strong></td></tr>
        <tr><td>Namba ya Usajili (Reg No):</td><td><?php echo htmlspecialchars($receipt['reg_number']); ?></td></tr>
        <tr><td>Jinsia:</td><td><?php echo htmlspecialchars($receipt['gender']); ?></td></tr>
        <tr><td>Jengo la Hosteli:</td><td><strong><?php echo htmlspecialchars($receipt['hostel_name']); ?></strong></td></tr>
        <tr><td>Namba ya Chumba:</td><td><strong><?php echo htmlspecialchars($receipt['room_number']); ?></strong></td></tr>
        <tr><td>Control Number (GePG):</td><td style="font-weight:bold;"><?php echo htmlspecialchars($receipt['control_number']); ?></td></tr>
        <tr class="total-row"><td>Kiasi Kilicholipwa:</td><td>Tsh <?php echo number_format($receipt['amount']); ?>/=</td></tr>
        <tr><td>Hali ya Malipo:</td><td style="color:green; font-weight:bold;">IMELIPWA (PAID)</td></tr>
    </table>
    
    <hr>
    
    <div class="text-center" style="font-size:12px; margin-top:15px;">
        *** Risiti hii imetengenezwa kiotomatiki na mfumo. Isainiwe na Warden siku ya kuripoti. ***
    </div>

    <button class="btn-print" onclick="window.print()">Piga Chapa / Download Risiti (PDF)</button>
</div>

</body>
</html>