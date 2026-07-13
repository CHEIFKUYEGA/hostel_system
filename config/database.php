<?php
class Database {
    private $host = "localhost";
    private $db_name = "cbe_hostel_db";
    private $username = "root"; // Badilisha kulingana na server yako
    private $password = "";     // Badilisha kulingana na server yako
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                $this->username, 
                $this->password
            );
            // Kuweka PDO error mode iwe Exception
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Kuweka default fetch mode iwe Associative Array
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            echo "Database Connection Error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>