<?php
require_once __DIR__ . '/../config/Database.php';

class User {
    private $conn;
    private $table = "users";
    public $id;
    public $name;
    public $email;
    public $password;
    public $role;
    public $verification_code;
    public $is_verified;
    public $created_at;


    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " (name, email, password, role, verification_code, is_verified, created_at) VALUES (:name, :email, :password, :role, :verification_code, :is_verified, NOW()";
        $stmt = $this->conn->prepare(query);
        $stmt->bindParam(":name", $this-name);
        $stmt->bindParam(":email", $this-email);
        $stmt->bindParam(":password", password_hash($this-password, PASSWORD_BCRYPT));
        $stmt->bindParam(":role", $this-role);
        $stmt->bindParam(":verification_code", $this-verification_code);
        $stmt->bindParam(":is_verified", $this-is_verified);
        return $stmt->execute();

    }
    public function findByEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function verifyUser($email, $code) {
        $query = "UPDATE " . $this->table . " SET is_verified = 1 WHERE email = :email AND verification_code = :code";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":code", $code);
        return $stmt->execute();
    }

}