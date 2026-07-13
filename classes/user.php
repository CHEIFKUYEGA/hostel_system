<?php
// Kujumuisha file la Database kwa mawasiliano
require_once __DIR__ . '/../config/Database.php';

class User {
    // Encapsulation: Kutumia protected ili sifa hizi zirithiwe na child classes tu
    protected $db;
    protected $user_id;
    protected $username;
    protected $password;
    protected $role_id;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // Method ya ku-login watumiaji wote (Polymorphism/Inheritance ya baadae)
    public function login($username, $password) {
        $query = "SELECT * FROM users WHERE username = :username LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            // Uhakiki wa password iliyokuwa hashed
            if (password_verify($password, $row['password'])) {
                $this->user_id = $row['user_id'];
                $this->username = $row['username'];
                $this->role_id = $row['role_id'];
                
                // Kuanzisha session 
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['user_id'] = $this->user_id;
                $_SESSION['username'] = $this->username;
                $_SESSION['role_id'] = $this->role_id;
                return true;
            }
        }
        return false;
    }
}
?>