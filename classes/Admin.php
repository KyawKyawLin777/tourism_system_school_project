<?php
class Admin {
    private $conn;
    private $table_name = "admins";

    public $id;
    public $username;
    public $email;
    public $password;
    public $full_name;
    public $role;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET username=:username, email=:email, password=:password, 
                    full_name=:full_name, role=:role";

        $stmt = $this->conn->prepare($query);

        $this->password = password_hash($this->password, PASSWORD_DEFAULT);

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":role", $this->role);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->full_name = $row['full_name'];
            $this->role = $row['role'];
            $this->status = $row['status'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET username=:username, email=:email, full_name=:full_name, 
                    role=:role, status=:status WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function login($username, $password) {
        $query = "SELECT id, username, password, full_name, role, status 
                  FROM " . $this->table_name . " 
                  WHERE username = ? AND status = 'Active' LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $username);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row && password_verify($password, $row['password'])) {
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->full_name = $row['full_name'];
            $this->role = $row['role'];
            return true;
        }
        return false;
    }
}
?>
