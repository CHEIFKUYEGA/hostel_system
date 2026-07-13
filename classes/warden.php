<?php
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Encryption.php';

class Warden extends User {

    // Kupata maombi yote yanayosubiri (Pending Applications) na ku-decrypt row data za wanafunzi
    public function getPendingApplications() {
        $query = "SELECT a.allocation_id, a.status, a.applied_at, r.room_number, h.hostel_name, sp.full_name, sp.reg_number
                  FROM allocations a
                  JOIN rooms r ON a.room_id = r.room_id
                  JOIN hostels h ON r.hostel_id = h.hostel_id
                  JOIN student_profiles sp ON a.user_id = sp.user_id
                  WHERE a.status = 'Pending'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $decrypted_apps = [];
        foreach ($results as $row) {
            $decrypted_apps[] = [
                'allocation_id' => $row['allocation_id'],
                'applied_at'    => $row['applied_at'],
                'room_number'   => $row['room_number'],
                'hostel_name'   => $row['hostel_name'],
                'status'        => $row['status'],
                'full_name'     => Encryption::decrypt($row['full_name']),
                'reg_number'    => Encryption::decrypt($row['reg_number'])
            ];
        }
        return $decrypted_apps;
    }

    // Kazi ya kupitisha au kukataa ombi la chumba (CRUD - Update Room Beds)
    public function updateApplicationStatus($allocation_id, $status) {
        try {
            $this->db->beginTransaction();

            // 1. Sasisha status ya allocation
            $query = "UPDATE allocations SET status = :status WHERE allocation_id = :allocation_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':allocation_id', $allocation_id);
            $stmt->execute();

            // 2. Kama imekubaliwa (Approved), punguza kitanda kimoja kwenye chumba husika
            if ($status == 'Approved') {
                $getRoom = "SELECT room_id FROM allocations WHERE allocation_id = :allocation_id";
                $stmtRoom = $this->db->prepare($getRoom);
                $stmtRoom->bindParam(':allocation_id', $allocation_id);
                $stmtRoom->execute();
                $room = $stmtRoom->fetch();
                $room_id = $room['room_id'];

                $updateBed = "UPDATE rooms SET available_beds = available_beds - 1 WHERE room_id = :room_id AND available_beds > 0";
                $stmtBed = $this->db->prepare($updateBed);
                $stmtBed->bindParam(':room_id', $room_id);
                $stmtBed->execute();
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    // Takwimu za haraka za Dashboard (System Dashboard Analytics)
    public function getSystemStats() {
        $stats = [];
        
        $stmt1 = $this->db->query("SELECT COUNT(*) as total FROM student_profiles");
        $stats['total_students'] = $stmt1->fetch()['total'];

        $stmt2 = $this->db->query("SELECT COUNT(*) as total FROM allocations WHERE status='Pending'");
        $stats['pending_allocations'] = $stmt2->fetch()['total'];

        $stmt3 = $this->db->query("SELECT SUM(available_beds) as total FROM rooms");
        $stats['free_beds'] = $stmt3->fetch()['total'];

        return $stats;
    }
}
?>