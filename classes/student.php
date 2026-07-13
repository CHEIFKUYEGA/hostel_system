<?php
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Encryption.php';

class Student extends User {
    
    public function register($username, $password, $fullName, $regNumber, $phoneNumber, $gender) {
        try {
            $this->db->beginTransaction();

            $query1 = "INSERT INTO users (username, password, role_id) VALUES (:username, :password, :role_id)";
            $stmt1 = $this->db->prepare($query1);
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $role_id = 2; 

            $stmt1->bindParam(':username', $username);
            $stmt1->bindParam(':password', $hashed_password);
            $stmt1->bindParam(':role_id', $role_id);
            $stmt1->execute();

            $last_user_id = $this->db->lastInsertId();

            $encrypted_name = Encryption::encrypt($fullName);
            $encrypted_reg = Encryption::encrypt($regNumber);
            $encrypted_phone = Encryption::encrypt($phoneNumber);
            $encrypted_gender = Encryption::encrypt($gender);

            $query2 = "INSERT INTO student_profiles (user_id, full_name, reg_number, phone_number, gender) 
                       VALUES (:user_id, :full_name, :reg_number, :phone_number, :gender)";
            $stmt2 = $this->db->prepare($query2);
            $stmt2->bindParam(':user_id', $last_user_id);
            $stmt2->bindParam(':full_name', $encrypted_name);
            $stmt2->bindParam(':reg_number', $encrypted_reg);
            $stmt2->bindParam(':phone_number', $encrypted_phone);
            $stmt2->bindParam(':gender', $encrypted_gender);
            $stmt2->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getProfile($user_id) {
        $query = "SELECT * FROM student_profiles WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $profile = $stmt->fetch();

        if ($profile) {
            return [
                'full_name'    => Encryption::decrypt($profile['full_name']),
                'reg_number'   => Encryption::decrypt($profile['reg_number']),
                'phone_number' => Encryption::decrypt($profile['phone_number']),
                'gender'       => Encryption::decrypt($profile['gender'])
            ];
        }
        return null;
    }

    // TAALUMA MPYA: Kupata vyumba vilivyo wazi kulingana na jinsia ya mwanafunzi
    public function getAvailableRooms($gender) {
        $query = "SELECT r.*, h.hostel_name FROM rooms r 
                  JOIN hostels h ON r.hostel_id = h.hostel_id 
                  WHERE h.gender_allowed = :gender AND r.available_beds > 0";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':gender', $gender);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // TAALUMA MPYA: Mwanafunzi kuomba chumba (Room Application)
    public function applyForRoom($user_id, $room_id) {
        // Angalia kama ana maombi ambayo tayari yapo hai au yanasubiri
        $check = "SELECT * FROM allocations WHERE user_id = :user_id AND (status='Pending' OR status='Approved')";
        $stmtCheck = $this->db->prepare($check);
        $stmtCheck->bindParam(':user_id', $user_id);
        $stmtCheck->execute();
        
        if($stmtCheck->rowCount() > 0) {
            return "Tayari una maombi yanayoshughulikiwa au umeshapata chumba!";
        }

        $query = "INSERT INTO allocations (user_id, room_id, status) VALUES (:user_id, :room_id, 'Pending')";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':room_id', $room_id);
        
        if($stmt->execute()) {
            return true;
        }
        return "Hitilafu imetokea wakati wa kutuma maombi.";
    }

    // TAALUMA MPYA: Kuona status ya chumba alichoomba mwanafunzi
    public function myAllocationStatus($user_id) {
        $query = "SELECT a.status, a.applied_at, r.room_number, h.hostel_name 
                  FROM allocations a
                  JOIN rooms r ON a.room_id = r.room_id
                  JOIN hostels h ON r.hostel_id = h.hostel_id
                  WHERE a.user_id = :user_id ORDER BY a.allocation_id DESC LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetch();
    }
}
?>